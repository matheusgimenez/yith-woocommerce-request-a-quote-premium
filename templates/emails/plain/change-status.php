<?php
/**
 * Plain Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @version 1.0.0
 * @since   1.6.0
 * @author  Yithemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

echo $email_heading . "\n\n";

$order_id = yit_get_prop( $order, 'id', true );

$order_id = apply_filters( 'ywraq_quote_number', $order_id );
if ( $status == 'accepted' ):
	printf( __( 'The Proposal #%d has been accepted', 'yith-woocommerce-request-a-quote' ), $order_id );
else:
	printf( __( 'The Proposal #%d has been rejected', 'yith-woocommerce-request-a-quote' ), $order_id );
endif;
echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );