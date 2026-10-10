<?php
/**
 * "Rate your team member" on the Our Team page. Ratings are stored in
 * CharisOS (public.team_ratings); comments stay private to staff. The
 * website only shows each member's average and number of ratings.
 */
require_once __DIR__ . '/charis-os.php';

/** Team members who can be rated: the names on the Our Team page. */
function rateable_members(): array {
    static $names = null;
    if ($names !== null) return $names;
    $body = (string) @file_get_contents(SITE_ROOT . '/content/legacy/creatives.body.html');
    $names = [];
    if (preg_match('~var CHARIS=\[(.*?)\];~s', $body, $m)) {
        preg_match_all("~name:'([^']{2,80})'~", $m[1], $n);
        $names = array_values(array_unique($n[1]));
    }
    return $names;
}

/** One-way visitor fingerprint (IP + browser), keyed so it cannot be reversed. */
function rating_fingerprint(): string {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $ip = trim(explode(',', (string) $ip)[0]);
    return substr(hash_hmac('sha256', $ip . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? ''), 'charis-ratings|' . charis_os_key()), 0, 40);
}

/** @return array<string, array{avg: float, count: int}> */
function team_ratings_summary(): array {
    $data = charis_cached('team-ratings', 300, function () {
        $r = charis_os_rpc('get_team_ratings', [], 4);
        if (!$r || empty($r['success'])) return null;
        return is_array($r['ratings'] ?? null) ? $r['ratings'] : [];
    });
    return is_array($data) ? $data : [];
}
