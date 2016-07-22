<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Settings_Misc extends RP4WP_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Set the page
		$this->set_page( 'misc' );

		// Set the title
		$this->set_title( __( 'Misc', 'related-posts-for-wp' ) );

		// The fields
		$this->sections = array(
			self::PREFIX . 'misc' => array(
				'id'          => 'misc',
				'label'       => __( 'Miscellaneous Settings', 'related-posts-for-wp' ),
				'description' => __( "A shelter for options that just don't fit in.", 'related-posts-for-wp' ),
				'fields'      => array(
					array(
						'id'          => 'clean_on_uninstall',
						'label'       => __( 'Remove Data on Uninstall?', 'related-posts-for-wp' ),
						'description' => __( 'Check this box if you would like to completely remove all of its data when the plugin is deleted.', 'related-posts-for-wp' ),
						'type'        => 'checkbox',
						'default'     => 0,
					),
					array(
						'id'          => 'show_love',
						'label'       => __( 'Show love?', 'related-posts-for-wp' ),
						'description' => __( "Display a 'Powered by' line under your related posts. <strong>BEWARE! Only for the real fans.</strong>", 'related-posts-for-wp' ),
						'type'        => 'checkbox',
						'default'     => 0,
					),
					array(
						'id'          => 'disable_sslverify',
						'label'       => __( 'Disable SSL Verification', 'related-posts-for-wp' ),
						'description' => __( "Disable SSL verification in license requests. Check this if you've got problems connecting to licensing server.", 'related-posts-for-wp' ),
						'type'        => 'checkbox',
						'default'     => 0,
					),
				) ),
		);

		// Parent constructor
		parent::__construct();

	}

}