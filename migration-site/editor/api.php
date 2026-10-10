<?php
/**
 * Read-only data for the website editor: the list of editable pages and each
 * page's defaults (the same text and photo names already public on the site).
 * Saving happens in CharisOS, never here.
 */
require dirname(__DIR__) . '/inc/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

const PAGE_VIEW = ['site' => '/', 'home' => '/', 'projects' => '/projects/', 'galleries' => '/projects/#galleries', 'services' => '/services/'];

$page = (string) ($_GET['page'] ?? '');
if ($page === '') {
    $photos = [];
    foreach (glob(SITE_ROOT . '/assets/img/photo/*-1600.webp') ?: [] as $f) $photos[] = substr(basename($f), 0, -10);
    sort($photos);
    echo json_encode(['pages' => EDITABLE_PAGES, 'view' => PAGE_VIEW, 'photos' => $photos], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
if (!isset(EDITABLE_PAGES[$page])) { http_response_code(404); echo '{"error":"unknown page"}'; exit; }

$raw = json_decode((string) file_get_contents(SITE_ROOT . "/content/{$page}.json"), true) ?: [];
// Drop notes and technical settings so they never show up as fields.
$strip = function ($v, string $path) use (&$strip, $page) {
    if (!is_array($v)) return $v;
    $out = [];
    foreach ($v as $k => $x) {
        $p = $path === '' ? (string) $k : "$path.$k";
        if (is_locked_path($page, $p)) continue;
        $out[$k] = $strip($x, $p);
    }
    return array_is_list($v) ? array_values($out) : $out;
};
echo json_encode([
    'page'     => $page,
    'label'    => EDITABLE_PAGES[$page],
    'view'     => PAGE_VIEW[$page],
    'defaults' => $strip($raw, ''),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
