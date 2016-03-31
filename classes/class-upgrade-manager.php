<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Upgrade_Manager {

	/**
	 * Check if there's a plugin update
	 */
	public function check() {

		// Get current version
		$current_version = get_option( RP4WP_Constants::OPTION_CURRENT_VERSION, 0 );

		// Check if update is required
		if ( version_compare( RP4WP::VERSION, $current_version, '>' ) ) {

			// Do update
			$this->do_upgrade( $current_version );

			// Update version code
			$this->update_current_version_code();

		}

	}

	/**
	 * An update is required, do it
	 *
	 * @param $current_version
	 */
	private function do_upgrade( $current_version ) {

		global $wpdb;

		// Upgrade to version 1.2.2
		if ( version_compare( $current_version, '1.2.2', '<' ) ) {

			/**
			 * Add a post type to currently existing links
			 */

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

		// Upgrade to 1.3.0
		if ( version_compare( $current_version, '1.3.0', '<' ) ) {

			/**
			 * Upgrade the 'rp4wp_installed_post_types' option
			 */

			// Get old post types
			$old_installed_post_types = get_option( RP4WP_Constants::OPTION_INSTALLED_PT, array() );

			// Check
			if ( count( $old_installed_post_types ) > 0 ) {

				// New installed post types
				$installed_post_types = array();

				// Loop
				foreach ( $old_installed_post_types as $old_installed_post_type => $old_installed_post_type_children ) {

					if ( is_array( $old_installed_post_type_children ) ) {
						$installed_post_types[ $old_installed_post_type ] = $old_installed_post_type_children;
					} else {
						$installed_post_types[ $old_installed_post_type_children ] = array( $old_installed_post_type_children );
					}

				}

				// Set new option
				update_option( RP4WP_Constants::OPTION_INSTALLED_PT, $installed_post_types );
			}

			/**
			 * Upgrade license key and email
			 */

			// Fetch old license key and email
			$plugin_slug      = str_replace( '.php', '', basename( RP4WP_PLUGIN_FILE ) );
			$api_key          = get_option( $plugin_slug . '_licence_key', '' );
			$activation_email = get_option( $plugin_slug . '_email', '' );

			// Check if values exist
			if ( '' != $api_key && '' != $activation_email ) {

				// Update option
				update_option( 'rp4wp_license', array( 'licence_key' => $api_key, 'email' => $activation_email ) );

				// Set local activation status to true
				RP4WP_Updater_Key_API::set_activated( true );

			}

		}

		// upgrade to 1.4.0, the configurator
		if ( version_compare( $current_version, '1.4.0', '<' ) ) {

			// get installed post types
			$pt_manager           = new RP4WP_Post_Type_Manager();
			$installed_post_types = $pt_manager->get_installed_post_types();

			// check
			if ( count( $installed_post_types ) > 0 ) {

				// get first pt
				foreach ( $installed_post_types as $settings_pt => $dump ) {
					break;
				}
				unset( $dump );

				// get current theme option
				$theme_options = get_option( 'rp4wp_themes', array() );


				// get settings
				$pt_settings = RP4WP::get()->settings[ 'general_' . $settings_pt ];

				// configurator options
				$configurator_options = array(
					'posts_per_row' => ( ( isset( $theme_options['theme'] ) && intval( $theme_options['theme'] ) > 0 ) ? intval( $theme_options['theme'] ) : 2 ),
					'fixed_height'  => 325,
					'css'           => ( isset( $theme_options['css'] ) ? $theme_options['css'] : '' )
				);

				// get settings from first pt
				$display_image  = intval( $pt_settings->get_option( 'display_image' ) );
				$excerpt_length = intval( $pt_settings->get_option( 'excerpt_length' ) );

				// set the right configuration
				if ( 1 == $display_image && $excerpt_length > 0 ) {
					// image + title + excerpt
					$configurator_options['configuration'] = '[{"type":"image","x":0,"y":0,"width":1,"height":5},{"type":"title","x":1,"y":0,"width":1,"height":2},{"type":"excerpt","x":1,"y":2,"width":1,"height":3}]';
				} else if ( 1 == $display_image ) {
					// title + image
					$configurator_options['configuration'] = '[{"type":"title","x":0,"y":0,"width":2,"height":1},{"type":"image","x":0,"y":1,"width":2,"height":4}]';
				} else if ( $excerpt_length > 0 ) {
					// title + excerpt
					$configurator_options['configuration'] = '[{"type":"title","x":0,"y":0,"width":2,"height":1},{"type":"excerpt","x":0,"y":1,"width":2,"height":4}]';
				} else {
					// title only
					$configurator_options['configuration'] = '[{"type":"title","x":0,"y":0,"width":2,"height":5}]';
				}

				// set configuration option
				update_option( 'rp4wp_configurator', $configurator_options );

				// delete old theme options
				delete_option( 'rp4wp_themes' );


			}

		}

		// Upgrade to 1.5.0
		if ( version_compare( $current_version, '1.5.0', '<' ) ) {

			/**
			 * Add INDEX to word column in cache table
			 */
			// check for index
			$wpdb->query( "SHOW INDEX FROM  `" . $wpdb->prefix . "rp4wp_cache` WHERE KEY_NAME = 'word'" );
			if ( 0 === intval( $wpdb->num_rows ) ) {

				// add index
				$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . "rp4wp_cache` ADD INDEX(`word`);" );

			}

			/**
			 * Move excluded post meta ID's to option to improve related post query
			 */
			// get all post ids that are excluded
			$excluded_ids = get_posts( array(
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'   => 'rp4wp_exclude',
						'value' => '1'
					),
				)
			) );

			// check if there are any excluded posts
			if ( count( $excluded_ids ) > 0 ) {
				// put excluded ids in comma separated string
				$excluded_ids = implode( ',', $excluded_ids );

				// put in option
				update_option( RP4WP_Constants::OPTION_EXCLUDED, $excluded_ids );
			}


			/**
			 * Delete CSS transient so the new frontend styling will apply after update
			 */
			delete_transient( RP4WP_Constants::TRANSIENT_COMPONENT_CSS );


		}

	}

	/**
	 * Update the current version code
	 */
	private function update_current_version_code() {
		update_option( RP4WP_Constants::OPTION_CURRENT_VERSION, RP4WP::VERSION );
	}

}