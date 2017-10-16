<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP {

	private static $instance = null;

	const VERSION = '1.7.3';

	/**
	 * @var RP4WP_Settings
	 */
	public $settings = null;

	/**
	 * Singleton get method
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return RP4WP
	 */
	public static function get() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the plugin file
	 *
	 * @access public
	 * @static
	 * @return String
	 */
	public static function get_plugin_file() {
		return RP4WP_PLUGIN_FILE;
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( self::get_plugin_file() ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'rp4wp_template_path', 'related-posts-for-wp/' );
	}

	/**
	 * The constructor
	 */
	private function __construct() {
		$this->init();

		// Init updates
		new RP4WP_Updater( RP4WP_PLUGIN_FILE );
	}

	/**
	 * Initialize the plugin
	 */
	private function init() {

		if ( ! class_exists( 'RP4WP_Updater' ) ) {
			include( plugin_dir_path( RP4WP_PLUGIN_FILE ) . '/includes/updater/class-rp4wp-updater.php' );
		}

		// Load plugin text domain
		load_plugin_textdomain( 'related-posts-for-wp', false, dirname( plugin_basename( RP4WP_PLUGIN_FILE ) ) . '/languages/' );

		// Check if we need to upgrade
		if ( is_admin() && ( false === defined( 'DOING_AJAX' ) || false === DOING_AJAX ) ) {
			$upgrade_manager = new RP4WP_Upgrade_Manager();
			add_action( 'admin_init', array( $upgrade_manager, 'check' ) );

		}

		// Check if we need to run the installer
		if ( is_admin() && get_option( RP4WP_Constants::OPTION_DO_INSTALL, false ) ) {

			// Redirect the user
			add_action( 'init', array( $this, 'start_installer' ), 1 );

			// Delete do install site option
			delete_option( RP4WP_Constants::OPTION_DO_INSTALL );
		}

		if ( is_admin() ) {
			// Check if we need to display an 'is installing' notice
			$is_installing_notice = new RP4WP_Is_Installing_Notice();
			$is_installing_notice->check();

			// check for dependencies
			$dep = new RP4WP_Dependencies();
			$dep->check();
		}

		// Setup settings
		$this->setup_settings();

		// Filters
		$filters        = include dirname( RP4WP_PLUGIN_FILE ) . '/includes/filters.php';
		$manager_filter = new RP4WP_Manager_Filter( $filters );
		$manager_filter->load_filters();

		// Hooks
		$actions      = include dirname( RP4WP_PLUGIN_FILE ) . '/includes/actions.php';
		$manager_hook = new RP4WP_Manager_Hook( $actions );
		$manager_hook->load_hooks();

		// Include template functions
		require_once( plugin_dir_path( self::get_plugin_file() ) . '/includes/template-functions.php' );

		// WP-CLI commands
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$commands = new RP4WP_Cli_Command_Provider();
			$commands->register();
		}

	}

	/**
	 * Start the plugin installation process
	 *
	 * @since  1.7.0
	 * @access public
	 *
	 */
	public function start_installer() {
		$installer = new RP4WP_Installer();
		$installer->run();
	}

	/**
	 * Setup the settings
	 *
	 * @since  1.0.0
	 * @access public
	 */
	private function setup_settings() {
		// Setup settings default array
		$this->settings = array();

		// Add a general settings page for each installed post type
		$pt_manager           = new RP4WP_Post_Type_Manager();
		$installed_post_types = $pt_manager->get_installed_post_types();
		if ( count( $installed_post_types ) ) {
			foreach ( $installed_post_types as $installed_post_type => $pt_children ) {
				$this->settings[ 'general_' . $installed_post_type ] = new RP4WP_Settings_General( $installed_post_type );
			}
		}

		// Add other settings pages
		$this->settings['configurator'] = new RP4WP_Settings_Configurator();
		$this->settings['categories']   = new RP4WP_Settings_Categories();
		$this->settings['weights']      = new RP4WP_Settings_Weights();
		$this->settings['words']        = new RP4WP_Settings_Words();
		$this->settings['license']      = new RP4WP_Settings_License();
		$this->settings['misc']         = new RP4WP_Settings_Misc();

	}

}
