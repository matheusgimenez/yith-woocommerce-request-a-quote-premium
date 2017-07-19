<?php
/**
 * Plain Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @version 1.0.0
 * @since   1.0.0
 * @author  Yithemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

echo $email_heading . "\n\n";

$quote_number = apply_filters( 'ywraq_quote_number', $raq_data['order-number'] );
echo sprintf( __( '%s n. %d', 'yith-woocommerce-request-a-quote' ), $email_title, $quote_number ) . "\n\n";

echo $email_description . "\n\n";

echo sprintf( __( 'Request date: %s', 'yith-woocommerce-request-a-quote' ), $raq_data['order-date'] ) . "\n\n";

if ( $raq_data['expiration_data'] != '' ) {
	echo sprintf( __( 'Expiration date: %s', 'yith-woocommerce-request-a-quote' ), $raq_data['expiration_data-date'] ) . "\n\n";
}

if ( ! empty( $raq_data['admin_message'] ) ) {
	echo $raq_data['admin_message'] . "\n\n";
}

//Include table
wc_get_template(
	'emails/plain/quote-table.php', array(
	'order' => $order
)
);

if ( get_option( 'ywraq_show_accept_link' ) != 'no' ) {
	echo ywraq_get_label( 'accept' ) . "\n";
	echo esc_url(
		add_query_arg(
			array(
				'request_quote' => $raq_data['order-number'],
				'status'        => 'accepted',
				'lang'          => yit_get_prop( $order,'wpml_language', true),
				'raq_nonce'     => ywraq_get_token( 'accept-request-quote', $raq_data['order-number'], $raq_data['user_email'] )
			), YITH_Request_Quote()->get_raq_page_url()
		)
	);
	echo "\n\n";
}

if ( get_option( 'ywraq_show_reject_link' ) != 'no' ) {
	echo ywraq_get_label( 'reject' ) . "\n";
	echo esc_url(
		add_query_arg(
			array(
				'request_quote' => $raq_data['order-number'],
				'status'        => 'rejected',
				'lang'          => yit_get_prop( $order,'wpml_language', true),
				'raq_nonce'     => ywraq_get_token( 'reject-request-quote', $raq_data['order-number'], $raq_data['user_email'] )
			), YITH_Request_Quote()->get_raq_page_url()
		)
	);

	echo "\n\n";
}

if ( ( $after_list = yit_get_prop( $order, '_ywraq_request_response_after', true ) ) != '' ) {
	echo apply_filters( 'ywraq_quote_after_list', $after_list, $raq_data['order-id'] ) . "\n\n";
}

echo __( 'Customer details', 'yith-woocommerce-request-a-quote' ) . "\n";

echo __( 'Name:', 'yith-woocommerce-request-a-quote' );
echo $raq_data['user_name'] . "\n";
echo __( 'Email:', 'yith-woocommerce-request-a-quote' );
echo $raq_data['user_email'] . "\n";


$billing_address = yit_get_prop( $order, 'ywraq_billing_address', true );
$billing_phone   = yit_get_prop( $order, 'ywraq_billing_phone', true );
$billing_vat     = yit_get_prop( $order, 'ywraq_billing_vat', true );

if ( $billing_address != '' ) {
	echo __( 'Billing Address:', 'yith-woocommerce-request-a-quote' );
	echo $billing_address . "\n";
}

if ( $billing_phone != '' ) {
	echo __( 'Billing Phone:', 'yith-woocommerce-request-a-quote' );
	echo $billing_phone . "\n";
}

if ( $billing_vat != '' ) {
	echo __( 'Billing VAT:', 'yith-woocommerce-request-a-quote' );
	echo $billing_vat . "\n";
}

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );

