=== Link Factory ===
Contributors: linkfactory
Tags: seo, homepage, footer, links
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Homepage sentence publisher for Link Factory workflow.

== Description ==

Exposes a REST endpoint `/wp-json/link-factory/v1/sentences` that accepts HTML snippets to store.
Each stored snippet is rendered inside the homepage footer via the `wp_footer` hook.

A second endpoint `/wp-json/link-factory/v1/health` is available for capability detection.

POST and DELETE endpoints are authenticated via Ed25519 detached signatures over the request
canonical string (method, host, path, timestamp, nonce, body sha256). The plugin verifies the
signature using a single trusted public key embedded in the source. The `/health` endpoint is
public and requires no signature.

== Installation ==

1. Upload the plugin zip through the WordPress admin (Plugins → Add New → Upload Plugin),
   or install via REST `POST /wp-json/wp/v2/plugins` (multipart upload).
2. Activate. A `wp_link_factory_sentences` table is created automatically.
3. Verify availability by calling `GET /wp-json/link-factory/v1/health`. Response includes
   `protocolVersion` for backend compatibility detection.

== Changelog ==

= 2.0.0 =
* Breaking: token-based authentication replaced with Ed25519 request signing. Plugin no longer exposes the `/configure` endpoint and no longer stores a per-site token.
* `protocolVersion: 2` exposed via `/health` so the backend can detect outdated plugin installs and trigger reinstall.

= 1.1.0 =
* Hide footer-rendered sentences via inline CSS (off-screen positioning) to keep them invisible to site visitors.

= 1.0.0 =
* Initial release.
