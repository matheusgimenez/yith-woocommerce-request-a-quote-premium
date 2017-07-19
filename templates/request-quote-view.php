<?php
/**
 * Table view to Request A Quote
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */


$colspan = get_option( 'ywraq_hide_total_column', 'yes' ) == 'yes' ? '4' : '5';
if ( count( $raq_content ) == 0 ):

	echo ywraq_get_list_empty_message();

else: ?>
	<form id="yith-ywraq-form" name="yith-ywraq-form" action="<?php echo esc_url( YITH_Request_Quote()->get_raq_page_url() ) ?>" method="post">
		<table class="shop_table cart  shop_table_responsive" id="yith-ywrq-table-list" cellspacing="0">
			<thead>
				<tr>
					<th class="product-remove">&nbsp;</th>
					<th class="product-thumbnail">&nbsp;</th>
					<th class="product-name"><?php _e( 'Product', 'yith-woocommerce-request-a-quote' ) ?></th>
					<th class="product-quantity"><?php _e( 'Quantity', 'yith-woocommerce-request-a-quote' ) ?></th>
					<?php if ( get_option( 'ywraq_hide_total_column', 'yes' ) == 'no' ): ?>
						<th class="product-subtotal"><?php _e( 'Total', 'yith-woocommerce-request-a-quote' ); ?></th>
					<?php endif ?>
				</tr>
			</thead>
			<tbody>
			<?php

				$total = 0;
				$total_exc = 0;
				$total_inc = 0;
				foreach ( $raq_content as $key => $raq ):
                    $product_id = ( isset( $raq['variation_id'] ) && $raq['variation_id'] != '' ) ? $raq['variation_id'] : $raq['product_id'];
				    $_product = wc_get_product( $product_id );

                    if( ! $_product ){
                        continue;
                    }

                    $show_price = true;

                    do_action( 'ywraq_before_request_quote_view_item', $raq_content, $key );
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'yith_ywraq_item_class', 'cart_item', $raq_content, $key ) ); ?>" <?php echo esc_attr( apply_filters( 'yith_ywraq_item_attributes', '', $raq_content, $key ) ); ?>>

					<td class="product-remove">
						<?php
						    echo apply_filters( 'yith_ywraq_item_remove_link', sprintf( '<a href="#"  data-remove-item="%s" data-wp_nonce="%s"  data-product_id="%d" class="yith-ywraq-item-remove remove" title="%s">&times;</a>', $key, wp_create_nonce( 'remove-request-quote-' . $product_id ), $product_id, __( 'Remove this item', 'yith-woocommerce-request-a-quote' ) ), $key );
						?>

					</td>

					<td class="product-thumbnail">
						<?php $thumbnail = $_product->get_image();

						if ( ! $_product->is_visible() ) {
							echo $thumbnail;
						} else {
							printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
						}
						?>
					</td>

					<td class="product-name" data-title="Product">
						<?php
						$title = $_product->get_title();

						if ( $_product->get_sku() != '' && get_option( 'ywraq_show_sku' ) == 'yes' ) {
							$title .=' '. apply_filters( 'ywraq_sku_label', __( ' SKU:', 'yith-woocommerce-request-a-quote' ) ) . $_product->get_sku();
						}
						?>
						<a href="<?php echo $_product->get_permalink() ?>"><?php echo $title ?></a>
						<?php
						// Meta data

						$item_data = array();

						// Variation data

						if ( ! empty( $raq['variation_id'] ) && is_array( $raq['variations'] ) ) {

							foreach ( $raq['variations'] as $name => $value ) {
								$label = '';

								if ( '' === $value ) {
									continue;
								}

								$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

								// If this is a term slug, get the term's nice name
								if ( taxonomy_exists( $taxonomy ) ) {
									$term = get_term_by( 'slug', $value, $taxonomy );
									if ( ! is_wp_error( $term ) && $term && $term->name ) {
										$value = $term->name;
									}
									$label = wc_attribute_label( $taxonomy );

								} else {

									if ( strpos( $name, 'attribute_' ) !== false ) {
										$custom_att = str_replace( 'attribute_', '', $name );

										if ( $custom_att != '' ) {
											$label = wc_attribute_label( $custom_att );
										} else {
											$label = $name;
										}
									}

								}

								$item_data[] = array(
									'key'   => $label,
									'value' => $value
								);
							}
						}

						$item_data = apply_filters( 'ywraq_request_quote_view_item_data', $item_data, $raq, $_product, $show_price );


						// Output flat or in list format
						if ( sizeof( $item_data ) > 0 ) {
							foreach ( $item_data as $data ) {
								echo esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . "\n";
							}
						}


						?>
					</td>


					<td class="product-quantity" data-title="Quantity">
						<?php
						$product_quantity = woocommerce_quantity_input( array(
							'input_name'  => "raq[{$key}][qty]",
							'input_value' => apply_filters( 'ywraq_quantity_input_value', $raq['quantity'] ),
							'max_value'   => apply_filters( 'ywraq_quantity_max_value', $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(), $_product ),
							'min_value'   => apply_filters( 'ywraq_quantity_min_value', 0, $_product ),
							'step'        => apply_filters( 'ywraq_quantity_step_value', 1, $_product )
						), $_product, false );

						echo $product_quantity;
						?>
					</td>

					<?php if ( get_option( 'ywraq_hide_total_column', 'yes' ) == 'no' ): ?>
						<td class="product-subtotal" data-title="Price">
							<?php

								if ( function_exists( 'wc_get_price_to_display' ) ) {
									$total += apply_filters( 'yith_ywraq_product_price', wc_get_price_to_display( $_product, array( 'qty' => $raq['quantity'] ) ), $_product, $raq );
									$total_exc += apply_filters( 'yith_ywraq_product_price_exc', wc_get_price_excluding_tax( $_product, array( 'qty' => $raq['quantity'] ) ), $_product, $raq );
									$total_inc += apply_filters( 'yith_ywraq_product_price_inc', wc_get_price_including_tax( $_product, array( 'qty' => $raq['quantity'] ) ), $_product, $raq );
								} else {
									$total += apply_filters( 'yith_ywraq_product_price', $_product->get_display_price( '', $raq[ 'quantity' ] ), $_product, $raq );
									$total_exc += apply_filters( 'yith_ywraq_product_price', $_product->get_price_excluding_tax( '', $raq[ 'quantity' ] ), $_product, $raq );
									$total_inc += apply_filters( 'yith_ywraq_product_price', $_product->get_price_including_tax( '', $raq[ 'quantity' ] ), $_product, $raq );
								}


                                $price = apply_filters( 'yith_ywraq_product_price_html', WC()->cart->get_product_subtotal( $_product, $raq[ 'quantity' ] ), $_product, $raq );
                                echo apply_filters( 'yith_ywraq_hide_price_template', $price, $product_id, $raq);
							?>
						</td>
					<?php endif ?>
				</tr>
				<?php do_action( 'ywraq_after_request_quote_view_item', $raq_content, $key ); ?>

			<?php endforeach ?>

				<?php if ( get_option( 'ywraq_hide_total_column', 'yes' ) == 'no' && get_option( 'ywraq_show_total_in_list', 'no' ) == 'yes'  ): ?>

				<tr>
					<td  colspan="3">
					</td>
					<th>
						<?php _e( 'Total:', 'yith-woocommerce-request-a-quote') ?>
					</th>
					<td class="raq-totals">
						<?php echo wc_price( $total_exc ); ?>
					</td>
				</tr>
				<?php endif ?>

				<tr>
					<td colspan="<?php echo $colspan ?>" class="actions">
						<?php if ( get_option( 'ywraq_show_return_to_shop' ) == 'yes' ):
							$shop_url = apply_filters( 'yith_ywraq_return_to_shop_url', get_option( 'ywraq_return_to_shop_url' ) );
							$label_return_to_shop = apply_filters( 'yith_ywraq_return_to_shop_label', get_option( 'ywraq_return_to_shop_label' ) );
							?>
							<a class="button wc-backward" href="<?php echo apply_filters( 'yith_ywraq_return_to_shop_url', $shop_url ); ?>"><?php echo $label_return_to_shop ?></a>
						<?php endif ?>
						<input type="submit" class="button" name="update_raq" value="<?php _e( 'Update List', 'yith-woocommerce-request-a-quote' ) ?>">
						<input type="hidden" id="update_raq_wpnonce" name="update_raq_wpnonce" value="<?php echo wp_create_nonce( 'update-request-quote-quantity' ) ?>">
					</td>
				</tr>

			</tbody>
		</table>
	</form>
<?php endif ?>

