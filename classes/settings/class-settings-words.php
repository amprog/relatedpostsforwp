<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Settings_Words extends RP4WP_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Set the page
		$this->set_page( 'words' );

		// Set the title
		$this->set_title( __( 'Words', 'related-posts-for-wp' ) );

		// The fields
		$this->sections = array(
			self::PREFIX . 'words' => array(
				'id'          => 'words',
				'label'       => __( 'Word related options', 'related-posts-for-wp' ),
				'description' => __( "Control what extra words should be excluded and what word combinations should be parsed as a single word.", 'related-posts-for-wp' ),
				'fields'      => array(
					array(
						'id'          => 'ignored_words',
						'label'       => __( 'Extra Ignored Words', 'related-posts-for-wp' ),
						'description' => sprintf( __( 'By default we already ignore a lot of words based on your language that are not related to your content. In English this would be words like %s and %s.', 'related-posts-for-wp' ), '<code>and</code>', '<code>or</code>' ) . '<br/>' . __( 'Add the extra words you would to ignore here, one word per row.', 'related-posts-for-wp' ),
						'type'        => 'textarea',
						'default'     => '',
					),
					array(
						'id'          => 'joined_words',
						'label'       => __( 'Joined Words', 'related-posts-for-wp' ),
						'description' => sprintf( __( 'By default we split content per word but some word combinations should be considered as one word. An example of this would be %s.', 'related-posts-for-wp' ), '<code>iPhone 6</code>' ) . '<br/>' . __( 'Add your joined words here, one joined word per row.', 'related-posts-for-wp' ),
						'type'        => 'textarea',
						'default'     => '',
					),
				) ),
		);

		// Parent constructor
		parent::__construct();

	}

}