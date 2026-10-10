# How to update the Charis Creations website

The site is plain PHP pages that read text from JSON files. There is no WordPress and no database.
Pushing to the `website-preview` branch deploys `migration-site/` to the preview over FTPS.
(This file is blocked from public view by `.htaccess`.)

## The website editor (no code)

The owner changes text, links and photos from CharisOS (Settings > Website, Owner only). CharisOS
reads `/editor/api.php` and calls `/editor/refresh.php` after each publish; keep content paths in the
JSON files stable, because published edits are stored against them. Published changes are stored in CharisOS and laid over the JSON files, so a code
deploy never wipes them; "Use original" in the editor goes back to what the JSON file says. Editing a JSON
file still works and changes the default. Setup for CharisOS is in `../charis-os-integration/README.md`.

## Where things live

| What you want to change | File |
|---|---|
| Phone numbers, email, address, socials, banner, CharisOS links, `noindex`, analytics ID | `content/site.json` |
| Home page text, featured work, logos, FAQ | `content/home.json` |
| Projects page, Project of the Month, gallery cards, trailers, showreel | `content/projects.json` |
| Services, packages, add-ons | `content/services.json` |
| Our Team | `content/team.json` |
| Charis Artistry | `content/artistry.json` |
| Privacy, Terms, Refund policies | `content/policies/*.html` (Hudson's wording only) |
| Colours, spacing, layout | `assets/css/site.css` |
| Menus, footer, page head | `inc/layout.php` |

Package names, inclusions, prices, terms and legal text change only with Hudson's sign-off.

## Editing a JSON file

- Keep the double quotes, and put a comma between items (no comma after the last one).
- Keys starting with `_` (like `_note`) are notes for editors; the site ignores them.
- An empty value hides that thing on the page. For example an empty `"price": ""` hides the price,
  and an empty `"items": []` hides the whole section. Nothing shows a placeholder.
- Check the file before pushing: paste it into any JSON validator, or run
  `python -m json.tool content/home.json`.

## Adding a photo

1. Export a WebP at 1600px wide and another at 800px wide, each under about 250 KB.
2. Name them `<name>-1600.webp` and `<name>-800.webp` and put them in `assets/img/photo/`.
3. Use `<name>` in the JSON (for example `"photo": "brenda-01"`).

A photo that is missing on the server is simply not shown, so a typo hides the image rather than
breaking the page. Hero wall images are `assets/img/hero/wall-01.webp` to `wall-18.webp`.
Client logos are white on transparent PNGs in `assets/img/logos/`.

## Links

In the JSON, a link can be a full address or a short key from `links` in `site.json`
(for example `"date"` for Check Your Date). Change the address once in `site.json` and every
button that uses that key follows.

Never link the private CharisOS pages (quote, reveal, peek, year, album, portal, client-portal,
intake, pay, refer, retainer-onboarding, winner, crew, the CharisOS index, makeup-os, CharisOS_Team).

## Videos

YouTube or Vimeo links only. Do not upload video files to the site.

## Launch day

1. In `content/site.json` set `"noindex": false` and confirm `baseUrl`.
2. Paste the existing Google Analytics ID into `analyticsId`.
3. Point the domain at this folder, confirm SSL, then check `/sitemap.xml` and `/robots.txt`.
4. Add a permanent redirect in `.htaccess` for every old WordPress address that is not already listed.

## Running it on your computer

From the `migration-site` folder: `php -S localhost:8000` and open http://localhost:8000.
(`.htaccess` redirects only run on the real server.)
