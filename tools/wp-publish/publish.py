#!/usr/bin/env python3
"""
publish.py — push Markdown content files to a WordPress site over the REST API.

Reads the "Alugence Website Content" .md format (page-type/meta header, H1 +
H2 body, FAQ section, and trailing Internal links / Schema / Compliance notes)
and creates or updates the matching page/post/guide/glossary term AS A DRAFT.

Nothing is ever published automatically — you review drafts in wp-admin and
click Publish yourself. Requires only the Python 3 standard library.

Usage:
    python3 publish.py FILE.md [FILE2.md ...]     # publish one or more files
    python3 publish.py content/**/*.md            # a whole batch (shell glob)
    python3 publish.py --dry-run FILE.md          # parse & show plan, write nothing
    python3 publish.py --slug my-page FILE.md      # force the target slug
    python3 publish.py --type post FILE.md         # force the post type
    python3 publish.py --allow-unpublish FILE.md   # permit editing a LIVE page
                                                    #   (it drops to draft while
                                                    #    you review — off by default)

Config lives in config.ini next to this script (see config.example.ini).
"""

import argparse
import base64
import configparser
import html as _html
import json
import os
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

HERE = os.path.dirname(os.path.abspath(__file__))
CONFIG_PATH = os.path.join(HERE, "config.ini")
NOTES_DIR = os.path.join(HERE, "notes")


# --------------------------------------------------------------------------
# Page type / URL  ->  WordPress post type + page template
# --------------------------------------------------------------------------
# The URL path in each file's first line ("# Title — `/path/`") is the most
# reliable router; page-type keywords are a fallback.
TYPE_BY_URL_PREFIX = [
    ("/blog/", "post"),
    ("/guides/", "tf_guide"),
    ("/glossary/", "tf_glossary"),
    ("/case-studies/", "tf_case_study"),
]

# Page-type keyword -> post type (fallback when the URL is ambiguous).
TYPE_BY_KEYWORD = [
    (r"glossary", "tf_glossary"),
    (r"pillar guide|commercial guide|checklist|^guide", "tf_guide"),
    (r"comparison|listicle|article", "post"),
    (r"proof", "tf_case_study"),
]

# Post type -> REST base.
REST_BASE = {
    "page": "pages",
    "post": "posts",
    "tf_guide": "tf_guide",
    "tf_glossary": "tf_glossary",
    "tf_case_study": "tf_case_study",
}

# Page template to assign on CREATE for a "page" post type, chosen from the
# page-type keyword. (Existing pages keep whatever template they already have.)
TEMPLATE_BY_KEYWORD = [
    (r"home|brand", ""),  # front page — no template file
    (r"pricing", "page-templates/pricing.php"),
    (r"author profile|person", "page-templates/person.php"),
    (r"trust / people|^about|team", "page-templates/about.php"),
    (r"roi|calculator", "page-templates/roi-calculator.php"),
    # services, sectors, local, segment pillars all use the spec-driven page
    (r"service|sector|local|segment|pillar", "page-templates/spec-page.php"),
]

# Post types whose VISIBLE body renders from post_content (script fully
# effective). Others render from the theme's PHP data layer/components.
CONTENT_DRIVEN = {"post", "tf_guide", "tf_glossary", "tf_case_study"}


# --------------------------------------------------------------------------
# Tiny, self-contained Markdown -> HTML  (paragraphs, headings, lists,
# tables, blockquotes, links, bold/italic/code). No third-party deps.
# --------------------------------------------------------------------------
def md_inline(t):
    t = t.strip()
    t = _html.escape(t, quote=False)
    t = re.sub(r"\[([^\]]+)\]\(([^)]+)\)",
               lambda m: '<a href="%s">%s</a>' % (m.group(2).strip(), m.group(1).strip()), t)
    t = re.sub(r"\*\*([^*]+)\*\*", r"<strong>\1</strong>", t)
    t = re.sub(r"(?<!\*)\*(?!\*)([^*]+)\*(?!\*)", r"<em>\1</em>", t)
    t = re.sub(r"`([^`]+)`", r"<code>\1</code>", t)
    return t


def md_table(rows):
    cells = [[c.strip() for c in r.strip("|").split("|")] for r in rows]
    cells = [c for c in cells if not all(re.match(r"^:?-+:?$", x or "-") for x in c)]
    if not cells:
        return ""
    head, body = cells[0], cells[1:]
    th = "".join("<th>%s</th>" % md_inline(c) for c in head)
    trs = "".join("<tr>%s</tr>" % "".join("<td>%s</td>" % md_inline(c) for c in r) for r in body)
    return ('<div class="table-wrap"><table><thead><tr>%s</tr></thead>'
            "<tbody>%s</tbody></table></div>" % (th, trs))


def md_body(lines, anchors=None):
    """lines -> HTML. `anchors` maps a heading's text to an id (from <a id>)."""
    out, i, n = [], 0, len(lines)
    while i < n:
        s = lines[i].strip()
        if not s:
            i += 1
            continue
        # `<a id="x"></a>` jump target -> remembered for the next heading
        anc = re.match(r'^<a id="([\w-]+)"></a>$', s)
        if anc and anchors is not None:
            anchors["__pending__"] = anc.group(1)
            i += 1
            continue
        if s.startswith("|"):
            tbl = []
            while i < n and lines[i].strip().startswith("|"):
                tbl.append(lines[i].strip())
                i += 1
            out.append(md_table(tbl))
            continue
        if re.match(r"^[-*] ", s):
            items = []
            while i < n and re.match(r"^[-*] ", lines[i].strip()):
                items.append("<li>%s</li>" % md_inline(re.sub(r"^[-*] ", "", lines[i].strip())))
                i += 1
            out.append("<ul>%s</ul>" % "".join(items))
            continue
        if re.match(r"^\d+\. ", s):
            items = []
            while i < n and re.match(r"^\d+\. ", lines[i].strip()):
                items.append("<li>%s</li>" % md_inline(re.sub(r"^\d+\. ", "", lines[i].strip())))
                i += 1
            out.append("<ol>%s</ol>" % "".join(items))
            continue
        if s.startswith(">"):
            buf = []
            while i < n and lines[i].strip().startswith(">"):
                buf.append(lines[i].strip().lstrip(">").strip())
                i += 1
            out.append("<blockquote><p>%s</p></blockquote>" % md_inline(" ".join(buf)))
            continue
        if s.startswith("### "):
            out.append("<h3>%s</h3>" % md_inline(s[4:]))
            i += 1
            continue
        out.append("<p>%s</p>" % md_inline(s))
        i += 1
    return "\n".join(p for p in out if p.strip())


# --------------------------------------------------------------------------
# Parse one content .md file into a structured record
# --------------------------------------------------------------------------
def slugify(text):
    text = re.sub(r"[^\w\s-]", "", text.lower()).strip()
    return re.sub(r"[\s_]+", "-", text)


def parse_file(path):
    raw = open(path, encoding="utf-8").read()
    lines = raw.split("\n")
    rec = {
        "path_url": None, "page_type": "", "meta_title": None, "meta_desc": None,
        "h1": None, "excerpt": "", "body_html": "", "faqs": [], "notes": {},
        "source": os.path.basename(path),
    }

    # URL path from the first heading:  # Title — `/path/`
    m = re.search(r"`(/[^`]*)`", lines[0]) if lines else None
    if m:
        rec["path_url"] = m.group(1)

    # Header metadata (scan the top block).
    for ln in lines[:25]:
        mt = re.search(r"\*\*Page type:\*\*\s*([^\n·|]+)", ln)
        if mt:
            rec["page_type"] = mt.group(1).strip()
        mtl = re.search(r"\*\*Meta title[^:]*:\*\*\s*(.+)", ln)
        if mtl:
            rec["meta_title"] = mtl.group(1).strip()
        mdl = re.search(r"\*\*Meta description[^:]*:\*\*\s*(.+)", ln)
        if mdl:
            rec["meta_desc"] = mdl.group(1).strip()

    # Walk the body: H1, intro (quick answer + on-this-page), H2 sections,
    # FAQ section, and the trailing notes sections.
    intro, sections, buf = [], [], []
    cur = None          # current H2 title, or "__intro__", or None
    cur_anchor = None   # id for the current section's heading
    notes_key, notes = None, {}
    seen_h1 = False
    pending_anchor = None

    def close_current():
        """Store whatever section is open (intro or an H2 section)."""
        nonlocal intro
        if cur == "__intro__":
            intro = list(buf)
        elif cur and buf:
            sections.append((cur, list(buf), cur_anchor))

    for ln in lines:
        h1 = re.match(r"^##\s+H1:?\s*(.*)", ln)
        note = re.match(r"^###\s+(Internal links|Schema|Compliance[^\n]*)", ln, re.I)
        anc = re.match(r'^\s*<a id="([\w-]+)"></a>\s*$', ln)

        if h1:
            seen_h1 = True
            if rec["h1"] is None:
                rec["h1"] = h1.group(1).strip()
            cur, buf, cur_anchor = "__intro__", [], None
            continue
        if note:
            # everything from here down is editorial instruction, not body
            close_current()
            cur, buf = None, []
            notes_key = note.group(1).strip()
            notes[notes_key] = []
            continue
        if notes_key is not None:
            if ln.strip() and ln.strip() != "---":
                notes[notes_key].append(ln.rstrip())
            continue
        if anc:
            pending_anchor = anc.group(1)
            continue
        if seen_h1 and re.match(r"^##\s+", ln) and not h1:
            close_current()
            cur = re.sub(r"^##\s+(?:H2:?\s*)?", "", ln).strip()
            buf = []
            cur_anchor = pending_anchor
            pending_anchor = None
            continue
        if ln.strip() == "---":
            continue
        if cur is not None:
            buf.append(ln)

    close_current()

    # Split FAQ sections out of the collected sections.
    kept = []
    for title, slines, aid in sections:
        if re.search(r"frequently asked|faq", title, re.I):
            rec["faqs"] = parse_faqs(slines)
        else:
            kept.append((title, slines, aid))
    sections = kept

    # Intro: pull the "Quick answer" blockquote into the excerpt, keep the
    # "On this page" nav, drop nothing else.
    body_parts, toc = [], ""
    intro_body = []
    for ln in intro:
        s = ln.strip()
        if s.startswith(">"):
            qa = re.sub(r"^>\s*", "", s)
            qa = re.sub(r"\*\*Quick answer:?\*\*\s*", "", qa, flags=re.I)
            qa = re.sub(r"\[([^\]]+)\]\([^)]+\)", r"\1", qa)
            qa = re.sub(r"\*\*([^*]+)\*\*", r"\1", qa)
            rec["excerpt"] = (rec["excerpt"] + " " + qa).strip()
            continue
        if s.lower().startswith("**on this page") or (s.startswith("[") and "](#" in s):
            body = re.sub(r"^\*\*On this page:?\*\*\s*", "", s, flags=re.I)
            toc = '<nav class="on-this-page" aria-label="On this page">%s</nav>' % md_inline(body)
            continue
        intro_body.append(ln)

    ih = md_body(intro_body)
    if ih:
        body_parts.append(ih)
    if toc:
        body_parts.append(toc)
    for title, slines, aid in sections:
        htext = re.sub(r"\*\*([^*]+)\*\*", r"\1", title)
        if aid:
            body_parts.append('<h2 id="%s">%s</h2>' % (aid, md_inline(htext)))
        else:
            body_parts.append("<h2>%s</h2>" % md_inline(htext))
        body_parts.append(md_body(slines))
    rec["body_html"] = "\n".join(p for p in body_parts if p.strip())
    rec["notes"] = {k: "\n".join(v).strip() for k, v in notes.items() if "".join(v).strip()}
    return rec


def parse_faqs(slines):
    faqs, q, buf = [], None, []
    for ln in slines:
        m = re.match(r"^###\s+(.+)", ln)
        if m:
            if q is not None:
                faqs.append([q, " ".join(x.strip() for x in buf if x.strip())])
            q, buf = m.group(1).strip(), []
        else:
            buf.append(ln)
    if q is not None:
        faqs.append([q, " ".join(x.strip() for x in buf if x.strip())])
    clean = []
    for qq, aa in faqs:
        aa = re.sub(r"\[([^\]]+)\]\([^)]+\)", r"\1", aa)
        aa = re.sub(r"\*\*([^*]+)\*\*", r"\1", aa)
        aa = re.sub(r"`([^`]+)`", r"\1", aa)
        clean.append([qq.strip(), aa.strip()])
    return clean


# --------------------------------------------------------------------------
# Routing: decide post type, slug, template
# --------------------------------------------------------------------------
def resolve_type(rec, forced=None):
    if forced:
        return forced
    if rec["path_url"]:
        for prefix, t in TYPE_BY_URL_PREFIX:
            if rec["path_url"].startswith(prefix):
                return t
    pt = rec["page_type"].lower()
    for pat, t in TYPE_BY_KEYWORD:
        if re.search(pat, pt):
            return t
    return "page"


def resolve_slug(rec, forced=None):
    if forced:
        return forced
    if rec["path_url"]:
        seg = rec["path_url"].rstrip("/").split("/")[-1]
        if seg:
            return seg
    return slugify(rec["h1"] or rec["source"])


def resolve_template(rec):
    pt = rec["page_type"].lower()
    for pat, tpl in TEMPLATE_BY_KEYWORD:
        if re.search(pat, pt):
            return tpl
    return "page-templates/spec-page.php"


# --------------------------------------------------------------------------
# WordPress REST client (Basic auth with an Application Password)
# --------------------------------------------------------------------------
class WP:
    def __init__(self, base_url, user, app_password):
        self.api = base_url.rstrip("/") + "/wp-json/wp/v2"
        token = base64.b64encode(("%s:%s" % (user, app_password)).encode()).decode()
        self.auth = "Basic " + token

    def _req(self, method, url, payload=None):
        data = json.dumps(payload).encode() if payload is not None else None
        req = urllib.request.Request(url, data=data, method=method)
        req.add_header("Authorization", self.auth)
        req.add_header("Content-Type", "application/json")
        req.add_header("Accept", "application/json")
        try:
            with urllib.request.urlopen(req, timeout=30) as r:
                return json.loads(r.read().decode())
        except urllib.error.HTTPError as e:
            body = e.read().decode(errors="replace")
            raise SystemExit("  ! REST error %s on %s\n    %s" % (e.code, url, body[:400]))
        except urllib.error.URLError as e:
            raise SystemExit("  ! Cannot reach %s (%s)" % (url, e.reason))

    def find_by_slug(self, rest_base, slug):
        q = urllib.parse.urlencode({"slug": slug, "status": "any", "per_page": 5})
        items = self._req("GET", "%s/%s?%s" % (self.api, rest_base, q))
        return items[0] if items else None

    def find_by_title(self, rest_base, title):
        q = urllib.parse.urlencode({"search": title, "status": "any", "per_page": 10})
        items = self._req("GET", "%s/%s?%s" % (self.api, rest_base, q))
        for it in items:
            if it.get("title", {}).get("rendered", "").strip().lower() == title.strip().lower():
                return it
        return None

    def create(self, rest_base, payload):
        return self._req("POST", "%s/%s" % (self.api, rest_base), payload)

    def update(self, rest_base, post_id, payload):
        return self._req("POST", "%s/%s/%d" % (self.api, rest_base, post_id), payload)


# --------------------------------------------------------------------------
# Build the REST payload for one record
# --------------------------------------------------------------------------
def build_payload(rec, post_type, slug, template, existing):
    meta = {}
    if rec["meta_title"]:
        meta["_tf_title_tag"] = rec["meta_title"]
    if rec["meta_desc"]:
        meta["_tf_meta_desc"] = rec["meta_desc"]
    if rec["faqs"] and post_type in ("post", "tf_guide"):
        meta["_tf_faqs"] = rec["faqs"]
    if rec["notes"]:
        meta["_tf_editor_notes"] = "\n\n".join(
            "## %s\n%s" % (k, v) for k, v in rec["notes"].items()
        )

    payload = {
        "title": rec["h1"] or slug,
        "slug": slug,
        "status": "draft",
        "content": rec["body_html"],
        "meta": meta,
    }
    if rec["excerpt"]:
        payload["excerpt"] = rec["excerpt"]
    # Only set a page template when CREATING a page; never change an existing
    # page's template.
    if post_type == "page" and existing is None and template:
        payload["template"] = template
    return payload


# --------------------------------------------------------------------------
# Main
# --------------------------------------------------------------------------
def load_config():
    if not os.path.exists(CONFIG_PATH):
        raise SystemExit(
            "No config.ini found next to publish.py.\n"
            "Copy config.example.ini to config.ini and fill in your site URL,\n"
            "username and application password (see README.md)."
        )
    cp = configparser.ConfigParser()
    cp.read(CONFIG_PATH)
    s = cp["wordpress"]
    return s["site_url"], s["username"], s["app_password"]


def write_notes(slug, rec):
    if not rec["notes"]:
        return None
    os.makedirs(NOTES_DIR, exist_ok=True)
    p = os.path.join(NOTES_DIR, slug + ".md")
    with open(p, "w", encoding="utf-8") as f:
        f.write("# Editor to-do for: %s\n\n" % (rec["h1"] or slug))
        f.write("_These sections from the content file are instructions, not page "
                "copy. Action them in wp-admin before publishing._\n\n")
        for k, v in rec["notes"].items():
            f.write("## %s\n\n%s\n\n" % (k, v))
    return p


def main():
    ap = argparse.ArgumentParser(description="Publish Markdown content to WordPress as drafts.")
    ap.add_argument("files", nargs="+", help="one or more .md content files")
    ap.add_argument("--dry-run", action="store_true", help="parse and show the plan; write nothing")
    ap.add_argument("--type", help="force the post type (page|post|tf_guide|tf_glossary|tf_case_study)")
    ap.add_argument("--slug", help="force the target slug (only sensible with a single file)")
    ap.add_argument("--allow-unpublish", action="store_true",
                    help="permit updating a currently-published page (it drops to draft)")
    args = ap.parse_args()

    wp = None
    if not args.dry_run:
        site, user, pw = load_config()
        wp = WP(site, user, pw)
        print("Connected: %s\n" % site)

    for path in args.files:
        if not os.path.exists(path):
            print("SKIP (not found): %s" % path)
            continue
        rec = parse_file(path)
        post_type = resolve_type(rec, args.type)
        slug = resolve_slug(rec, args.slug if len(args.files) == 1 else None)
        template = resolve_template(rec)
        rest_base = REST_BASE.get(post_type, "pages")

        print("• %s" % rec["source"])
        print("    title      : %s" % (rec["h1"] or "(none)"))
        print("    post type  : %s   slug: %s" % (post_type, slug))
        if post_type == "page":
            print("    template   : %s" % (template or "(front page / default)"))
        print("    meta title : %s" % (rec["meta_title"] or "(none)"))
        print("    meta desc  : %s" % ((rec["meta_desc"] or "")[:70] + ("…" if rec["meta_desc"] and len(rec["meta_desc"]) > 70 else "")))
        print("    FAQs       : %d" % len(rec["faqs"]))
        if post_type not in CONTENT_DRIVEN:
            print("    NOTE       : this page type renders its visible body from the")
            print("                 theme's components/data layer — post_content is stored")
            print("                 but is NOT the on-site source. Meta/template still apply.")
        if rec["notes"]:
            print("    instructions: %s (written to notes/%s.md)" % (", ".join(rec["notes"].keys()), slug))

        if args.dry_run:
            existing = None
            print("    action     : DRY RUN — no write\n")
            write_notes(slug, rec)
            continue

        existing = wp.find_by_slug(rest_base, slug)
        if existing is None and rec["h1"]:
            existing = wp.find_by_title(rest_base, rec["h1"])

        if existing is not None and existing.get("status") == "publish" and not args.allow_unpublish:
            print("    action     : SKIP — a PUBLISHED %s already exists (id %s)." % (post_type, existing["id"]))
            print("                 Re-run with --allow-unpublish to edit it (it will")
            print("                 drop to draft for your review).\n")
            continue

        payload = build_payload(rec, post_type, slug, template, existing)
        if existing is not None:
            res = wp.update(rest_base, existing["id"], payload)
            print("    action     : UPDATED (id %s) → draft" % res["id"])
        else:
            res = wp.create(rest_base, payload)
            print("    action     : CREATED (id %s) → draft" % res["id"])
        notes_file = write_notes(slug, rec)
        if notes_file:
            print("    review     : %s" % notes_file)
        print("    edit in wp : %s\n" % res.get("link", "").replace("__trashed", ""))

    if not args.dry_run:
        print("Done. Review the drafts in wp-admin → Pages/Posts, then Publish when ready.")


if __name__ == "__main__":
    main()
