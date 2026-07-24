<?php
/**
 * Plugin bootstrap class.
 *
 * @package LinkFactory
 */

namespace LinkFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	public static function activate() {
		Repository::install_schema();
	}

	public static function boot() {
		$repository = new Repository();
		$rest       = new Rest( $repository );
		$footer     = new Footer( $repository );

		add_action( 'rest_api_init', array( $rest, 'register_routes' ) );
		add_action( 'wp_footer', array( $footer, 'render' ) );
	}
}
