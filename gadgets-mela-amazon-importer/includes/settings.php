<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gm_ai_get_settings() {
	$defaults = array(
		'access_key'   => '',
		'secret_key'   => '',
		'partner_tag'  => 'technicalco0e-21',
		'marketplace'  => 'amazon.in',
		'wishlist_url' => 'https://www.amazon.in/hz/wishlist/ls/J23J5F6XHRWC',
	);
	return wp_parse_args( get_option( 'gm_ai_settings', array() ), $defaults );
}

function gm_ai_register_settings() {
	register_setting( 'gm_ai_settings_group', 'gm_ai_settings', 'gm_ai_sanitize_settings' );
}
add_action( 'admin_init', 'gm_ai_register_settings' );

function gm_ai_sanitize_settings( $input ) {
	return array(
		'access_key'   => isset( $input['access_key'] ) ? sanitize_text_field( $input['access_key'] ) : '',
		'secret_key'   => isset( $input['secret_key'] ) ? sanitize_text_field( $input['secret_key'] ) : '',
		'partner_tag'  => isset( $input['partner_tag'] ) ? sanitize_text_field( $input['partner_tag'] ) : 'technicalco0e-21',
		'marketplace'  => isset( $input['marketplace'] ) ? sanitize_text_field( $input['marketplace'] ) : 'amazon.in',
		'wishlist_url' => isset( $input['wishlist_url'] ) ? esc_url_raw( $input['wishlist_url'] ) : '',
	);
}
