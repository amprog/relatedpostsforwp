<?php

if ( ! function_exists( 'rp4wp_children' ) ) {

	/**
	 * Generate the Related Posts for WordPress children list
	 *
	 * @param bool $id
	 * @param bool $output
	 * @param string $template
	 * @param int $limit
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string
	 */
	function rp4wp_children( $id = false, $output = true, $template = 'related-posts-default.php', $limit = - 1 ) {

		// Get the current ID if ID not set
		if ( false === $id ) {
			$id = get_the_ID();
		}

		// Get the post type
		$post_type = get_post_type( $id );

		// Check if this Post Type is installed
		$pt_manager = new RP4WP_Post_Type_Manager();
		if ( $pt_manager->is_post_type_installed( $post_type ) && isset( RP4WP()->settings[ 'general_' . $post_type ] ) ) {

			// related Post Manager
			$related_post_manager = new RP4WP_Related_Post_Manager();

			// the Output
			$content = $related_post_manager->generate_related_posts_list( $id, $template, $limit );

			// Output or return the content
			if ( $output ) {
				echo $content;
			} else {
				return $content;
			}
		}

	}

}


if ( ! function_exists( 'rp4wp_children' ) ) {
	/**
	 * Get other templates (e.g. product attributes) passing attributes and including the file.
	 *
	 * @access public
	 *
	 * @param string $template_name
	 * @param array $args (default: array())
	 * @param string $template_path (default: '')
	 * @param string $default_path (default: '')
	 *
	 * @return void
	 */
	function rp4wp_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

		// Manager Template
		$manager_template = new RP4WP_Manager_Template();

		// Load the template output
		$manager_template->get_template( $template_name, $args, $template_path, $default_path );

	}
}

if ( ! function_exists( 'rp4wp_thumbnail' ) ) {

	/**
	 * Related Posts for WordPress thumbnail function
	 *
	 * @param int $post_id
	 * @param string $post_type
	 */
	function rp4wp_thumbnail( $post_id, $post_type ) {

		// get thumbnail
		$thumbnail = RP4WP_Thumbnail_Helper::get()->get_thumbnail( $post_id, $post_type );

		// only output $thumbnail if not empty
		if ( ! empty( $thumbnail ) ) {
			echo $thumbnail;
		}
	}
}
