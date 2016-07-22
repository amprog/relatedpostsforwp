<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Settings_License extends RP4WP_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Set the page
		$this->set_page( 'license' );

		// Set the title
		$this->set_title( __( 'License', 'related-posts-for-wp' ) );

		$is_activated = RP4WP_Updater_Key_API::is_activated();

		// The fields
		$this->sections = array(
			self::PREFIX . 'misc' => array(
				'id'     => 'misc',
				'fields' => array(
					array(
						'id'      => 'license_status',
						'label'   => __( 'License Status', 'related-posts-for-wp' ),
						//'description' => sprintf( __( 'Your license status. You can deactivate your license on this page or in your %sMy Account%s page.', 'related-posts-for-wp' ), '<a href="https://www.relatedpostsforwp.com/my-account/" target="_blank">', '</a>' ),
						'type'    => 'license_status',
						'default' => $is_activated,
					),
					array(
						'id'          => 'licence_key',
						'label'       => __( 'License Key', 'related-posts-for-wp' ),
						'description' => sprintf( __( 'Your license key. You can find your license key in your %sMy Account%s page.', 'related-posts-for-wp' ), '<a href="https://www.relatedpostsforwp.com/my-account/" target="_blank">', '</a>' ),
						'type'        => 'text',
						'default'     => '',
						'disabled'    => $is_activated,
					),
					array(
						'id'          => 'email',
						'label'       => __( 'Activation Email', 'related-posts-for-wp' ),
						'description' => sprintf( __( 'Your activation email address. You can find your activation email address in your %sMy Account%s page.', 'related-posts-for-wp' ), '<a href="https://www.relatedpostsforwp.com/my-account/" target="_blank">', '</a>' ),
						'type'        => 'text',
						'default'     => get_option( 'admin_email' ),
						'disabled'    => $is_activated,
					),
				)
			),
		);

		if ( false == $is_activated ) {
			$this->set_button_title( 'Save and Activate License' );
		}else {
			$this->set_button_title( 'Deactivate License' );
		}

		// Parent constructor
		parent::__construct();

	}

}