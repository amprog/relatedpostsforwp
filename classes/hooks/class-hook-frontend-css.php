<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Frontend_Css extends RP4WP_Hook {
	protected $tag = 'wp_head';

	public function run() {
		global $post;

		// Only run on single
		if ( is_singular() && false === apply_filters( 'rp4wp_disable_css', false ) ) {

			// Check if the post type is installed
			$pt_manager = new RP4WP_Post_Type_Manager();
			if ( $pt_manager->is_post_type_installed( $post->post_type ) ) {

				// get component related css
				$component_manager = new RP4WP_Manager_Component();
				$css = $component_manager->get_component_css();

				// output css
				if ( '' != $css ) {
					echo "<!-- Related Posts for WP Premium CSS -->
<style type='text/css'>
" . $css . "
</style>" . PHP_EOL;
				}
			}
		}

	}
}