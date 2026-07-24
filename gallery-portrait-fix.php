<?php
/**
 * gallery-portrait-fix.php
 * Adjusts background-position so portrait photos show faces (upper area)
 * rather than cropping at dead-center. Also slightly reduces zoom.
 *
 * Upload to: public_html/gallery-portrait-fix.php
 * Visit once: chariscreationsltd.com/gallery-portrait-fix.php
 * Self-deletes on success.
 */

require_once( 'wp-load.php' );

$page_id = 335;
$raw  = get_post_meta( $page_id, '_elementor_data', true );
$data = json_decode( $raw, true );

$override = <<<'CSS'

<style id="cg-portrait-fix">
/* Portrait photo position fix — shows faces/upper body instead of mid-crop */
.cg-stage-img {
  background-position: center 20% !important;
  transform: scale(1.0) !important;
  transition: opacity 0.7s ease, transform 24s linear !important;
}
.cg-stage-img.cg-active {
  transform: scale(1.0) !important;
}
.cg-stage-img.cg-pan {
  transform: scale(1.10) translate(-0.5%, 0.5%) !important;
  background-position: 52% 22% !important;
  transition: opacity 0.7s ease, transform 24s linear, background-position 24s ease !important;
}
</style>
CSS;

$patched = 0;

function inject_portrait_fix( &$nodes, $css, &$patched ) {
    foreach ( $nodes as &$node ) {
        if ( ! empty( $node['settings']['html'] ) ) {
            $html = $node['settings']['html'];
            if ( strpos( $html, 'cg-stage-img' ) !== false ) {
                $html = preg_replace( '/<style id="cg-portrait-fix">.*?<\/style>/s', '', $html );
                if ( strpos( $html, '</body>' ) !== false ) {
                    $html = str_replace( '</body>', $css . "\n</body>", $html );
                } else {
                    $html .= "\n" . $css;
                }
                $node['settings']['html'] = $html;
                $patched++;
            }
        }
        if ( ! empty( $node['elements'] ) ) inject_portrait_fix( $node['elements'], $css, $patched );
    }
}

inject_portrait_fix( $data, $override, $patched );

if ( $patched === 0 ) die( '<b style="color:orange">⚠️ Widget not found.</b>' );

$encoded = wp_slash( json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
update_post_meta( $page_id, '_elementor_data', $encoded );
if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
}

unlink( __FILE__ );
echo '<pre style="background:#0d2b0d;color:#4ade80;padding:20px;font-family:monospace">
✅ Portrait fix applied.

  background-position: center 20%  (shows upper body / face, not mid-crop)
  transform: scale 1.0 → 1.10  (very gentle 10% zoom over 24s)

Ctrl+Shift+R and test.
This file has self-deleted.
</pre>';
