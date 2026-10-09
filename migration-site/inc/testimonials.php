<?php
/**
 * Live client reviews from CharisOS.
 *
 * CharisOS publishes approved reviews through its own Supabase function,
 * get_public_testimonials(p_limit), the same one its testimonials widget uses.
 * The clients table itself is readable only by signed-in staff, so the
 * website never sees phone numbers, emails or notes.
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
const CHARIS_OS_RPC   = 'get_public_testimonials';
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

    $ch = curl_init(CHARIS_OS_URL . '/rest/v1/rpc/' . CHARIS_OS_RPC);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['p_limit' => TESTIMONIALS_MAX]),
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => ['apikey: ' . $key, 'Authorization: Bearer ' . $key, 'Content-Type: application/json', 'Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $code !== 200) return null;
    $data = json_decode($body, true);
    if (!is_array($data) || empty($data['success'])) return null;
    $rows = is_array($data['testimonials'] ?? null) ? $data['testimonials'] : [];

    $out = [];
    foreach ($rows as $r) {
        $quote = trim((string) ($r['comment'] ?? ''));
        if ($quote === '') continue;
        $date = '';
        $rawDate = $r['date'] ?? $r['reviewDate'] ?? $r['eventDate'] ?? '';
        if ($rawDate && ($t = strtotime((string) $rawDate))) $date = date('F Y', $t);
        $out[] = [
            'quote'  => $quote,
            'name'   => trim((string) ($r['name'] ?? '')) ?: 'Charis client',
            'event'  => trim((string) ($r['eventType'] ?? '')),
            'date'   => $date,
            'rating' => max(0, min(5, (int) ($r['rating'] ?? 0))),
        ];
    }
    return $out;
}
