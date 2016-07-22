<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Component_Wrapper {

	private $id;
	private $height;
	private $components;
	private $pos;

	/**
	 * Constructor
	 *
	 * @param int $id
	 * @param String $pos
	 */
	public function __construct( $id, $pos ) {
		$this->id         = $id;
		$this->pos        = $pos;
		$this->height     = 0;
		$this->components = array();
	}

	/**
	 * Add component
	 *
	 * @param $component
	 */
	public function add_component( $component ) {
		$this->components[] = $component;
		$this->height += $component->height;
	}

	/**
	 * Get component id
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get position
	 *
	 * @return String
	 */
	public function get_pos() {
		return $this->pos;
	}

	/**
	 * Get components
	 *
	 * @return array
	 */
	public function get_components() {
		return $this->components;
	}

	/**
	 * Check if we've got components
	 *
	 * @return bool
	 */
	public function has_components() {
		if ( count( $this->get_components() ) > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get height
	 *
	 * @return int
	 */
	public function get_height() {
		return $this->height;
	}

}