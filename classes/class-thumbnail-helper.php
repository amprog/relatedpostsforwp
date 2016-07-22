<?php

class RP4WP_Thumbnail_Helper {

	/**
	 * @var RP4WP_Thumbnail_Helper
	 */
	private static $instance = null;

	/**
	 * Private constructor
	 */
	private function __construct() {
		//
	}

	/**
	 * Singleton get method
	 *
	 * @return RP4WP_Thumbnail_Helper
	 */
	public static function get() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get thumbnail width
	 *
	 * @param string $post_type
	 *
	 * @return int
	 */
	public function get_width( $post_type ) {
		return absint( RP4WP::get()->settings[ 'general_' . $post_type ]->get_option( 'thumbnail_width' ) );
	}

	/**
	 * Get thumbnail height
	 *
	 * @param string $post_type
	 *
	 * @return int
	 */
	public function get_height( $post_type ) {
		return absint( RP4WP::get()->settings[ 'general_' . $post_type ]->get_option( 'thumbnail_height' ) );
	}

	/**
	 * Get thumbnail crop setting
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function get_crop( $post_type ) {
		return ( 1 == RP4WP::get()->settings[ 'general_' . $post_type ]->get_option( 'thumbnail_crop' ) );
	}

	/**
	 * Register the custom thumbnail size
	 *
	 * @param string $post_type
	 */
	public function register_thumbnail_size( $post_type ) {
		add_image_size( 'rp4wp-thumbnail-' . $post_type, $this->get_width( $post_type ), $this->get_height( $post_type ), $this->get_crop( $post_type ) );
	}

	/**
	 * Get thumbnail size (note: not dimension but the WP registered size variable)
	 *
	 * @param string $post_type
	 *
	 * @return string
	 */
	public function get_size( $post_type ) {
		return $thumbnail_size = apply_filters( 'rp4wp_thumbnail_size', 'rp4wp-thumbnail-' . $post_type );
	}

	/**
	 * Get attachment ID based on URL
	 * Code source: http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
	 *
	 * @param string $attachment_url
	 *
	 * @return bool|null|string
	 */
	private function get_attachment_id_from_url( $attachment_url ) {
		global $wpdb;
		$attachment_id = false;

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

		}

		return $attachment_id;
	}

	/**
	 * Find first image in post
	 *
	 * @param int $post_id
	 * @param string $post_type
	 *
	 * @return string
	 */
	public function get_first_image( $post_id, $post_type ) {

		$thumbnail = '';

		// get post
		$post = get_post( $post_id );

		// check if $post is WP_Post
		if ( $post instanceof WP_Post ) {

			// regex pattern
			$pattern = '`<img(?:.*)src=(?:\'|"{1})([^"|\']+)(?:\'|"{1})(?:.*)/>`is';

			// do pregmatch
			if ( 1 == preg_match( $pattern, $post->post_content, $matches ) ) {

				// check if matches are set
				if ( is_array( $matches ) && isset( $matches[1] ) ) {

					// query database for attachment ID
					$attachment_id = $this->get_attachment_id_from_url( $matches[1] );

					// check if attachment found in DB
					if ( false !== $attachment_id && 0 !== $attachment_id ) {

						// get correct thumbnail size for thumbnail
						$attachment_src = wp_get_attachment_image_src( $attachment_id, $this->get_size( $post_type ) );

						// check if attachement source was found
						if ( is_array( $attachment_src ) && count( $attachment_src ) > 0 ) {

							// set correct thumbnail
							$thumbnail = sprintf( '<img src="%s" class="attachment-thumbnail wp-post-image" alt="%s">', $attachment_src[0], $post->post_title );
						}
					}

				}
			}

		}

		return $thumbnail;
	}

	/**
	 * Get placeholder image
	 *
	 * @param int $post_id
	 * @param string $post_type
	 *
	 * @return string
	 */
	public function get_placeholder_image( $post_id, $post_type ) {

		$thumbnail = '';

		// get post
		$post = get_post( $post_id );

		// get placeholder source
		$placeholder = RP4WP::get()->settings[ 'general_' . $post_type ]->get_option( 'thumbnail_placeholder' );

		// check if placeholder is set
		if ( ! empty( $placeholder ) ) {

			// set correct thumbnail
			$thumbnail = sprintf( '<img src="%s" class="attachment-thumbnail wp-post-image" alt="%s">', $placeholder, $post->post_title );
		}

		return $thumbnail;
	}

	/**
	 * Get the thumbnail by post ID
	 *
	 * @param int $post_id
	 * @param string $post_type
	 *
	 * @return string
	 */
	public function get_thumbnail( $post_id, $post_type ) {

		// check if post has a featured image
		if ( has_post_thumbnail( $post_id ) ) {
			// get the featured image
			$thumbnail = get_the_post_thumbnail( $post_id, $this->get_size( $post_type ) );
		}

		// find first image in content if thumbnail still empty
		if ( empty( $thumbnail ) ) {
			$thumbnail = $this->get_first_image( $post_id, $post_type );
		}

		// use thumbnail placeholder if image still empty
		if ( empty( $thumbnail ) ) {
			$thumbnail = $this->get_placeholder_image( $post_id, $post_type );
		}

		return $thumbnail;
	}

}