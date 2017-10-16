<?php

function rp4wp_premium_activate_plugin() {
	add_option( 'rp4wp_premium_start_install', true );
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