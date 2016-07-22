<?php

function rp4wp_premium_activate_plugin() {
	global $wpdb;

	if ( ! defined( 'RP4WP_PLUGIN_FILE_INSTALLER' ) ) {
		return;
	}

	// Remove hide license key notice
	$plugin_slug = str_replace( '.php', '', basename( RP4WP_PLUGIN_FILE_INSTALLER ) );
	delete_option( $plugin_slug . '_hide_key_notice' );

	// Create database table
	rp4wp_premium_create_db_table();

	// Add option that will redirect user to installation wizard
	add_option( 'rp4wp_do_install', true );

	// Check if we need to migrate data from free plugin
	$post_options = get_option( 'rp4wp', array() );
	if ( count( $post_options ) > 0 ) {

		// Move options
		update_option( 'rp4wp_general_post', $post_options );
		delete_option( 'rp4wp' );

		// Set the post type 'post' as installed
		require_once( plugin_dir_path( RP4WP_PLUGIN_FILE_INSTALLER ) . 'classes/class-post-type-manager.php' );
		require_once( plugin_dir_path( RP4WP_PLUGIN_FILE_INSTALLER ) . 'classes/class-constants.php' );
		$post_type_manager = new RP4WP_Post_Type_Manager();
		$post_type_manager->add_post_type( 'post', array( 'post' ) );

		// add the post type meta to existing links

		// Get id's that need an upgrade
		$upgrade_ids = get_posts(
			array(
				'post_type'      => RP4WP_Constants::LINK_PT,
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => RP4WP_Constants::PM_PT_PARENT,
						'value'   => '1',
						'compare' => 'NOT EXISTS'
					)
				)
			)
		);

		// Preparing the sql lines
		if ( count( $upgrade_ids ) > 0 ) {
			$sql_lines = array();

			// Loop
			foreach ( $upgrade_ids as $upgrade_id ) {
				$sql_lines[] = "(" . $upgrade_id . ", '" . RP4WP_Constants::PM_PT_PARENT . "', 'post')";
			}

			// Insert the rows
			$wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`,`meta_key`,`meta_value`) VALUES" . implode( ',', $sql_lines ) . " ;" );
		}

	}

	// Set plugin version
	update_option( 'rp4wp_current_version', RP4WP::VERSION );

	// add licensing option to prevent the first update_option calling add_option
	add_option( 'rp4wp_license', array() );
}

function rp4wp_premium_create_db_table() {

	global $wpdb;

	// Create the table
	$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "rp4wp_cache` (
  `post_id` bigint(20) unsigned NOT NULL,
  `word` varchar(255) CHARACTER SET utf8 NOT NULL,
  `weight` float unsigned NOT NULL,
  `post_type` varchar(20) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`post_id`,`word`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	$wpdb->query( $sql );

	// add index
	$wpdb->query("ALTER TABLE `" . $wpdb->prefix . "rp4wp_cache` ADD INDEX(`word`);");

}

function rp4wp_premium_deactivate_plugin() {
	include_once plugin_dir_path( RP4WP_PLUGIN_FILE ) . 'classes/class-updater-key-api.php';

	// Plugin slug
	$plugin_slug = str_replace( '.php', '', basename( RP4WP_PLUGIN_FILE_INSTALLER ) );

	// Get license options
	$license_options = get_option( 'rp4wp_license', array() );

	// Only continue if there's a license key
	if ( ! isset( $license_options['licence_key'] ) ) {
		return;
	}

	// Deactivate license
	RP4WP_Updater_Key_API::deactivate( array(
		'api_product_id' => $plugin_slug,
		'licence_key'    => $license_options['licence_key'],
	) );

	// Always delete license related options
	delete_option( 'rp4wp_license' );
	delete_site_transient( 'update_plugins' );
}