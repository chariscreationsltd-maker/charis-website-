<?php
/**
 * Connection to CharisOS (Supabase), shared by every live feature on the site:
 * client reviews, the website editor's published changes and uploaded images.
 *
 * The website only ever calls public CharisOS functions with the publishable
 * key. What those functions may return is decided inside CharisOS, so the
 * key cannot read private data. The key is not kept in this public repo: it
 * comes from the CHARIS_OS_KEY environment variable or from
 * inc/charis-os-key.php, a one-line file placed on the server by hand
 * (never committed): <?php return 'KEY';
 */

const CHARIS_OS_URL = 'https://vlmcwmjhmenbnymwfkdk.supabase.co';

/** Public address of images uploaded through the website editor. */
const SITE_MEDIA_PREFIX = CHARIS_OS_URL . '/storage/v1/object/public/site-media/';

function charis_os_key(): string {
    static $key = null;
    if ($key !== null) return $key;
    $k = getenv('CHARIS_OS_KEY');
    if ($k) return $key = trim($k);
    $f = __DIR__ . '/charis-os-key.php';
    if (is_file($f)) { $v = include $f; if (is_string($v)) return $key = trim($v); }
    return $key = '';
}

/** Folder for short-lived copies of CharisOS answers (never committed). */
function charis_cache_dir(): string {
    $dir = __DIR__ . '/cache';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (!is_dir($dir) || !is_writable($dir)) $dir = sys_get_temp_dir();
    return $dir;
}

/**
 * Call a public CharisOS function. Returns the decoded answer, or null when
 * CharisOS could not be reached, the key is missing or the function failed.
 */
function charis_os_rpc(string $fn, array $args = [], int $timeout = 5): ?array {
    $key = charis_os_key();
    if ($key === '' || !function_exists('curl_init')) return null;
    $ch = curl_init(CHARIS_OS_URL . '/rest/v1/rpc/' . rawurlencode($fn));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode((object) $args),
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_HTTPHEADER     => ['apikey: ' . $key, 'Authorization: Bearer ' . $key, 'Content-Type: application/json', 'Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $code !== 200) return null;
    $data = json_decode((string) $body, true);
    return is_array($data) ? $data : null;
}

/**
 * Cached call: a fresh copy is used for $ttl seconds; when CharisOS cannot be
 * reached the last good copy keeps being used (and retried after $ttl).
 * $fetch returns an array, or null for "could not reach CharisOS".
 */
function charis_cached(string $name, int $ttl, callable $fetch): ?array {
    $file = charis_cache_dir() . '/' . preg_replace('/[^a-z0-9-]/', '', $name) . '.json';
    $cached = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    if (is_array($cached) && time() - filemtime($file) < $ttl) return $cached;
    $fresh = $fetch();
    if ($fresh !== null) {
        @file_put_contents($file, json_encode($fresh, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return $fresh;
    }
    if (is_array($cached)) { @touch($file); return $cached; }
    return null;
}

/** Forget a cached answer so the next page view asks CharisOS again. */
function charis_cache_clear(string $name): void {
    $file = charis_cache_dir() . '/' . preg_replace('/[^a-z0-9-]/', '', $name) . '.json';
    if (is_file($file)) @unlink($file);
}
