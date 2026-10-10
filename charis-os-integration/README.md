# CharisOS ↔ website: what the CharisOS developer needs to do

> **Update, 10 October 2026.** CharisOS has run 01 and 02. The editor now lives **inside CharisOS**
> (Settings > Website, Owner only), not on the website. `site_editor_allowed()` allows **Owner only**,
> and `get_public_testimonials()` also returns `photo` (https address or null, consented photos only).
> The website side is done: `/editor/api.php` and `/editor/refresh.php` send
> `Access-Control-Allow-Origin: https://app.chariscreationsltd.com` (with `Vary: Origin`), the sign-in page
> at `/editor/` is gone (it now says "The website is edited from CharisOS."), and review cards show the
> client's photo when one is sent (accepted from the site-media bucket, charis.smugmug.com and
> photos.smugmug.com only), and no photo otherwise. Content paths are kept stable; any rename will be
> announced first. Sections below describe the original plan; where they mention the editor at
> `/editor/` on the website or Owner/Admin, read "CharisOS Settings > Website" and "Owner".

The new website (`migration-site/`, preview at https://preview.chariscreationsltd.com/) talks to
CharisOS in three ways. Everything below runs in the existing CharisOS Supabase project
(`vlmcwmjhmenbnymwfkdk`). Nothing changes in the CharisOS app code, and no existing table is altered.

| # | Feature | What the website calls | Status |
|---|---------|------------------------|--------|
| 1 | Client reviews on the home page | `get_public_testimonials(p_limit)`, anon, read-only | **Run `01_public_testimonials.sql`** |
| 2 | Website editor at `/editor/` | `get_site_content()` (anon), `site_editor_me`, `save_site_content`, `list_site_content_history`, `restore_site_content` (signed in) + `site-media` storage bucket | **Run `02_website_editor.sql`** |
| 3 | Enquiry form embedded on the home page | `contact-widget.html` → `submit_website_lead(...)` | Live already; **commit its SQL to the repo** (check G) |
| 4 | Buttons that open public CharisOS pages | Plain links, listed below | **Confirm each page is live** |

## Run order

1. Supabase → SQL Editor → New query → paste **`01_public_testimonials.sql`** → Run.
2. New query → paste **`02_website_editor.sql`** → Run. Safe to run again later.
3. New query → paste **`03_checks.sql`** → Run each block and compare with the "Expected" line above it.

Both scripts are idempotent, and both were run against Postgres 16 with mock Supabase `auth` and `storage` schemas.
The tests covered anon, an Editor account and an Owner account.

## 1 · Client reviews

`get_public_testimonials` returns only approved reviews (`clients.review_approved = true`) and only five fields:
name, comment, rating, event type, date. Phone, email and notes never leave the `clients` table, which stays
staff-only. The website reads it server-side every 15 minutes. The CharisOS `testimonials-widget.html` already
calls this function and will start working too.

## 2 · Website editor

**How it works**

- The owner opens `https://chariscreationsltd.com/editor/` and signs in with their **normal CharisOS email and
  password** (Supabase Auth, password grant). There are no new accounts or passwords.
- Only profiles with `app_role` **Owner** or **Admin** can publish or upload. That is `site_editor_allowed()`.
  To let PAs edit the website too, add `'PA'` to that function and re-run it.
- An edit is stored as a flat `{ "path": value }` object per page in `site_content`,
  for example `{"hero.headline.0": "Stories told beautifully"}`. The website lays it over its built-in
  content files. Each publish also goes into `site_content_history`, which keeps the last 50 per page so any
  version can be restored.
- Photos are resized in the browser to 1600px and 800px WebP (JPEG on Safari) and uploaded to the public
  **`site-media`** bucket as `{page}/{id}_{w}x{h}-1600.webp` and `…-800.webp`.
- The website fetches `get_site_content()` server-side, caches it for 5 minutes, and clears the cache on publish.
  If CharisOS is down, the site keeps the last good copy. If it has never had one, it shows its built-in content.

**Security model**

- Both tables have RLS on and **no policies**, and grants are revoked from anon and authenticated. The only way
  in is through the `SECURITY DEFINER` functions, and each write function checks `site_editor_allowed()`.
- `save_site_content` accepts only the five page names (`site`, `home`, `projects`, `galleries`, `services`),
  a JSON object, field names matching `^[A-Za-z0-9][A-Za-z0-9_.]{0,199}$`, and at most 400 KB per publish.
- The bucket is public-read (the website has to show the photos). Insert, update and delete require
  `site_editor_allowed()`. The bucket accepts WebP, JPEG and PNG up to 8 MB.
- On its side, the website ignores any path that isn't already in its content files. It rejects
  `javascript:`/`data:` links, and accepts photos only from this bucket or its own `/assets/img/photo/` folder.

**Settings to check in Supabase**

- Auth → Providers → Email: password sign-in **enabled** (it already is, since CharisOS uses it).
- No redirect URLs or CORS changes are needed. The editor uses the REST endpoints with the publishable key,
  and Supabase allows browser calls from any origin.
- Storage: if the project has a global upload limit below 8 MB, it is fine. The editor's files are usually under 500 KB.

## 3 · Enquiry form (already live)

The home page embeds `https://app.chariscreationsltd.com/contact-widget.html` in an iframe. The widget calls
`submit_website_lead(p_name, p_phone, p_email, p_event_type, p_event_date, p_message, p_source)`.
That function exists in Supabase but **its SQL is not in the CharisOS repo**. Run check **G** in `03_checks.sql`
and commit the output, so it can't be lost.

Two more things for this widget:

- It must stay frameable by `https://chariscreationsltd.com` and `https://preview.chariscreationsltd.com`.
  Don't add `X-Frame-Options: DENY`, or a `frame-ancestors` rule that leaves those out.
- The website passes `?service=Livestream` / `?service=Podcast` from the Services page. Keep reading that parameter into `p_source` or the event type.

## 4 · Public CharisOS pages the website links to

These are the only CharisOS addresses the website uses (`migration-site/content/site.json → links`, plus two on
the Services page). Each one must be live, work on a phone, and need **no sign-in**.

| Link name | Address | In CharisOS repo? |
|-----------|---------|-------------------|
| enquiry | `/contact-widget.html` (also `?service=…`) | Yes. Embedded on home |
| testimonials | `/testimonials-widget.html` | Yes |
| weddingTimeline | `/wedding-timeline.html` | Yes |
| prepChecklist | `/prep-checklist.html` | Yes |
| whatToWear | `/what-to-wear.html` | Yes |
| makeupPackages | `/artistry.html` | Yes |
| graduation | `/graduation.html` | Yes |
| date | `/date.html` (Check your date, the main button) | **No.** Confirm it's live and commit it |
| book | `/book.html` (studio booking) | **No.** Confirm and commit |
| builder | `/builder.html` (package builder) | **No.** Confirm and commit |
| brief | `/brief.html` (project brief) | **No.** Confirm and commit |
| invest | `/invest.html` | **No.** Confirm and commit |
| gift | `/gift.html` (gift a session) | **No.** Confirm and commit |
| october | `/october.html` (seasonal offer) | **No.** Confirm, or tell the web team to remove it after the offer |

The website never links to private CharisOS pages: quote, reveal, peek, year, album, portal, client-portal, intake,
pay, refer, retainer-onboarding, winner, crew, the CharisOS index, makeup-os or CharisOS_Team. If a new public page is
added, send the web team its address; the owner can then point any button at it from the editor
(Site-wide → Links).

## On the website server (already done)

The publishable (anon) key sits in `migration-site/inc/charis-os-key.php`, which was placed on Hostinger by hand
and is never committed. The browser editor receives the same publishable key, which is what that key is for:
RLS and the function checks above decide what it can do. **Never** put the `service_role` key anywhere on the website.

## Files

- `01_public_testimonials.sql`: reviews feed
- `02_website_editor.sql`: editor tables, functions, photo bucket and policies
- `03_checks.sql`: read-only checks with expected results
