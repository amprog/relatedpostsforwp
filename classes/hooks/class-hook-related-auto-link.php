<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Related_Auto_Link extends RP4WP_Hook {
	protected $tag = 'transition_post_status';
	protected $args = 3;
	protected $priority = 11;

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

		// Is automatic linking enabled?
		if ( 1 != RP4WP::get()->settings[ 'general_' . $post->post_type ]->get_option( 'automatic_linking' ) ) {
			return;
		}

		// Check if the current post is already auto linked
		if ( 1 != get_post_meta( $post->ID, RP4WP_Constants::PM_POST_AUTO_LINKED, true ) ) {

			// Post Link Manager
			$pl_manager = new RP4WP_Post_Link_Manager();

			// Get automatic linking post amount
			$automatic_linking_post_amount = RP4WP::get()->settings[ 'general_' . $post->post_type ]->get_option( 'automatic_linking_post_amount' );

			// Count already linked posts
			$already_linked_posts = $pl_manager->get_children_count( $post->ID );

			// Subtract already linked post count from posts to link amount
			if ( count( $already_linked_posts ) > 0 ) {
				$automatic_linking_post_amount = $automatic_linking_post_amount - $already_linked_posts;
			}

			// Related Posts Manager
			$related_post_manager = new RP4WP_Related_Post_Manager();

			// Link related posts
			$related_post_manager->link_related_post( $post->ID, $post->post_type, $automatic_linking_post_amount );

			// Set the auto linked meta
			update_post_meta( $post->ID, RP4WP_Constants::PM_POST_AUTO_LINKED, 1 );
		}

	}
}