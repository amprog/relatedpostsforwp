<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Related_Posts_Widget extends WP_Widget {

	public function __construct() {
		// Parent construct
		parent::__construct(
			'rp4wp_related_posts_widget',
			__( 'Related Posts for WordPress', 'related-posts-for-wp' ),
			array( 'description' => __( 'Display related posts.', 'related-posts-for-wp' ) )
		);
	}

	public function widget( $args, $instance ) {

		// Not on frontpage please
		if ( is_front_page() && false === is_page() ) {
			return;
		}

		// Post Link Manager
		$pl_manager = new RP4WP_Post_Link_Manager();

		// Get content
		$widget_content = rp4wp_children( get_the_ID(), false, 'widget-related-posts-default.php' );

		// Only display if there's content
		if ( '' != $widget_content ) {
			// Output the widget
			echo $args['before_widget'];
			echo $widget_content;
			echo $args['after_widget'];
		}

	}
}