<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gm_ai_product_schema() {
	if ( ! is_singular( 'gm_amazon_product' ) ) {
		return;
	}
	global $post;
	$title = get_the_title( $post->ID );
	$img   = get_post_meta( $post->ID, '_gm_image_1', true );
	$data  = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => $title,
		'description' => wp_strip_all_tags( $post->post_content ),
		'sku'         => get_post_meta( $post->ID, '_gm_asin', true ),
		'image'       => array_filter( array(
			$img,
			get_post_meta( $post->ID, '_gm_image_2', true ),
			get_post_meta( $post->ID, '_gm_image_3', true ),
			get_post_meta( $post->ID, '_gm_image_4', true ),
		) ),
		'offers'      => array(
			'@type'         => 'Offer',
			'price'         => get_post_meta( $post->ID, '_gm_price', true ),
			'priceCurrency' => 'INR',
			'url'           => get_post_meta( $post->ID, '_gm_affiliate_url', true ),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>';
}
add_action( 'wp_head', 'gm_ai_product_schema' );
