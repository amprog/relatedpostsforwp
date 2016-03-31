<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Ajax_Install_Save_Options extends RP4WP_Hook {
	protected $tag = 'wp_ajax_rp4wp_install_save_options';

	public function run() {

		// check nonce
		check_ajax_referer( RP4WP_Constants::NONCE_AJAX, 'nonce' );

		// get the rel amount
		$rel_amount = isset( $_POST['rel_amount'] ) ? $_POST['rel_amount'] : 3;

		// get the related post age
		$post_age = isset( $_POST['rp4wp_related_posts_age'] ) ? $_POST['rp4wp_related_posts_age'] : 0;

		// check if Post Type is set
		if ( ! isset( $_POST['pt'] ) ) {
			echo 'No Post Type set!';
			exit;
		}

		// post Type
		$post_type = $_POST['pt'];

		if ( isset( RP4WP::get()->settings[ 'general_' . $post_type ] ) ) {
			// set the correct options from step 3
			$options                                  = RP4WP::get()->settings[ 'general_' . $post_type ]->get_options();
			$options['automatic_linking_post_amount'] = $rel_amount;
			$options['max_post_age']                  = $post_age;
			update_option( 'rp4wp_general_' . $post_type, $options );
		}

		// success
		echo 'success';

		exit;
	}

}