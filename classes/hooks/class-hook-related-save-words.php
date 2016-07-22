<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Related_Save_Words extends RP4WP_Hook {
	protected $tag = 'transition_post_status';
	protected $args = 3;

	public function run( $new_status, $old_status, $post ) {

		// verify this is not an auto save routine.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Post status must be publish
		if ( 'publish' != $new_status ) {
			return;
		}

		// Check if this post type is installed
		$pt_manager = new RP4WP_Post_Type_Manager();
		if ( ! $pt_manager->is_post_type_installed( $post->post_type ) ) {
			return;
		}

		// Save Words
		$related_word_manager = new RP4WP_Related_Word_Manager();
		$related_word_manager->save_words_of_post( $post->ID, $post->post_type );

	}
}