# Think Further — WordPress Theme

Custom marketing theme for [thinkfurther.ai](https://thinkfurther.ai) — an AI consultancy with two practices: **business AI consultancy & transformation** (primary) and **AI search consultancy** (specialised service line).

Hand-built classic theme. No page builder, no build step, no plugin dependencies.

## Deploying with WP Pusher

1. In wp-admin, install and activate [WP Pusher](https://wppusher.com/).
2. Go to **WP Pusher → Install Theme**, connect GitHub, and enter this repository (`grahampinkney433-dotcom/think-further`), branch `main` (or your deploy branch).
3. Install, then activate **Think Further** under **Appearance → Themes**.

The repository root **is** the theme root (`style.css` lives at the top level), so no subdirectory configuration is needed.

## Post-install setup (5 minutes)

1. **Pages** — create these pages and assign the matching template under *Page Attributes → Template*:
   | Page title | Template |
   |---|---|
   | AI Consultancy | AI Consultancy & Transformation |
   | AI Search | AI Search Consultancy |
   | Approach | Approach |
   | Contact | Contact |
2. **Homepage** — Settings → Reading → set "Your homepage displays" to a static page (create a blank page called "Home"; the front-page template renders regardless of its content). Optionally set a "Posts page" called "Insights".
3. **Menus** — Appearance → Menus: create a menu with the four pages above and assign it to **Primary Navigation** and **Footer Navigation**. (Until you do, the theme auto-links those pages as a fallback.)
4. **Business details & hero copy** — Customise → **Think Further Options**: contact email, booking/calendar link, LinkedIn, hero headline, footer tagline. In headline fields, wrap words in `*asterisks*` for the italic serif accent (e.g. `The ones that win *think further.*`).
5. **Permalinks** — Settings → Permalinks → "Post name".

## Contact form

The contact page includes a native form (nonce-verified, honeypot-protected) that emails the address set in the Customizer via `wp_mail()`. For reliable delivery, pair it with any SMTP plugin (e.g. WP Mail SMTP).

## Theme structure

```
style.css              Theme header (styles live in assets/css/main.css)
functions.php          Bootstrap: enqueues, head cleanup
inc/                   Setup, Customizer options, template tags, contact form
page-templates/        AI Transformation, AI Search, Approach, Contact
template-parts/        Page hero, CTA band, post cards
assets/css/main.css    Full design system (design tokens at the top)
assets/js/main.js      Canvas hero, scroll reveals, counters, nav — no dependencies
```

## Editing the design

All colour, type and spacing tokens are CSS custom properties at the top of `assets/css/main.css`. Typography: Fraunces (display), Inter (body), IBM Plex Mono (labels), loaded from Google Fonts.
