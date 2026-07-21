# WordPress content publisher

Push Markdown content files straight into your live WordPress site as **drafts**,
so you never have to copy/paste into wp-admin again. Nothing is ever published
automatically — you always review the draft and click **Publish** yourself.

It reads the "Alugence Website Content" `.md` format (page-type + meta header,
H1/H2 body, FAQ section, and the trailing *Internal links / Schema / Compliance*
notes) and creates or updates the matching page, guide, blog post or glossary
term.

Requires only **Python 3** — no installs, no extra libraries.

---

## 1. One-time setup

### a) Generate an Application Password in WordPress

An Application Password is WordPress's built-in, secure way to let an outside
tool sign in as you — without ever typing your real password into the script.
You can revoke it any time, and it can't be used to log into wp-admin in a
browser.

1. Log in to **wp-admin** on your live site.
2. Go to **Users → Profile** (top-right, or Users → All Users → your name).
3. Scroll down to **Application Passwords**.
4. In **New Application Password Name**, type something you'll recognise, e.g.
   `Content publisher`, and click **Add New Application Password**.
5. WordPress shows you a password like `abcd EFGH ijkl MNOP qrst UVWX`
   **once**. Copy it now (spaces and all) — you can't see it again.

> If you don't see the Application Passwords section, your site must be served
> over **https** (any normal live site is). It's disabled on plain-http sites
> for security.

### b) Put your details in `config.ini`

1. In this folder, copy `config.example.ini` to a new file named `config.ini`.
2. Open `config.ini` and fill in:
   - `site_url` — your site, e.g. `https://alugence.ai`
   - `username` — your wp-admin username
   - `app_password` — the password from step (a), pasted exactly (with spaces)

`config.ini` is **git-ignored**, so your password is never pushed to GitHub.

---

## 2. Running it

From this folder (`tools/wp-publish/`):

```bash
# See exactly what WOULD happen, without touching the site — always safe:
python3 publish.py --dry-run ../../path/to/your-file.md

# Publish a single file (as a draft):
python3 publish.py ../../path/to/your-file.md

# Publish a whole batch at once:
python3 publish.py "../../content/**/*.md"
```

Tips:

- Start with `--dry-run` on a new batch. It prints the plan (post type, slug,
  meta, FAQ count, create-vs-update) and writes nothing.
- You can pass any number of files. Re-run it any time with new files, edits to
  old ones, or a mix — it works out create vs update per file (see below).

Handy flags:

| Flag | What it does |
|------|--------------|
| `--dry-run` | Parse and show the plan; write nothing. |
| `--type post` | Force the post type (`page`, `post`, `tf_guide`, `tf_glossary`, `tf_case_study`) instead of auto-detecting. |
| `--slug my-page` | Force the target slug (use with a single file). |
| `--allow-unpublish` | Let it edit a page that's already **published live** (it drops to draft for review). Off by default so live pages are never taken offline by accident. |

---

## 3. How it decides "new page" vs "update to an existing page"

For each file, the script works out a **slug** (the last part of the URL in the
file's first line, e.g. `` `/guides/what-is-geo/` `` → `what-is-geo`), then asks
your site: *"is there already a page/post with this slug?"*

- **No match found → CREATE.** It makes a brand-new draft, using the right
  template for the page type.
- **Match found → UPDATE.** It replaces that page's content and SEO fields,
  keeping the template the page already has, and saves it as a draft.

If no slug match exists, it also tries to match on the **exact title** before
deciding it's new. So you get updates, not accidental duplicates, as long as the
slug or title lines up with what's already on the site.

**Safety rail:** if the matching page is already **published**, the script
*skips* it and tells you, rather than pulling a live page down to draft. Add
`--allow-unpublish` when you genuinely want to revise a live page (it then drops
to draft so you can review before re-publishing).

---

## 4. What goes where

| Content file part | Where it lands in WordPress |
|---|---|
| H1 | Page/post **title** |
| Body copy + H2 sections | Page/post **content** |
| Quick-answer blockquote | **Excerpt** (the quotable summary) |
| FAQ section | Saved as FAQ data → renders as the FAQ accordion + FAQPage schema (guides & blog) |
| Meta title / Meta description | The theme's **SEO title & description** fields |
| Internal links / Schema / Compliance notes | **Not** page copy — saved as an editor note on the post and written to `notes/<slug>.md` as a to-do for you to action |

### Important: two kinds of page

- **Guides, blog posts, glossary terms, case studies** render their visible body
  from the content you push here — the script is fully effective for these.
- **Service, sector, home, pricing** and similar "structured" pages render their
  visible body from the **theme's built-in components** (the design system's
  cards, pricing tables, step lists, etc.), *not* from flat page content. For
  these, the script still creates/updates the page and sets the SEO title,
  description and template — but it will **warn you** that the body copy shown to
  visitors comes from the theme, not from what you pushed. Those pages are best
  maintained through the theme's content layer; ask if you'd like that wired to
  read from here too.

---

## 5. After a run

- Open **wp-admin → Pages / Posts**, filter by **Draft**, and review.
- Check `notes/<slug>.md` for any internal-link / schema / compliance to-dos.
- Click **Publish** when you're happy. The script never does that for you.
