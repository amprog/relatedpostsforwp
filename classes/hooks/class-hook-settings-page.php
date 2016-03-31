<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Settings_Page extends RP4WP_Hook {
	protected $tag = 'admin_menu';

	/**
	 * Hook callback, add the sub menu page
	 *
	 * @since  1.1.0
	 * @access public
	 */
	public function run() {

		$parent = 'options-general.php';
		foreach ( RP4WP()->settings as $page => $sections ) {
			$menu_hook = add_submenu_page( $parent, __( 'Related Posts', 'related-posts-for-wp' ), __( 'Related Posts', 'related-posts-for-wp' ), 'manage_options', 'rp4wp_' . $page, array(
				$this,
				'screen'
			) );

			add_action( 'load-' . $menu_hook, array( $this, 'enqueue_assets' ) );

			$parent = null;
		}

	}

	/**
	 * Enqueue settings page assets
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'rp4wp-settings-css', plugins_url( '/assets/css/settings.css', RP4WP::get_plugin_file() ) );
		add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
	}

	/**
	 * The sidebar
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function sidebar() {
		?>
		<div class="rp4wp-sidebar">

			<div class="rp4wp-box">
				<div class="rp4wp-sidebar-header">
					<h3>Related Posts for WordPress</h3>
				</div>

				<p><?php _e( 'Plugin version', 'related-posts-for-wp' ); ?>: <?php echo RP4WP::VERSION; ?></p>
			</div>

			<div class="rp4wp-box">
				<h3 class="rp4wp-title">Looking for support?</h3>

				<p><?php printf( __( 'Got a question? Simply send us an email at %ssupport@relatedpostsforwp.com%s. Please note that support requires an active license.', 'related-posts-for-wp' ), '<a href="mailto:support@relatedpostsforwp.com">', '</a>' ); ?></p>
			</div>

			<div class="rp4wp-box">
				<h3 class="rp4wp-title"><?php _e( 'More information', 'related-posts-for-wp' ); ?></h3>

				<p><?php printf( __( "<a href='%s'>Documentation</a>", 'related-posts-for-wp' ), 'https://www.relatedpostsforwp.com/documentation/' ); ?></p>

				<p><?php printf( __( "<a href='%s'>Changelog</a>", 'related-posts-for-wp' ), 'https://www.relatedpostsforwp.com/changelog/' ); ?></p>

				<p><?php printf( __( "<a href='%s'>Give us a review</a>", 'related-posts-for-wp' ), 'http://wordpress.org/support/view/plugin-reviews/related-posts-for-wp' ); ?></p>

				<p><a href="http://www.never5.com/" target="_blank"><?php _e( "Check out our other plugins at Never5.com", 'related-posts-for-wp' ); ?></a></p>

			</div>

			<div class="rp4wp-box">
				<h3 class="rp4wp-title"><?php _e( 'About Never5', 'related-posts-for-wp' ); ?></h3>

				<a href="http://www.never5.com" target="_blank"><img src="<?php echo plugins_url( '/assets/images/never5-logo.png', RP4WP::get_plugin_file() ); ?>" alt="Never5" style="float:left;padding:0 10px 10px 0;" /></a>

				<p><?php printf( __( 'At %sNever5%s we create high quality premium WordPress plugins, with extensive support. We offer solutions in related posts, advanced download management, vehicle management and connecting post types.', 'related-posts-for-wp'), '<a href="http://www.never5.com" target="_blank">', '</a>' ); ?></p>

				<p><?php printf( __( "%sFollow Never5 on Twitter%s", 'related-posts-for-wp' ), '<a href="https://twitter.com/Never5Plugins" target="_blank">', '</a>' ); ?></p>
			</div>

		</div>
	<?php
	}

	/**
	 * Display the settings tab
	 *
	 * @param $cur_page
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 */
	private function tabs( $cur_page ) {

		// New copy to local var
		$settings_copy = RP4WP()->settings;

		// Get first tab
		$first_tab = array_shift( $settings_copy );

		// Tabs
		$tabs = array(
			$first_tab->get_page() => __( 'General', 'related-posts-for-wp' )
		);

		// New copy to local var
		$settings_copy = RP4WP()->settings;

		// Add all non general setting pages as tabs
		if ( count( $settings_copy ) > 0 ) {
			foreach ( $settings_copy as $sc_key => $sc_val ) {
				if ( false === strpos( $sc_key, 'general_' ) ) {
					$tabs[$sc_val->get_page()] = $sc_val->get_title();
				}
			}

		}

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab_key => $tab_label ) {
			echo '<a href="' . admin_url( 'options-general.php?page=' . $tab_key ) . '" class="nav-tab ' . ( ( $cur_page == $tab_key ) ? 'nav-tab-active' : '' ) . '">' . $tab_label . '</a>';
		}
		echo '<a href="' . admin_url( '?page=rp4wp_install&rp4wp_nonce=' . wp_create_nonce( RP4WP_Constants::NONCE_INSTALL ) ) . '" class="nav-tab">' . __( 'Installer', 'related-posts-for-wp' ) . '</a>';
		echo '</h2>' . PHP_EOL;
	}

	/**
	 * Print the post type method
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 */
	private function post_type_sections( $cur_page ) {

		// New copy to local var
		$settings_copy = RP4WP()->settings;

		// Add switcher
		if ( count( $settings_copy ) > 0 ) {

			echo '<div class="rp4wp-post-type-switcher">' . PHP_EOL;
			echo '<ul class="subsubsub">' . PHP_EOL;

			echo '<li class="label">' . __( 'Post Type', 'related-posts-for-wp' ) . ':</li>' . PHP_EOL;

			$first = true;
			foreach ( $settings_copy as $sc_key => $sc_val ) {
				if ( 0 === strpos( $sc_key, 'general_' ) ) {
					$post_type = substr( $sc_key, 8 );
					$pt_object = get_post_type_object( $post_type );
					if ( null != $pt_object ) {
						echo '<li>' . ( ( ! $first ) ? ' | ' : '' ) . '<a href="' . admin_url( 'options-general.php?page=rp4wp_' . $sc_key ) . '"' . ( ( 'rp4wp_general_' . $post_type == $cur_page ) ? 'class="current"' : '' ) . '>' . $pt_object->labels->name . '</a></li>' . PHP_EOL;
						$first = false;
					}
				}
			}

			echo '</ul>' . PHP_EOL;
			echo '</div>' . PHP_EOL;
		}

	}

	/**
	 * Settings screen output
	 *
	 * @since  1.1.0
	 * @access public
	 */
	public function screen() {

		// The current page
		$cur_page = sanitize_text_field( $_GET['page'] );

		$page_title   = '';
		$settings_key = substr( $cur_page, 6 );
		if ( isset( RP4WP()->settings[$settings_key] ) ) {
			$page_title = RP4WP()->settings[$settings_key]->get_title();
		}

		?>
		<div class="wrap">
			<?php $this->tabs( $cur_page ); ?>



			<div class="rp4wp-content">
				<?php
				if ( false !== strstr( $settings_key, 'general_' ) ) {
					$this->post_type_sections( $cur_page );
				}
				?>
				<h2><?php echo $page_title; ?></h2>

				<form method="post" action="options.php" id="rp4wp-settings-form">
					<?php settings_fields( $cur_page ); // pass slug name of page, also referred to in Settings API as option group name
					do_settings_sections( $cur_page );    // pass slug name of page
					submit_button( RP4WP()->settings[$settings_key]->get_button_title() );
					?>
				</form>
			</div>
			<?php $this->sidebar(); ?>
		</div>
	<?php
	}
}