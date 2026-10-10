<?php
/**
 * Called by CharisOS right after a publish so the change shows at once
 * instead of after the 5-minute cache. Clearing the cache is harmless (the
 * next page view simply asks CharisOS again), and it is rate-limited.
 */
require dirname(__DIR__) . '/inc/bootstrap.php';

// CharisOS (Settings > Website) reads this from the owner's browser.
const CHARIS_OS_ORIGIN = 'https://app.chariscreationsltd.com';
header('Vary: Origin');
if (($_SERVER['HTTP_ORIGIN'] ?? '') === CHARIS_OS_ORIGIN) {
    header('Access-Control-Allow-Origin: ' . CHARIS_OS_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Max-Age: 86400');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }
$file = charis_cache_dir() . '/site-content.json';
if (!is_file($file) || time() - filemtime($file) >= 5) charis_cache_clear('site-content');
echo '{"ok":true}';
