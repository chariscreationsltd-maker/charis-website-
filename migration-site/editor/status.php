<?php
/**
 * Health check for the CharisOS connection, without any client data:
 * whether the server has the CharisOS key, and how many approved reviews
 * and published editor pages it is currently showing.
 * Add ?fresh=1 to ask CharisOS again right now (rate-limited to once a minute).
 */
require dirname(__DIR__) . '/inc/bootstrap.php';
require_once SITE_ROOT . '/inc/testimonials.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$dir = charis_cache_dir();
$age = function (string $name) use ($dir) {
    $f = "$dir/$name.json";
    return is_file($f) ? time() - filemtime($f) : null;
};
if (!empty($_GET['fresh'])) {
    foreach (['testimonials', 'site-content'] as $n) {
        $a = $age($n);
        if ($a === null || $a >= 60) charis_cache_clear($n);
    }
}
$hadCache = $age('testimonials') !== null;
$shown = live_testimonials();          // what the home page uses (asks CharisOS when the copy is old)
$reached = $age('testimonials') !== null && ($age('testimonials') < 5 || $hadCache);
$live = $reached ? $shown : null;
echo json_encode([
    'charis_os_key_on_server' => charis_os_key() !== '',
    'charis_os_answered'      => $live !== null,
    'approved_reviews' => $live === null ? null : count($live),
    'reviews_with_photo'      => $live === null ? null : count(array_filter($live, fn($r) => $r['photo'] !== '')),
    'reviews_on_home_page'    => count($shown),
    'review_cache_age_seconds' => $age('testimonials'),
    'editor_pages_published'  => array_keys(site_overrides()),
    'cache_folder_writable'   => is_writable($dir),
], JSON_PRETTY_PRINT);
