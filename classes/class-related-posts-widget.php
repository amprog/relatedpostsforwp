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

	/**
	 * update function.
	 *
	 * @see WP_Widget->update
	 * @access public
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['template']        = sanitize_file_name( $new_instance['template'] );
		$instance['template_custom'] = sanitize_file_name( $new_instance['template_custom'] );

		return $instance;
	}

	/**
	 * Widget form
	 *
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form( $instance ) {
		$template        = isset( $instance['template'] ) ? sanitize_file_name( $instance['template'] ) : 'widget-related-posts-default.php';
		$template_custom = isset( $instance['template_custom'] ) ? sanitize_file_name( $instance['template_custom'] ) : '';

		$template_name = esc_attr( $this->get_field_name( 'template' ) );
		$template_id   = esc_attr( $this->get_field_id( 'template' ) );
		?>
        <p>
            <strong><?php _e( 'Output template', 'related-posts-for-wp' ); ?>:</strong>

            <ul>
            <li><input type="radio" name="<?php echo $template_name; ?>" id="<?php echo $template_id; ?>1" value="widget-related-posts-default.php"<?php checked( "widget-related-posts-default.php", $template, true ); ?> /> <label for="<?php echo $template_id; ?>1">Default Widget</label></li>
            <li><input type="radio" name="<?php echo $template_name; ?>" id="<?php echo $template_id; ?>2" value="related-posts-default.php"<?php checked( "related-posts-default.php", $template, true ); ?> /> <label for="<?php echo $template_id; ?>2">Styling Configurator</label></li>
            <li><input type="radio" name="<?php echo $template_name; ?>" id="<?php echo $template_id; ?>3" value="custom"<?php checked( "custom", $template, true ); ?> /> <label for="<?php echo $template_id; ?>3">Custom: <input type="text" name="<?php echo esc_attr( $this->get_field_name( 'template_custom' ) ); ?>" value="<?php echo esc_attr( $template_custom ); ?>" placeholder="" /></label></li>
            </ul>
        </p>
		<?php
	}

	public function widget( $args, $instance ) {

		// Not on frontpage please
		if ( is_front_page() && false === is_page() ) {
			return;
		}

		// get template
		$template = ! empty( $instance['template'] ) ? sanitize_file_name( $instance['template'] ) : 'widget-related-posts-default.php';
		if ( "custom" == $template ) {
			$template = ! empty( $instance['template_custom'] ) ? sanitize_file_name( $instance['template_custom'] ) : 'widget-related-posts-default.php';
		}

		// Get content
		$widget_content = rp4wp_children( get_the_ID(), false, $template );

		// Only display if there's content
		if ( '' != $widget_content ) {
			// Output the widget
			echo $args['before_widget'];
			echo $widget_content;
			echo $args['after_widget'];
		}

	}
}