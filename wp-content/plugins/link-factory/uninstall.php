<?php
/**
 * Fires when the Link Factory plugin is deleted via wp-admin.
 *
 * Drops the sentences table so a subsequent reinstall starts from a clean slate.
 *
 * @package LinkFactory
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

global $wpdb;
$table_name = $wpdb->prefix . 'link_factory_sentences';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
