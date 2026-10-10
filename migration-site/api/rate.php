<?php
/**
 * Save a team member rating in CharisOS. POST JSON {member, rating, comment}.
 * Only names on the Our Team page are accepted; one rating per visitor per
 * member per day (checked here and again in CharisOS).
 */
require dirname(__DIR__) . '/inc/bootstrap.php';
require_once SITE_ROOT . '/inc/team-ratings.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

function reply(int $code, array $body): void { http_response_code($code); echo json_encode($body); exit; }

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') reply(405, ['ok' => false, 'error' => 'method']);
// Same-site requests only.
$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
if ($origin !== '' && !preg_match('~^https://(www\.|preview\.)?chariscreationsltd\.com$~', $origin) && !preg_match('~^http://localhost(:\d+)?$~', $origin)) reply(403, ['ok' => false, 'error' => 'origin']);

$in = json_decode((string) file_get_contents('php://input', false, null, 0, 8192), true);
if (!is_array($in)) reply(400, ['ok' => false, 'error' => 'input']);
$member = trim((string) ($in['member'] ?? ''));
$rating = (int) ($in['rating'] ?? 0);
$comment = trim(strip_tags((string) ($in['comment'] ?? '')));
$comment = mb_substr(preg_replace('/\s+/u', ' ', $comment), 0, 1000);
if (!in_array($member, rateable_members(), true)) reply(400, ['ok' => false, 'error' => 'member']);
if ($rating < 1 || $rating > 5) reply(400, ['ok' => false, 'error' => 'rating']);

// Local brake as well: one rating per visitor per member per day.
$fp = rating_fingerprint();
$lockDir = charis_cache_dir() . '/ratings';
if (!is_dir($lockDir)) @mkdir($lockDir, 0755, true);
$lock = $lockDir . '/' . substr(hash('sha256', $fp . '|' . $member), 0, 32);
if (is_file($lock) && time() - filemtime($lock) < 86400) reply(429, ['ok' => false, 'error' => 'already_rated']);

$r = charis_os_rpc('submit_team_rating', ['p_member' => $member, 'p_rating' => $rating, 'p_comment' => $comment, 'p_fingerprint' => $fp], 6);
if (!$r) reply(503, ['ok' => false, 'error' => 'unavailable']);
if (empty($r['success'])) reply(($r['error'] ?? '') === 'already_rated' ? 429 : 400, ['ok' => false, 'error' => (string) ($r['error'] ?? 'rejected')]);

@touch($lock);
charis_cache_clear('team-ratings');
$sum = team_ratings_summary()[$member] ?? null;
reply(200, ['ok' => true, 'avg' => $sum ? (float) $sum['avg'] : (float) $rating, 'count' => $sum ? (int) $sum['count'] : 1]);
