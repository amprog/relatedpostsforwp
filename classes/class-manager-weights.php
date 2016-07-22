<?php

class RP4WP_Manager_Weights {

	private $weight_types = array( 'title', 'tag', 'cat', 'custom_tax', 'link' );

	/**
	 * Get the weight of a type
	 *
	 * @param $type
	 *
	 * @return int
	 */
	public function get_weight( $type ) {
		$weight = 1;
		if ( in_array( $type, $this->weight_types ) ) {
			$weight = apply_filters( 'rp4wp_weight_' . $type, RP4WP()->settings['weights']->get_option( 'weight_' . $type ) );
		}

		return $weight;
	}

}