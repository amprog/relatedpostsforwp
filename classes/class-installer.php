<?php

/**
 * Class RP4WP_Installer
 */
class RP4WP_Installer {

	/**
	 * Run the installer
	 */
	public function run() {

		// Make sure we always show license key notice. Remove hide license key notice
		$plugin_slug = str_replace( '.php', '', basename( RP4WP_PLUGIN_FILE ) );
		delete_option( $plugin_slug . '_hide_key_notice' );

		// Create database table
		$this->create_database_table();

		// Migrate data from free plugin
		$this->migrate_free_data();

		// Set plugin version
		update_option( RP4WP_Constants::OPTION_CURRENT_VERSION, RP4WP::VERSION );

		// Add licensing option to prevent the first update_option calling add_option
		add_option( 'rp4wp_license', array() );

		// Redirect user to wizard
		$this->redirect_to_wizard();
	}


	/**
	 * Migrate data from free/lite version to premium
	 */
	private function migrate_free_data() {
		global $wpdb;

		// Check if we need to migrate data from free plugin
		$post_options = get_option( 'rp4wp', array() );
		if ( count( $post_options ) > 0 ) {

			// Move options
			update_option( 'rp4wp_general_post', $post_options );
			delete_option( 'rp4wp' );

			// Set the post type 'post' as installed
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
	}

	/**
	 * Create database table for cache
	 */
	public function create_database_table() {
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
		$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . "rp4wp_cache` ADD INDEX(`word`);" );
	}

	/**
	 * Redirect user to wizard where they can create post connections
	 */
	private function redirect_to_wizard() {
		// Redirect to installation wizard
		wp_redirect( admin_url() . '?page=rp4wp_install&rp4wp_nonce=' . wp_create_nonce( RP4WP_Constants::NONCE_INSTALL ), 307 );
		exit;
	}

}