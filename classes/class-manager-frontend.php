<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Manager_Frontend {

	/**
	 * Get the column CSS class
	 *
	 * @param $row_counter
	 *
	 * @return string
	 */
	public static function get_column_class( $row_counter ) {
		$row_class = '';

		$cols_per_row = RP4WP::get()->settings['configurator']->get_option( 'posts_per_row' );

		$mod = ( $row_counter % $cols_per_row );

		if ( 0 === $mod ) {
			$row_class .= ' rp4wp-col-first';
		}

		if ( ( $cols_per_row - 1 ) == $mod ) {
			$row_class .= ' rp4wp-col-last';
		}

		return $row_class;
	}

}