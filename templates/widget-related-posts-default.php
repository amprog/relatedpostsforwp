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

			// setup the postdata
			setup_postdata( $post );

			?>
			<li>
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</li>
		<?php
		}
		$post = $o_post;
		wp_reset_postdata();
		?>
	</ul>
</div>