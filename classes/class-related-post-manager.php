<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Related_Post_Manager {

	/**
	 * Get related posts by post id and post type
	 *
	 * @param int $post_id
	 * @param String $post_type
	 * @param int $limit
	 *
	 * @return array
	 */
	public function get_related_posts( $post_id, $post_type, $limit = - 1 ) {
		global $wpdb;

		// Post Type Manager
		$ptm = new RP4WP_Post_Type_Manager();

		// Post Link Manager
		$plm = new RP4WP_Post_Link_Manager();

		// Int y'all
		$post_id = intval( $post_id );

		// Related post types
		$related_post_types = $ptm->get_installed_post_type( $post_type );

		// Only continue of we've got > 1 related post type
		if ( 0 === count( $related_post_types ) ) {
			return array();
		}

		// Format the related post types
		$formatted_post_types = "( '" . implode( "','", esc_sql( $related_post_types ) ) . "' )";

		// Get max post age
		$max_post_age = 0;
		if ( isset( RP4WP::get()->settings[ 'general_' . $post_type ] ) ) {
			// set the correct options from step 3
			$max_post_age = intval( RP4WP::get()->settings[ 'general_' . $post_type ]->get_option( 'max_post_age' ) );
		}

		// make $max_post_age filterable
		$max_post_age = apply_filters( 'rp4wp_max_post_age', $max_post_age, $post_id, $post_type );

		// get limit cat options
		$limit_cats = 0;
		if ( 'post' == $post_type ) {
			$limit_cats = absint( RP4WP()->settings['categories']->get_option( 'limit_related_categories' ) );
		}

		// Build SQl
		$sql = "
		SELECT R.`post_id` AS `ID`, ( SUM( O.`weight` ) *  SUM( R.`weight` ) ) AS `CMS`
		FROM `" . RP4WP_Related_Word_Manager::get_database_table() . "` O
		INNER JOIN `" . RP4WP_Related_Word_Manager::get_database_table() . "` R ON R.`word` = O.`word` 
		INNER JOIN `" . $wpdb->posts . "` P ON P.`ID` = R.`post_id` 
		";

		// join term_relationships table if we're limiting categories
		if ( 1 === $limit_cats ) {
			$sql .= "INNER JOIN `".$wpdb->term_relationships."` TR on TR.`object_id` = R.`post_id` 
			";
		}

		// add WHERE statements
		$sql .= "
		WHERE 1=1
		AND O.`post_id` = %d
		AND R.`post_type` IN " . $formatted_post_types . "
		AND R.`post_id` != %d 
		AND P.`post_status` = 'publish'
		";

		// add categories we're limiting categories on (if we're limitng)
		if ( 1 === $limit_cats ) {

			// get categories we're limited to
			$cats = RP4WP()->settings['categories']->get_option( 'related_categories' );
			if ( is_array( $cats ) ) {
				$cats_str = "";
				foreach ( $cats as $cat ) {
					$cats_str .= "," . absint( $cat );
				}
				$cats_str = substr( $cats_str, 1 );
				$sql      .= "AND TR.term_taxonomy_id IN ( " . $cats_str . " )
				";
			}

		}

		// get current related posts
		$cur_related_posts = $plm->get_child_ids( $post_id );
		foreach( $cur_related_posts as $crp_k => $crp_v ) { $cur_related_posts[$crp_k] = absint($crp_v); }
		$formatted_current_rel_posts = implode( ",", $cur_related_posts );
		if ( ! empty( $formatted_current_rel_posts ) ) {
			$sql .= "AND R.`post_id` NOT IN (" . $formatted_current_rel_posts . ")
			";
		}

		// get excluded ids
		$excluded_ids = get_option( RP4WP_Constants::OPTION_EXCLUDED, '' );

		// make $excluded_ids filterable
		$excluded_ids = apply_filters( 'rp4wp_excluded_ids', $excluded_ids, $post_id, $post_type );

		// check if there are excluded ids
		if ( false !== $excluded_ids && ! empty( $excluded_ids ) ) {
			// add excluded where statement to query
			$sql .= "AND R.`post_id` NOT IN (" . $excluded_ids . ")
			";
		}

		// check if we got a maximum post age for this post type
		// check is max post age is > 0
		if ( $max_post_age > 0 ) {

			// calculate date in past
			$date_time_oldest = new DateTime();
			$date_time_oldest->modify( '-' . $max_post_age . ' days' );

			// make the post age key filterable
			$post_age_column = apply_filters( 'rp4wp_post_age_column', 'post_date' );

			// add to SQL
			$sql .= "AND P.`" . $post_age_column . "` >= '" . $date_time_oldest->format( 'Y-m-d' ) . "'
				";

		}

		// add group by and order by to SQL
		$sql .= "GROUP BY R.`post_id`
		ORDER BY `CMS` DESC
		";

		// Check & Add Limit
		if ( - 1 != $limit ) {
			$sql .= "
			LIMIT 0,%d";
			// Prepare SQL
			$sql = $wpdb->prepare( $sql, $post_id, $post_id, $limit );
		} else {
			// Prepare SQL
			$sql = $wpdb->prepare( $sql, $post_id, $post_id );
		}

		// Allow filtering of SQL to find related posts
		$sql = apply_filters( 'rp4wp_get_related_posts_sql', $sql, $post_id, $post_type, $limit );

		// Get post from related cache
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get non auto linked posts
	 *
	 * @param $limit
	 *
	 * @return array
	 */
	public function get_not_auto_linked_posts_ids( $post_type, $limit ) {
		return get_posts( array(
			'fields'         => 'ids',
			'post_type'      => $post_type,
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => RP4WP_Constants::PM_POST_AUTO_LINKED,
					'compare' => 'NOT EXISTS',
//					'value'   => ''
				),
			)
		) );
	}


	/**
	 * Deprecated, use get_unlinked_post_count() instead
	 *
	 * @deprecated 1.4.0
	 *
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public function get_uncached_post_count( $post_type ) {

		// Deprecated notice
		_deprecated_function( __FUNCTION__, '1.4.0', __CLASS__ . '->get_unlinked_post_count()' );

		return $this->get_unlinked_post_count( $post_type );
	}

	/**
	 * Get the unlinked post count
	 *
	 * @param String $post_type
	 *
	 * @since  1.6.0
	 * @access public
	 *
	 * @return mixed
	 */
	public function get_unlinked_post_count( $post_type ) {
		global $wpdb;

		$post_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(P.ID) FROM " . $wpdb->posts . " P LEFT JOIN " . $wpdb->postmeta . " PM ON (P.ID = PM.post_id AND PM.meta_key = '" . RP4WP_Constants::PM_POST_AUTO_LINKED . "') WHERE 1=1 AND P.post_type = '%s' AND P.post_status = 'publish' AND PM.post_id IS NULL GROUP BY P.post_status", $post_type ) );

		if ( ! is_numeric( $post_count ) ) {
			$post_count = 0;
		}

		return $post_count;
	}

	/**
	 * Link x related posts to post
	 *
	 * @param $post_id
	 * @param $post_type
	 * @param $amount
	 *
	 * @return boolean
	 */
	public function link_related_post( $post_id, $post_type, $amount ) {

		// only fetch related posts if we need to link at least one post
		if ( $amount > 0 ) {

			// get related posts
			$related_posts = $this->get_related_posts( $post_id, $post_type, $amount );

			if ( count( $related_posts ) > 0 ) {

				global $wpdb;

				$post_link_manager = new RP4WP_Post_Link_Manager();

				$batch_data = array();
				foreach ( $related_posts as $related_post ) {
					$batch_data[] = $post_link_manager->add( $post_id, $related_post->ID, $post_type, true );
				}

				// Do batch insert
				$wpdb->query( "INSERT INTO `$wpdb->posts`
						(`post_date`,`post_date_gmt`,`post_content`,`post_title`,`post_type`,`post_status`)
						VALUES
						" . implode( ',', array_map( array( $this, 'batch_data_get_post' ), $batch_data ) ) . "
						" );

				// Get the first post link insert ID
				$pid = $wpdb->insert_id;

				// Set the correct ID's for batch meta insert
				foreach ( $batch_data as $bk => $bd ) {
					$batch_data[ $bk ]['meta'] = array_map( array(
						$this,
						'batch_data_set_pid'
					), $bd['meta'], array_fill( 0, count( $bd['meta'] ), $pid ) );
					$pid ++;
				}

				// Insert all the meta
				$wpdb->query( "INSERT INTO `$wpdb->postmeta`
				(`post_id`,`meta_key`,`meta_value`)
				VALUES
				" . implode( ',', array_map( array( $this, 'batch_data_get_meta' ), $batch_data ) ) . "
				" );

			}
		}


		update_post_meta( $post_id, RP4WP_Constants::PM_POST_AUTO_LINKED, 1 );

		return true;
	}

	/**
	 * Get post batch data
	 *
	 * @param $batch
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return mixed
	 */
	public function batch_data_get_post( $batch ) {
		return $batch['post'];
	}

	/**
	 * Get meta batch data
	 *
	 * @param $batch
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function batch_data_get_meta( $batch ) {
		return implode( ',', $batch['meta'] );
	}

	/**
	 * Set the post ID's in batch data
	 *
	 * @param $batch
	 * @param $pid
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function batch_data_set_pid( $batch, $pid ) {
		return sprintf( $batch, $pid );
	}

	/**
	 * Link x related posts to y not already linked posts
	 *
	 * @param int $rel_amount
	 * @param string $post_type
	 * @param int $post_amount
	 *
	 * @return boolean
	 */
	public function link_related_posts( $rel_amount, $post_type, $post_amount = - 1 ) {
		global $wpdb;

		// Get unlinked posts
		$post_ids = $this->get_not_auto_linked_posts_ids( $post_type, $post_amount );

		$total = count( $post_ids );

		// Check & Loop
		if ( $total > 0 ) {

			// total posts linked
			$done = 0;

			// Post Link Manager
			$pl_manager = new RP4WP_Post_Link_Manager();

			foreach ( $post_ids as $post_id ) {

				// amount of posts we want to link
				$posts_to_link_amount = $rel_amount;

				// Count already linked posts
				$already_linked_posts = $pl_manager->get_children_count( $post_id );

				// Subtract already linked post count from posts to link amount
				if ( count( $already_linked_posts ) > 0 ) {
					$posts_to_link_amount = $posts_to_link_amount - $already_linked_posts;
				}

				// link if we've at least one post to link
				$this->link_related_post( $post_id, $post_type, $posts_to_link_amount );

				// increment done
				$done++;

				// WP_CLI feedback
				if ( defined( 'WP_CLI' ) && WP_CLI ) {
					$perc = ceil( ( $done / $total ) * 100 );
					$bar  = "\r[" . ( $perc > 0 ? str_repeat( "=", $perc - 1 ) : "" ) . ">";
					$bar .= str_repeat( " ", 100 - $perc ) . "] - $perc%";
					print( $bar );
				}

			}

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				WP_CLI::line( '' );
			}
		}

		// Done
		return true;
	}

	/**
	 * Remove linked posts of post type
	 *
	 * @param $post_type
	 * @param bool $delete_manual
	 *
	 * @since 1.3.0
	 *
	 * @return bool
	 */
	public function remove_linked_posts( $post_type, $delete_manual = false ) {

		global $wpdb;

		// Get ID's of related post link posts
		$link_ids = get_posts(
			array(
				'post_type'      => RP4WP_Constants::LINK_PT,
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => RP4WP_Constants::PM_PT_PARENT,
						'value'   => $post_type,
						'compare' => '='
					),
					array(
						'key'     => RP4WP_Constants::PM_MANUAL,
						'value'   => '1',
						'compare' => 'NOT EXISTS'
					)
				)
			)
		);

		// Only run queries if we have ID's
		if ( count( $link_ids ) > 0 ) {
			// Delete all link posts
			$wpdb->query( "DELETE FROM $wpdb->posts WHERE `ID` IN (" . implode( ",", $link_ids ) . ");" );

			// Delete all link post meta
			$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE `post_id` IN (" . implode( ",", $link_ids ) . ");" );
		}

		// Remove the post meta we attached to posts
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE `meta_key` = '" . RP4WP_Constants::PM_POST_AUTO_LINKED . "' AND `post_id` IN (SELECT `ID` FROM $wpdb->posts WHERE `post_type` = '%s' ) ;", $post_type ) );

	}

	/**
	 * Generate the related posts list
	 *
	 * @param int $post_id
	 * @param string $template
	 * @param int $limit
	 *
	 * @return string
	 */
	public function generate_related_posts_list( $post_id, $template = 'related-posts-default.php', $limit = - 1 ) {

		// the output
		$output = '';

		// get the post type
		$post_type = get_post_type( $post_id );

		// The settings object
		$pt_settings = RP4WP()->settings[ 'general_' . $post_type ];

		// Post Link Manager
		$pl_manager = new RP4WP_Post_Link_Manager();

		// Get the children
		$related_posts = apply_filters( 'rp4wp_related_posts_list', $pl_manager->get_children( $post_id, array( 'posts_per_page' => $limit ) ), $post_id );

		// Count
		if ( count( $related_posts ) > 0 ) {

			// Manager Template
			$manager_template = new RP4WP_Manager_Template();

			// Load the template output
			ob_start();
			$manager_template->get_template( $template, array(
				'related_posts'  => $related_posts,
				'heading_text'   => $pt_settings->get_option( 'heading_text' ),
				'excerpt_length' => $pt_settings->get_option( 'excerpt_length' ),
				'post_type'      => $post_type
			) );

			$output = trim( ob_get_clean() );

		}

		return $output;
	}

}