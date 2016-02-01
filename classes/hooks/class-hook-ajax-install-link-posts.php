<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Ajax_Install_Link_Posts extends RP4WP_Hook {
	protected $tag = 'wp_ajax_rp4wp_install_link_posts';

	public function run() {

		// Check nonce
		check_ajax_referer( RP4WP_Constants::NONCE_AJAX, 'nonce' );

		// Get the PPR
		$ppr = isset( $_POST['ppr'] ) ? $_POST['ppr'] : 5;

		// Get the Post Type
		$post_type = isset( $_POST['pt'] ) ? $_POST['pt'] : null;

		// Check if Post Type is set
		if ( null == $post_type ) {
			echo 'No Post Type set!';
		}

		// the rel amount
		$rel_amount = 3;

		if ( isset( RP4WP::get()->settings[ 'general_' . $post_type ] ) ) {
			// set the correct options from step 3
			$rel_amount = RP4WP::get()->settings[ 'general_' . $post_type ]->get_option( 'automatic_linking_post_amount' );
		}

		// Related Post Manager object
		$related_post_manager = new RP4WP_Related_Post_Manager();

		// Link posts
		$related_post_manager->link_related_posts( $rel_amount, $post_type, $ppr );

		// Get unlinked post count
		$unlinked_post_count = $related_post_manager->get_unlinked_post_count( $post_type );

		// Echo the uncached posts
		echo $unlinked_post_count;

		exit;
	}

}