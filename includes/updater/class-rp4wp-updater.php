<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RP4WP_Updater
 *
 * @version  3.0
 */
class RP4WP_Updater {
	private $plugin_name = '';
	private $plugin_file = '';
	private $plugin_slug = '';
	private $api_url = 'https://www.relatedpostsforwp.com/?wc-api=license_wp_api_update';
	private $errors = array();
	private $plugin_data = array();

	private $api_key = '';
	private $activation_email = '';

	/**
	 * Constructor, used if called directly.
	 */
	public function __construct( $file ) {
		$this->init_updates( $file );
	}

	/**
	 * Init the updater
	 *
	 * @param String $file
	 */
	public function init_updates( $file ) {
		$this->plugin_file = $file;
		$this->plugin_slug = str_replace( '.php', '', basename( $this->plugin_file ) );
		$this->plugin_name = basename( dirname( $this->plugin_file ) ) . '/' . $this->plugin_slug . '.php';

		add_filter( 'block_local_requests', '__return_false' );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Ran on WP admin_init hook
	 */
	public function admin_init() {

		$this->load_errors();

		add_action( 'shutdown', array( $this, 'store_errors' ) );
		add_action( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );

		// Get License options
		$license_options = RP4WP::get()->settings['license']->get_options();

		// Setup plugin data
		$this->plugin_data      = get_plugin_data( $this->plugin_file );
		$this->api_key          = $license_options['licence_key'];
		$this->activation_email = $license_options['email'];

		// Activated notice
		if ( ! empty( $_GET[ 'dismiss-' . sanitize_title( $this->plugin_slug ) ] ) ) {
			update_option( $this->plugin_slug . '_hide_key_notice', 1 );
		}

		if ( false == RP4WP_Updater_Key_API::is_activated() && sizeof( $this->errors ) === 0 && ! get_option( $this->plugin_slug . '_hide_key_notice' ) ) {
			add_action( 'admin_notices', array( $this, 'key_notice' ) );
		}

		add_action( 'admin_notices', array( $this, 'error_notices' ) );
	}

	/**
	 * Add an error message
	 *
	 * @param string $message Your error message
	 * @param string $type Type of error message
	 */
	public function add_error( $message, $type = '' ) {
		if ( $type ) {
			$this->errors[ $type ] = $message;
		} else {
			$this->errors[] = $message;
		}
	}

	/**
	 * Load errors from option
	 */
	public function load_errors() {
		$this->errors = get_option( $this->plugin_slug . '_errors', array() );
	}

	/**
	 * Store errors in option
	 */
	public function store_errors() {
		update_option( $this->plugin_slug . '_errors', $this->errors );
	}

	/**
	 * Output errors
	 */
	public function error_notices() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $error ) {
				?>
				<div class="error">
				<p><?php echo wp_kses_post( $error ); ?></p>
				</div>
				<?php
				// unset error
				unset( $this->errors[ $key ] );
			}
		}
	}

	/**
	 * Show a notice prompting the user to update
	 */
	public function key_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="updated">
		<p class="rp4wp-updater-dismiss" style="float:right;"><a
				href="<?php echo esc_url( add_query_arg( 'dismiss-' . sanitize_title( $this->plugin_slug ), '1' ) ); ?>"><?php _e( 'Hide notice' ); ?></a>
		</p>

		<p><?php printf( '<a href="%s">Please enter your licence key</a> to get updates for <strong>%s</strong>.', admin_url( 'options-general.php?page=rp4wp_license' ), esc_html( $this->plugin_data['Name'] ) ); ?></p>

		<p>
			<small class="description"><?php printf( 'Lost your key? <a href="%s">Manage your license here</a>.', esc_url( 'https://www.relatedpostsforwp.com/my-account/' ) ); ?></small>
		</p>
		</div><?php
	}

	/**
	 * Check for plugin updates
	 */
	public function check_for_updates( $check_for_updates_data ) {
		global $wp_version;

		// Only check for data if license is activated
		if ( false == RP4WP_Updater_Key_API::is_activated() ) {
			return $check_for_updates_data;
		}

		if ( empty( $check_for_updates_data->checked ) ) {
			return $check_for_updates_data;
		}

		$current_ver = isset( $check_for_updates_data->checked[ $this->plugin_name ] ) ? $check_for_updates_data->checked[ $this->plugin_name ] : '';

		// only continue when the plugin version is set
		if( '' == $current_ver ) {
			return $check_for_updates_data;
		}

		$args = array(
			'request'        => 'pluginupdatecheck',
			'plugin_name'    => $this->plugin_name,
			'version'        => $current_ver,
			'api_product_id' => $this->plugin_slug,
			'license_key'    => $this->api_key,
			'email'          => $this->activation_email,
			'instance'       => site_url()
		);

		// Check for a plugin update
		$response = $this->do_license_request( $args );

		if ( isset( $response->errors ) ) {
			$this->handle_errors( $response->errors );
		}

		// Set version variables
		if ( isset( $response ) && is_object( $response ) && $response !== false ) {
			// New plugin version from the API
			$new_ver = (string) $response->new_version;
		}

		// If there is a new version, modify the transient to reflect an update is available
		if ( isset( $new_ver ) ) {
			if ( $response !== false && version_compare( $new_ver, $current_ver, '>' ) ) {
				$check_for_updates_data->response[ $this->plugin_name ] = $response;
			}
		}

		return $check_for_updates_data;
	}

	/**
	 * Take over the Plugin info screen
	 */
	public function plugins_api( $false, $action, $args ) {
		global $wp_version;

		// Only take over plugin info screen if license is activated
		if ( false == RP4WP_Updater_Key_API::is_activated() ) {
			return $false;
		}

		if ( ! isset( $args->slug ) || ( $args->slug !== $this->plugin_slug ) ) {
			return $false;
		}

		// Get the current version
		$plugin_info = get_site_transient( 'update_plugins' );
		$current_ver = isset( $plugin_info->checked[ $this->plugin_name ] ) ? $plugin_info->checked[ $this->plugin_name ] : '';

		// only continue when the plugin version is set
		if( '' == $current_ver ) {
			return $false;
		}

		$args = array(
			'request'        => 'plugininformation',
			'plugin_name'    => $this->plugin_name,
			'version'        => $current_ver,
			'api_product_id' => $this->plugin_slug,
			'license_key'    => $this->api_key,
			'email'          => $this->activation_email,
			'instance'       => site_url()
		);

		// Check for a plugin update
		$response = $this->do_license_request( $args );

		if ( isset( $response->errors ) ) {
			$this->handle_errors( $response->errors );
		}

		// If everything is okay return the $response
		if ( isset( $response ) && is_object( $response ) && $response !== false ) {
			return $response;
		}
	}

	/**
	 * Handle errors from the API
	 *
	 * @param  array $errors
	 */
	public function handle_errors( $errors ) {

		// loop through errors
		foreach( $errors as $error_key => $error ) {

			// add error to WP
			$this->add_error( $error, $error_key );

			if( 'no_activation' == $error_key ) {

				// Deactivate license on server
				RP4WP_Updater_Key_API::deactivate( array(
					'api_product_id' => $this->plugin_slug,
					'license_key'    => $this->api_key,
				) );

				// Set local activation status to false
				RP4WP_Updater_Key_API::set_activated( false );

			}

		}

	}

	/**
	 * Sends and receives data to and from the server API
	 * @return object $response
	 */
	public function do_license_request( $args ) {

		// SSL Verify Check
		$request_args = array();
		if ( 1 == RP4WP()->settings['misc']->get_option( 'disable_sslverify' ) ) {
			$request_args['sslverify'] = false;
		}

		$request = wp_remote_get( $this->api_url . '&' . http_build_query( $args, '', '&' ), $request_args );

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		}

		$response = maybe_unserialize( wp_remote_retrieve_body( $request ) );

		if ( is_object( $response ) ) {
			return $response;
		} else {
			return false;
		}
	}
}