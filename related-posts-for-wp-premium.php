<?php
/*
	Plugin Name: Related Posts for WordPress Premium
	Plugin URI: http://www.relatedpostsforwp.com/
	Description: The best way to display related posts in WordPress.
	Version: 1.5.6
	Author: Never5
	Author URI: http://www.never5.com/
	License: GPL v3

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

function rp4wp_pro_load_plugin() {

	// Define
	define( 'RP4WP_PLUGIN_FILE', __FILE__ );

	// include files
	require_once dirname( __FILE__ ) . '/vendor/autoload_52.php';
	require_once dirname( __FILE__ ) . '/includes/functions.php';

	// Instantiate main plugin object
	RP4WP();

}

// Create object - Plugin init
add_action( 'plugins_loaded', 'rp4wp_pro_load_plugin', 10 );

//
if ( is_admin() && ! is_multisite() && ( false === defined( 'DOING_AJAX' ) || false === DOING_AJAX ) ) {

	define( 'RP4WP_PLUGIN_FILE_INSTALLER', __FILE__ );

	// include files
	require_once dirname( __FILE__ ) . '/vendor/autoload_52.php';
	require_once dirname( __FILE__ ) . '/includes/functions.php';

	// Load installer functions
	require_once plugin_dir_path( __FILE__ ) . 'includes/installer-functions.php';

	// Activation hook
	register_activation_hook( __FILE__, 'rp4wp_premium_activate_plugin' );

	// Deactivation hook
	register_deactivation_hook( __FILE__, 'rp4wp_premium_deactivate_plugin' );
}
