<?php
/**
 * Charis Creations website: shared setup for every page.
 * Text and images live in /content/*.json so a future editor can change them
 * without touching templates. Templates live in the page folders.
 */
declare(strict_types=1);

define('SITE_ROOT', dirname(__DIR__));
date_default_timezone_set('Africa/Kampala');

require __DIR__ . '/icons.php';

/** Load a JSON content file from /content. Fails loudly in the log, quietly on screen. */
function content(string $name): array {
    static $cache = [];
    if (isset($cache[$name])) return $cache[$name];
    $file = SITE_ROOT . '/content/' . $name . '.json';
    $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    if (!is_array($data)) {
        error_log("Charis: content file missing or invalid JSON: $name.json");
        $data = [];
    }
    return $cache[$name] = $data;
}

$SITE = content('site');

/** Escape text for HTML. */
function e(?string $s): string {
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Escape text but keep line breaks written as \n in the content files. */
function nl(?string $s): string {
    return nl2br(e($s), false);
}

/** Resolve a link: a key from site.json "links", an internal path, or a full URL. */
function href(?string $key): string {
    global $SITE;
    if ($key === null || $key === '') return '';
    if (isset($SITE['links'][$key])) return $SITE['links'][$key];
    return $key;
}

/** True when the link leaves this site (CharisOS, WhatsApp, socials). */
function is_external(string $url): bool {
    return preg_match('#^https?://#', $url) === 1
        && preg_match('#^https?://(www\.)?chariscreationsltd\.com(/|$)#', $url) !== 1;
}

/** A WhatsApp link with a prefilled message. */
function wa(string $message = '', ?string $base = null): string {
    global $SITE;
    $base = $base ?: $SITE['whatsapp'];
    return $message === '' ? $base : $base . '?text=' . rawurlencode($message);
}

/** Cache-busted asset URL. */
function asset(string $path): string {
    $file = SITE_ROOT . '/' . ltrim($path, '/');
    $v = is_file($file) ? substr(md5((string) filemtime($file)), 0, 8) : '1';
    return '/' . ltrim($path, '/') . '?v=' . $v;
}

/** Does a photo set exist? Photos are /assets/img/photo/{name}-1600.webp and -800.webp. */
function has_photo(?string $name): bool {
    return $name !== null && $name !== '' && is_file(SITE_ROOT . "/assets/img/photo/{$name}-1600.webp");
}

/**
 * Responsive photo. $sizes is the CSS sizes hint. Width and height are read from
 * the file so the browser reserves space (no layout jump).
 */
function photo(?string $name, string $alt = '', string $sizes = '100vw', string $class = '', bool $lazy = true, bool $priority = false): string {
    if (!has_photo($name)) return '';
    $large = "/assets/img/photo/{$name}-1600.webp";
    $small = "/assets/img/photo/{$name}-800.webp";
    [$w, $h] = getimagesize(SITE_ROOT . $large) ?: [1600, 1000];
    [$sw] = is_file(SITE_ROOT . $small) ? getimagesize(SITE_ROOT . $small) : [800];
    $attrs = $class ? ' class="' . e($class) . '"' : '';
    $attrs .= $lazy && !$priority ? ' loading="lazy"' : '';
    $attrs .= $priority ? ' fetchpriority="high"' : '';
    return sprintf(
        '<img src="%s" srcset="%s %dw, %s %dw" sizes="%s" width="%d" height="%d" alt="%s" decoding="async"%s>',
        e($large), e($small), $sw, e($large), $w, e($sizes), $w, $h, e($alt), $attrs
    );
}

/** The seasonal banner that should show right now, or null. */
function active_banner(): ?array {
    global $SITE;
    $today = date('Y-m-d');
    foreach ($SITE['banners'] ?? [] as $b) {
        if (empty($b['on'])) continue;
        if (!empty($b['start']) && $today < $b['start']) continue;
        if (!empty($b['end']) && $today >= $b['end']) continue;
        return $b;
    }
    return null;
}

/** A button or text link. $variant: primary | outline | text. */
function button(string $label, string $url, string $variant = 'primary', string $extraClass = ''): string {
    $cls = $variant === 'text' ? 'link-arrow' : "btn btn--$variant";
    if ($extraClass) $cls .= " $extraClass";
    // Booking links open in the same tab: on phones and inside WhatsApp's browser, new tabs get lost.
    $arrow = $variant === 'text' ? ' <span class="arrow" aria-hidden="true">' . icon('arrow') . '</span>' : '';
    $track = '';
    if (strpos($url, 'wa.me/') !== false) $track = ' data-track="whatsapp"';
    elseif (strpos($url, '/date.html') !== false) $track = ' data-track="date-check"';
    elseif (strpos($url, '/book.html') !== false) $track = ' data-track="studio-booking"';
    elseif (strpos($url, '/builder.html') !== false) $track = ' data-track="package-builder"';
    elseif (strpos($url, '/brief.html') !== false || strpos($url, 'contact-widget') !== false) $track = ' data-track="enquiry"';
    return '<a class="' . $cls . '" href="' . e($url) . '"' . $track . '>' . e($label) . $arrow . '</a>';
}

require __DIR__ . '/layout.php';

/** Text from a content file that may contain simple links: only <a href>, <b>, <em>, <br> survive. */
function rich(?string $s): string {
    $s = strip_tags((string) $s, '<a><b><strong><em><br>');
    // Drop every attribute except a safe href on links.
    return preg_replace_callback('#<a\b[^>]*>#i', function ($m) {
        if (preg_match('#href\s*=\s*"((?:https?://|/|mailto:|tel:)[^"]*)"#i', $m[0], $h)) {
            return '<a href="' . e($h[1]) . '">';
        }
        return '<a>';
    }, preg_replace('#<(b|strong|em|br)\b[^>]*>#i', '<$1>', $s));
}
