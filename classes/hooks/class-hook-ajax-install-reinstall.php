<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Ajax_Install_Reinstall extends RP4WP_Hook {
	protected $tag = 'wp_ajax_rp4wp_install_reinstall';

	/**
	 * Hook into admin AJAX to delete a link
	 *
	 * @todo write method
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
			$parent = sanitize_text_field( $_POST['parent'] );

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

			// get children of parent
			$children = $ptm->get_installed_post_type( $parent );

			// Related word manager
			$word_manager = new RP4WP_Related_Word_Manager();

			// always delete parent words
			$word_manager->delete_words_by_post_type( $parent );

			// check if there are children
			if ( count( $children ) > 0 ) {

				// loop
				foreach ( $children as $child ) {

					// delete children words
					$word_manager->delete_words_by_post_type( $child );
				}
			}

			// Redirect to setup reinstall
			$redirect = true;

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