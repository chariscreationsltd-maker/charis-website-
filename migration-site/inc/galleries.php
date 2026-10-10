<?php
/**
 * Gallery photos for the Projects collages.
 *
 * Order of preference:
 *  1. photos set for the gallery in the editor / content/galleries.json ("photos")
 *  2. the list saved when the photos were moved off WordPress (media/galleries.json),
 *     files in /media/ on this site
 * Nothing is loaded from WordPress any more.
 */

/** @return string[] image URLs */
function gallery_photos(string $key): array {
    $G = content('galleries');
    $manual = $G['galleries'][$key]['photos'] ?? [];
    if ($manual) {
        return array_values(array_map(fn($p) => photo_src(media_url($p), 1600), $manual));
    }
    static $moved = null;
    if ($moved === null) {
        $f = SITE_ROOT . '/media/galleries.json';
        $moved = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
    }
    $list = is_array($moved[$key] ?? null) ? $moved[$key] : [];
    return array_values(array_filter($list, fn($p) => is_string($p) && str_starts_with($p, '/media/') && !str_contains($p, '..') && is_file(SITE_ROOT . rawurldecode($p))));
}
