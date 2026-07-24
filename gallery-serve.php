<?php
/**
 * gallery-serve.php  v5 — FINAL
 * Uses wp_fbv_attachment_folder (FileBird 6.5.2 table name).
 *
 * Folder IDs (from your wp_fbv table):
 *   1=Documentary  2=wedding folder  3=introduction folder
 *   4=cooperate events folder  5=kukyala folder  6=commercial folder
 *   7=podcasts folder  8=branding folder  9=studio  10=testimonials
 */

require_once( __DIR__ . '/wp-load.php' );
header( 'Content-Type: application/json; charset=utf-8' );
header( 'Access-Control-Allow-Origin: *' );
header( 'Cache-Control: public, max-age=1800' );

global $wpdb;

$cat   = isset( $_GET['cat'] )   ? sanitize_text_field( $_GET['cat'] ) : '';
$debug = isset( $_GET['debug'] ) && $_GET['debug'] === '1';

$fbv_table = $wpdb->prefix . 'fbv_attachment_folder';

/* ── Folder ID map ───────────────────────────────────────────────────────── */
$folder_ids = [
    'wedding'       => 2,
    'introduction'  => 3,
    'documentary'   => 1,
    'kukyala'       => 5,
    'podcasts'      => 7,
    'commercial'    => 6,
    'branding'      => 8,
    'corporate'     => 4,
    'studio'        => 9,
    'clientstories' => 10,
];

/* ── Debug mode ──────────────────────────────────────────────────────────── */
if ( $debug ) {
    $cols = $wpdb->get_results( "DESCRIBE $fbv_table", ARRAY_A );
    $counts = $wpdb->get_results(
        "SELECT f.id, f.name, COUNT(af.attachment_id) AS cnt
         FROM {$wpdb->prefix}fbv f
         LEFT JOIN $fbv_table af ON af.folder_id = f.id
         GROUP BY f.id ORDER BY f.name"
    );
    // If attachment_id column name differs, try alternative
    if ( $wpdb->last_error ) {
        $cols2 = $wpdb->get_col( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$fbv_table' AND TABLE_SCHEMA = DATABASE()" );
        echo json_encode( [ 'table' => $fbv_table, 'columns' => $cols2, 'error' => $wpdb->last_error ], JSON_PRETTY_PRINT );
        exit;
    }
    echo json_encode( [
        'table'   => $fbv_table,
        'columns' => $cols,
        'folders' => $counts,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    exit;
}

if ( ! $cat || ! isset( $folder_ids[ $cat ] ) ) {
    echo json_encode( [ 'images' => [], 'source' => 'unknown_cat' ] );
    exit;
}

$fid    = (int) $folder_ids[ $cat ];
$images = [];
$source = 'none';

/* ── Query FileBird attachment_folder table ──────────────────────────────── */
$att_ids = $wpdb->get_col( $wpdb->prepare(
    "SELECT attachment_id FROM $fbv_table
     WHERE folder_id = %d
     ORDER BY attachment_id DESC
     LIMIT 60",
    $fid
) );

if ( $att_ids ) {
    foreach ( $att_ids as $id ) {
        $url = wp_get_attachment_image_url( (int) $id, 'large' );
        if ( $url ) $images[] = $url;
    }
    $source = 'filebird_v6';
}

/* ── Fallback: gallery-config.php manual IDs ────────────────────────────── */
if ( empty( $images ) ) {
    $config_file = __DIR__ . '/gallery-config.php';
    if ( file_exists( $config_file ) ) {
        $config = include $config_file;
        if ( ! empty( $config[ $cat ] ) ) {
            foreach ( $config[ $cat ] as $id ) {
                if ( ! $id ) continue;
                $url = wp_get_attachment_image_url( (int) $id, 'large' );
                if ( $url ) $images[] = $url;
            }
            if ( ! empty( $images ) ) $source = 'config';
        }
    }
}

echo json_encode( [
    'images' => array_values( array_unique( $images ) ),
    'count'  => count( $images ),
    'source' => $source,
], JSON_UNESCAPED_SLASHES );
