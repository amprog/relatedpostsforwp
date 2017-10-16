<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Settings_Categories extends RP4WP_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Set the page
		$this->set_page( 'categories' );

		// Set the title
		$this->set_title( __( 'Categories', 'related-posts-for-wp' ) );

		// The fields
		$this->sections = array(
			self::PREFIX . 'categories' => array(
				'id'     => 'categories',
				'fields' => array(
					array(
						'id'          => 'limit_related_categories',
						'label'       => __( 'Limit Post Categories', 'related-posts-for-wp' ),
						'description' => __( 'Limit the categories related content is selected from.', 'related-posts-for-wp' ),
						'type'        => 'checkbox',
						'default'     => 0,
					),
					array(
						'id'          => 'related_categories',
						'label'       => __( 'Select Categories', 'related-posts-for-wp' ),
						'description' => __( 'Select what categories we need to select related content from.', 'related-posts-for-wp' ),
						'type'        => 'category_list',
						'default'     => array(),
						'class'       => 'rp4wp_settings_post_related_categories',
					)
				)
			),
		);

		// Parent constructor
		parent::__construct();

	}

}