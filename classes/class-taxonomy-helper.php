<?php

class RP4WP_Taxonomy_Helper {

	/**
	 * @var RP4WP_Taxonomy_Helper
	 */
	private static $instance = null;

	/**
	 * Private constructor
	 */
	private function __construct() {
		//
	}

	/**
	 * Singleton get method
	 *
	 * @return RP4WP_Thumbnail_Helper
	 */
	public static function get() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieve name from WP_Term
	 *
	 * @param WP_Term $term
	 *
	 * @return string
	 */
	public static function fetch_name_from_term( $term ) {
		return $term->name;
	}

	/**
	 * @param int $post_id
	 * @param string $taxonomy
	 *
	 * @return string
	 */
	public static function format_terms_of_post( $post_id, $taxonomy ) {
		$terms_str    = '';
		$term_objects = wp_get_post_terms( $post_id, esc_attr( $taxonomy ) );
		if ( ! empty( $term_objects ) ) {
			$terms     = array_map( array( __CLASS__, 'fetch_name_from_term' ), $term_objects );
			$terms_str = implode( ', ', $terms );
		}

		return $terms_str;
	}

}