<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gm_ai_render_product_card( $post_id ) {
	$title = get_the_title( $post_id );
	$price = get_post_meta( $post_id, '_gm_price', true );
	$link  = get_post_meta( $post_id, '_gm_affiliate_url', true );
	$cta   = get_post_meta( $post_id, '_gm_cta_text', true );
	$cta   = $cta ? $cta : 'Buy on Amazon';
	ob_start();
	?>
	<div class="gm-ai-card">
		<div class="gm-ai-main-image"><?php if ( get_post_meta( $post_id, '_gm_image_1', true ) ) : ?><img loading="lazy" src="<?php echo esc_url( get_post_meta( $post_id, '_gm_image_1', true ) ); ?>" alt="<?php echo esc_attr( $title ); ?>"><?php endif; ?></div>
		<div class="gm-ai-gallery"><?php for ( $i = 1; $i <= 4; $i++ ) : $img = get_post_meta( $post_id, '_gm_image_' . $i, true ); if ( $img ) : ?><img loading="lazy" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>"><?php endif; endfor; ?></div>
		<h3><?php echo esc_html( $title ); ?></h3>
		<?php if ( $price ) : ?><p class="gm-ai-price"><?php echo esc_html( $price ); ?></p><?php endif; ?>
		<a href="<?php echo esc_url( $link ); ?>" class="gm-ai-btn" target="_blank" rel="nofollow sponsored"><?php echo esc_html( $cta ); ?></a>
	</div>
	<?php
	return ob_get_clean();
}

function gm_ai_products_shortcode() {
	$q = new WP_Query( array( 'post_type' => 'gm_amazon_product', 'posts_per_page' => 20 ) );
	ob_start();
	echo '<div class="gm-ai-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		echo gm_ai_render_product_card( get_the_ID() );
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'gm_amazon_products', 'gm_ai_products_shortcode' );

function gm_ai_product_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'gm_amazon_product' );
	return gm_ai_render_product_card( (int) $atts['id'] );
}
add_shortcode( 'gm_amazon_product', 'gm_ai_product_shortcode' );
