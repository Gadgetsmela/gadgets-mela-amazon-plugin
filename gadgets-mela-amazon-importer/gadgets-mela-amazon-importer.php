<?php
/**
 * Plugin Name: Gadgets Mela Amazon Wishlist Importer
 * Description: Import Amazon wishlist products into WordPress with affiliate link handling and frontend shortcodes.
 * Version: 1.0.0
 * Author: Gadgets Mela
 * Text Domain: gadgets-mela-amazon-importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GM_AMAZON_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'GM_AMAZON_IMPORTER_URL', plugin_dir_url( __FILE__ ) );

require_once GM_AMAZON_IMPORTER_PATH . 'includes/product-post-type.php';
require_once GM_AMAZON_IMPORTER_PATH . 'includes/settings.php';
require_once GM_AMAZON_IMPORTER_PATH . 'includes/importer.php';
require_once GM_AMAZON_IMPORTER_PATH . 'includes/admin-menu.php';
require_once GM_AMAZON_IMPORTER_PATH . 'includes/shortcodes.php';
require_once GM_AMAZON_IMPORTER_PATH . 'includes/schema.php';

function gm_amazon_importer_activate() {
	if ( false === get_option( 'gm_ai_settings' ) ) {
		add_option(
			'gm_ai_settings',
			array(
				'partner_tag'  => 'technicalco0e-21',
				'marketplace'  => 'amazon.in',
				'wishlist_url' => 'https://www.amazon.in/hz/wishlist/ls/J23J5F6XHRWC',
			)
		);
	}

	gm_register_amazon_product_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'gm_amazon_importer_activate' );

function gm_amazon_importer_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'gm_amazon_importer_deactivate' );

function gm_amazon_importer_admin_assets( $hook ) {
	if ( false === strpos( $hook, 'gm-amazon-importer' ) ) {
		return;
	}

	wp_enqueue_style( 'gm-ai-admin', GM_AMAZON_IMPORTER_URL . 'assets/css/admin.css', array(), '1.0.0' );
	wp_enqueue_script( 'gm-ai-admin', GM_AMAZON_IMPORTER_URL . 'assets/js/admin.js', array( 'jquery' ), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'gm_amazon_importer_admin_assets' );

function gm_amazon_importer_frontend_assets() {
	wp_enqueue_style( 'gm-ai-frontend', GM_AMAZON_IMPORTER_URL . 'assets/css/frontend.css', array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'gm_amazon_importer_frontend_assets' );
