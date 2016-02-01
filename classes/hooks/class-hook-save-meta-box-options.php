<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Save_Meta_Box_Options extends RP4WP_Hook {
	protected $tag = 'save_post';

	public function run( $post_id ) {

		// nonce post must be set
		if ( ! isset( $_POST['rp4wp_meta_box_options_nonce'] ) ) {
			return;
		}

		// check nonce
		if ( ! wp_verify_nonce( $_POST['rp4wp_meta_box_options_nonce'], 'rp4wp_meta_box_options' ) ) {
			return;
		}

		// auto save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// caps
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// get excludes
		$excludes = explode( ',', get_option( RP4WP_Constants::OPTION_EXCLUDED, '' ) );

		// only update option when needed
		$exclude_needs_update = false;

		// set exclude post meta
		if ( isset( $_POST['rp4wp_exclude'] ) ) {
			// add to array if not already in array
			if ( ! in_array( $post_id, $excludes ) ) {
				$excludes[]           = $post_id;
				$exclude_needs_update = true;
			}
		} else {
			// remove from array if in array
			if ( ( $exclude_key = array_search( $post_id, $excludes ) ) !== false ) {
				unset( $excludes[ $exclude_key ] );
				$exclude_needs_update = true;
			}
		}

		// check if need update
		if ( $exclude_needs_update ) {
			// update option
			update_option( RP4WP_Constants::OPTION_EXCLUDED, implode( ',', $excludes ) );
		}

	}
}