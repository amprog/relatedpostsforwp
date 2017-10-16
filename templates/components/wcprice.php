<?php
$product = wc_get_product( $related_post->ID );
if ( ! $product ) {
	return;
}
echo $product->get_price_html();