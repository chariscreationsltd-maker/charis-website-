<?php
/**
 * Repository for the wp_link_factory_sentences table.
 *
 * @package LinkFactory
 */

namespace LinkFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repository {

	public static function install_schema() {
		global $wpdb;
		$table_name      = $wpdb->prefix . LINK_FACTORY_TABLE;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id varchar(64) NOT NULL,
			html longtext NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public function insert( $id, $html ) {
		global $wpdb;
		$table = $wpdb->prefix . LINK_FACTORY_TABLE;
		return $wpdb->insert(
			$table,
			array(
				'id'         => $id,
				'html'       => $html,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s' )
		);
	}

	public function find_all() {
		global $wpdb;
		$table = $wpdb->prefix . LINK_FACTORY_TABLE;
		return $wpdb->get_results( "SELECT id, html FROM {$table} ORDER BY created_at ASC", ARRAY_A );
	}

	public function delete_by_id( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . LINK_FACTORY_TABLE;
		return (int) $wpdb->delete( $table, array( 'id' => $id ), array( '%s' ) );
	}
}
