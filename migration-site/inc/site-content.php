<?php
/**
 * Changes published from the website editor (/editor/).
 *
 * The text, links and photos in /content/*.json are the site's defaults. When
 * the owner publishes a change in the editor it is stored in CharisOS as a
 * small list of "path => new value" pairs per page, for example
 *   { "hero.headline": "New headline", "gallery.items.2.photo": "https://…-1600.webp" }
 * and laid over the defaults here. Because edits live in CharisOS, deploying
 * new code never wipes them, and deleting an edit brings the default back.
 *
 * Published changes are cached for 5 minutes (the editor clears the cache when
 * it publishes). If CharisOS cannot be reached the last good copy is used; if
 * there has never been one the site simply shows the defaults.
 */

require_once __DIR__ . '/charis-os.php';

const SITE_CONTENT_TTL = 300;

/** Pages the editor may change, with the names the owner sees. */
const EDITABLE_PAGES = [
    'site'      => 'Site-wide: contact details, links, banners',
    'home'      => 'Home',
    'projects'  => 'Projects',
    'galleries' => 'Gallery photos',
    'services'  => 'Services',
];

/** Technical settings that stay in code: never changed from the editor. */
const LOCKED_PATHS = [
    'site'     => ['baseUrl', 'noindex', 'analyticsId', 'legalName'],
    'projects' => ['potm'],
];

function is_locked_path(string $page, string $path): bool {
    foreach (explode('.', $path) as $seg) if ($seg !== '' && $seg[0] === '_') return true;
    foreach (LOCKED_PATHS[$page] ?? [] as $lock) {
        if ($path === $lock || str_starts_with($path, $lock . '.')) return true;
    }
    return false;
}

/** @return array<string, array<string, mixed>> page => [path => value] */
function site_overrides(): array {
    static $all = null;
    if ($all !== null) return $all;
    $data = charis_cached('site-content', SITE_CONTENT_TTL, function () {
        $r = charis_os_rpc('get_site_content', [], 4);
        if (!$r || empty($r['success'])) return null;
        return is_array($r['pages'] ?? null) ? $r['pages'] : [];
    });
    return $all = is_array($data) ? $data : [];
}

/** Is this an image the editor uploaded to CharisOS? */
function is_media_url(?string $s): bool {
    return is_string($s) && str_starts_with($s, SITE_MEDIA_PREFIX)
        && preg_match('#^[A-Za-z0-9/_.-]+-1600\.(webp|jpg)$#', substr($s, strlen(SITE_MEDIA_PREFIX))) === 1;
}

/** Is a value safe to use for this field? Rejects script links and foreign images. */
function override_ok(string $path, $value, $default): bool {
    $key = substr($path, (int) strrpos('.' . $path, '.'));
    if (is_array($default)) {
        // Only photo lists may be replaced whole.
        if ($key !== 'photos' || !is_array($value) || count($value) > 400) return false;
        foreach ($value as $p) {
            if (!is_string($p)) return false;
            if (!is_media_url($p) && !preg_match('#^[a-z0-9-]{1,80}$#', $p) && !preg_match('#^https://chariscreationsltd\.com/[^"\'<>\s]+$#', $p)) return false;
        }
        return true;
    }
    if (is_bool($default)) return is_bool($value);
    if (is_int($default) || is_float($default)) return is_int($value) || is_float($value);
    if (!is_string($value) || !is_string($default) || strlen($value) > 8000) return false;
    if (preg_match('#^\s*(javascript|data|vbscript):#i', $value)) return false;
    if ($key === 'photo') return $value === '' || is_media_url($value) || preg_match('#^[a-z0-9-]{1,80}$#', $value) === 1;
    return true;
}

/** Lay published changes over a page's defaults. Unknown paths are ignored. */
function apply_overrides(string $page, array $data): array {
    if (!isset(EDITABLE_PAGES[$page])) return $data;
    $patch = site_overrides()[$page] ?? [];
    if (!is_array($patch)) return $data;
    foreach ($patch as $path => $value) {
        $path = (string) $path;
        if ($path === '' || is_locked_path($page, $path)) continue;
        $ref = &$data;
        $ok = true;
        foreach (explode('.', $path) as $seg) {
            if (!is_array($ref) || !array_key_exists(ctype_digit($seg) ? (int) $seg : $seg, $ref)) { $ok = false; break; }
            $ref = &$ref[ctype_digit($seg) ? (int) $seg : $seg];
        }
        if ($ok && override_ok($path, $value, $ref)) $ref = $value;
        unset($ref);
    }
    return $data;
}
