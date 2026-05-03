<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gm_ai_admin_menu() {
	add_menu_page( 'Gadgets Mela Amazon Importer', 'Gadgets Mela Amazon Importer', 'manage_options', 'gm-amazon-importer', 'gm_ai_settings_page', 'dashicons-cart', 26 );
	add_submenu_page( 'gm-amazon-importer', 'Settings', 'Settings', 'manage_options', 'gm-amazon-importer', 'gm_ai_settings_page' );
	add_submenu_page( 'gm-amazon-importer', 'Wishlist Import', 'Wishlist Import', 'manage_options', 'gm-ai-wishlist-import', 'gm_ai_wishlist_import_page' );
	add_submenu_page( 'gm-amazon-importer', 'Imported Products', 'Imported Products', 'manage_options', 'edit.php?post_type=gm_amazon_product' );
	add_submenu_page( 'gm-amazon-importer', 'Manual Product Add', 'Manual Product Add', 'manage_options', 'gm-ai-manual-add', 'gm_ai_manual_add_page' );
}
add_action( 'admin_menu', 'gm_ai_admin_menu' );

function gm_ai_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$settings = gm_ai_get_settings();
	?>
	<div class="wrap gm-ai-wrap"><h1>Settings</h1>
	<form method="post" action="options.php">
	<?php settings_fields( 'gm_ai_settings_group' ); ?>
	<table class="form-table">
	<tr><th>Amazon Access Key</th><td><input type="text" name="gm_ai_settings[access_key]" value="<?php echo esc_attr( $settings['access_key'] ); ?>" class="regular-text"></td></tr>
	<tr><th>Amazon Secret Key</th><td><input type="password" name="gm_ai_settings[secret_key]" value="<?php echo esc_attr( $settings['secret_key'] ); ?>" class="regular-text" autocomplete="new-password"></td></tr>
	<tr><th>Partner Tag</th><td><input type="text" name="gm_ai_settings[partner_tag]" value="<?php echo esc_attr( $settings['partner_tag'] ); ?>" class="regular-text"></td></tr>
	<tr><th>Marketplace</th><td><input type="text" name="gm_ai_settings[marketplace]" value="<?php echo esc_attr( $settings['marketplace'] ); ?>" class="regular-text"></td></tr>
	<tr><th>Default Wishlist URL</th><td><input type="url" name="gm_ai_settings[wishlist_url]" value="<?php echo esc_url( $settings['wishlist_url'] ); ?>" class="regular-text code"></td></tr>
	</table><?php submit_button(); ?>
	</form></div>
	<?php
}

function gm_ai_wishlist_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$messages = array();
	$settings = gm_ai_get_settings();
	if ( isset( $_POST['gm_ai_import_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gm_ai_import_nonce'] ) ), 'gm_ai_import_action' ) ) {
		$wishlist_url = isset( $_POST['wishlist_url'] ) ? esc_url_raw( wp_unslash( $_POST['wishlist_url'] ) ) : '';
		$messages     = gm_ai_fetch_wishlist_products( $wishlist_url );
	}
	?>
	<div class="wrap gm-ai-wrap"><h1>Wishlist Import</h1>
	<form method="post"><?php wp_nonce_field( 'gm_ai_import_action', 'gm_ai_import_nonce' ); ?>
	<p><input type="url" name="wishlist_url" class="large-text" value="<?php echo esc_url( $settings['wishlist_url'] ); ?>"></p>
	<?php submit_button( 'Fetch Wishlist Products' ); ?></form>
	<?php foreach ( $messages as $message ) : ?><div class="notice notice-info"><p><?php echo esc_html( $message ); ?></p></div><?php endforeach; ?>
	</div>
	<?php
}

function gm_ai_manual_add_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$created = 0;
	if ( isset( $_POST['gm_ai_manual_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gm_ai_manual_nonce'] ) ), 'gm_ai_manual_action' ) ) {
		$data = array();
		foreach ( array( 'title','price','asin','original_url','affiliate_url','category','cta_text','description' ) as $f ) {
			$data[ $f ] = isset( $_POST[ $f ] ) ? wp_unslash( $_POST[ $f ] ) : '';
		}
		for ( $i = 1; $i <= 4; $i++ ) { $data[ 'image_' . $i ] = isset( $_POST[ 'image_' . $i ] ) ? wp_unslash( $_POST[ 'image_' . $i ] ) : ''; }
		$result = gm_ai_manual_insert_product( $data );
		if ( ! is_wp_error( $result ) ) { $created = (int) $result; }
	}
	?>
	<div class="wrap gm-ai-wrap"><h1>Manual Product Add</h1>
	<?php if ( $created ) : ?><div class="notice notice-success"><p>Product imported successfully. ID: <?php echo esc_html( (string) $created ); ?></p></div><?php endif; ?>
	<form method="post"><?php wp_nonce_field( 'gm_ai_manual_action', 'gm_ai_manual_nonce' ); ?>
	<p><input name="title" required placeholder="Product title" class="large-text"></p>
	<p><textarea name="description" placeholder="Short description" class="large-text"></textarea></p>
	<p><input name="price" placeholder="Price" class="regular-text"> <input name="asin" placeholder="ASIN" class="regular-text"></p>
	<p><input name="original_url" type="url" placeholder="Original Amazon URL" class="large-text"></p>
	<p><input name="affiliate_url" type="url" placeholder="Affiliate URL (optional)" class="large-text"></p>
	<?php for ( $i = 1; $i <= 4; $i++ ) : ?><p><input name="image_<?php echo esc_attr( $i ); ?>" type="url" placeholder="Image URL <?php echo esc_attr( $i ); ?>" class="large-text"></p><?php endfor; ?>
	<p><input name="category" placeholder="Category" class="regular-text"> <input name="cta_text" placeholder="CTA button text" value="Buy on Amazon" class="regular-text"></p>
	<?php submit_button( 'Import Product' ); ?>
	</form></div>
	<?php
}
