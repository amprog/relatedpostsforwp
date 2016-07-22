<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Shortcode extends RP4WP_Hook {
	protected $tag = 'init';

	public function run() {
		add_shortcode( 'rp4wp', array( $this, 'output' ) );
	}

	/**
	 * Output the shortcode
	 *
	 * @param array $atts
	 *
	 * @since  1.3.0
	 * @access public
	 *
	 * @return string
	 */
	public function output( $atts ) {

		// get shortcode attributes
		$atts = shortcode_atts( array(
			'id'       => get_the_ID(),
			'limit'    => - 1,
			'template' => 'related-posts-default.php',
		), $atts );

		// output children
		return rp4wp_children( $atts['id'], false, $atts['template'], $atts['limit'] );
	}
}