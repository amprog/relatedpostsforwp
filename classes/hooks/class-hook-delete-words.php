<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Delete_Words extends RP4WP_Hook {
	protected $tag = 'delete_post';
	protected $args = 1;

	public function run( $post_id ) {

		// Check if the current user can delete posts
		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		// check if post type is installed
		$pt_manager = new RP4WP_Post_Type_Manager();
		if ( ! $pt_manager->is_post_type_installed( get_post_type( $post_id ) ) ) {
			return;
		}

		// Related Post Manager
		$related_word_manager = new RP4WP_Related_Word_Manager();
		$related_word_manager->delete_words_by_post_id( $post_id );

	}
}