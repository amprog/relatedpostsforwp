<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Meta_Box_Options extends RP4WP_Hook {
	protected $tag = 'admin_init';

	public function run() {

		// get installed post types
		$post_type_manager   = new RP4WP_Post_Type_Manager();
		$instaled_post_types = $post_type_manager->get_installed_post_types();

		// store the installed post types
		$post_types = array();

		// loop and add
		foreach ( $instaled_post_types as $instaled_post_type_children ) {
			if ( is_array( $instaled_post_type_children ) && count( $instaled_post_type_children ) > 0 ) {
				foreach( $instaled_post_type_children as $instaled_post_type_child ) {
					if ( ! in_array( $instaled_post_type_child, $post_types ) ) {
						$post_types[] = $instaled_post_type_child;
					}
				}
			}
		}

		// add meta box
		foreach( $post_types as $post_type ) {
			new RP4WP_Meta_Box_Options( $post_type );
		}

	}
}