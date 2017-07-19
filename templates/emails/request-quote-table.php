<?php
/**
 * HTML Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 1.4.4
 * @author  Yithemes
 */
$show_price        = true;
$show_total_column = ( get_option( 'ywraq_hide_total_column', 'yes' ) == 'yes' ) ? false : true;
$total             = 0;
$colspan           = 1;
$quote_number      = apply_filters( 'ywraq_quote_number', $raq_data['order_id'] );
if ( get_option( 'ywraq_enable_order_creation', 'yes' ) == 'yes' ) :
	?>
	<h2><?php printf( __( 'Request a Quote #%s', 'yith-woocommerce-request-a-quote' ), $quote_number ) ?></h2>
<?php else: ?>
	<h2><?php _e( 'Request a Quote', 'yith-woocommerce-request-a-quote' ) ?></h2>
<?php endif ?>
<?php do_action( 'yith_ywraq_email_before_raq_table', $raq_data ); ?>
	<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;border-collapse: collapse;">
		<thead>
		<tr>
			<?php if ( get_option( 'ywraq_show_preview' ) == 'yes' ):
				$colspan = 2;
				?>
				<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Preview', 'yith-woocommerce-request-a-quote' ); ?></th>
			<?php endif ?>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'yith-woocommerce-request-a-quote' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'yith-woocommerce-request-a-quote' ); ?></th>
			<?php if ( $show_total_column ): ?>
				<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Subtotal', 'yith-woocommerce-request-a-quote' ); ?></th>
			<?php endif ?>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( ! empty( $raq_data['raq_content'] ) ):
			foreach ( $raq_data['raq_content'] as $key => $item ):

				if ( isset( $item['variation_id'] ) && $item['variation_id'] ) {
					$_product = wc_get_product( $item['variation_id'] );
				} else {
					$_product = wc_get_product( $item['product_id'] );
				}

				if ( ! $_product ) {
					continue;
				}

				$title = $_product->get_title();

				if ( $_product->get_sku() != '' && get_option( 'ywraq_show_sku' ) == 'yes' ) {
					$title .= ' ' . apply_filters( 'ywraq_sku_label', __( ' SKU:', 'yith-woocommerce-request-a-quote' ) ) . $_product->get_sku();
				}

				do_action( 'ywraq_before_request_quote_view_item', $raq_data, $key );


				?>

				<tr>
					<?php if ( get_option( 'ywraq_show_preview' ) == 'yes' ): ?>
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

					<td scope="col" style="text-align:left;border: 1px solid #eee;"><a href="<?php echo $_product->get_permalink() ?>"><?php echo $title ?></a>
						<?php if ( isset( $item['variations'] ) || isset( $item['addons'] ) || isset( $item['yith_wapo_options'] ) ): ?>
							<small><?php echo yith_ywraq_get_product_meta( $item, true, $show_price ); ?></small><?php endif ?></td>
					<td scope="col" style="text-align:left;border: 1px solid #eee;"><?php echo $item['quantity'] ?></td>
					<?php if ( $show_total_column ): ?>
						<td scope="col" style="text-align:left;border: 1px solid #eee;"><?php
							if ( $show_price ) {

								//wc 2.7
								if ( function_exists( 'wc_get_price_to_display' ) ) {
									$total += apply_filters( 'yith_ywraq_product_price', wc_get_price_to_display( $_product, array( 'qty' => $item['quantity'] ) ), $_product, $item );
								} else {
									$total += apply_filters( 'yith_ywraq_product_price', $_product->get_display_price( '', $item['quantity'] ), $_product, $item );
								}


								$price = apply_filters( 'yith_ywraq_product_price_html', WC()->cart->get_product_subtotal( $_product, $item['quantity'] ), $_product, $item );
								echo apply_filters( 'yith_ywraq_hide_price_template', $price, $_product->get_id(), $item );
							}
							?></td>
					<?php endif ?>
				</tr>
				<?php
				do_action( 'ywraq_after_request_quote_view_item_on_email', $raq_data['raq_content'], $key );
			endforeach;
			?>
			<!--
            <?php if ( $show_total_column ): ?>
           
            <tr>
                <td colspan="<?php echo $colspan ?>" style="text-align:right; border: 1px solid #eee;">

                </td>
                <th  style="text-align:left; border: 1px solid #eee;">
                    <?php _e( 'Total:', 'yith-woocommerce-request-a-quote' ) ?>
                </th>
                <td class="raq-totals" scope="col" style="text-align:left;border: 1px solid #eee;">
                    <?php echo wc_price( $total ); ?>
                </td>
            </tr>
            <?php endif; ?>
            -->
		<?php endif; ?>
		</tbody>
	</table>
<?php do_action( 'yith_ywraq_email_after_raq_table', $raq_data ); ?>