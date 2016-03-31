<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RP4WP_Updater_Key_API
 */
class RP4WP_Updater_Key_API {
	private static $endpoint = 'https://www.relatedpostsforwp.com/?wc-api=license_wp_api_activation';

	const OPTION_ACTIVATED = 'rp4wp_license_activated';

	/**
	 * Set the if license is activated
	 *
	 * @param $activated
	 */
	public static function set_activated( $activated ) {
		if ( true === $activated ) {
			update_option( self::OPTION_ACTIVATED, true );
		} else {
			delete_option( self::OPTION_ACTIVATED );
		}
	}

	/**
	 * Check if license is activated
	 *
	 * @return bool
	 */
	public static function is_activated() {
		return get_option( self::OPTION_ACTIVATED, false );
	}

	/**
	 * Attempt to activate a plugin license
	 */
	public static function activate( $args ) {
		$defaults = array(
			'request'  => 'activate',
			'instance' => site_url(),
		);

		$args = wp_parse_args( $defaults, $args );

		// SSL Verify Check
		$request_args = array();
		if ( 1 == RP4WP()->settings['misc']->get_option( 'disable_sslverify' ) ) {
			$request_args['sslverify'] = false;
		}

		$request = wp_remote_get( self::$endpoint . '&' . http_build_query( $args, '', '&' ), $request_args );

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		} else {
			return wp_remote_retrieve_body( $request );
		}
	}

	/**
	 * Attempt t deactivate a license
	 */
	public static function deactivate( $args ) {
		$defaults = array(
			'request'  => 'deactivate',
			'instance' => site_url(),
		);

		$args = wp_parse_args( $defaults, $args );

		// SSL Verify Check
		$request_args = array();
		if ( 1 == RP4WP()->settings['misc']->get_option( 'disable_sslverify' ) ) {
			$request_args['sslverify'] = false;
		}

		$request = wp_remote_get( self::$endpoint . '&' . http_build_query( $args, '', '&' ), $request_args );

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		} else {
			return wp_remote_retrieve_body( $request );
		}
	}
}