<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Post_Type_Manager {

	/**
	 * Get the available post types
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_available_post_types() {
		$raw_post_types = get_post_types( array( '_builtin' => false ), 'objects' );
		$post_types     = array();

		$post_types['post'] = __( 'Posts', 'related-posts-for-wp' );
		$post_types['page'] = __( 'Pages', 'related-posts-for-wp' );

		if ( count( $raw_post_types ) > 0 ) {
			foreach ( $raw_post_types as $pt_key => $post_type ) {
				if ( $pt_key == 'attachments' || $pt_key == 'rp4wp_link' ) {
					continue;
				}
				$post_types[ $pt_key ] = $post_type->labels->name;
			}
		}

		return $post_types;
	}

	/**
	 * Get the installed post types
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return mixed|void
	 */
	public function get_installed_post_types() {
		return apply_filters( 'rp4wp_installed_post_types', get_option( RP4WP_Constants::OPTION_INSTALLED_PT, array() ) );
	}

	/**
	 * Get one installed post type
	 *
	 * @param $parent
	 *
	 * @return array()
	 */
	public function get_installed_post_type( $parent ) {
		$post_types = $this->get_installed_post_types();
		if ( isset( $post_types[ $parent ] ) ) {
			return $post_types[ $parent ];
		}

		return array();
	}

	/**
	 * Add a post type to the installed post types option
	 *
	 * @param String $parent
	 * @param String $children
	 *
	 * @since  1.3.0
	 * @access public
	 *
	 */
	public function add_post_type( $parent, $children ) {

		// Get the post types
		$post_types = $this->get_installed_post_types();

		// Check if $post_type doesn't exists in $post_types
		if ( isset( $post_types[ $parent ] ) ) {
			unset( $post_types[ $parent ] );
		}

		// Add the parent type and the children
		$post_types[ $parent ] = $children;

		// Update option
		update_option( RP4WP_Constants::OPTION_INSTALLED_PT, $post_types );

	}

	/**
	 * Remove a post type from the installed post types option
	 *
	 * @param $post_type
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function remove_post_type( $post_type ) {

		// Get the post types
		$post_types = $this->get_installed_post_types();

		// Check if $post_type exists in $post_types
		if ( isset( $post_types[ $post_type ] ) ) {

			// Remove it
			unset( $post_types[ $post_type ] );

			// Update option
			update_option( RP4WP_Constants::OPTION_INSTALLED_PT, $post_types );
		}
	}

	/**
	 * Check if a post type is installed
	 *
	 * @param $post_type
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_post_type_installed( $post_type ) {

		// Get the post types
		$post_types = $this->get_installed_post_types();

		if ( isset( $post_types[ $post_type ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a post type is used at all (parent or child)
	 *
	 * @param $post_type
	 *
	 * @return boolean
	 */
	public function is_post_type_used( $post_type ) {

		// post types
		$post_types = $this->get_installed_post_types();

		// loop
		foreach ( $post_types as $parent => $children ) {

			// check if parent equals post type
			if ( $parent == $post_type ) {
				return true;
			}

			// check if post type in children array
			if ( in_array( $post_type, $children ) ) {
				return true;
			}

		}

		return false;

	}

}