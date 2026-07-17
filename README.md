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

## Brand AI Visibility Checker (`/tools/ai-visibility-checker/`)

Checks how visible a brand is across AI assistants and AI search. A visitor enters their brand (plus an optional website and up to four competitors) and gets a dashboard: an overall **AI Visibility Score /100**, then breakdown cards for **citations**, **mentions**, **share of voice** vs competitors, **sentiment**, and the **sources** AI systems pull from — each with a plain-English explanation of what it means and why it matters. Ends with a "Book a call" lead-capture CTA.

It is built in two clearly separated layers:

- **Front end** (`assets/js/visibility-checker.js`) — input form, animated loading state and the results dashboard, all styled to the brand (Beam / AI-search arm). It only renders what the backend returns; it never calls an LLM directly.
- **Backend service** (`inc/visibility-checker.php`) — a real WordPress REST route (`POST /wp-json/tf/v1/visibility-check`) that queries the AI sources, aggregates the six signals, enforces cost controls, and logs each check. Each source is abstracted behind its own function (`tf_vis_source_openai`, `tf_vis_source_anthropic`, `tf_vis_source_gemini`, `tf_vis_source_perplexity`) and registered in `tf_vis_sources()`, so sources are added/removed in one place (also filterable via `tf_vis_sources`).

**Supplying API keys** (never committed): define any of these as environment variables **or** PHP constants in `wp-config.php` — a source with no key is skipped automatically:

```
TF_VIS_OPENAI_KEY      (or env OPENAI_API_KEY)
TF_VIS_ANTHROPIC_KEY   (or env ANTHROPIC_API_KEY)
TF_VIS_GEMINI_KEY      (or env GEMINI_API_KEY / GOOGLE_API_KEY)
TF_VIS_PERPLEXITY_KEY  (or env PERPLEXITY_API_KEY)
```

Optional model overrides: `TF_VIS_OPENAI_MODEL` (`gpt-4o-mini`), `TF_VIS_ANTHROPIC_MODEL` (`claude-opus-4-8`), `TF_VIS_GEMINI_MODEL` (`gemini-1.5-flash`), `TF_VIS_PERPLEXITY_MODEL` (`sonar`). **If no keys are configured, the tool falls back to clearly-labelled SAMPLE data** (the dashboard shows a "Sample data" banner) so it stays demonstrable — the real-call code path is fully built, not stubbed.

**Cost controls (enforced server-side, before any spend):** a per-visitor **daily cap** (by IP + first-party session cookie), configurable via `TF_VIS_CHECKS_PER_DAY` (default `3`); a **required, validated email** before a check runs (curbs bot/throwaway abuse, not just lead capture); and a honeypot field. Every check is logged (timestamp, brand, email, score, sample flag) to the `tf_vis_log` option and mirrored to the PHP error log — review it under **Tools → AI Visibility Log**, which also tallies live (paid) vs sample checks. The page is created automatically by Theme Setup.

## SEO & GEO architecture (workbook build)

The site implements the "Alugence — SEO & GEO Requirements" workbook as a structural scaffold. The single source of truth is **`inc/seo-spec.php`**: every URL, H1, title tag (≤60), meta description (≤155), nav placement, schema type, sector use-case list and pricing tier lives there — edit that file, not templates. Body copy is clearly-labelled placeholder blocks (dashed indigo boxes) awaiting the content stage; copy pasted into a page's WP editor renders in each template's editor-content slot.

- **Architecture (Tab 2)** — two-arm silos (`/ai-consultancy/` 18 children, `/ai-search/` 6), `/industries/` ×10, `/case-studies/`, `/guides/` + `/blog/` + `/glossary/` + `/tools/` under Resources, trust pages (About, Team, bios at `/about/team/{person}/`, Careers, Pricing, Contact), legal. **Appearance → Theme Setup** creates the entire tree, dropdown menu, `/blog/%postname%/` permalinks and draft guide/glossary seeds in one click.
- **Schema (Tab 8)** — site-wide Organization (@id, areaServed GB/US, sameAs) + WebSite/SearchAction + BreadcrumbList; Service (+ItemList on pillars), FAQPage, AboutPage, ContactPage, OfferCatalog (pricing), WebApplication (tools), Article, DefinedTerm/DefinedTermSet, Person (bios).
- **GEO layer (Tab 9)** — canonical entity paragraph (`tf_entity_paragraph()`) reused verbatim on Home/About/llms.txt; definition-first blocks; question-format H2s; expanded `/llms.txt`; robots.txt AI-crawler allow-list (GPTBot, PerplexityBot, ClaudeBot, Google-Extended, etc.).
- **Tools** — `/tools/ai-roi-calculator/` implements Tab 16's exact calculation and readiness rubric with GBP/USD toggle; the gated report uses the documented `submitRoiLead()` hook in `assets/js/roi-calculator.js`. `/tools/ai-visibility-checker/` is the Brand AI Visibility Checker (see its section above) — REST-backed, with real per-source LLM calls, cost controls and usage logging.
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
inc/visibility-checker.php  Brand AI Visibility Checker backend: REST route,
                       per-source LLM callers, cost caps, usage log
assets/js/visibility-checker.js  Visibility Checker front end (form + dashboard)
```

## Brand system (v1.0)

The theme implements the official Alugence identity:

- **Colour** — Ink `#0B0D12` / Paper `#F6F7F9` neutrals; **Signal Indigo `#2B34E0`** as the primary decision colour; **Volt `#CDF94E`** as the rationed energy accent (CTAs, headline emphasis, markers — never large fills or small text on white); **Beam `#29D3C2`** (`#0B7768` text-safe) cues the AI-search arm. 60/30/10 usage.
- **Typography** — Space Grotesk (display, 600), IBM Plex Sans (body, 17px), IBM Plex Mono (labels, 12px/0.16em uppercase), from Google Fonts.
- **System** — 1120px max width, 8-pt spacing, 6px/16px radii, one shadow token, and the signature motifs: forward arrow, fine grid, indigo mesh.
- **Two-arm cue** — pages using the AI Search template get a `tf-arm-search` body class that switches accent tokens (`--arm-light`/`--arm-dark`) from Indigo/Volt to Beam.

All tokens are CSS custom properties at the top of `assets/css/main.css`. Official logo SVGs ship in `assets/img/`.
