<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gm_register_amazon_product_cpt() {
	$labels = array(
		'name'          => __( 'Amazon Products', 'gadgets-mela-amazon-importer' ),
		'singular_name' => __( 'Amazon Product', 'gadgets-mela-amazon-importer' ),
	);

	register_post_type(
		'gm_amazon_product',
		array(
			'labels'       => $labels,
			'public'       => true,
			'show_in_menu' => false,
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
		)
	);
}
add_action( 'init', 'gm_register_amazon_product_cpt' );

function gm_amazon_product_meta_boxes() {
	add_meta_box( 'gm_amazon_meta', __( 'Product Details', 'gadgets-mela-amazon-importer' ), 'gm_amazon_product_meta_box_cb', 'gm_amazon_product', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'gm_amazon_product_meta_boxes' );

function gm_amazon_product_meta_box_cb( $post ) {
	wp_nonce_field( 'gm_save_product_meta', 'gm_product_meta_nonce' );
	$fields = array(
		'price'         => get_post_meta( $post->ID, '_gm_price', true ),
		'asin'          => get_post_meta( $post->ID, '_gm_asin', true ),
		'original_url'  => get_post_meta( $post->ID, '_gm_original_url', true ),
		'affiliate_url' => get_post_meta( $post->ID, '_gm_affiliate_url', true ),
		'category'      => get_post_meta( $post->ID, '_gm_category', true ),
		'cta_text'      => get_post_meta( $post->ID, '_gm_cta_text', true ),
	);
	for ( $i = 1; $i <= 4; $i++ ) {
		$fields[ 'image_' . $i ] = get_post_meta( $post->ID, '_gm_image_' . $i, true );
	}
	?>
	<p><label>Price</label><br><input type="text" name="gm_price" value="<?php echo esc_attr( $fields['price'] ); ?>" class="widefat"></p>
	<p><label>ASIN</label><br><input type="text" name="gm_asin" value="<?php echo esc_attr( $fields['asin'] ); ?>" class="widefat"></p>
	<p><label>Original Amazon URL</label><br><input type="url" name="gm_original_url" value="<?php echo esc_url( $fields['original_url'] ); ?>" class="widefat"></p>
	<p><label>Affiliate URL</label><br><input type="url" name="gm_affiliate_url" value="<?php echo esc_url( $fields['affiliate_url'] ); ?>" class="widefat"></p>
	<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
		<p><label><?php echo esc_html( 'Image URL ' . $i ); ?></label><br><input type="url" name="gm_image_<?php echo esc_attr( $i ); ?>" value="<?php echo esc_url( $fields[ 'image_' . $i ] ); ?>" class="widefat"></p>
	<?php endfor; ?>
	<p><label>Category</label><br><input type="text" name="gm_category" value="<?php echo esc_attr( $fields['category'] ); ?>" class="widefat"></p>
	<p><label>CTA Button Text</label><br><input type="text" name="gm_cta_text" value="<?php echo esc_attr( $fields['cta_text'] ? $fields['cta_text'] : 'Buy on Amazon' ); ?>" class="widefat"></p>
	<?php
}

function gm_save_amazon_product_meta( $post_id ) {
	if ( ! isset( $_POST['gm_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gm_product_meta_nonce'] ) ), 'gm_save_product_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$map = array(
		'gm_price'         => '_gm_price',
		'gm_asin'          => '_gm_asin',
		'gm_original_url'  => '_gm_original_url',
		'gm_affiliate_url' => '_gm_affiliate_url',
		'gm_category'      => '_gm_category',
		'gm_cta_text'      => '_gm_cta_text',
	);

	foreach ( $map as $source => $meta ) {
		if ( isset( $_POST[ $source ] ) ) {
			$value = in_array( $source, array( 'gm_original_url', 'gm_affiliate_url' ), true ) ? esc_url_raw( wp_unslash( $_POST[ $source ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $source ] ) );
			update_post_meta( $post_id, $meta, $value );
		}
	}

	for ( $i = 1; $i <= 4; $i++ ) {
		if ( isset( $_POST[ 'gm_image_' . $i ] ) ) {
			update_post_meta( $post_id, '_gm_image_' . $i, esc_url_raw( wp_unslash( $_POST[ 'gm_image_' . $i ] ) ) );
		}
	}
}
add_action( 'save_post_gm_amazon_product', 'gm_save_amazon_product_meta' );
