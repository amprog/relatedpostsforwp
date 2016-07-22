<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Meta_Box_Options {

	private $post_type;

	public function __construct( $post_type ) {

		// Check if we're in the admin/backend
		if ( ! is_admin() ) {
			return;
		}

		// Set the post type
		$this->post_type = $post_type;

		// Add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

	}

	/**
	 * Add metabox to dashboard
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_box() {

		// Add meta box to parent
		add_meta_box(
			'rp4wp_metabox_exclude_post',
			__( 'Related Posts Options', 'related-posts-for-wp' ),
			array( $this, 'view' ),
			$this->post_type,
			'side',
			'core'
		);

	}

	/**
	 * Meta box view
	 *
	 * @param WP_Post $post
	 *
	 * @access public
	 * @return void
	 */
	public function view( $post ) {

		$excludes = explode( ',', get_option( RP4WP_Constants::OPTION_EXCLUDED, '' ) );

		echo "<div class='rp4wp_mb_options'>\n";

		wp_nonce_field( 'rp4wp_meta_box_options', 'rp4wp_meta_box_options_nonce' );

		echo '<label for="rp4wp_exclude"><input type="checkbox" name="rp4wp_exclude" id="rp4wp_exclude" value="1" ' . checked( true, in_array( $post->ID, $excludes ), false ) . ' /> ' . __( 'Exclude', 'related-posts-for-wp' ) . '</label>';

		echo "</div>\n";
	}

}