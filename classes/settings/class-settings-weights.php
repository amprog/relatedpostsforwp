<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Settings_Weights extends RP4WP_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Set the page
		$this->set_page( 'weights' );

		// Set the title
		$this->set_title( __( 'Weights', 'related-posts-for-wp' ) );

		// The fields
		$this->sections = array(
			self::PREFIX . 'weights' => array(
				'id'          => 'weights',
				'label'       => __( 'Weight settings', 'related-posts-for-wp' ),
				'description' => __( "Easily adjust the weights by using the sliders below. Please note that you need to rerun the installer after changing weights.", 'related-posts-for-wp' ),
				'fields'      => array(
					array(
						'id'          => 'weight_reset',
						'label'       => __( 'Reset Weight', 'related-posts-for-wp' ),
						'description' => __( "Reset the weights back to their defaults. Don't forget to Save the Changes after resetting the weights.", 'related-posts-for-wp' ),
						'type'        => 'button_link',
						'href'        => 'javascript:RP4WP_Weight_Reset();',
						'default'     => __( 'Reset Weight', 'related-posts-for-wp' ),
					),
					array(
						'id'          => 'weight_title',
						'label'       => __( 'Title', 'related-posts-for-wp' ),
						'description' => __( 'The weight of the title.', 'related-posts-for-wp' ),
						'type'        => 'weight_selector',
						'default'     => '80',
					),
					array(
						'id'          => 'weight_link',
						'label'       => __( 'Links', 'related-posts-for-wp' ),
						'description' => __( 'The weight of the links found in the content.', 'related-posts-for-wp' ),
						'type'        => 'weight_selector',
						'default'     => '20',
					),
					array(
						'id'          => 'weight_cat',
						'label'       => __( 'Categories', 'related-posts-for-wp' ),
						'description' => __( 'The weight of the categories.', 'related-posts-for-wp' ),
						'type'        => 'weight_selector',
						'default'     => '20',
					),
					array(
						'id'          => 'weight_tag',
						'label'       => __( 'Tags', 'related-posts-for-wp' ),
						'description' => __( 'The weight of the tags.', 'related-posts-for-wp' ),
						'type'        => 'weight_selector',
						'default'     => '10',
					),
					array(
						'id'          => 'weight_custom_tax',
						'label'       => __( 'Custom Taxonomies', 'related-posts-for-wp' ),
						'description' => __( 'The weight of custom taxonomies.', 'related-posts-for-wp' ),
						'type'        => 'weight_selector',
						'default'     => '15',
					),
				) ),
		);

		// Parent constructor
		parent::__construct();

	}

}
 