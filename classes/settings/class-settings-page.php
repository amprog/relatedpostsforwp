<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class RP4WP_Settings
 *
 * @todo Make class for each input type with own sanitize method.
 */
abstract class RP4WP_Settings_Page {

	const PREFIX = 'rp4wp_';

	private $page;
	private $title;
	private $button_title;

	protected $sections;
	private $defaults;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Set defaults
		foreach ( $this->sections as $section ) {
			foreach ( $section['fields'] as $field ) {
				$this->defaults[ $field['id'] ] = $field['default'];
			}
		}

		// Setup settings
		add_action( 'admin_init', array( $this, 'setup' ) );
	}

	/**
	 * Set the page
	 *
	 * @param $page
	 *
	 * @since  1.0.0
	 * @access public
	 */
	protected function set_page( $page ) {
		$this->page = self::PREFIX . $page;
	}

	/**
	 * Get the page
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return mixed
	 */
	public function get_page() {
		return $this->page;
	}

	/**
	 * Get the title
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Set the button title
	 *
	 * @param String $button_title
	 */
	protected function set_button_title( $button_title ) {
		$this->button_title = $button_title;
	}

	/**
	 * Get the button title
	 *
	 * @return String
	 */
	public function get_button_title() {
		return $this->button_title;
	}

	/**
	 * Set the title
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Setup the settings
	 *
	 * @since  1.1.0
	 * @access public
	 */
	public function setup() {
		if ( count( $this->sections ) > 0 ) {

			foreach ( $this->sections as $section ) {

				// Add the section
				add_settings_section(
					self::PREFIX . $section['id'],
					( isset( $section['label'] ) ? $section['label'] : '' ),
					array( $this, 'section_intro' ),
					$this->page
				);

				// Check & Loop
				if ( count( $section['fields'] ) > 0 ) {
					foreach ( $section['fields'] as $field ) {

						// Add section
						add_settings_field(
							self::PREFIX . $field['id'],
							( isset( $field['label'] ) ? $field['label'] : false ),
							array( $this, 'do_field' ),
							$this->page,
							self::PREFIX . $section['id'],
							$field
						);

					}
				}

			}

			// Register section setting
			register_setting( $this->page, $this->page, array( $this, 'sanitize_option' ) );
		}
	}

	/**
	 * Method that is called when adding a section
	 *
	 * @param $section
	 *
	 * @since  1.1.0
	 * @access public
	 */
	public function section_intro( $section ) {

		// The section intro
		if ( isset( $this->sections[ $section['id'] ]['description'] ) && '' !== $this->sections[ $section['id'] ]['description'] ) {
			echo '<p>' . $this->sections[ $section['id'] ]['description'] . '</p>' . PHP_EOL;
		}

		// Print messages
		$message_handler = new RP4WP_Manager_Settings_Messages();
		$message_handler->print_messages();

		// Clear messages
		$message_handler->clear_messages();

	}

	/**
	 * Method that outputs the correct field
	 *
	 * @param $field
	 *
	 * @since  1.1.0
	 * @access public
	 */
	public function do_field( $field ) {

		// For now we just do a simple switch here, make this more OOP in future version
		switch ( $field['type'] ) {
			case 'checkbox':
				echo '<input type="checkbox" name="' . $this->page . '[' . $field['id'] . ']' . '" id="' . $field['id'] . '" value="1" ' . checked( 1, $this->get_option( $field['id'] ), false ) . ' />';
				break;
			case 'text':
				echo '<input type="text" name="' . $this->page . '[' . $field['id'] . ']' . '" id="' . $field['id'] . '" value="' . $this->get_option( $field['id'] ) . '" class="rp4wp-input-text"' . ( ( isset( $field['disabled'] ) && true == $field['disabled'] ) ? ' disabled="disabled"' : '' ) . ' />';
				break;
			case 'textarea':
				echo '<textarea name="' . $this->page . '[' . $field['id'] . ']' . '" id="' . $field['id'] . '">' . $this->get_option( $field['id'] ) . '</textarea>';
				break;
			case 'select':

				// DB value
				$db_value = $this->get_option( $field['id'] );

				// Select DOM
				echo '<select name="' . $this->page . '[' . $field['id'] . ']' . '" id="' . $field['id'] . '">';
				if ( count( $field['options'] ) > 0 ) {
					foreach ( $field['options'] as $val => $label ) {
						echo '<option value="' . $val . '"' . selected( $val, $db_value, false ) . '>' . $label . '</option>';
					}
				}
				//. $this->get_option( $field['id'] ) .
				echo '</select>';
				break;
			case 'button_link':
				echo '<a href="' . $field['href'] . '" class="button">' . $field['default'] . '</a>';
				break;
			case 'image':
				echo '<input type="text" name="' . $this->page . '[' . $field['id'] . ']' . '" id="' . $field['id'] . '" value="' . $this->get_option( $field['id'] ) . '" class="rp4wp-input-image"' . ( ( isset( $field['disabled'] ) && true == $field['disabled'] ) ? ' disabled="disabled"' : '' ) . ' placeholder="' . __( 'Image URL', 'related-posts-for-wp' ) . '" />';
				echo '<a href="javascript:;" class="button" onclick="rp4wpUploadImage(\'' . $field['id'] . '\');">' . __( 'Upload Image', 'related-posts-for-wp' ) . '</a>';
				break;
			case 'weight_selector':
				$this->weight_selector( $field );
				break;
			case 'license_status':
				$this->license_status( $field );
				break;
			case 'configurator':
				$this->configurator( $field );
				break;
		}

		// Description
		if ( isset( $field['description'] ) && '' != $field['description'] ) {
			echo '<label class="rp4wp-description rp4wp-description-' . $field['type'] . '" for="' . $field['id'] . '">' . $field['description'] . '</label>';
		}

		// Check if this option is being filtered
		if ( has_filter( $this->page . '_' . $field['id'] ) ) {
			echo '<small>This option is overwritten by a filter.</small>';
		}

		// End of line
		echo PHP_EOL;
	}

	/**
	 * Sanitize the option value
	 *
	 * @param array $post_data
	 *
	 * @since  1.1.0
	 * @access public
	 *
	 * @return array
	 */
	public function sanitize_option( $post_data ) {

		/**
		 * @todo When options are moved to a more OOP setup, each input class will have their own sanitization method.
		 */

		if ( false !== strstr( $this->page, 'rp4wp_general_' ) ) {
			// Unset automatic_linking if not set in post
			if ( ! isset( $post_data['automatic_linking'] ) ) {
				$post_data['automatic_linking'] = 0;
			}

			// automatic_linking must be an integer
			if ( isset( $post_data['automatic_linking'] ) ) {
				$post_data['automatic_linking'] = intval( $post_data['automatic_linking'] );
			}

			// automatic_linking_post_amount must be an integer
			if ( isset( $post_data['automatic_linking_post_amount'] ) ) {
				$post_data['automatic_linking_post_amount'] = intval( $post_data['automatic_linking_post_amount'] );
			}

			// Excerpt length must be an integer
			if ( isset( $post_data['excerpt_length'] ) ) {
				$post_data['excerpt_length'] = intval( $post_data['excerpt_length'] );
			}
		} else if ( false !== strstr( $this->page, 'rp4wp_license' ) ) {

			// License sanitize callback

			try {

				$plugin_slug = str_replace( '.php', '', basename( RP4WP_PLUGIN_FILE ) );

				// Try to activate the license
				if ( false == RP4WP_Updater_Key_API::is_activated() ) {

					$license_key = sanitize_text_field( $post_data['licence_key'] );
					$email       = sanitize_text_field( $post_data['email'] );

					if ( empty( $license_key ) ) {
						throw new Exception( 'Please enter your license key.' );
					}

					if ( empty( $email ) ) {
						throw new Exception( 'Please enter the email address associated with your license.' );
					}

					$activate_results = json_decode( RP4WP_Updater_Key_API::activate( array(
						'email'          => $email,
						'license_key'    => $license_key,
						'api_product_id' => $plugin_slug
					) ), true );

					if ( ! empty( $activate_results['activated'] ) ) {

						// Set post data from API respond
						$post_data['licence_key'] = $license_key;
						$post_data['email']       = $email;

						// Add successful activation messages
						$message_handler = new RP4WP_Manager_Settings_Messages();
						$message_handler->add_message( 'License successfully activated', 'updated' );

						// Set local activation status to true
						RP4WP_Updater_Key_API::set_activated( true );

					} elseif ( $activate_results === false ) {

						throw new Exception( 'Connection failed to the License Key API server. Try again later.' );

					} elseif ( isset( $activate_results['error_code'] ) ) {

						throw new Exception( $activate_results['error'] );

					}

				} else {

					// Get license options
					$license_options = get_option( 'rp4wp_license', array() );

					// Throw error if there's no license key in the database
					if ( isset( $license_options['licence_key'] ) && '' != $license_options['licence_key'] ) {

						// Try to deactivate the license
						RP4WP_Updater_Key_API::deactivate( array(
							'api_product_id' => $plugin_slug,
							'license_key'    => $license_options['licence_key'],
						) );

						// Set local activation status to false
						RP4WP_Updater_Key_API::set_activated( false );

						// Set the correct license key as post data
						$post_data['licence_key'] = $license_options['licence_key'];
					}

				}


			} catch ( Exception $e ) {
				// Add exception messages as error message to message handler
				$message_handler = new RP4WP_Manager_Settings_Messages();
				$message_handler->add_message( $e->getMessage(), 'error' );

				// Set local activation status to false
				RP4WP_Updater_Key_API::set_activated( false );
			}

		} else if ( false !== strstr( $this->page, 'rp4wp_configurator' ) ) {

			// delete components transient
			delete_transient( RP4WP_Constants::TRANSIENT_COMPONENTS );

			// delete component css transient
			delete_transient( RP4WP_Constants::TRANSIENT_COMPONENT_CSS );

			// Set correct component order in configuration
			$config = json_decode( $post_data['configuration'] );
			if ( null !== $config && count( $config ) > 0 ) {

				// sort components
				usort( $config, array( $this, 'sort_components' ) );

				$post_data['configuration'] = json_encode( $config );

			}

			// Remove tags from custom CSS
			if ( '' != $post_data['css'] ) {
				$post_data['css'] = strip_tags( $post_data['css'] );
			}


		} else if ( false !== strstr( $this->page, 'rp4wp_words' ) ) {

			// delete joined words transient
			delete_transient( RP4WP_Constants::TRANSIENT_JOINED_WORDS );

			// delete extra ignored words transient
			delete_transient( RP4WP_Constants::TRANSIENT_EXTRA_IGNORED_WORDS );

		}

		return $post_data;
	}

	/**
	 * Sort the components
	 *
	 * @param stdClass $a
	 * @param stdClass $b
	 *
	 * @return bool
	 */
	public function sort_components( $a, $b ) {
		return ( ( $a->y === $b->y ) ? $a->x > $b->x : $a->y > $b->y );
	}

	/**
	 * Get the plugin options
	 *
	 * @since  1.1.0
	 * @access public
	 *
	 * @return mixed|void
	 */
	public function get_options() {
		return apply_filters( 'rp4wp_options', wp_parse_args( get_option( $this->page, array() ), $this->defaults ), $this->page );
	}

	/**
	 * Return a single option
	 *
	 * @param $option
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return mixed|bool
	 */
	public function get_option( $option ) {
		$options = $this->get_options();

		return apply_filters( $this->page . '_' . $option, isset( $options[ $option ] ) ? $options[ $option ] : false );
	}

	/**
	 * A weight slider
	 *
	 * @param $field
	 *
	 * @access private
	 */
	private function weight_selector( $field ) {

		$val = $this->get_option( $field['id'] );

		echo '<div class="rp4wp-weight-slider">' . PHP_EOL;
		echo '<input type="range" name="' . $this->page . '[' . $field['id'] . ']' . '" min="0" max="100" step="5" value="' . $val . '" list="weights-' . $field['id'] . '" />' . PHP_EOL;
		echo '<p>' . $val . '</p>' . PHP_EOL;
		echo '</div>' . PHP_EOL;
	}

	/**
	 * A license status field
	 *
	 * @param $field
	 */
	private function license_status( $field ) {

		// Get value
		$val = $this->get_option( $field['id'] );

		// The License Status
		echo '<div class="license_status"><span class="' . ( ( true == $val ) ? 'activated' : 'deactivated' ) . '">';
		if ( true == $val ) {
			_e( 'ACTIVATED', 'related-posts-for-wp' );
		} else {
			_e( 'NOT ACTIVE', 'related-posts-for-wp' );
		}
		echo '</span></div>' . PHP_EOL;
	}

	/**
	 * Configurator field
	 *
	 * @param $field
	 */
	private function configurator( $field ) {

		// Value
		echo '<input type="hidden" name="' . $this->page . '[' . $field['id'] . ']' . '" id="' . $field['id'] . '" value="' . esc_html( $this->get_option( $field['id'] ) ) . '" class="rp4wp-config" />';
		?>

		<div class="rp4wp-configurator-wrapper">

			<div class="rp4wp-conf-box rp4wp-conf-box-left">
				<div class="rp4wp-conf-box-name">
					<h3>Configuration</h3>
				</div>
				<div class="rp4wp-configurator"></div>
			</div>

			<div class="rp4wp-conf-box rp4wp-conf-box-right">
				<div class="rp4wp-conf-box-name">
					<h3>Components</h3>
				</div>
				<div class="rp4wp-conf-box-description">
					<p>Add new components by clicking the links below. Don't be afraid to play around, you can remove
						them just as easy. No hard feelings.</p>
				</div>
				<div class="rp4wp-components">
					<ul>
						<li><a href="javascript:;" data-type="title">Add Post Title</a></li>
						<li><a href="javascript:;" data-type="excerpt">Add Post Excerpt</a></li>
						<li><a href="javascript:;" data-type="image">Add Post Image</a></li>
						<li><a href="javascript:;" data-type="custom">Add Custom Text</a></li>
						<li><a href="javascript:;" data-type="meta">Add Post Meta</a></li>
					</ul>
				</div>
			</div>

			<div class="rp4wp-conf-box rp4wp-conf-box-right">
				<div class="rp4wp-conf-box-name">
					<h3>Presets</h3>
				</div>
				<div class="rp4wp-conf-box-description">
					<p>Presets are configurations created by us ready for to use on your website. You can also use them
						as a base and customize them to your liking.</p>
				</div>
				<div class="rp4wp-presets">
					<ul>
						<li><a href="javascript:;" data-config='{ "height" : 250, "ppr" : 2, "config" : [{"type":"title","x":0,"y":0,"width":2,"height":1},{"type":"image","x":0,"y":1,"width":2,"height":2},{"type":"excerpt","x":0,"y":3,"width":2,"height":2}] }'>The Blog</a></li>
						<li><a href="javascript:;" data-config='{ "height" : 150, "ppr" : 3, "config" : [{"type":"image","x":0,"y":0,"width":2,"height":3},{"type":"title","x":0,"y":3,"width":2,"height":2}] }'>Petjack</a></li>
						<li><a href="javascript:;" data-config='{ "height" : 75, "ppr" : 4, "config" : [{"type":"title","x":0,"y":0,"width":2,"height":5}] }'>Compact</a></li>
						<li><a href="javascript:;" data-config='{ "height" : 200, "ppr" : 1, "config" : [{"type":"title","x":0,"y":0,"width":1,"height":2},{"type":"excerpt","x":1,"y":0,"width":1,"height":5},{"type":"image","x":0,"y":2,"width":1,"height":3}] }'>Hipster</a></li>
					</ul>
				</div>
			</div>

		</div>
	<?php
	}

}