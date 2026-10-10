<?php
/**
 * One-time move of media off WordPress.
 *
 * Copies every photo and video the new site still loads from
 * chariscreationsltd.com/wp-content/uploads into this site's own /media/
 * folder (same year/month paths), and saves each Projects gallery's photo list
 * to media/galleries.json so the collages no longer need WordPress.
 *
 * Both sites live on the same hosting account, so files are copied straight
 * from the WordPress folder when it can be found; otherwise they are
 * downloaded once. Files already copied are skipped, so it is safe to run
 * again. It works in batches of about 40 seconds and continues by itself.
 * It only ever copies public media within this account; it changes nothing on
 * WordPress. Delete this file after the move.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/inc/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
set_time_limit(0);
ignore_user_abort(true);

const WP_UPLOADS_URL = '~^https?://(?:www\.)?chariscreationsltd\.com/wp-content/uploads/([^\s"\'<>?#\\\\]+\.(?:jpe?g|png|webp|gif|mp4|mov|webm))$~iu';
const GALLERY_SERVE  = 'https://chariscreationsltd.com/gallery-serve.php?cat=';
const BATCH_SECONDS  = 40;

$mediaDir = SITE_ROOT . '/media';
if (!is_dir($mediaDir)) @mkdir($mediaDir, 0755, true);
if (!is_file("$mediaDir/.htaccess")) {
    @file_put_contents("$mediaDir/.htaccess", "Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar|pl|py|cgi|sh)$\">\n  Require all denied\n</FilesMatch>\n");
}

function fetch_url(string $url, ?string $toFile = null): ?string {
    $ch = curl_init($url);
    $fh = $toFile ? fopen($toFile, 'wb') : null;
    curl_setopt_array($ch, [
        CURLOPT_FOLLOWLOCATION => true, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 600,
        CURLOPT_USERAGENT => 'CharisSiteMove/1.0',
    ] + ($fh ? [CURLOPT_FILE => $fh] : [CURLOPT_RETURNTRANSFER => true]));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($fh) fclose($fh);
    if ($body === false || $code !== 200) { if ($toFile) @unlink($toFile); return null; }
    return $toFile ? '' : (string) $body;
}

// 1. Every WordPress media address the site uses today.
$urls = [];
$files = array_merge(glob(SITE_ROOT . '/content/*.json') ?: [], glob(SITE_ROOT . '/content/legacy/*.html') ?: []);
foreach ($files as $f) {
    $text = (string) file_get_contents($f);
    preg_match_all('~https?://(?:www\.)?chariscreationsltd\.com/wp-content/uploads/[^"\'\s)<>\\\\]+~i', $text, $m);
    foreach ($m[0] as $u) if (!str_ends_with($u, '/')) $urls[$u] = true;
    // Team page builds names onto a base: var B='…/uploads/2026/04/'; B+'Staff-photos-1.jpg'
    if (preg_match('~var B\s*=\s*\'(https?://[^\']+/wp-content/uploads/[^\']+)\'~', $text, $b)) {
        preg_match_all('~\bB\+\'([^\']+)\'~', $text, $n);
        foreach ($n[1] as $name) $urls[$b[1] . $name] = true;
    }
}

// 2. Gallery photo lists (asked once, then kept in media/galleries.json).
$galleryFile = "$mediaDir/galleries.json";
$galleries = is_file($galleryFile) ? (json_decode((string) file_get_contents($galleryFile), true) ?: []) : [];
foreach (array_keys(content('galleries')['galleries'] ?? []) as $key) {
    if (!isset($galleries[$key])) {
        $data = json_decode((string) fetch_url(GALLERY_SERVE . rawurlencode($key)), true);
        $list = [];
        foreach ((array) ($data['images'] ?? []) as $u) if (is_string($u) && preg_match(WP_UPLOADS_URL, $u, $mm)) $list[] = '/media/' . $mm[1];
        if ($data !== null) $galleries[$key] = $list;
    }
    foreach ($galleries[$key] ?? [] as $local) $urls['https://chariscreationsltd.com/wp-content/uploads/' . substr($local, 7)] = true;
}
@file_put_contents($galleryFile, json_encode($galleries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// 3. Copy, in a time-boxed batch.
$wpRoots = array_filter([dirname(SITE_ROOT) . '/wp-content/uploads', dirname(SITE_ROOT, 2) . '/wp-content/uploads', ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../wp-content/uploads'], 'is_dir');
$start = time();
$copied = $skipped = 0; $failed = []; $left = 0; $bytes = 0;
foreach (array_keys($urls) as $u) {
    if (!preg_match(WP_UPLOADS_URL, $u, $mm) || str_contains($mm[1], '..')) { $failed[] = "$u (not a media file)"; continue; }
    $rel = rawurldecode($mm[1]);
    $dest = "$mediaDir/$rel";
    if (is_file($dest) && filesize($dest) > 0) { $skipped++; continue; }
    if (time() - $start > BATCH_SECONDS) { $left++; continue; }
    if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0755, true);
    $ok = false;
    foreach ($wpRoots as $root) {
        if (is_file("$root/$rel") && @copy("$root/$rel", $dest)) { $ok = true; break; }
    }
    if (!$ok) { $tmp = "$dest.part"; if (fetch_url($u, $tmp) !== null && @rename($tmp, $dest)) $ok = true; }
    if ($ok) { $copied++; $bytes += (int) filesize($dest); } else $failed[] = $u;
}
$total = count($urls);
$done = $left === 0;
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="robots" content="noindex">
<?php if (!$done): ?><meta http-equiv="refresh" content="1"><?php endif; ?>
<title>Moving media off WordPress</title>
<style>body{font:15px/1.6 system-ui,sans-serif;background:#0a0a0a;color:#eee;max-width:760px;margin:40px auto;padding:0 20px}b{color:#f16623}code{color:#ffb27f}li{word-break:break-all}</style>
</head><body>
<h1><?= $done ? 'Done' : 'Working…' ?></h1>
<p><?= $total ?> files in total. This batch: <b><?= $copied ?></b> copied (<?= round($bytes / 1048576, 1) ?> MB), <?= $skipped ?> already here, <?= $left ?> still to go, <?= count($failed) ?> failed.</p>
<p>Copy source: <?= $wpRoots ? 'WordPress folder on this account (' . e(implode(', ', $wpRoots)) . '), download as fallback' : 'download from chariscreationsltd.com (WordPress folder not found from here)' ?>.</p>
<p>Galleries saved: <?php foreach ($galleries as $k => $l) echo e($k) . ' (' . count($l) . ') '; ?></p>
<?php if ($failed): ?><h2>Failed</h2><ul><?php foreach (array_slice($failed, 0, 50) as $f) echo '<li>' . e($f) . '</li>'; ?></ul><?php endif; ?>
<?php if ($done): ?><p><b>All done.</b> Tell Claude it finished<?= $failed ? ' and paste the failed list' : '' ?>.</p><?php else: ?><p>This page reloads by itself until everything is copied. Keep it open.</p><?php endif; ?>
</body></html>
