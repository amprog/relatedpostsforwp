<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Ajax_Install_Save_Words extends RP4WP_Hook {
	protected $tag = 'wp_ajax_rp4wp_install_save_words';

	public function run() {

		// Check nonce
		check_ajax_referer( RP4WP_Constants::NONCE_AJAX, 'nonce' );

		// Get the PPR
		$ppr = isset( $_POST['ppr'] ) ? $_POST['ppr'] : 25;

		// Check if Post Type is set
		if ( ! isset( $_POST['pt'] ) ) {
			echo 'No Post Type set!';
			exit;
		}

		if ( ! isset( $_POST['linked_pt_cur'] ) ) {
			echo 'No linked_pt_cur set!';
			exit;
		}

		// Post Type
		$post_type = $_POST['pt'];

		// Current index of linked posts
		$linked_pt_cur = $_POST['linked_pt_cur'];

		// Get the available post types
		$ptm = new RP4WP_Post_Type_Manager();

		// Get children
		$pt_children = $ptm->get_installed_post_type( $post_type );

		// Adding parent post type to the 'to be linked' array
		array_unshift( $pt_children, $post_type );

		// Check if the current linked post type exists
		if ( ! isset( $pt_children[ $linked_pt_cur ] ) ) {
			echo 'Linked post type not set';
			exit;
		}

		// Related Post Manager
		$related_word_manager = new RP4WP_Related_Word_Manager();

		// Save words
		$related_word_manager->save_all_words( $pt_children[ $linked_pt_cur ], $ppr );

		// Get uncached post count
		$uncached_post_count = $related_word_manager->get_uncached_post_count( $pt_children[ $linked_pt_cur ] );

		// Echo the uncached posts
		echo $uncached_post_count;

		exit;
	}

}