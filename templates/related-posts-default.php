<div class="rp4wp-related-posts rp4wp-related-<?php echo esc_attr( $post_type ); ?>">

	<?php
	if ( '' != $heading_text ) {
		$heading = "<h3>" . $heading_text . "</h3>";
		echo apply_filters( 'rp4wp_heading', $heading );
	}
	?>
	<ul class="rp4wp-posts-list">
		<?php
		global $post;
		$o_post      = $post;
		$row_counter = 0;
		foreach ( $related_posts as $post ) {

			// Setup the postdata
			setup_postdata( $post );

			// Load the content template
			$manager_template = new RP4WP_Manager_Template();

			$manager_template->get_template( 'related-post-default.php', array(
				'related_post'   => $post,
				'excerpt_length' => $excerpt_length,
				'row_counter'    => $row_counter,
				'parent'         => $o_post
			) );

			$row_counter ++;
		}
		$post = $o_post;
		wp_reset_postdata();
		?>
	</ul>
	<?php
	if ( '1' == RP4WP()->settings['misc']->get_option( 'show_love' ) ) {

		// Base
		$base_url     = "https://www.relatedpostsforwp.com";
		$query_string = "?";

		// Allow affiliates to add affiliate ID to Power By link
		$ref = apply_filters( 'rp4wp_poweredby_affiliate_id', '' );
		if ( '' !== $ref ) {
			$ref = intval( $ref );
			$query_string .= "ref=" . $ref . '&';
		}

		// The UTM campaign stuff
		$query_string .= sprintf( "utm_source=%s&utm_medium=link&utm_campaign=poweredby", strtolower( preg_replace( "`[^A-z0-9\-.]+`i", '', str_ireplace( ' ', '-', html_entity_decode( get_bloginfo( 'name' ) ) ) ) ) );

		// The URL
		$url = esc_url( $base_url . htmlentities( $query_string ) );

		$manager_template->get_template( 'show-love.php', array( 'link' => $url ) );
	}
	?>

	pooop
</div>