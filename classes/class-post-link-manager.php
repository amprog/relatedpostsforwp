<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Post_Link_Manager {

	private $temp_child_order;

	public function __construct() {
	}

	/**
	 * Create query arguments used to fetch links
	 *
	 * @access private
	 *
	 * @param int $post_id
	 * @param string $meta_key
	 *
	 * @return array
	 */
	private function create_link_args( $meta_key, $post_id ) {
		$args = array(
			'post_type'      => RP4WP_Constants::LINK_PT,
			'posts_per_page' => - 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => $meta_key,
					'value'   => $post_id,
					'compare' => '=',
				)
			)
		);

		return $args;
	}

	/**
	 * Method to add a PostLink
	 *
	 * @access public
	 *
	 * @param int $parent_id
	 * @param int $child_id
	 * @param string $post_type_parent
	 * @param bool $batch
	 * @param bool $manual
	 *
	 * @return int ($link_id)
	 */
	public function add( $parent_id, $child_id, $post_type_parent, $batch = false, $manual = false ) {
		global $wpdb;

		// Setup the insert data
		$data = array(
			'post' => "('" . current_time( 'mysql', 0 ) . "', '" . current_time( 'mysql', 1 ) . "','','Related Posts for WordPress Link','" . RP4WP_Constants::LINK_PT . "','publish')",
			'meta' => array(
				"(%d, '" . RP4WP_Constants::PM_PT_PARENT . "', '$post_type_parent')",
				"(%d, '" . RP4WP_Constants::PM_PARENT . "', '$parent_id')",
				"(%d, '" . RP4WP_Constants::PM_CHILD . "', '$child_id')",
			)
		);

		if ( true == $manual ) {
			$data['meta'][] = "(%d, '" . RP4WP_Constants::PM_MANUAL . "', '1')";
		}


		// If this is a batch insert, return data
		if ( true === $batch ) {
			return $data;
		}

		// Create post link
		$wpdb->query( "	INSERT INTO `$wpdb->posts`
						(`post_date`,`post_date_gmt`,`post_content`,`post_title`,`post_type`,`post_status`)
						VALUES
						{$data['post']}
						" );

		// Get the link ID
		$link_id = $wpdb->insert_id;

		// Create post meta
		$wpdb->query( "INSERT INTO `$wpdb->postmeta`
				(`post_id`,`meta_key`,`meta_value`)
				VALUES
				" . implode( ',', array_map( array(
				$wpdb,
				'prepare'
			), $data['meta'], array_fill( 0, count( $data['meta'] ), $link_id ) ) ) . "
				" );

		// Do action rp4wp_after_link_add
		do_action( 'rp4wp_after_link_add', $link_id );

		// Return link id
		return $link_id;
	}

	/**
	 * Delete a link
	 *
	 * @access public
	 *
	 * @param int $link_id
	 *
	 * @return void
	 */
	public function delete( $link_id ) {
		// Action
		do_action( 'rp4wp_before_link_delete', $link_id );

		// Delete link
		wp_delete_post( $link_id, true );

		// Action
		do_action( 'rp4wp_after_link_delete', $link_id );

		return;
	}

	/**
	 * Get the children count of a post
	 *
	 * @param int $parent_id
	 *
	 * @return int
	 */
	public function get_children_count( $parent_id ) {

		// get basic link args and add the fields=>ids to them
		$link_args           = $this->create_link_args( RP4WP_Constants::PM_PARENT, $parent_id );
		$link_args['fields'] = 'ids';

		// Do query
		$link_query = new WP_Query( $link_args );

		// return post count
		return intval( $link_query->post_count );
	}

	/**
	 * Get children based on parent_id.
	 * It's possible to add extra arguments to the WP_Query with the $extra_args argument
	 *
	 * @access public
	 *
	 * @param int $parent_id
	 * @param array $extra_args
	 *
	 * @return array
	 */
	public function get_children( $parent_id, $extra_args = null ) {
		global $post;

		// Store current post
		$o_post = $post;

		// Do WP_Query
		$link_args = $this->create_link_args( RP4WP_Constants::PM_PARENT, $parent_id );

		/*
		 * Check $extra_args for `posts_per_page`.
		 * This is the only arg that should be added to link query instead of the child query
		 */
		if ( isset( $extra_args['posts_per_page'] ) ) {

			// Set posts_per_page to link arguments
			$link_args['posts_per_page'] = $extra_args['posts_per_page'];
			unset( $extra_args['posts_per_page'] );
		}

		/*
		 * Check $extra_args for `order`.
		 * If 'order' is set without 'orderby', we should add it to the link arguments
		 */
		if ( isset( $extra_args['order'] ) && ! isset( $extra_args['orderby'] ) ) {
			$link_args['order'] = $extra_args['order'];
			unset( $extra_args['order'] );
		}

		/**
		 * Filter args for link query
		 */
		$link_args = apply_filters( 'rp4wp_get_children_link_args', $link_args, $parent_id );

		// Create link query
		$link_query = new WP_Query( $link_args );

		// Store child ids
		// @todo remove the usage of get_the_id()
		$child_ids = array();
		while ( $link_query->have_posts() ) : $link_query->the_post();
			$child_ids[ get_the_id() ] = get_post_meta( get_the_id(), RP4WP_Constants::PM_CHILD, true );
		endwhile;

		// Get children with custom args
		if ( $extra_args !== null && count( $extra_args ) > 0 ) {

			if ( ! isset( $extra_args['orderby'] ) ) {
				$this->temp_child_order = array();
				foreach ( $child_ids as $child_id ) {
					$this->temp_child_order[] = $child_id;
				}
			}

			// Get child again, but this time by $extra_args
			$children = array();

			//Child WP_Query arguments
			if ( count( $child_ids ) > 0 ) {
				$child_id_values = array_values( $child_ids );
				$child_post_type = get_post_type( array_shift( $child_id_values ) );
				$child_args      = array(
					'post_type'      => $child_post_type,
					'posts_per_page' => - 1,
					'post__in'       => $child_ids,
				);

				// Extra arguments
				$child_args = array_merge_recursive( $child_args, $extra_args );

				/**
				 * Filter args for child query
				 */
				$child_args = apply_filters( 'rp4wp_get_children_child_args', $child_args, $parent_id );

				// Child Query
				$child_query = new WP_Query( $child_args );

				while ( $child_query->have_posts() ) : $child_query->the_post();
					// Add post to correct original sort key
					$children[ array_search( $child_query->post->ID, $child_ids ) ] = $child_query->post;
				endwhile;

				// Fix sorting
				if ( ! isset( $extra_args['orderby'] ) ) {
					uasort( $children, array( $this, 'sort_get_children_children' ) );
				}

			}
		} else {
			// No custom arguments found, get all objects of stored ID's
			$children = array();
			foreach ( $child_ids as $link_id => $child_id ) {
				$children[ $link_id ] = get_post( $child_id );
			}
		}

		// Reset global post variables
		wp_reset_postdata();

		// Restoring post
		$post = $o_post;

		// Return children
		return $children;
	}

	/**
	 * Custom sort method to reorder children
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 */
	public function sort_get_children_children( $a, $b ) {
		return array_search( $a->ID, $this->temp_child_order ) - array_search( $b->ID, $this->temp_child_order );
	}

	/**
	 * Delete all links involved in given post_id
	 *
	 * @access public
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function delete_links_related_to( $post_id ) {
		$involved_query = new WP_Query( array(
			'post_type'      => RP4WP_Constants::LINK_PT,
			'posts_per_page' => - 1,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => RP4WP_Constants::PM_PARENT,
					'value'   => $post_id,
					'compare' => '=',
				),
				array(
					'key'     => RP4WP_Constants::PM_CHILD,
					'value'   => $post_id,
					'compare' => '=',
				)
			)
		) );
		while ( $involved_query->have_posts() ) : $involved_query->the_post();
			wp_delete_post( $involved_query->post->ID, true );
		endwhile;

		return true;
	}

}