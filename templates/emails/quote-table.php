<?php
/**
 * HTML Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 1.4.4
 * @author  Yithemes
 */

/** @var WC_Order $order_id */
$order_id = yit_get_prop( $order, 'id', true);
add_filter('woocommerce_is_attribute_in_product_name','__return_false');

?>
<?php if( ( $before_list = yit_get_prop( $order, '_ywraq_request_response_before', true ) ) != ''): ?>
	<p><?php echo apply_filters( 'ywraq_quote_before_list', $before_list, $order_id ) ?></p>
<?php endif; ?>

<?php
$colspan = 2;

do_action( 'yith_ywraq_email_before_raq_table', $order );
?>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;border-collapse: collapse;">
	<thead>
	<tr>
		<?php if ( get_option( 'ywraq_show_preview' ) == 'yes' ): ?>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Preview', 'yith-woocommerce-request-a-quote' ); ?></th>
		<?php endif ?>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'yith-woocommerce-request-a-quote' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'yith-woocommerce-request-a-quote' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Subtotal', 'yith-woocommerce-request-a-quote' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$items = $order->get_items();

	if ( ! empty( $items ) ):

		foreach ( $items as $item ):

			if( isset( $item['variation_id'] ) && $item['variation_id'] ){
				$_product = wc_get_product( $item['variation_id'] );
			}else{
				$_product = wc_get_product( $item['product_id'] );
			}

			if( ! $_product ){
				continue;
			}

			$subtotal = wc_price( $item['line_total'] );

			if ( get_option( 'ywraq_show_old_price' ) == 'yes' ) {
				$subtotal = ( $item['line_subtotal'] != $item['line_total'] ) ? '<small><del>' . wc_price( $item['line_subtotal'] ) . '</del></small> ' . wc_price( $item['line_total'] ) : wc_price( $item['line_subtotal'] );
			}

			$title    = $_product->get_title();

			if ( $_product->get_sku() != '' && get_option( 'ywraq_show_sku' ) == 'yes' ) {
				$title .= ' '. apply_filters( 'ywraq_sku_label', __( ' SKU:', 'yith-woocommerce-request-a-quote' ) ) . $_product->get_sku();
			}

			//retro compatibility
			$im = false;
			if ( version_compare( WC()->version, '2.7.0', '<' ) ) {
				$im = new WC_Order_Item_Meta( $item );
			}

			?>
			<tr>
				<?php if ( get_option( 'ywraq_show_preview' ) == 'yes' ):
					$colspan = 3;
					?>
					<td scope="col" style="text-align:center;border: 1px solid #eee;">
						<?php

						$dimensions = wc_get_image_size( 'shop_thumbnail' );
						$height     = esc_attr( $dimensions['height'] );
						$width      = esc_attr( $dimensions['width'] );
						$src        = ( $_product->get_image_id() ) ? current( wp_get_attachment_image_src( $_product->get_image_id(), 'shop_thumbnail' ) ) : wc_placeholder_img_src();

						?>
						<a href="<?php echo $_product->get_permalink(); ?>"><img src="<?php echo $src; ?>" height="<?php echo $height; ?>" width="<?php echo $width; ?>" /></a>
					</td>
				<?php endif ?>

				<td scope="col" style="text-align:left;border: 1px solid #eee;">
					<a href="<?php echo $_product->get_permalink() ?>"><?php echo $title ?></a>
						<small><?php
							if ( $im ) {
								$im->display();
							} else {
								wc_display_item_meta( $item );
							}
							?></small></td>
				<td scope="col" style="text-align:center;border: 1px solid #eee;"><?php echo $item['qty'] ?></td>
				<td scope="col" style="text-align:right;border: 1px solid #eee;"><?php echo apply_filters('ywraq_quote_subtotal_item', ywraq_formatted_line_total( $order, $item ), $item['line_total'], $_product); ?></td>

			</tr>

			<?php
		endforeach; ?>

		<?php
		foreach ( $order->get_order_item_totals() as $key => $total ) {
			?>
			<tr>
				<th scope="col" colspan="<?php echo $colspan ?>" style="text-align:right;border: 1px solid #eee;"><?php echo $total['label']; ?></th>
				<td scope="col" style="text-align:right;border: 1px solid #eee;"><?php echo $total['value']; ?></td>
			</tr>
			<?php
		}
		?>

	<?php endif; ?>
	</tbody>
</table>

<?php

do_action( 'yith_ywraq_email_after_raq_table', $order ); ?>

