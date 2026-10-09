# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

PHP-rendered pages on Hostinger, deployed over FTP from the `migration-site/` folder (GitHub Actions: `website-preview` branch to the preview, `main` to live). Pages are PHP templates that read editable content from JSON files in `migration-site/content/`, so the site works as plain HTML for visitors and a future on-site editor (with its own login and two-step verification) can change text and images without touching code. No WordPress, no plugins. Chosen by Patrick, 9 Oct 2026.

## Users

- Primary: couples and families in Uganda planning a wedding, introduction (kwanjula, kuhinjira), kukyala or studio portrait session. Mostly on phones, often arriving from Instagram, TikTok or WhatsApp. Job: see real work, judge whether Charis fits, check a date, start a conversation.
- Secondary: organisations (government offices, NGOs, brands, churches) commissioning corporate coverage, documentaries, brand films, podcasts and livestreams. Job: judge credibility, then send a project brief.

## Product Purpose

Charis Creations Limited is a photography and film company in Namugongo, Kampala, founded in 2013. The website shows the work and moves visitors to one of a few next steps: Check Your Date, Book a Studio Session, Build Your Package, Send a Brief or Enquiry, or WhatsApp. Success is qualified enquiries arriving in CharisOS.

## Positioning

Charis does not just shoot the day; clients get a planned, private client experience through CharisOS (planning tools, a private project page, a briefed crew, a private reveal page). "We don't sell photography. We sell peace of mind."

## Operating Context

- Public CharisOS pages live on https://app.chariscreationsltd.com and are only linked, never copied (see the link map in the Master Website Handover, section 07).
- Private CharisOS pages (quote, reveal, peek, year, album, portal, client-portal, intake, pay, refer, retainer-onboarding, winner, crew, CharisOS index, makeup-os, CharisOS_Team) must never be linked, listed in the sitemap or indexed.
- Enquiry form and testimonials are iframes from CharisOS.
- Galleries live on SmugMug (public gallery links only). Videos live on YouTube or Vimeo, never uploaded to the site.
- Charis Artistry (makeup and hair) is a sister service with its own WhatsApp (+256 779 721 401).

## Capabilities and Constraints

- Source of truth for copy and structure: `CHARIS_MASTER_WEBSITE_HANDOVER_FINAL.pdf` (10 Oct 2026), approved by Hudson Timothy Tumusiime.
- Do not change package names, quantities, prices, terms, refunds, timeframes or legal text without Hudson's sign-off.
- Policy pages use Hudson's wording exactly; never write legal text.
- No delivery-date promises anywhere on the site.
- Open decisions (TO CONFIRM): hero photo set, six featured projects, eight public SmugMug links, six trailers, showreel, team photos and numbers, three starting prices, studio price visibility, main and Artistry Instagram handles, logo permissions, final Terms wording, domain (possible move to chariscreations.com), public email.

## Brand Commitments

- Name: Charis Creations Limited. Tagline: "Bringing Image to Life."
- Owned lines, keep verbatim: "Unscripted. Unfiltered. Unforgettable." · "We don't direct your moments. We disappear into them." · "Everything about your wedding is temporary. Except the film."
- Voice: short, warm, confident. No long dashes. No unprovable claims ("most trusted", percentages, counts) and nothing that talks down to people.
- Binding visual constraints from the handover: backgrounds #0A0A0A and #1A1A1A only; logo orange (#F16623) only for primary buttons and small accents; headlines Barlow Condensed bold uppercase; body Barlow, at least 16px on phones; no pins, custom cursor, rotating clock, flip cards or oversized scrolling text; subtle motion that respects reduced-motion.
- Logo: `migration-site/assets/charis-logo-navbar.png`.

## Evidence on Hand

- Real photography in `migration-site/assets/hero-slides/` and the WordPress backup on Patrick's computer.
- Real client logos used on the current site (WordPress uploads, 2026/04).
- Contacts: +256 706 028 899 (main, WhatsApp), +256 778 028 899, chariscreationsltd@gmail.com, Block 103 / Plot 801, Mungu Valley Road, Namugongo Sonde, P.O. Box 151380, Mukono.
- Absent and not to be fabricated: testimonials outside the CharisOS widget, team size, project counts, client-satisfaction figures, prices not yet confirmed.

## Product Principles

1. The photograph leads; the interface recedes.
2. Every page ends in one clear, real next step into CharisOS or WhatsApp.
3. Only real work, real people, real numbers. Hide what is not confirmed rather than inventing it.
4. Phones first, on slow connections and inside WhatsApp's browser.
5. Owned by Hudson: repository, domain, analytics and editor access stay in his control.

## Accessibility & Inclusion

WCAG AA contrast, visible keyboard focus, labelled form fields, real image descriptions (empty for decorative images), keyboard-friendly menus, no sound on autoplay, reduced-motion support.
