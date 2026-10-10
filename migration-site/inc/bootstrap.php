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
require __DIR__ . '/site-content.php';

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
    // Changes the owner published in the website editor sit on top of the file.
    return $cache[$name] = apply_overrides($name, $data);
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

/**
 * Address of one size of a photo. A photo is either a set in /assets/img/photo/
 * ({name}-1600.webp and {name}-800.webp), an image uploaded through the website
 * editor (stored in CharisOS with the same -1600 / -800 pair), or a full URL.
 */
function photo_src(?string $name, int $size = 1600): string {
    if ($name === null || $name === '') return '';
    if (is_media_url($name)) return preg_replace('#-1600\.(webp|jpg)$#', "-{$size}.$1", $name);
    if (preg_match('#^(https?:)?//|^/#', $name)) return $name;
    return "/assets/img/photo/{$name}-{$size}.webp";
}

/** Does a photo exist? */
function has_photo(?string $name): bool {
    if ($name === null || $name === '') return false;
    if (is_media_url($name)) return true;
    return is_file(SITE_ROOT . "/assets/img/photo/{$name}-1600.webp");
}

/** [width, height] of the large size. Editor uploads carry it in the file name (…_1600x1067-1600.webp). */
function photo_size(string $name): array {
    if (is_media_url($name)) {
        return preg_match('#_(\d{2,5})x(\d{2,5})-1600\.#', $name, $m) ? [(int) $m[1], (int) $m[2]] : [1600, 1067];
    }
    $file = SITE_ROOT . photo_src($name, 1600);
    return (is_file($file) ? getimagesize($file) : null) ?: [1600, 1000];
}

/**
 * Responsive photo. $sizes is the CSS sizes hint. Width and height are known
 * up front so the browser reserves space (no layout jump).
 */
function photo(?string $name, string $alt = '', string $sizes = '100vw', string $class = '', bool $lazy = true, bool $priority = false): string {
    if (!has_photo($name)) return '';
    $large = photo_src($name, 1600);
    $small = photo_src($name, 800);
    [$w, $h] = photo_size($name);
    $sw = min(800, $w);
    if (!is_media_url($name) && is_file(SITE_ROOT . $small)) { [$sw] = getimagesize(SITE_ROOT . $small) ?: [800]; }
    $attrs = $class ? ' class="' . e($class) . '"' : '';
    $attrs .= $lazy && !$priority ? ' loading="lazy"' : '';
    $attrs .= $priority ? ' fetchpriority="high"' : '';
    return sprintf(
        '<img src="%s" srcset="%s %dw, %s %dw" sizes="%s" width="%d" height="%d" alt="%s" decoding="async"%s>',
        e($large), e($small), $sw, e($large), $w, e($sizes), $w, $h, e($alt), $attrs
    );
}

/**
 * Media that used to load from WordPress (chariscreationsltd.com/wp-content/uploads)
 * now lives in this site's /media/ folder under the same year/month path
 * (moved 10 October 2026). Any old address that still turns up, for example
 * pasted into the editor, is pointed at the local copy: nothing is loaded
 * from WordPress.
 */
function media_url(string $url): string {
    if (preg_match('~^https?://(?:www\.)?chariscreationsltd\.com/wp-content/uploads/([^\s"\'<>?#]*)$~iu', $url, $m) && !str_contains($m[1], '..')) {
        return '/media/' . $m[1];
    }
    return $url;
}

/** media_url() for every WordPress media address inside a block of HTML. */
function media_html(string $html): string {
    return (string) preg_replace_callback('~https?://(?:www\.)?chariscreationsltd\.com/wp-content/uploads/[^\s"\'<>?#\\\\)]*~iu', fn($m) => media_url($m[0]), $html);
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
