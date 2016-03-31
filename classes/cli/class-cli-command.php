<?php

class RP4WP_Cli_Command extends WP_CLI_Command {

	/**
	 * Cache post types
	 *
	 * ## OPTIONS
	 *
	 * <post_type>
	 * : The post type to cache
	 *
	 * ## EXAMPLES
	 *
	 *     wp rp4wp cache post
	 *
	 * @synopsis <post_type>
	 */
	public function cache( $args, $assoc_args ) {

		// args
		$post_type = trim( $args[0] );

		if ( '' !== $post_type ) {
			$related_word_manager = new RP4WP_Related_Word_Manager();
			$related_word_manager->save_all_words( $post_type );

			// output success
			WP_CLI::success( sprintf( 'Successfully cached %s', $post_type ) );
		}

	}

	/**
	 * Link post type to installed post types. Note that the post type already needs to be installed
	 *
	 * ## OPTIONS
	 *
	 * <post_type>
	 * : The post type to link
	 *
	 * <rel_amount>
	 * : The amount of related posts (excludes manually linked posts on a per post basis)
	 *
	 * [<max_post_age>]
	 * : The maximum age of a related post in days
	 *
	 * ## EXAMPLE
	 *
	 *     wp rp4wp link post 3
	 *
	 * @synopsis <post_type> <rel_amount> [<max_post_age>]
	 */
	public function link( $args, $assoc_args ) {

		// args
		$post_type  = trim( $args[0] );
		$rel_amount = intval( $args[1] );

		// check if post type and rel_amount are correctly set
		if ( '' !== $post_type && $rel_amount > 0 ) {

			// check if maximum post age is set
			if ( isset( $args[2] ) ) {
				$max_post_age = intval( $args[2] );

				// set the maximum post age
				if ( isset( RP4WP::get()->settings[ 'general_' . $post_type ] ) ) {
					$options                 = RP4WP::get()->settings[ 'general_' . $post_type ]->get_options();
					$options['max_post_age'] = $max_post_age;
					update_option( 'rp4wp_general_' . $post_type, $options );
				}
			}

			// link related
			$related_post_manager = new RP4WP_Related_Post_Manager();
			$related_post_manager->link_related_posts( $rel_amount, $post_type );

			// output success
			WP_CLI::success( sprintf( 'Successfully linked %s', $post_type ) );
		}

	}

	/**
	 * Remove all links from post type
	 *
	 * ## OPTIONS
	 *
	 * <post_type>
	 * : The post type where links are removed from
	 *
	 * ## EXAMPLE
	 *
	 *     wp rp4wp remove_related post
	 *
	 * @synopsis <post_type>
	 */
	public function remove_related( $args, $assoc_args ) {

		// args
		$post_type = $args[0];

		// Related post manager
		$related_post_manager = new RP4WP_Related_Post_Manager();

		// Remove linked related posts
		$related_post_manager->remove_linked_posts( $post_type );

		// output success
		WP_CLI::success( sprintf( 'Successfully removed %s', $post_type ) );
	}

	/**
	 * Install a post type. This includes caching and linking post types.
	 *
	 * ## OPTIONS
	 *
	 * <post_type>
	 * : The post type where related posts are linked to
	 *
	 * <linked_post_types>
	 * : The post types (comma separated) which are linked to the first argument
	 *
	 * <rel_amount>
	 * : The amount of related posts per post
	 *
	 * [<max_post_age>]
	 * : The maximum age of a related post in days
	 *
	 * ## EXAMPLE
	 *
	 *     wp rp4wp remove_related post
	 *
	 * @synopsis <post_type> <linked_post_types> <rel_amount> [<max_post_age>]
	 */
	public function install( $args, $assoc_args ) {

		// args
		$post_type         = trim( $args[0] );
		$linked_post_types = explode( ',', trim( $args[1] ) );
		$rel_amount        = intval( $args[2] );
		$max_post_age      = intval( ( isset( $args[3] ) ? $args[3] : 0 ) );

		// we need at least one linked post type to continue
		if ( count( $linked_post_types ) > 0 ) {

			/**
			 * SETTINGS
			 */

			// set the linked post types
			$ptm = new RP4WP_Post_Type_Manager();
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

			WP_CLI::line( 'Caching Words' );

			// related word manage
			$related_word_manager = new RP4WP_Related_Word_Manager();

			// cache words of parent post type
			$related_word_manager->save_all_words( $post_type );

			// cache words of linked post types
			foreach ( $linked_post_types as $linked_post_type ) {
				$related_word_manager->save_all_words( $linked_post_type );
			}

			/**
			 * LINK POSTS
			 */
			WP_CLI::line( 'Linking Posts' );

			$related_post_manager = new RP4WP_Related_Post_Manager();
			$related_post_manager->link_related_posts( $rel_amount, $post_type );

			// output success
			WP_CLI::success( sprintf( 'Successfully installed %s', $post_type ) );

		}


	}

}