#!/usr/bin/env python3
"""
create-tool-page.py — create (or verify) the Brand AI Visibility Checker page
on the live WordPress site via the REST API.

Unlike publish.py (which pushes Markdown content), this creates a *template*
page: it has no body copy — the tool renders from the theme template
page-templates/ai-visibility-checker.php. This script sets the correct slug,
parent (/tools/) and page template, as a DRAFT you review and publish.

It is idempotent: if the page already exists it reports it and (optionally)
re-asserts the template, rather than creating a duplicate.

Prereqs:
  1. The latest theme is deployed on the live site (WP Pusher) so the
     "AI Visibility Checker" template is available.
  2. config.ini holds site_url / username / app_password (see README.md).

Usage:
  python3 create-tool-page.py            # create as draft (safe default)
  python3 create-tool-page.py --publish  # create and publish immediately
  python3 create-tool-page.py --dry-run  # show the plan, write nothing
"""

import argparse
import base64
import configparser
import json
import os
import urllib.error
import urllib.parse
import urllib.request

HERE = os.path.dirname(os.path.abspath(__file__))
CONFIG_PATH = os.path.join(HERE, "config.ini")

# The page we are creating.
PAGE = {
    "slug": "ai-visibility-checker",
    "title": "Brand AI Visibility Checker",
    "template": "page-templates/ai-visibility-checker.php",
    "parent_slug": "tools",
}


def load_config():
    if not os.path.exists(CONFIG_PATH):
        raise SystemExit(
            "No config.ini found next to this script.\n"
            "Copy config.example.ini to config.ini and fill in your site URL,\n"
            "username and Application Password (see README.md)."
        )
    cp = configparser.ConfigParser()
    cp.read(CONFIG_PATH)
    s = cp["wordpress"]
    return s["site_url"], s["username"], s["app_password"]


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
            raise SystemExit("  ! REST error %s on %s\n    %s" % (e.code, url, e.read().decode(errors="replace")[:500]))
        except urllib.error.URLError as e:
            raise SystemExit("  ! Cannot reach %s (%s)" % (url, e.reason))

    def page_by_slug(self, slug):
        q = urllib.parse.urlencode({"slug": slug, "status": "any", "per_page": 5})
        items = self._req("GET", "%s/pages?%s" % (self.api, q))
        return items[0] if items else None


def main():
    ap = argparse.ArgumentParser(description="Create the AI Visibility Checker page on live WordPress.")
    ap.add_argument("--publish", action="store_true", help="publish immediately instead of creating a draft")
    ap.add_argument("--dry-run", action="store_true", help="show the plan; write nothing")
    args = ap.parse_args()

    status = "publish" if args.publish else "draft"

    print("Page to create:")
    print("  title    : %s" % PAGE["title"])
    print("  slug     : %s" % PAGE["slug"])
    print("  template : %s" % PAGE["template"])
    print("  parent   : /%s/" % PAGE["parent_slug"])
    print("  status   : %s" % status)

    if args.dry_run:
        print("\nDRY RUN — no write. (Run without --dry-run to create it.)")
        return

    site, user, pw = load_config()
    wp = WP(site, user, pw)
    print("\nConnected: %s" % site)

    # Resolve parent (/tools/).
    parent = wp.page_by_slug(PAGE["parent_slug"])
    parent_id = parent["id"] if parent else 0
    if not parent_id:
        print("  ! Note: no '/%s/' page found — creating at the top level instead." % PAGE["parent_slug"])
        print("    Run Theme Setup, or create the Tools page first, for the /tools/... URL.")

    # Already there?
    existing = wp.page_by_slug(PAGE["slug"])
    if existing:
        tmpl = existing.get("template", "")
        print("  Page already exists (id %s, status %s, template '%s')." % (existing["id"], existing["status"], tmpl))
        if tmpl != PAGE["template"]:
            wp._req("POST", "%s/pages/%d" % (wp.api, existing["id"]), {"template": PAGE["template"]})
            print("  → template corrected to %s" % PAGE["template"])
        print("  Edit: %s" % existing.get("link", ""))
        return

    payload = {
        "title": PAGE["title"],
        "slug": PAGE["slug"],
        "status": status,
        "parent": parent_id,
        "template": PAGE["template"],
    }
    res = wp._req("POST", "%s/pages" % wp.api, payload)
    got_tmpl = res.get("template", "")
    print("  CREATED page id %s (status %s)." % (res["id"], res["status"]))
    if got_tmpl != PAGE["template"]:
        print("  ! WARNING: the site stored template '%s', not '%s'." % (got_tmpl, PAGE["template"]))
        print("    The theme with that template may not be deployed yet (WP Pusher).")
        print("    Deploy the latest theme, then re-run — or set the template in the page editor.")
    else:
        print("  template : %s ✓" % got_tmpl)
    print("  Edit in wp-admin: %s" % res.get("link", ""))
    if status == "draft":
        print("\nReview it in wp-admin → Pages, then Publish when you're happy.")


if __name__ == "__main__":
    main()
