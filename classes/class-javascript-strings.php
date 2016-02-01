<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'RP4WP_Javascript_Strings' ) ) {
	class RP4WP_Javascript_Strings {

		private static $value = null;

		private static function fill() {
			$js_strings = array(
				'confirm_delete_related_post' => __( 'Are you sure you want to delete this related post?', 'related-posts-for-wp' ),
				'lbl_delete'                  => __( 'Delete this post type', 'related-posts-for-wp' ),
				'lbl_relink'                  => __( 'Relink related posts', 'related-posts-for-wp' ),
				'lbl_reinstall'               => __( 'Reinstall this post type', 'related-posts-for-wp' ),
				'lbl_save'                    => __( 'Save post type', 'related-posts-for-wp' ),
				'lbl_add_pt'                  => __( 'Add post type', 'related-posts-for-wp' ),
			);

			$ptm = new RP4WP_Post_Type_Manager();

			self::$value = array_merge( $js_strings, $ptm->get_available_post_types() );
		}

		public static function get() {
			if ( self::$value === null ) {
				self::fill();
			}

			return self::$value;
		}

	}
}