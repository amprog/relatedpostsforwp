<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Meta_Box extends RP4WP_Hook {
	protected $tag = 'admin_init';

	public function run() {

		// Post Type Manager
		$post_type_manager = new RP4WP_Post_Type_Manager();
		$post_types        = $post_type_manager->get_installed_post_types();

		// Add them for all post types
		if ( count( $post_types ) > 0 ) {
			foreach ( $post_types as $post_type => $pt_children ) {
				new RP4WP_Meta_Box_Manage( $post_type );
			}
		}

	}
}