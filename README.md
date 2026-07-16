# Alugence — WordPress Theme

Custom marketing theme for [alugence.ai](https://alugence.ai) — an AI consultancy with two practices: **business AI consultancy & transformation** (primary) and **AI search consultancy** (specialised service line).

Hand-built classic theme. No page builder, no build step, no plugin dependencies.

## Deploying with WP Pusher

1. In wp-admin, install and activate [WP Pusher](https://wppusher.com/).
2. Go to **WP Pusher → Install Theme**, connect GitHub, and enter this repository (`grahampinkney433-dotcom/think-further` — the repo keeps its original name; the theme inside it is Alugence), branch `main` (or your deploy branch).
3. Install, then activate **Alugence** under **Appearance → Themes**.

The repository root **is** the theme root (`style.css` lives at the top level), so no subdirectory configuration is needed.

## Post-install setup (1 minute)

1. Go to **Appearance → Theme Setup** and click **Run setup**. This creates the core pages with the right templates (Home, AI Consultancy, AI Search, Approach, Contact, Insights), assigns the navigation menu, sets the static homepage and posts page, and enables pretty permalinks. It's idempotent and never overwrites existing content.
2. **Business details & hero copy** — Customise → **Alugence Options**: contact email, booking/calendar link, LinkedIn, hero headline, footer tagline. In headline fields, wrap words in `*asterisks*` for the colour accent (e.g. `The ones that win *look further.*`).

## Case studies

Add engagements under **Case Studies** in wp-admin (client, sector and headline result live in the *Case Study Details* box). They appear at `/work/`, and the homepage automatically shows a "Selected work" section once at least one is published.

## Business AI Capabilities Audit (`/ai-audit/`)

An interactive readiness questionnaire (15 questions, 6 sections) that produces an AI Readiness Score out of 100 with per-section sub-scores, a band label (Emerging → Leading), and rules-based recommendations. Fully front-end — no backend involved in questions, scoring or results.

**Editing content:** everything lives in the `CONFIG` block at the top of `assets/js/audit.js` — questions and answer options (`SECTIONS`), section weights, score `BANDS`, and `RECOMMENDATIONS` (per-section, tiered `low`/`mid`). The comments in that file explain each knob.

**Connecting your email/CRM tool:** the optional "Email me my results" form calls a single function, `submitLead(lead)`, in `assets/js/audit.js`. It currently logs the payload and shows a success state. The doc block above it documents the exact payload shape and gives a drop-in `fetch()` example for a CRM webhook (HubSpot, Mailchimp, Zapier/Make, or a WP REST route calling `wp_mail()`). Replace that one function body and you're live.

Progress is auto-saved to `sessionStorage`, so visitors can go back or reload without losing answers. The page is created automatically by Theme Setup.

## SEO & GEO architecture (workbook build)

The site implements the "Alugence — SEO & GEO Requirements" workbook as a structural scaffold. The single source of truth is **`inc/seo-spec.php`**: every URL, H1, title tag (≤60), meta description (≤155), nav placement, schema type, sector use-case list and pricing tier lives there — edit that file, not templates. Body copy is clearly-labelled placeholder blocks (dashed indigo boxes) awaiting the content stage; copy pasted into a page's WP editor renders in each template's editor-content slot.

- **Architecture (Tab 2)** — two-arm silos (`/ai-consultancy/` 18 children, `/ai-search/` 6), `/industries/` ×10, `/case-studies/`, `/guides/` + `/blog/` + `/glossary/` + `/tools/` under Resources, trust pages (About, Team, bios at `/about/team/{person}/`, Careers, Pricing, Contact), legal. **Appearance → Theme Setup** creates the entire tree, dropdown menu, `/blog/%postname%/` permalinks and draft guide/glossary seeds in one click.
- **Schema (Tab 8)** — site-wide Organization (@id, areaServed GB/US, sameAs) + WebSite/SearchAction + BreadcrumbList; Service (+ItemList on pillars), FAQPage, AboutPage, ContactPage, OfferCatalog (pricing), WebApplication (tools), Article, DefinedTerm/DefinedTermSet, Person (bios).
- **GEO layer (Tab 9)** — canonical entity paragraph (`tf_entity_paragraph()`) reused verbatim on Home/About/llms.txt; definition-first blocks; question-format H2s; expanded `/llms.txt`; robots.txt AI-crawler allow-list (GPTBot, PerplexityBot, ClaudeBot, Google-Extended, etc.).
- **Tools** — `/tools/ai-roi-calculator/` implements Tab 16's exact calculation and readiness rubric with GBP/USD toggle; the gated report uses the documented `submitRoiLead()` hook in `assets/js/roi-calculator.js`.
- **Pricing (Tab 17)** — tiers in `tf_spec_pricing()`; figures are the workbook's INDICATIVE benchmarks — replace before launch.

## SEO & machine readability

Built in (and automatically disabled if Yoast or Rank Math is active): meta descriptions, Open Graph/Twitter cards (using the post's featured image), ProfessionalService JSON-LD, FAQPage JSON-LD on the service pages, and a live-generated **`/llms.txt`** describing the firm, services, case studies and latest insights to AI crawlers. If `/llms.txt` 404s after deployment, run **Appearance → Theme Setup** once (it refreshes rewrite rules).

## Contact form

The contact page includes a native form (nonce-verified, honeypot-protected) that emails the address set in the Customizer via `wp_mail()`. For reliable delivery, pair it with any SMTP plugin (e.g. WP Mail SMTP).

## Theme structure

```
style.css              Theme header (styles live in assets/css/main.css)
functions.php          Bootstrap: enqueues, head cleanup, JSON-LD
inc/                   Setup, Customizer, template tags, contact form,
                       case studies CPT, one-click admin setup
page-templates/        AI Transformation, AI Search, Approach, Contact
template-parts/        Page hero, CTA band, post + case study cards
archive/single-tf_case_study.php   /work/ case study templates
assets/css/main.css    Full design system (design tokens at the top)
assets/js/main.js      Canvas hero, scroll reveals, counters, nav — no dependencies
```

## Brand system (v1.0)

The theme implements the official Alugence identity:

- **Colour** — Ink `#0B0D12` / Paper `#F6F7F9` neutrals; **Signal Indigo `#2B34E0`** as the primary decision colour; **Volt `#CDF94E`** as the rationed energy accent (CTAs, headline emphasis, markers — never large fills or small text on white); **Beam `#29D3C2`** (`#0B7768` text-safe) cues the AI-search arm. 60/30/10 usage.
- **Typography** — Space Grotesk (display, 600), IBM Plex Sans (body, 17px), IBM Plex Mono (labels, 12px/0.16em uppercase), from Google Fonts.
- **System** — 1120px max width, 8-pt spacing, 6px/16px radii, one shadow token, and the signature motifs: forward arrow, fine grid, indigo mesh.
- **Two-arm cue** — pages using the AI Search template get a `tf-arm-search` body class that switches accent tokens (`--arm-light`/`--arm-dark`) from Indigo/Volt to Beam.

All tokens are CSS custom properties at the top of `assets/css/main.css`. Official logo SVGs ship in `assets/img/`.
