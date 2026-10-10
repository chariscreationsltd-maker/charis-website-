<?php
/** Average rating and count per team member (no comments). */
require dirname(__DIR__) . '/inc/bootstrap.php';
require_once SITE_ROOT . '/inc/team-ratings.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');
header('X-Robots-Tag: noindex');
$all = team_ratings_summary();
$out = [];
foreach (rateable_members() as $name) {
    if (isset($all[$name])) $out[$name] = ['avg' => (float) $all[$name]['avg'], 'count' => (int) $all[$name]['count']];
}
echo json_encode(['ratings' => (object) $out], JSON_UNESCAPED_UNICODE);
