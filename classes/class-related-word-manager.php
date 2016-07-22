<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RP4WP_Related_Word_Manager {

	const DB_TABLE = 'rp4wp_cache';

	private $ignored_words = null;

	/**
	 * Get the database table
	 *
	 * @return string
	 */
	public static function get_database_table() {
		global $wpdb;

		return $wpdb->prefix . self::DB_TABLE;
	}

	/**
	 * Internal method that formats and outputs the $ignored_words array to screen
	 */
	public function dedupe_and_order_ignored_words( $lang ) {
		$output = 'return array(';

		$ignored_words = $this->get_ignored_words( $lang );

		$temp_words = array();
		foreach ( $ignored_words as $word ) {

			// Only add word if it's not already added
			if ( ! in_array( $word, $temp_words ) ) {
				if ( false !== strpos( $word, "Ã" ) ) {
					continue;
				}
				$temp_words[] = str_ireplace( "'", "", $word );
			}

		}

		sort( $temp_words );


		foreach ( $temp_words as $word ) {
			$output .= " '{$word}',";
		}


		$output .= ");";

		echo $output;
		die();
	}

	/**
	 * Get the ignored words
	 *
	 * @param string $lang
	 *
	 * @return array
	 */
	private function get_ignored_words( $lang ) {

		if ( null == $this->ignored_words ) {

			// Require the lang file
			$relative_path = '/ignored-words/' . $lang . '.php';

			// Validate the file path to prevent traversal attacks
			if ( 0 !== validate_file( $relative_path ) ) {
				return array();
			}

			$filename = dirname( __FILE__ ) . $relative_path;

			// Check if file exists
			if ( ! file_exists( $filename ) ) {
				return array();
			}

			// Require the file
			$ignored_words = require( $filename );

			// Check if the the $ignored_words are set
			if ( is_null( $ignored_words ) || ! is_array( $ignored_words ) ) {
				return array();
			}

			// add extra ignored words (setting)
			$ignored_words = array_merge( $ignored_words, $this->get_extra_ignored_words() );

			// Words to ignore
			$this->ignored_words = apply_filters( 'rp4wp_ignored_words', $ignored_words );
		}

		return $this->ignored_words;
	}

	/**
	 * Get the words from the post content
	 *
	 * @param $post
	 *
	 * @return array $words
	 */
	private function get_content_words( $post ) {

		$content = $post->post_content;

		// Remove all line break
		$content = trim( preg_replace( '/\s+/', ' ', $content ) );

		// Array to store the linked words
		$linked_words = array();

		// Find all links in the content
		if ( true == preg_match_all( '`<a[^>]*href="([^"]+)">[^<]*</a>`si', $content, $matches ) ) {
			if ( count( $matches[1] ) > 0 ) {

				// Get link weight
				$manager_weights = new RP4WP_Manager_Weights();
				$link_weight     = $manager_weights->get_weight( 'link' );

				// Loop
				foreach ( $matches[1] as $url ) {

					// Get the post Id
					$link_post_id = url_to_postid( $url );

					if ( 0 == $link_post_id ) {
						continue;
					}

					// Get the post
					$link_post = get_post( $link_post_id );

					// Check if we found a linked post
					if ( $link_post != null ) {
						// Get words of title
						$title_words = $this->explode( $link_post->post_title );

						// Check, Loop
						if ( is_array( $title_words ) && count( $title_words ) > 0 ) {
							$linked_words = $this->add_words_from_array( $linked_words, $title_words, $link_weight );
						}
					}

				}

			}
		}

		// Remove all html tags
		$content = strip_tags( $content );

		// Remove all shortcodes
		$content = strip_shortcodes( $content );

		// Remove the <!--more--> tag
		$content = str_ireplace( '<!--more-->', '', $content );

		// Remove everything but letters and numbers
		$content = preg_replace( '/[^a-z0-9]+/i', ' ', $content );

		// UTF8 fix content
		$content = $this->convert_characters( $content );

		// Split string into words
		$words = $this->explode( $content );

		// Add the $linked_words
		$words = array_merge( $words, $linked_words );

		// Return the $words
		return $words;
	}

	/**
	 * Add words from an array to the "base" words array, multiplied by their weight
	 *
	 * @param array $base_words
	 * @param array $words
	 * @param int $weight
	 *
	 * @return array
	 */
	private function add_words_from_array( array $base_words, $words, $weight = 1 ) {

		// Check if weight > 0 and if $words is array
		if ( $weight > 0 && is_array( $words ) ) {
			foreach ( $words as $word ) {
				$word                      = $this->convert_characters( $word );
				$word_multiplied_by_weight = array_fill( 0, $weight, $word );
				$base_words                = array_merge( $base_words, $word_multiplied_by_weight );
			}
		}

		return $base_words;
	}

	/**
	 * Convert UTF-8 characters correctly
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private function convert_characters( $string ) {
		// Detect encoding, only encode if string isn't encoded already
		if ( 'UTF-8' !== mb_detect_encoding( $string, 'UTF-8', true ) ) {
			$string = utf8_encode( $string );
		}

		// Replace all 'special characters' with normal ones
		if ( strpos( $string = htmlentities( $string, ENT_QUOTES, 'UTF-8' ), '&' ) !== false ) {
			$string = html_entity_decode( preg_replace( '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string ), ENT_QUOTES, 'UTF-8' );
		}

		// Return string
		return $string;
	}

	/**
	 * Custom method of trim that will also remove punctuation
	 *
	 * @param $word
	 *
	 * @return mixed
	 */
	private function trim( $word ) {
		return trim( $word, " \t\n\r\0\x0B?!,.'\":;/\\)(-" );
	}

	/**
	 * Custom explode method that will also join joined-words prior to exploding
	 *
	 * @param $string
	 *
	 * @return array
	 */
	private function explode( $string ) {

		$joined_words = $this->get_joined_words();

		if ( count( $joined_words ) > 0 ) {
			foreach ( $joined_words as $replace => $search ) {
				$string = str_ireplace( $search, $replace, $string );
			}
		}

		return explode( ' ', $string );
	}

	/**
	 * Get extra ignored words array
	 *
	 * @return array
	 */
	public function get_extra_ignored_words() {

		// load transient
		$extra_ignored_words = get_transient( RP4WP_Constants::TRANSIENT_EXTRA_IGNORED_WORDS );

		// check transient
		if ( false === $extra_ignored_words ) {
			$extra_ignored_words = array();

			// get joined words and explode them on new line
			$extra_ignored_words_raw = explode( PHP_EOL, RP4WP()->settings['words']->get_option( 'ignored_words' ) );

			// check
			if ( count( $extra_ignored_words_raw ) > 0 ) {

				// loop
				foreach ( $extra_ignored_words_raw as $extra_ignored_word ) {
					$extra_ignored_words[] = trim( $extra_ignored_word );
				}

			}

			// set transient
			set_transient( RP4WP_Constants::TRANSIENT_EXTRA_IGNORED_WORDS, $extra_ignored_words, ( 7 * DAY_IN_SECONDS ) );
		}

		// return joined words
		return $extra_ignored_words;
	}

	/**
	 * Get joined words array that includes replacements
	 *
	 * @return array
	 */
	public function get_joined_words() {

		// load transient
		$joined_words = get_transient( RP4WP_Constants::TRANSIENT_JOINED_WORDS );

		// check transient
		if ( false === $joined_words ) {
			$joined_words = array();

			// get joined words and explode them on new line
			$joined_words_raw = explode( PHP_EOL, RP4WP()->settings['words']->get_option( 'joined_words' ) );

			// check
			if ( count( $joined_words_raw ) > 0 ) {

				// loop
				foreach ( $joined_words_raw as $joined_word ) {
					$joined_words[ sanitize_title( $joined_word ) ] = trim( $joined_word );
				}

			}

			// set transient
			set_transient( RP4WP_Constants::TRANSIENT_JOINED_WORDS, $joined_words, ( 7 * DAY_IN_SECONDS ) );
		}

		// return joined words
		return $joined_words;
	}

	/**
	 * Get the words of a post
	 *
	 * @param     int $post_id
	 *
	 * @return    array  $words
	 */
	public function get_words_of_post( $post_id ) {

		// Set the Local
		setlocale( LC_CTYPE, 'en_US.UTF8' );

		// Get the Post
		$post = get_post( $post_id );

		// Get the weights
		$manager_weights   = new RP4WP_Manager_Weights();
		$title_weight      = $manager_weights->get_weight( 'title' );
		$tag_weight        = $manager_weights->get_weight( 'tag' );
		$cat_weight        = $manager_weights->get_weight( 'cat' );
		$custom_tax_weight = $manager_weights->get_weight( 'custom_tax' );

		// Get raw words
		$raw_words = $this->get_content_words( $post );

		// Get words from title
		$title_words = $this->explode( $post->post_title );
		$raw_words   = $this->add_words_from_array( $raw_words, $title_words, $title_weight );

		// Get tags and add them to list
		$tags = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
		if ( is_array( $tags ) && count( $tags ) > 0 ) {
			foreach ( $tags as $tag ) {
				$tag_words = $this->explode( $tag );
				$raw_words = $this->add_words_from_array( $raw_words, $tag_words, $tag_weight );
			}
		}

		// Get categories and add them to list
		$categories = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
		if ( is_array( $categories ) && count( $categories ) > 0 ) {
			foreach ( $categories as $category ) {
				$cat_words = $this->explode( $category );
				$raw_words = $this->add_words_from_array( $raw_words, $cat_words, $cat_weight );
			}
		}

		// Loop through all taxonomies
		$custom_taxonomies = get_taxonomies( array( '_builtin' => false ) );
		if ( count( $custom_taxonomies ) > 0 ) {
			$terms = wp_get_object_terms( $post->ID, array_values( $custom_taxonomies ) );
			if ( count( $terms ) > 0 ) {
				foreach ( $terms as $term ) {
					$term_words = $this->explode( $term->name );
					$raw_words  = $this->add_words_from_array( $raw_words, $term_words, $custom_tax_weight );
				}
			}
		}

		// Loop through meta
		$meta_fields = apply_filters( 'rp4wp_related_meta_fields', array(), $post->ID, $post );
		if ( is_array( $meta_fields ) && count( $meta_fields ) > 0 ) {
			foreach ( $meta_fields as $meta_field ) {

				$meta_values = get_post_meta( $post->ID, $meta_field );
				if ( is_array( $meta_values ) && count( $meta_values ) > 0 ) {

					// Add all values in a string
					$meta_value_string = '';
					foreach ( $meta_values as $meta_value ) {
						$meta_value_string .= ' ' . $meta_value;
					}

					// Trim it
					$meta_value_string = $this->trim( $meta_value_string );

					// Only proceed it string isn't empty
					if ( '' != $meta_value_string ) {
						$meta_value_words = $this->explode( $meta_value_string );
						$raw_words        = $this->add_words_from_array( $raw_words, $meta_value_words, apply_filters( 'rp4wp_related_meta_fields_weight', 20, $post, $meta_field ) );
					}
				}

			}
		}

		// Count words and store them in array
		$words = array();

		if ( is_array( $raw_words ) && count( $raw_words ) > 0 ) {

			$ignored_words = $this->get_ignored_words( apply_filters( 'rp4wp_ignored_words_lang', get_locale(), $post_id ) );

			foreach ( $raw_words as $word ) {

				// Trim word
				$word = strtolower( $this->trim( $word ) );

				// Only use words longer than 1 character
				if ( strlen( $word ) < 2 ) {
					continue;
				}

				// Skip ignored words
				if ( in_array( $word, $ignored_words ) ) {
					continue;
				}

				// Add word
				if ( isset( $words[ $word ] ) ) {
					$words[ $word ] += 1;
				} else {
					$words[ $word ] = 1;
				}

			}
		}

		// reverse sort, most important words at top
		arsort( $words );

		// store new words
		$new_words = array();

		// count total 'raw' words
		$total_raw_words = count( $raw_words );

		// count words added
		$words_added = 0;

		foreach ( $words as $word => $amount ) {

			// add word to new words with relative weight
			$new_words[ $word ] = ( $amount / $total_raw_words );

			// we only add 6 most important words
			$words_added ++;
			if ( $words_added > 5 ) {
				break;
			}
		}

		return $new_words;
	}

	/**
	 * Save words of given post
	 *
	 * @param $post_id
	 */
	public function save_words_of_post( $post_id, $post_type ) {
		global $wpdb;

		// Get words
		$words = $this->get_words_of_post( $post_id );

		// Check words
		if ( is_array( $words ) && count( $words ) > 0 ) {

			// Delete all currents words of post
			$this->delete_words_by_post_id( $post_id );

			// Loop words
			// @todo Add w/ batch in the future
			foreach ( $words as $word => $amount ) {

				// Insert word row
				$wpdb->insert(
					self::get_database_table(),
					array(
						'post_id'   => $post_id,
						'word'      => $word,
						'weight'    => $amount,
						'post_type' => $post_type,
					),
					array(
						'%d',
						'%s',
						'%f',
						'%s',
					)
				);

			}

		}

		// Update this post as cached
		update_post_meta( $post_id, RP4WP_Constants::PM_CACHED, 1 );

	}

	/**
	 * Get uncached posts
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public function get_uncached_post_ids( $post_type, $limit = - 1 ) {

		// Get Posts without 'cached' PM
		return get_posts( array(
			'fields'         => 'ids',
			'post_type'      => $post_type,
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => RP4WP_Constants::PM_CACHED,
					'compare' => 'NOT EXISTS',
//					'value'   => ''
				),
			)
		) );

	}

	/**
	 * Get the uncached post count
	 *
	 * @since  1.6.0
	 * @access public
	 *
	 * @return mixed
	 */
	public function get_uncached_post_count( $post_type ) {
		global $wpdb;

		$post_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(P.ID) FROM " . $wpdb->posts . " P LEFT JOIN " . $wpdb->postmeta . " PM ON (P.ID = PM.post_id AND PM.meta_key = '" . RP4WP_Constants::PM_CACHED . "') WHERE 1=1 AND P.post_type = '%s' AND P.post_status = 'publish' AND PM.post_id IS NULL GROUP BY P.post_status", $post_type ) );

		if ( ! is_numeric( $post_count ) ) {
			$post_count = 0;
		}

		return $post_count;
	}

	/**
	 * Save all words of posts
	 */
	public function save_all_words( $post_type, $limit = - 1 ) {
		global $wpdb;

		// Get uncached posts
		$post_ids = $this->get_uncached_post_ids( $post_type, $limit );

		// Check & Loop
		if ( count( $post_ids ) > 0 ) {

			$done  = 0;
			$total = count( $post_ids );

			foreach ( $post_ids as $post_id ) {
				$this->save_words_of_post( $post_id, $post_type );

				$done++;

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
	 * Delete words by post ID
	 *
	 * @param $post_id
	 */
	public function delete_words_by_post_id( $post_id ) {
		global $wpdb;

		// Delete words
		$wpdb->delete( self::get_database_table(), array( 'post_id' => $post_id ), array( '%d' ) );

		// Delete post meta
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE `meta_key` = '" . RP4WP_Constants::PM_CACHED . "' AND `post_id` = %d ;", $post_id ) );
	}

	/**
	 * Delete words by post type
	 *
	 * @param $post_type
	 *
	 * @since 1.3.0
	 */
	public function delete_words_by_post_type( $post_type ) {
		global $wpdb;

		// Delete words
		$wpdb->delete( self::get_database_table(), array( 'post_type' => $post_type ), array( '%s' ) );

		// Delete post meta
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE `meta_key` = '" . RP4WP_Constants::PM_CACHED . "' AND `post_id` IN (SELECT `ID` FROM $wpdb->posts WHERE `post_type` = '%s' ) ;", $post_type ) );
	}

}