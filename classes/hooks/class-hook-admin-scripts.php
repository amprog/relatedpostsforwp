<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Admin_Scripts extends RP4WP_Hook {
	protected $tag = 'admin_enqueue_scripts';

	public function run() {
		global $pagenow;

		// Post screen
		if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {

			// Load PL JS
			wp_enqueue_script(
				'rp4wp_edit_post_js',
				plugins_url( '/assets/js/edit-post' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', RP4WP::get_plugin_file() ),
				array( 'jquery', 'jquery-ui-sortable' ),
				RP4WP::VERSION
			);

			// Make JavaScript strings translatable
			wp_localize_script( 'rp4wp_edit_post_js', 'rp4wp_js', RP4WP_Javascript_Strings::get() );

			// CSS
			wp_enqueue_style(
				'rp4wp_edit_post_css',
				plugins_url( '/assets/css/edit-post.css', RP4WP::get_plugin_file() ),
				array(),
				RP4WP::VERSION
			);
		}

		if ( 'options-general.php' == $pagenow && isset( $_GET['page'] ) && 0 === strpos( $_GET['page'], 'rp4wp_' ) ) {

			// Configurator
			if ( 'rp4wp_configurator' === $_GET['page'] ) {
				
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-widget' );
				wp_enqueue_script( 'jquery-ui-mouse' );
				wp_enqueue_script( 'jquery-ui-draggable' );
				wp_enqueue_script( 'jquery-ui-resizable' );

				// Load the configurator file
				wp_enqueue_script(
					'rp4wp_configurator_js',
					plugins_url( '/assets/js/configurator/configurator.min.js', RP4WP::get_plugin_file() ),
					array(
						'jquery',
						'jquery-ui-core',
						'jquery-ui-widget',
						'jquery-ui-mouse',
						'jquery-ui-draggable',
						'jquery-ui-resizable'
					),
					RP4WP::VERSION
				);

				// Configurator CSS
				wp_enqueue_style(
					'rp4wp_configurator_css',
					plugins_url( '/assets/css/configurator.css', RP4WP::get_plugin_file() ),
					array(),
					RP4WP::VERSION
				);

			}

			// Weights JS
			if ( 'rp4wp_weights' === $_GET['page'] ) {
				wp_enqueue_script(
					'rp4wp_settings_weight_js',
					plugins_url( '/assets/js/settings-weight' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', RP4WP::get_plugin_file() ),
					array( 'jquery' ),
					RP4WP::VERSION
				);
			}

			// Main settings JS
			wp_enqueue_script(
				'rp4wp_settings_js',
				plugins_url( '/assets/js/settings' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', RP4WP::get_plugin_file() ),
				array( 'jquery' ),
				RP4WP::VERSION
			);
		}

	}
}