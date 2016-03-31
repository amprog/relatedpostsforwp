<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Manager_Component {

	/**
	 * Create component wrapper for components array
	 *
	 * @param $component_wrapper
	 *
	 * @return stdClass
	 */
	private function create_wrapper_component_object( $component_wrapper ) {

		if ( 0 == count( $component_wrapper->get_components() ) ) {
			return null;
		}

		$wrapper_component             = new stdClass();
		$wrapper_component->id         = $component_wrapper->get_id();
		$wrapper_component->pos        = $component_wrapper->get_pos();
		$wrapper_component->type       = 'wrapper';
		$wrapper_component->width      = 1;
		$wrapper_component->height     = $component_wrapper->get_height();
		$wrapper_component->components = $component_wrapper->get_components();

		return $wrapper_component;
	}

	/**
	 * Get the components
	 *
	 * @return array
	 */
	public function get_components() {

		// get transient
		$components = get_transient( RP4WP_Constants::TRANSIENT_COMPONENTS );

		// check if transient isset
		if ( false === $components ) {

			// empty components array
			$components = array();

			// fetch db components
			$db_components = json_decode( RP4WP::get()->settings['configurator']->get_option( 'configuration' ) );

			if ( $db_components != null ) {

				if ( count( $db_components ) > 0 ) {

					// left and right wrapper
					$left_wrapper  = new RP4WP_Component_Wrapper( 0, 'left' );
					$right_wrapper = new RP4WP_Component_Wrapper( 1, 'right' );

					// count used for component ids so we can style them dynamically
					$component_count = 2;

					// loop through database components
					foreach ( $db_components as $db_component ) {

						// set component id
						$db_component->id = $component_count;

						// increment component id
						$component_count ++;

						// check if the component if 50% width
						if ( 1 == $db_component->width ) {
							if ( 0 == $db_component->x ) {
								// add the left wrapper is component is positioned left
								$left_wrapper->add_component( $db_component );
							} else {
								// add the right wrapper is component is positioned right
								$right_wrapper->add_component( $db_component );
							}
						} else {

							/**
							 * we reached a full with component so add the wrappers to the components array
							 */

							// add left wrapper
							$left_component_wrapper_object = $this->create_wrapper_component_object( $left_wrapper );
							if ( null !== $left_component_wrapper_object ) {
								$components[] = $left_component_wrapper_object;
								$left_wrapper = new RP4WP_Component_Wrapper( $component_count, 'left' );
								$component_count ++;
							}

							// add right wrapper
							$right_component_wrapper_object = $this->create_wrapper_component_object( $right_wrapper );
							if ( null !== $right_component_wrapper_object ) {
								$components[]  = $right_component_wrapper_object;
								$right_wrapper = new RP4WP_Component_Wrapper( $component_count, 'right' );
								$component_count ++;
							}

							// add the full width component
							$components[] = $db_component;
						}

					}

					// add left wrapper to components if it contains components
					$left_component_wrapper_object = $this->create_wrapper_component_object( $left_wrapper );
					if ( null !== $left_component_wrapper_object ) {
						$components[] = $left_component_wrapper_object;
					}

					// add right wrapper to components if it contains components
					$right_component_wrapper_object = $this->create_wrapper_component_object( $right_wrapper );
					if ( null !== $right_component_wrapper_object ) {
						$components[] = $right_component_wrapper_object;
					}

				}

			}

			// set transient
			set_transient( RP4WP_Constants::TRANSIENT_COMPONENTS, $components );
		}

		// return components
		return $components;
	}

	/**
	 * Get the component related CSS
	 *
	 * @return string
	 */
	public function get_component_css() {

		// get css from transient
		$css = get_transient( RP4WP_Constants::TRANSIENT_COMPONENT_CSS );

		// check if transient is set
		if ( false === $css ) {
			$css = '';

			// load configuration
			$components = $this->get_components();

			// check
			if ( count( $components ) > 0 ) {

				// Get options
				$cols         = RP4WP::get()->settings['configurator']->get_option( 'posts_per_row' );
				$fixed_height = RP4WP::get()->settings['configurator']->get_option( 'fixed_height' );

				$col_width_normal = ( 100 / $cols ) + 1;
				$col_width_edges  = ( 100 / $cols ) - 1;

				// Basic component styling
				$css .= 'ul.rp4wp-posts-list {float: left;}
					.rp4wp-col {
						width: 100%;
						margin-bottom: 30px;
						list-style:none;
						box-sizing: border-box;
						overflow:hidden;
						float: left;
					}
					.rp4wp_component_wrapper {
						width: 100%;
						float: left;
					}
					.rp4wp_component {
						width: 100%;
						padding: 0 0 5%;
						box-sizing: border-box;
						float: left;
						overflow: hidden;
					}
					.rp4wp_component a {border:0;}
					.rp4wp_component_image a {display:block; height:100%} .rp4wp_component_image img {width:100%;height:100%;}
					.rp4wp_component_title a {text-decoration: none; font-weight: bold; border: 0;}

					@media (min-width: 768px) {
						.rp4wp-col {
							width: ' . $col_width_normal . '%;
							' . ( ( 0 != $fixed_height ) ? 'height:' . $fixed_height . 'px;' : '' ) . '
							padding: 0 2%;

						}
						.rp4wp-col-first {
							width: ' . $col_width_edges . '%;
							padding-left:0;
							padding-right: 2%;
						}
						.rp4wp-col-last {
							width: ' . $col_width_edges . '%;
							padding-right:0;
							padding-left: 2%;
						}
						.rp4wp_component_wrapper {
							width: 50%;
						}
						.rp4wp_component_wrapper_left {
							padding-right: 5%;
						}
						.rp4wp_component_wrapper_right {
							padding-left:5%;
						}
					}
			';

				// rtl support
				if ( is_rtl() ) {
					$css .= '
				.rp4wp-col {
					float: right;
				}

				@media (min-width: 768px) {
						.rp4wp-col-first {
							padding-right:0;
							padding-left: 2%;
						}
						.rp4wp-col-last {
							padding-left:0;
							padding-right: 2%;
						}
						.rp4wp_component_wrapper_left {
							padding-left: 5%;
							padding-right: 0;
						}
						.rp4wp_component_wrapper_right {
							padding-right:5%;
							padding-left: 0;
						}
					}
				';
				}

				// Loop through components
				foreach ( $components as $component ) {
					//	width: ' . ( 50 * $component->width ) . '%;

					// add component with the correct height
					$css .= '.rp4wp_component_' . $component->id . '{
					height: ' . ( 20 * $component->height ) . '%;
				}';

					// add dynamic height for components in wrapper
					if ( 'wrapper' == $component->type ) {
						foreach ( $component->components as $inner_component ) {
							$css .= '.rp4wp_component_' . $inner_component->id . '{
								height: ' . ( ( 100 / $component->height ) * $inner_component->height ) . '%;
						}';
						}
					}
				}

				// Load & add custom CSS
				$custom_css = RP4WP::get()->settings['configurator']->get_option( 'css' );
				if ( '' !== $custom_css ) {
					$css .= $custom_css;
				}

				// Output the CSS
				$css = trim( $css );
			}

			// set transient
			set_transient( RP4WP_Constants::TRANSIENT_COMPONENT_CSS, $css );
		}

		return $css;
	}

	/**
	 * Load component template
	 *
	 * @param $component
	 * @param $related_post
	 * @param $excerpt_length
	 * @param string $parent
	 */
	public function load_component_template( $component, $related_post, $excerpt_length, $parent ) {

		$manager_template = new RP4WP_Manager_Template();

		echo '<div class="rp4wp_component rp4wp_component_' . $component->type . ' rp4wp_component_' . $component->id . '">';
		$manager_template->get_template( 'components/' . $component->type . '.php', array(
			'related_post'   => $related_post,
			'excerpt_length' => $excerpt_length,
			'custom'         => isset( $component->custom ) ? $component->custom : '',
			'parent'         => $parent
		) );
		echo '</div>';
	}

}