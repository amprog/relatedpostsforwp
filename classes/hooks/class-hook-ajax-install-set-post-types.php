<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Ajax_Install_Set_Post_Types extends RP4WP_Hook {
	protected $tag = 'wp_ajax_rp4wp_install_set_post_types';

	/**
	 * Hook into admin AJAX to delete a link
	 *
	 * @access public
	 * @return void
	 */
	public function run() {

		// Check nonce
		check_ajax_referer( RP4WP_Constants::NONCE_AJAX, 'nonce' );

		try {

			// Parent
			if ( ! isset( $_POST['parent'] ) ) {
				throw new Exception( 'Parent not set' );
			}

			// Parent and post types
			$parent     = sanitize_text_field( $_POST['parent'] );
			$post_types = array_map( 'sanitize_text_field', ( isset( $_POST['post_types'] ) ? $_POST['post_types'] : array() ) );

			// Check if user is allowed to do this
			if ( ! current_user_can( 'edit_posts' ) ) {
				return;
			}

			// Related post manager
			$related_post_manager = new RP4WP_Related_Post_Manager();

			// Remove linked related posts
			$related_post_manager->remove_linked_posts( $parent );

			// Add the post types
			$ptm = new RP4WP_Post_Type_Manager();

			// Check if we're adding or removing
			if ( count( $post_types ) > 0 ) {

				// Add the post type
				$ptm->add_post_type( $parent, $post_types );

				// Let's go0oo
				$redirect = true;
			} else {

				// get children of parent
				$children = $ptm->get_installed_post_type( $parent );

				// Remove post type
				$ptm->remove_post_type( $parent );

				// Related word manager
				$word_manager = new RP4WP_Related_Word_Manager();

				// check if parent is used as child in any other relations
				if ( false === $ptm->is_post_type_used( $parent ) ) {
					// delete word cache of parent
					$word_manager->delete_words_by_post_type( $parent );
				}

				// check if there are children
				if ( count( $children ) > 0 ) {

					// loop
					foreach ( $children as $child ) {

						// check if this child post type is used in any other relation
						if ( false === $ptm->is_post_type_used( $child ) ) {

							// delete words
							$word_manager->delete_words_by_post_type( $child );
						}
					}
				}

				// No redirect needed
				$redirect = false;
			}

			// Success response
			$response = array( 'result' => 'success', 'redirect' => $redirect );

		} catch ( Exception $e ) {
			// Failure response
			$response = array( 'result' => 'failure', 'redirect' => false, 'error' => $e->getMessage() );
		}

		// Send response
		wp_send_json( $response );
	}

}