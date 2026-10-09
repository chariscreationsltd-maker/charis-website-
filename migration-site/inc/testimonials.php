<?php
/**
 * Live client reviews from CharisOS.
 *
 * CharisOS keeps each client's review on their row in the Supabase `clients`
 * table (review_text, review_rating, review_event_type, review_date) and an
 * admin ticks `review_approved` before it may appear publicly. This file asks
 * Supabase, on the server, for approved reviews only and only the review
 * fields: no phone numbers, emails or notes ever reach the website.
 *
 * Results are cached for 15 minutes. If CharisOS cannot be reached the last
 * good copy is used; if there has never been one, the function returns []
 * and the page falls back to the CharisOS testimonials widget.
 *
 * The publishable key is not kept in this public repo. It comes from the
 * CHARIS_OS_KEY environment variable or from inc/charis-os-key.php, a one-line
 * file placed on the server by hand (never committed): <?php return 'KEY';
 */

const CHARIS_OS_URL   = 'https://vlmcwmjhmenbnymwfkdk.supabase.co';
const CHARIS_OS_TABLE = 'clients';   // can point at a view such as public_testimonials later
const TESTIMONIALS_TTL = 900;        // seconds
const TESTIMONIALS_MAX = 6;

function charis_os_key(): string {
    $k = getenv('CHARIS_OS_KEY');
    if ($k) return trim($k);
    $f = __DIR__ . '/charis-os-key.php';
    if (is_file($f)) { $v = include $f; if (is_string($v)) return trim($v); }
    return '';
}

function testimonials_cache_file(): string {
    $dir = __DIR__ . '/cache';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (!is_dir($dir) || !is_writable($dir)) $dir = sys_get_temp_dir();
    return $dir . '/testimonials.json';
}

/** @return array<int, array{quote:string,name:string,event:string,date:string,rating:int}> */
function live_testimonials(): array {
    $cache = testimonials_cache_file();
    $cached = is_file($cache) ? json_decode((string) file_get_contents($cache), true) : null;
    if (is_array($cached) && time() - filemtime($cache) < TESTIMONIALS_TTL) return $cached;

    $fresh = fetch_testimonials();
    if ($fresh !== null) {
        @file_put_contents($cache, json_encode($fresh, JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $fresh;
    }
    if (is_array($cached)) { @touch($cache); return $cached; } // stale but real; retry in 15 min
    return [];
}

/** null means "could not reach CharisOS"; [] means "reached it, nothing approved yet". */
function fetch_testimonials(): ?array {
    $key = charis_os_key();
    if ($key === '' || !function_exists('curl_init')) return null;

    $q = http_build_query([
        'select'          => 'name,partner_name,review_text,review_rating,review_event_type,review_date',
        'review_approved' => 'is.true',
        'review_text'     => 'neq.',
        'order'           => 'review_date.desc.nullslast',
        'limit'           => TESTIMONIALS_MAX,
    ]);
    $ch = curl_init(CHARIS_OS_URL . '/rest/v1/' . CHARIS_OS_TABLE . '?' . $q);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => ['apikey: ' . $key, 'Authorization: Bearer ' . $key, 'Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $code !== 200) return null;
    $rows = json_decode($body, true);
    if (!is_array($rows)) return null;

    $out = [];
    foreach ($rows as $r) {
        $quote = trim((string) ($r['review_text'] ?? ''));
        if ($quote === '') continue;
        $name = trim((string) ($r['name'] ?? ''));
        $partner = trim((string) ($r['partner_name'] ?? ''));
        if ($partner !== '' && stripos($name, $partner) === false) $name .= ' & ' . strtok($partner, ' ');
        $date = '';
        if (!empty($r['review_date']) && ($t = strtotime((string) $r['review_date']))) $date = date('F Y', $t);
        $out[] = [
            'quote'  => $quote,
            'name'   => $name,
            'event'  => trim((string) ($r['review_event_type'] ?? '')),
            'date'   => $date,
            'rating' => max(0, min(5, (int) ($r['review_rating'] ?? 0))),
        ];
    }
    return $out;
}
