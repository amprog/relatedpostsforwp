<?php

class RP4WP_Cli_Command_Dev extends WP_CLI_Command {

	/**
	 * Dev test flow
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     wp rp4wp-dev test
	 */
	public function test( $args, $assoc_args ) {

		$limit = 274;
//		$limit = 10;

		WP_CLI::line( 'Start rp4wp-dev test' );
		WP_CLI::line( '---------------------' );

		// args
		$post_type         = 'post';
		$linked_post_types = array( 'post' );
		$rel_amount        = 3;
		$max_post_age      = 0;

		/**
		 * UNINSTALL
		 */
		// Add the post types
		$ptm = new RP4WP_Post_Type_Manager();

		// Remove post type
		$ptm->remove_post_type( $post_type );

		// Related post manager
		$related_post_manager = new RP4WP_Related_Post_Manager();

		// Remove linked related posts
		$related_post_manager->remove_linked_posts( $post_type );

		// delete words
		$word_manager = new RP4WP_Related_Word_Manager();
		$word_manager->delete_words_by_post_type( $post_type );

		/**
		 * SETTINGS
		 */

		// set the linked post types
		$ptm->add_post_type( $post_type, $linked_post_types );

		// set the post type options
		if ( isset( RP4WP::get()->settings[ 'general_' . $post_type ] ) ) {
			$options                                  = RP4WP::get()->settings[ 'general_' . $post_type ]->get_options();
			$options['automatic_linking_post_amount'] = $rel_amount;
			$options['max_post_age']                  = $max_post_age;
			update_option( 'rp4wp_general_' . $post_type, $options );
		}

		/**
		 * CACHE POSTS
		 */
		WP_CLI::line( 'Start building word cache' );

		// related word manage
		$related_word_manager = new RP4WP_Related_Word_Manager();

		$start = time();

		// cache words of parent post type
		$related_word_manager->save_all_words( $post_type, $limit );

		// cache words of linked post types
		foreach ( $linked_post_types as $linked_post_type ) {
			$related_word_manager->save_all_words( $linked_post_type, $limit );
		}

		$runtime = ( time() - $start );
		$avg     = ( $runtime / $limit );

		WP_CLI::line( sprintf( 'Words cached - Runtime: %d - Average: %f', $runtime, $avg ) );

		$start = time();

		/**
		 * LINK POSTS
		 */
		WP_CLI::line( 'Start linking posts' );
		$related_post_manager->link_related_posts( $rel_amount, $post_type, $limit );

		$runtime = ( time() - $start );
		$avg     = ( $runtime / $limit );

		WP_CLI::line( sprintf( 'Posts linked - Runtime: %d - Average: %f', $runtime, $avg ) );

		// output success
		WP_CLI::success( sprintf( 'Successfully installed %s', $post_type ) );

	}

	/**
	 * Remove all links from post type
	 *
	 * ## OPTIONS
	 *
	 * <lang>
	 * : The language
	 *
	 * ## EXAMPLE
	 *
	 *     wp rp4wp-dev dedupe_words nl_NL
	 *
	 * @synopsis <lang>
	 */
	public function dedupe_words($args) {
		$wm = new RP4WP_Related_Word_Manager();
		$wm->dedupe_and_order_ignored_words($args[0]);
	}

}