<?php
$author = get_user_by( 'ID', $related_post->post_author );
if ( is_object( $author ) ) :
	?>
	<span><?php echo $author->display_name; ?></span>
	<?php
endif;