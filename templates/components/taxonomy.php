<?php


// fetch and format terms
$terms = RP4WP_Taxonomy_Helper::format_terms_of_post( $related_post->ID, $custom );

if ( ! empty( $terms ) ) :
	?>
	<span><?php echo $terms; ?></span>
	<?php
endif;
?>