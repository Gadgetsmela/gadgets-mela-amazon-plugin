<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gm_ai_build_affiliate_url( $url ) {
	$settings = gm_ai_get_settings();
	$tag      = ! empty( $settings['partner_tag'] ) ? $settings['partner_tag'] : 'technicalco0e-21';
	if ( empty( $url ) ) {
		return '';
	}
	return add_query_arg( 'tag', rawurlencode( $tag ), $url );
}

function gm_ai_manual_insert_product( $data ) {
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'gm_amazon_product',
			'post_status' => 'publish',
			'post_title'  => sanitize_text_field( $data['title'] ),
			'post_content'=> sanitize_textarea_field( $data['description'] ),
		)
	);
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}
	update_post_meta( $post_id, '_gm_price', sanitize_text_field( $data['price'] ) );
	update_post_meta( $post_id, '_gm_asin', sanitize_text_field( $data['asin'] ) );
	update_post_meta( $post_id, '_gm_original_url', esc_url_raw( $data['original_url'] ) );
	update_post_meta( $post_id, '_gm_affiliate_url', esc_url_raw( gm_ai_build_affiliate_url( $data['affiliate_url'] ? $data['affiliate_url'] : $data['original_url'] ) ) );
	update_post_meta( $post_id, '_gm_category', sanitize_text_field( $data['category'] ) );
	update_post_meta( $post_id, '_gm_cta_text', sanitize_text_field( $data['cta_text'] ) );
	for ( $i = 1; $i <= 4; $i++ ) {
		update_post_meta( $post_id, '_gm_image_' . $i, esc_url_raw( $data[ 'image_' . $i ] ) );
	}
	return $post_id;
}

function gm_ai_fetch_wishlist_products( $wishlist_url ) {
	$settings = gm_ai_get_settings();
	$response = array();
	if ( ! empty( $settings['access_key'] ) && ! empty( $settings['secret_key'] ) ) {
		$response[] = __( 'PA-API credentials found. Integrate signed PA-API request workflow for production use.', 'gadgets-mela-amazon-importer' );
	}
	$response[] = sprintf( __( 'Wishlist URL received: %s', 'gadgets-mela-amazon-importer' ), esc_url_raw( $wishlist_url ) );
	$response[] = __( 'If fetch is unavailable, use Manual Product Add to import products.', 'gadgets-mela-amazon-importer' );
	return $response;
}
