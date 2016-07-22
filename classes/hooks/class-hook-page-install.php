<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Hook_Page_Install extends RP4WP_Hook {
	protected $tag = 'admin_menu';

	public function run() {

		$menu_hook = add_submenu_page( null, 'RP4WPINSTALL', 'RP4WPINSTALL', 'edit_posts', 'rp4wp_install', array(
			$this,
			'content'
		) );

		add_action( 'load-' . $menu_hook, array( $this, 'enqueue_install_assets' ) );
	}

	/**
	 * Enqueue install assets
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function enqueue_install_assets() {
		global $wp_scripts;
		wp_enqueue_style( 'rp4wp-install-css', plugins_url( '/assets/css/install.css', RP4WP::get_plugin_file() ), array(), RP4WP::VERSION );
		wp_enqueue_script( 'rp4wp-install-js', plugins_url( '/assets/js/install' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', RP4WP::get_plugin_file() ), array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-progressbar'
		), RP4WP::VERSION );

		wp_enqueue_script( 'rp4wp-tipped', plugins_url( '/assets/js/lib/tipped.js', RP4WP::get_plugin_file() ), array(), RP4WP::VERSION );

		// Make JavaScript strings translatable
		wp_localize_script( 'rp4wp-install-js', 'rp4wp_js', RP4WP_Javascript_Strings::get() );

		wp_enqueue_style( 'jquery-ui-smoothness', "https://ajax.googleapis.com/ajax/libs/jqueryui/" . $wp_scripts->query( 'jquery-ui-core' )->ver . "/themes/smoothness/jquery-ui.css", false, null );
	}

	/**
	 * The screen content
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function content() {

		// Check nonce
		$installer_nonce = ( isset( $_GET['rp4wp_nonce'] ) ? $_GET['rp4wp_nonce'] : '' );
		if ( ! wp_verify_nonce( $installer_nonce, RP4WP_Constants::NONCE_INSTALL ) ) {
			wp_die( 'Woah! It looks like something else tried to run the Related Posts for WordPress installation wizard! We were able to stop them, nothing was lost. Please report this incident at <a href="http://wordpress.org/support/plugin/related-posts-for-wp" target="_blank">our forums.</a>' );
		}

		// The steps
		$steps = array(
			1 => __( 'Welcome', 'related-posts-for-wp' ),
			2 => __( 'Caching Posts', 'related-posts-for-wp' ),
			3 => __( 'Linking Posts', 'related-posts-for-wp' ),
			4 => __( 'Finished', 'related-posts-for-wp' ),
		);

		// What's the current step?
		$cur_step = intval( isset( $_GET['step'] ) ? $_GET['step'] : 1 );

		// Set the post type in var
		$post_type = esc_html( isset( $_GET['pt'] ) ? $_GET['pt'] : '' );

		// Check if the post type is set
		if ( $cur_step > 1 && empty( $post_type ) ) {
			wp_die( sprintf( __( 'Missing post type parameter, please report this incident via %sour website%s.', 'related-posts-for-wp' ), '<a href="https://www.relatedpostsforwp.com/support/" target="_blank">', '</a>' ) );
		}

		// Try to create the cache table if we're in step 1 and this is a multisite
		if ( 1 == $cur_step && is_multisite() ) {
			require_once plugin_dir_path( RP4WP_PLUGIN_FILE ) . 'includes/installer-functions.php';
			rp4wp_premium_create_db_table();
		}

		// Check installer resume options
		if ( 2 == $cur_step ) {

			// Add is installing site option
			add_option( RP4WP_Constants::OPTION_IS_INSTALLING, $post_type );

			// Get current linked post type
			$linked_pt_cur = absint( isset( $_GET['cur'] ) ? $_GET['cur'] : 0 );

			// Get the available post types
			$ptm = new RP4WP_Post_Type_Manager();

			$linked_pt_count = count( $ptm->get_installed_post_type( $post_type ) );

		} elseif ( 4 == $cur_step ) {

			// Installer is done, remove the option
			delete_option( RP4WP_Constants::OPTION_IS_INSTALLING );
		}

		?>
		<div class="wrap">
			<h2>Related Posts for WordPress <?php _e( 'Installation', 'related-posts-for-wp' ); ?></h2>

			<ul class="install-steps">
				<?php

		foreach ( $steps as $step => $label ) {
			echo "<li id='step-bar-" . $step . "'" . ( ( $cur_step == $step ) ? " class='step-bar-active'" : "" ) . "><span>" . $step . '. ' . $label . "</span></li>" . PHP_EOL;
		}
		?>
			</ul>
			<br class="clear" />

			<h3><?php echo $steps[ $cur_step ]; ?></h3>

			<?php
		echo "<div class='rp4wp-step rp4wp-step-" . $cur_step . "' rel='" . $cur_step . "'>";

		// Hidden fields
		echo "<input type='hidden' id='rp4wp_admin_url' value='" . admin_url() . "' />" . PHP_EOL;

		// Echo the post type & total posts when post type is set
		if ( ! empty( $post_type ) ) {
			echo "<input type='hidden' id='rp4wp_post_type' value='" . $post_type . "' />" . PHP_EOL;
			echo "<input type='hidden' id='rp4wp_total_posts' value='" . wp_count_posts( $post_type )->publish . "' />" . PHP_EOL;
		}

		// Echo the amount of linked post types
		if ( isset( $linked_pt_count ) ) {
			echo "<input type='hidden' id='linked_pt_count' value='" . $linked_pt_count . "' />" . PHP_EOL;
		}

		// Echo the current linked post type
		if ( isset( $linked_pt_cur ) ) {
			echo "<input type='hidden' id='linked_pt_cur' value='" . $linked_pt_cur . "' />" . PHP_EOL;
		}

		// Echo the nonce
		if ( ! empty( $installer_nonce ) ) {
			echo "<input type='hidden' id='rp4wp_nonce' value='" . $installer_nonce . "' />" . PHP_EOL;
		}

		// AJAX nonce
		echo '<input type="hidden" name="rp4wp-ajax-nonce" id="rp4wp-ajax-nonce" value="' . wp_create_nonce( RP4WP_Constants::NONCE_AJAX ) . '" />';

		switch ( $cur_step ) {
			case 1:
				?>
				<p><?php _e( 'Thank you for choosing Related Posts for WordPress!', 'related-posts-for-wp' ); ?></p>
				<p><?php _e( "Below you'll find your post types available for installation, by installing a post type we'll set up a cache and offer you the ability to automatic link (custom) posts. To install a post type, simply click the 'Install Post Type' button.", 'related-posts-for-wp' ); ?></p>
				<h3>Post Types</h3>
				<?php

				// Get the available post types
				$ptm = new RP4WP_Post_Type_Manager();

				// Get installed post types
				$installed_post_types = $ptm->get_installed_post_types();

				// Available post types
				$available_post_types = $ptm->get_available_post_types();

				echo '<input type="hidden" id="rp4wp-availabe_post_types" value="' . implode( ',', array_keys( $available_post_types ) ) . '" />';

				echo '<table cellpadding="0" cellspacing="0" border="0" class="rp4wp-table-pt-overview">' . PHP_EOL;

				echo "<tr>" . PHP_EOL;
				echo "<th>" . __( 'Post Type', 'related-posts-for-wp' ) . "</th>" . PHP_EOL;
				echo "<th>" . __( 'Related to Post Types', 'related-posts-for-wp' ) . "</th>" . PHP_EOL;
				echo "<th>&nbsp;</th>" . PHP_EOL;
				echo "</tr>" . PHP_EOL;

				// Da loop
				if ( count( $available_post_types ) > 0 ) {

					foreach ( $available_post_types as $pt_key => $pt_label ) {

						$is_active = false;
						if ( isset( $installed_post_types[ $pt_key ] ) && count( $installed_post_types[ $pt_key ] ) > 0 ) {
							$is_active = true;
						}

						echo '<tr rel="' . $pt_key . '" ' . ( ( ! $is_active ) ? 'class="inactive"' : '' ) . '>';
						echo '<td class="rp4wp-parent">' . $pt_label . '</td>' . PHP_EOL;

						echo '<td class="rp4wp-children">';

						echo '<ul>' . PHP_EOL;

						if ( $is_active ) {
							foreach ( $installed_post_types[ $pt_key ] as $linked_pt ) {
								echo '<li id="' . $linked_pt . '"><span>' . $available_post_types[ $linked_pt ] . '</span></li>' . PHP_EOL;
							}
						}

						echo '</ul>' . PHP_EOL;

						echo '</td>' . PHP_EOL;

						echo '<td class="rp4wp-button">';

						echo '<div class="rp4wp-buttons-wrap">' . PHP_EOL;

						echo '<a href="javascript:;" class="button button-primary rp4wp-btn-edit rp4wp-has-tip" title="' . __( 'Edit this post type', 'related-posts-for-wp' ) . '" rel="edit"></a>';

						echo '</div>' . PHP_EOL;

						echo '</td>' . PHP_EOL;
						echo '</tr>';
					}

				}

				echo '</table>' . PHP_EOL;

				break;
			case 2:

				// Echo current uncached posts
				$related_word_manager = new RP4WP_Related_Word_Manager();

				// amount of posts needs caching
				$posts_todo = $related_word_manager->get_uncached_post_count( $post_type );

				echo "<input type='hidden' id='rp4wp_posts_todo' value='" . $posts_todo . "' />" . PHP_EOL;

				?>
				<p><?php _e( 'Thank you for choosing Related Posts for WordPress!', 'related-posts-for-wp' ); ?></p>
				<p><?php _e( 'Before you can start using Related Posts for WordPress we need to cache your current posts.', 'related-posts-for-wp' ); ?></p>
				<p><?php _e( "This is a one time process which might take some time now, depending on the amount of posts you have, but will ensure your website's performance when using the plugin.", 'related-posts-for-wp' ); ?></p>

				<p style="font-weight: bold;"><?php _e( 'Do NOT close this window, wait for this process to finish and this wizard to take you to the next step.', 'related-posts-for-wp' ); ?></p>

				<div id="progress-container">
					<div id="progressbar"></div>
					<p>Todo: <span id="progress-todo"><?php echo $posts_todo; ?></span></p>
					<p>Done: <span id="progress-done">0</span></p>
				</div>

				<?php
				break;
			case 3:

				// Echo current unlinked posts
				$related_post_manager = new RP4WP_Related_Post_Manager();

				// amount of posts need linking
				$posts_todo = $related_post_manager->get_unlinked_post_count( $post_type );

				echo "<input type='hidden' id='rp4wp_posts_todo' value='" . $posts_todo . "' />" . PHP_EOL;

				// Get the automatic linking post amount
				$alpa = 3;
				if ( isset( RP4WP()->settings[ 'general_' . $post_type ] ) ) {
					$alpa = RP4WP()->settings[ 'general_' . $post_type ]->get_option( 'automatic_linking_post_amount' );
				}

				// get the post age
				// Get the automatic linking post amount
				$max_post_age = 0;
				if ( isset( RP4WP()->settings[ 'general_' . $post_type ] ) ) {
					$max_post_age = RP4WP()->settings[ 'general_' . $post_type ]->get_option( 'max_post_age' );
				}

				?>
				<p style="font-weight: bold;"><?php _e( 'Great! All your posts were successfully cached!', 'related-posts-for-wp' ); ?></p>
				<p><?php _e( "You can let me link your posts, based on what I think is related, to each other. And don't worry, if I made a mistake at one of your posts you can easily correct this by editing it manually!", 'related-posts-for-wp' ); ?></p>
				<p><?php _e( 'Want me to start linking posts to each other? Fill in the amount of related posts each post should have and click on the "Link now" button. Rather link your posts manually? Click "Skip linking".', 'related-posts-for-wp' ); ?></p>
				<p style="font-weight: bold;"><?php _e( 'Do NOT close this window if you click the "Link now" button, wait for this process to finish and this wizard to take you to the next step.', 'related-posts-for-wp' ); ?></p>
				<br class="clear"/>

				<div class="rp4wp-install-link-box">
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td><label for="rp4wp_related_posts_amount"><?php _e( 'Amount of related posts:', 'related-posts-for-wp' ); ?></label></td>
							<td><input class="form-input-tip" type="text" id="rp4wp_related_posts_amount" value="<?php echo $alpa; ?>" /></td>
							<td class="rp4pw-install-step3-table-desc"><?php printf( __( 'The amount of related items per %s', 'related-posts-for-wp' ), $post_type ); ?></td>
						</tr>


						<tr>
							<td><label for="rp4wp_related_posts_age"><?php _e( 'Maximum Post Age:', 'related-posts-for-wp' ); ?></label></td>
							<td><input class="form-input-tip" type="text" id="rp4wp_related_posts_age" value="<?php echo $max_post_age; ?>"/></td>
							<td class="rp4pw-install-step3-table-desc"><?php printf( __( 'The maximum age in days of %s that will be linked. (0 = unlimited)', 'related-posts-for-wp' ), $post_type ); ?></td>
						</tr>

						<tr>
							<td>&nbsp;</td>
							<td colspan="2" class="rp4pw-install-step3-table-buttons">
								<a href="javascript:;" class="button button-primary button-large rp4wp-link-now-btn" id="rp4wp-link-now"><?php _e( 'Link now', 'related-posts-for-wp' ); ?></a>
								<a href="<?php echo admin_url( sprintf( '?page=rp4wp_install&step=4&pt=%s&&rp4wp_nonce=%s', $post_type, wp_create_nonce( RP4WP_Constants::NONCE_INSTALL ) ) ); ?>" class="button"><?php _e( 'Skip linking', 'related-posts-for-wp' ); ?></a>
							</td>
						</tr>

					</table>
				</div>

				<br class="clear"/>

				<div id="progress-container">
					<div id="progressbar"></div>
					<p>Todo: <span id="progress-todo"><?php echo $posts_todo; ?></span></p>
					<p>Done: <span id="progress-done">0</span></p>
				</div>
				<?php
				break;
			case 4:
				?>
				<p><?php _e( "That's it, you're good to go!", 'related-posts-for-wp' ); ?></p>
				<p><?php printf( __( 'Thanks again for using Related Posts for WordPress and if you have any questions be sure to ask them at the %sWordPress.org forums.%s', 'related-posts-for-wp' ), '<a href="http://wordpress.org/support/plugin/related-posts-for-wp" target="_blank">', '</a>' ); ?></p>
				<p>
					<a href="<?php echo admin_url( sprintf( '?page=rp4wp_install&rp4wp_nonce=%s', wp_create_nonce( RP4WP_Constants::NONCE_INSTALL ) ) ); ?>"
					   class="button button-primary"><?php _e( 'Click here to return to step 1', 'related-posts-for-wp' ); ?></a>
				</p>
				<p>
					<?php
					$local_settings = (array) RP4WP()->settings;
					$first_setting  = array_shift( $local_settings );
					echo '<a href="' . admin_url( 'options-general.php?page=' . $first_setting->get_page() ) . '" class="button button-primary">' . __( 'Click here to go to the settings page', 'related-posts-for-wp' ) . '</a>';
					?>
				</p>
			<?php
		}
		?>
		</div>

		</div>

	<?php
	}

}