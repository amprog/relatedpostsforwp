<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'RP4WP_Filter_Plugin_Links' ) ) {

	class RP4WP_Filter_Plugin_Links extends RP4WP_Filter {
		protected $tag = 'plugin_action_links_related-posts-for-wp-premium/related-posts-for-wp-premium.php';

		/**
		 * Add custom plugin links
		 *
		 * @param array $links
		 *
		 * @since  1.4.0
		 * @access public
		 *
		 * @return array
		 */
		public function run( $links ) {

			// Get the first setting page
			$local_settings = (array) RP4WP()->settings;
			$first_setting  = array_shift( $local_settings );

			// Add link
			array_unshift( $links, '<a href="' . admin_url( sprintf( 'options-general.php?page=%s', $first_setting->get_page() ) ) . '">' . __( 'Settings', 'related-posts-for-wp' ) . '</a>' );

			return $links;
		}
	}

}