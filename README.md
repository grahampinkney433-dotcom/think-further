# Think Further — WordPress Theme

Custom marketing theme for [thinkfurther.ai](https://thinkfurther.ai) — an AI consultancy with two practices: **business AI consultancy & transformation** (primary) and **AI search consultancy** (specialised service line).

Hand-built classic theme. No page builder, no build step, no plugin dependencies.

## Deploying with WP Pusher

1. In wp-admin, install and activate [WP Pusher](https://wppusher.com/).
2. Go to **WP Pusher → Install Theme**, connect GitHub, and enter this repository (`grahampinkney433-dotcom/think-further`), branch `main` (or your deploy branch).
3. Install, then activate **Think Further** under **Appearance → Themes**.

The repository root **is** the theme root (`style.css` lives at the top level), so no subdirectory configuration is needed.

## Post-install setup (1 minute)

1. Go to **Appearance → Theme Setup** and click **Run setup**. This creates the core pages with the right templates (Home, AI Consultancy, AI Search, Approach, Contact, Insights), assigns the navigation menu, sets the static homepage and posts page, and enables pretty permalinks. It's idempotent and never overwrites existing content.
2. **Business details & hero copy** — Customise → **Think Further Options**: contact email, booking/calendar link, LinkedIn, hero headline, footer tagline. In headline fields, wrap words in `*asterisks*` for the italic serif accent (e.g. `The ones that win *think further.*`).

## Case studies

Add engagements under **Case Studies** in wp-admin (client, sector and headline result live in the *Case Study Details* box). They appear at `/work/`, and the homepage automatically shows a "Selected work" section once at least one is published.

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

## Editing the design

All colour, type and spacing tokens are CSS custom properties at the top of `assets/css/main.css`. Typography: Fraunces (display), Inter (body), IBM Plex Mono (labels), loaded from Google Fonts.
