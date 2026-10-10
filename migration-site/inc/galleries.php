<?php
/**
 * Gallery photos for the Projects collages.
 *
 * For now the photos come from the current WordPress site, the same way its
 * Projects page loads them: gallery-serve.php returns the images in each
 * FileBird folder. The list is cached for 6 hours (stale copies are used if
 * the old site cannot be reached).
 *
 * Before the old site is switched off, put the final photo URLs (or names in
 * /assets/img/photo/) into content/galleries.json under "photos" and they are
 * used instead of the old site.
 */

const GALLERY_SOURCE = 'https://chariscreationsltd.com/gallery-serve.php?cat=';
const GALLERY_TTL    = 21600;

/** @return string[] image URLs */
function gallery_photos(string $key): array {
    $G = content('galleries');
    $manual = $G['galleries'][$key]['photos'] ?? [];
    if ($manual) {
        return array_map(fn($p) => preg_match('#^(https?:)?//|^/#', $p) ? $p : "/assets/img/photo/{$p}-1600.webp", $manual);
    }
    $dir = __DIR__ . '/cache';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (!is_dir($dir) || !is_writable($dir)) $dir = sys_get_temp_dir();
    $file = $dir . '/gallery-' . preg_replace('/[^a-z]/', '', $key) . '.json';
    $cached = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    if (is_array($cached) && time() - filemtime($file) < GALLERY_TTL) return $cached;

    $fresh = null;
    if (function_exists('curl_init')) {
        $ch = curl_init(GALLERY_SOURCE . rawurlencode($key));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 6, CURLOPT_FOLLOWLOCATION => true]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = ($body !== false && $code === 200) ? json_decode($body, true) : null;
        if (is_array($data) && isset($data['images']) && is_array($data['images'])) {
            $fresh = array_values(array_filter($data['images'], fn($u) => is_string($u) && preg_match('#^https://#', $u)));
        }
    }
    if ($fresh !== null) { @file_put_contents($file, json_encode($fresh, JSON_UNESCAPED_SLASHES), LOCK_EX); return $fresh; }
    if (is_array($cached)) { @touch($file); return $cached; }
    return [];
}
