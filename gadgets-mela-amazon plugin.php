
<?php
/**
 * Plugin Name: Gadgets Mela Amazon Wishlist Importer
 * Plugin URI: https://gadgetsmela2.com
 * Description: Import and manage Amazon affiliate products from wishlist/manual entry for Gadgets Mela.
 * Version: 1.0.0
 * Author: Gadgets Mela
 * Text Domain: gadgets-mela-amazon-importer
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GMAWI_VERSION', '1.0.0');
define('GMAWI_PLUGIN_FILE', __FILE__);
define('GMAWI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GMAWI_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once GMAWI_PLUGIN_DIR . 'includes/product-post-type.php';
require_once GMAWI_PLUGIN_DIR . 'includes/settings.php';
require_once GMAWI_PLUGIN_DIR . 'includes/admin-menu.php';
require_once GMAWI_PLUGIN_DIR . 'includes/shortcodes.php';
require_once GMAWI_PLUGIN_DIR . 'includes/schema.php';

register_activation_hook(__FILE__, 'gmawi_activate_plugin');

function gmawi_activate_plugin() {
    gmawi_register_product_post_type();
    flush_rewrite_rules();

    if (!get_option('gmawi_partner_tag')) {
        update_option('gmawi_partner_tag', 'technicalco0e-21');
    }

    if (!get_option('gmawi_marketplace')) {
        update_option('gmawi_marketplace', 'amazon.in');
    }

    if (!get_option('gmawi_default_wishlist')) {
        update_option('gmawi_default_wishlist', 'https://www.amazon.in/hz/wishlist/ls/J23J5F6XHRWC');
    }
}

register_deactivation_hook(__FILE__, 'gmawi_deactivate_plugin');

function gmawi_deactivate_plugin() {
    flush_rewrite_rules();
}

add_action('wp_enqueue_scripts', 'gmawi_enqueue_frontend_assets');

function gmawi_enqueue_frontend_assets() {
    wp_enqueue_style(
        'gmawi-frontend',
        GMAWI_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        GMAWI_VERSION
    );
}

add_action('admin_enqueue_scripts', 'gmawi_enqueue_admin_assets');

function gmawi_enqueue_admin_assets($hook) {
    if (strpos($hook, 'gmawi') === false && $hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    wp_enqueue_style(
        'gmawi-admin',
        GMAWI_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        GMAWI_VERSION
    );

    wp_enqueue_script(
        'gmawi-admin',
        GMAWI_PLUGIN_URL . 'assets/js/admin.js',
        array('jquery'),
        GMAWI_VERSION,
        true
    );
}

function gmawi_generate_affiliate_url($url) {
    $partner_tag = get_option('gmawi_partner_tag', 'technicalco0e-21');

    if (empty($url)) {
        return '';
    }

    $url = esc_url_raw($url);

    if (strpos($url, 'tag=') !== false) {
        return $url;
    }

    $separator = (strpos($url, '?') !== false) ? '&' : '?';

    return $url . $separator . 'tag=' . urlencode($partner_tag);
}

function gmawi_get_product_meta($post_id) {
    return array(
        'price' => get_post_meta($post_id, '_gmawi_price', true),
        'asin' => get_post_meta($post_id, '_gmawi_asin', true),
        'original_url' => get_post_meta($post_id, '_gmawi_original_url', true),
        'affiliate_url' => get_post_meta($post_id, '_gmawi_affiliate_url', true),
        'image_1' => get_post_meta($post_id, '_gmawi_image_1', true),
        'image_2' => get_post_meta($post_id, '_gmawi_image_2', true),
        'image_3' => get_post_meta($post_id, '_gmawi_image_3', true),
        'image_4' => get_post_meta($post_id, '_gmawi_image_4', true),
        'cta_text' => get_post_meta($post_id, '_gmawi_cta_text', true) ?: 'Buy on Amazon',
    );
}
