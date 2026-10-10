<?php
/**
 * Called by the editor right after a publish so the change shows at once
 * instead of after the 5-minute cache. Clearing the cache is harmless (the
 * next page view simply asks CharisOS again), and it is rate-limited.
 */
require dirname(__DIR__) . '/inc/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }
$file = charis_cache_dir() . '/site-content.json';
if (!is_file($file) || time() - filemtime($file) >= 5) charis_cache_clear('site-content');
echo '{"ok":true}';
