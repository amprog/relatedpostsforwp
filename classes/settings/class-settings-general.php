<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Settings_General extends RP4WP_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct( $post_type ) {

		// Set the page
		$this->set_page( 'general_' . $post_type );

		// Set the title
		$this->set_title( sprintf( __( 'General settings for %s', 'related-posts-for-wp' ), $post_type ) );

		// The fields
		$this->sections = array(
			self::PREFIX . 'automatic_linking'     => array(
				'id'          => 'automatic_linking',
				'label'       => sprintf( __( 'Automatic %ss linking', 'related-posts-for-wp' ), $post_type ),
				'description' => sprintf( __( 'The following options affect how related posts are automatically linked for %ss.', 'related-posts-for-wp' ), $post_type ),
				'fields'      => array(
					array(
						'id'          => 'automatic_linking',
						'label'       => __( 'Enable', 'related-posts-for-wp' ),
						'description' => sprintf( __( 'Checking this will enable automatically linking posts to new %ss', 'related-posts-for-wp' ), $post_type ),
						'type'        => 'checkbox',
						'default'     => 1,
					),
					array(
						'id'          => 'automatic_linking_post_amount',
						'label'       => __( 'Amount of Posts', 'related-posts-for-wp' ),
						'description' => sprintf( __( 'The amount of automatically linked %ss', 'related-posts-for-wp' ), $post_type ),
						'type'        => 'text',
						'default'     => '3',
					),
					array(
						'id'          => 'max_post_age',
						'label'       => __( 'Maximum Post Age', 'related-posts-for-wp' ),
						'description' => sprintf( __( 'The maximum age in days of %ss that will be linked. (0 = unlimited)', 'related-posts-for-wp' ), $post_type ),
						'type'        => 'text',
						'default'     => '0',
					)
				)
			),
			self::PREFIX . 'css'                   => array(
				'id'          => 'css',
				'label'       => __( 'Frontend Settings', 'related-posts-for-wp' ),
				'description' => sprintf( __( 'The following options affect how related %ss are displayed on the frontend.', 'related-posts-for-wp' ), $post_type ),
				'fields'      => array(
					array(
						'id'          => 'heading_text',
						'label'       => __( 'Heading text', 'related-posts-for-wp' ),
						'description' => sprintf( __( 'The text that is displayed above the related %ss. To disable, leave field empty.', 'related-posts-for-wp' ), $post_type ),
						'type'        => 'text',
						'default'     => __( 'Related Posts', 'related-posts-for-wp' ),
					),
					array(
						'id'          => 'excerpt_length',
						'label'       => __( 'Excerpt length', 'related-posts-for-wp' ),
						'description' => __( 'The amount of words to be displayed below the title on website. To disable, set value to 0.', 'related-posts-for-wp' ),
						'type'        => 'text',
						'default'     => '15',
					),
				)
			),
			self::PREFIX . 'thumbnail_size'        => array(
				'id'          => 'thumbnail_size',
				'label'       => __( 'Thumbnail size', 'related-posts-for-wp' ),
				'description' => sprintf( __( 'These settings affect the display and dimensions of your related post thumbnails – the display on the front-end will still be affected by CSS styles. After changing these settings you may need to %sregenerate your thumbnails.%s', 'related-posts-for-wp' ), '<a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">', '</a>' ),
				'fields'      => array(
					array(
						'id'          => 'thumbnail_width',
						'label'       => __( 'Width', 'related-posts-for-wp' ),
						'description' => __( "Thumbnail's width in pixels.", 'related-posts-for-wp' ),
						'type'        => 'text',
						'default'     => get_option( 'thumbnail_size_w' ),
					),
					array(
						'id'          => 'thumbnail_height',
						'label'       => __( 'Height', 'related-posts-for-wp' ),
						'description' => __( "Thumbnail's height in pixels.", 'related-posts-for-wp' ),
						'type'        => 'text',
						'default'     => get_option( 'thumbnail_size_h' ),
					),
					array(
						'id'          => 'thumbnail_crop',
						'label'       => __( 'Crop', 'related-posts-for-wp' ),
						'description' => __( 'Crop the image from the center.', 'related-posts-for-wp' ),
						'type'        => 'checkbox',
						'default'     => 1,
					),
				)
			),
			self::PREFIX . 'thumbnail_placeholder' => array(
				'id'          => 'thumbnail_placeholder',
				'label'       => __( 'Thumbnail Placeholder', 'related-posts-for-wp' ),
				'description' => __( 'The placeholder will be used if no featured image is set and no images could be found in the content.', 'related-posts-for-wp' ),
				'fields'      => array(
					array(
						'id'          => 'thumbnail_placeholder',
						'label'       => __( 'Placeholder', 'related-posts-for-wp' ),
						'description' => __( "Thumbnail placeholder, the image that will be used if no other image is found.", 'related-posts-for-wp' ),
						'type'        => 'image',
						'default'     => '',
					),
				)
			)
		);

		// Parent constructor
		parent::__construct();

	}

}