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
 * and the review cards keep their placeholders.
 */

require_once __DIR__ . '/charis-os.php';

const TESTIMONIALS_TTL = 900;        // seconds
const TESTIMONIALS_MAX = 6;

/** @return array<int, array{quote:string,name:string,event:string,date:string,rating:int}> */
function live_testimonials(): array {
    return charis_cached('testimonials', TESTIMONIALS_TTL, 'fetch_testimonials') ?? [];
}

/** null means "could not reach CharisOS"; [] means "reached it, nothing approved yet". */
function fetch_testimonials(): ?array {
    $data = charis_os_rpc('get_public_testimonials', ['p_limit' => TESTIMONIALS_MAX]);
    if (!$data || empty($data['success'])) return null;
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
