<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Cli_Command_Provider {

	/**
	 * Register commands
	 */
	public function register() {
		WP_CLI::add_command( 'rp4wp', 'RP4WP_Cli_Command' );
		WP_CLI::add_command( 'rp4wp-dev', 'RP4WP_Cli_Command_Dev' );
	}

}