<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Settings_Configurator extends RP4WP_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Set the page
		$this->set_page( 'configurator' );

		// Set the title
		$this->set_title( __( 'Styling', 'related-posts-for-wp' ) );

		// The fields
		$this->sections = array(
			self::PREFIX . 'configuration' => array(
				'id'          => 'configuration',
				'description' => __( 'Full control on how your related posts are displayed.', 'related-posts-for-wp' ),
				'fields'      => array(
					array(
						'id'      => 'configuration',
						'type'    => 'configurator',
						'default' => '[{"type":"title","x":0,"y":0,"width":2,"height":1},{"type":"image","x":0,"y":1,"width":2,"height":2},{"type":"excerpt","x":0,"y":3,"width":2,"height":2}]',
					),
					array(
						'id'          => 'posts_per_row',
						'label'       => __( 'Posts per row', 'related-posts-for-wp' ),
						'description' => __( 'The amount of related posts per row.', 'related-posts-for-wp' ),
						'type'        => 'select',
						'options'     => array(
							1 => '1',
							2 => '2',
							3 => '3',
							4 => '4'
						),
						'default'     => '2',
					),
					array(
						'id'          => 'fixed_height',
						'label'       => __( 'Row fixed height', 'related-posts-for-wp' ),
						'description' => __( 'The fixed height per row in pixels, set to 0 to allow dynamic heights..', 'related-posts-for-wp' ),
						'type'        => 'text',
						'default'     => '325',
					),
					array(
						'id'          => 'css',
						'label'       => __( 'Custom CSS', 'related-posts-for-wp' ),
						'description' => __( 'Add custom CSS to selected theme. Warning! This is an advanced feature! An error here will break frontend display. To disable, leave field empty.', 'related-posts-for-wp' ),
						'type'        => 'textarea',
						'default'     => '',
					),
				)
			),
		);

		// Parent constructor
		parent::__construct();

	}

}