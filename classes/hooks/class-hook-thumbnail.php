<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Thumbnail extends RP4WP_Hook {
	protected $tag = 'init';

	public function run() {

		// post type manager
		$pt_manager = new RP4WP_Post_Type_Manager();

		// get installed post types
		$post_types = $pt_manager->get_installed_post_types();

		// check & loop
		if ( count( $post_types ) > 0 ) {
			foreach ( $post_types as $parent => $children ) {

				// add thumbnail for post type
				RP4WP_Thumbnail_Helper::get()->register_thumbnail_size( $parent );
			}
		}

	}
}