<?php
/**
 * HTML Template Email Send Quote
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 1.1.8
 * @author  Yithemes
 */

do_action( 'woocommerce_email_header', $email_heading, $email );

$args_accept = array(
	'request_quote' => $raq_data['order-id'],
	'status'        => 'accepted',
	'raq_nonce'     => ywraq_get_token( 'accept-request-quote', $raq_data['order-id'], $raq_data['user_email'] )
);

$args_reject = array(
	'request_quote' => $raq_data['order-id'],
	'status'        => 'rejected',
	'raq_nonce'     => ywraq_get_token( 'reject-request-quote', $raq_data['order-id'], $raq_data['user_email'] )
);



$order_id = yit_get_prop( $order, 'id', true );
$exdata   = yit_get_prop( $order, '_ywcm_request_expire', true );

if ( function_exists( 'wc_format_datetime' ) ) {
	$order_date = wc_format_datetime( $order->get_date_created() );
	$exdata     = new WC_DateTime( $exdata, new DateTimeZone( 'UTC' ) );
	$exp_date   = wc_format_datetime( $exdata );
} else {
	$date_format = isset( $raq_data['lang'] ) ? ywraq_get_date_format( $raq_data['lang'] ) : wc_date_format();
	$order_date  = date_i18n( $date_format, ywraq_adjust_type( 'date_created', yit_get_prop( $order, 'date_created', true ) ) );
	$exp_date    = date_i18n( $date_format, strtotime( $exdata ) );
}

?>

	<h2><?php printf( __( '%s n. %s', 'yith-woocommerce-request-a-quote' ), $email_title, $raq_data['order-number'] ) ?></h2>

	<p><?php echo $email_description; ?></p>

	<?php if ( get_option( 'ywraq_hide_table_is_pdf_attachment' ) == 'no' ): ?>
		<p><strong><?php _e( 'Request date', 'yith-woocommerce-request-a-quote' ) ?></strong>: <?php  echo $order_date ?></p>
	<?php if ( $raq_data['expiration_data'] != '' ): ?>
		<p><strong><?php _e( 'Expiration date', 'yith-woocommerce-request-a-quote' ) ?></strong>: <?php echo $exp_date ?></p>
	<?php endif ?>

	<?php if ( ! empty( $raq_data['admin_message'] ) ): ?>
		<p><?php echo $raq_data['admin_message'] ?></p>
	<?php endif ?>

	<?php
	wc_get_template( 'emails/quote-table.php', array(
		'order' => $order
	) );
	?>
	<p></p>
<?php endif ?>
	<p>
		<?php if ( get_option( 'ywraq_show_accept_link' ) != 'no' ): ?>
			<a href="<?php echo esc_url( add_query_arg( $args_accept, YITH_Request_Quote()->get_raq_page_url() ) ) ?>"><?php ywraq_get_label( 'accept', true ) ?></a>
		<?php endif;
		echo ( get_option( 'ywraq_show_accept_link' ) != 'no' && get_option( 'ywraq_show_reject_link' ) != 'no' ) ? ' | ' : '';
		if ( get_option( 'ywraq_show_reject_link' ) != 'no' ): ?>
			<a href="<?php echo esc_url( add_query_arg( $args_reject, YITH_Request_Quote()->get_raq_page_url() ) ) ?>"><?php ywraq_get_label( 'reject', true ) ?></a>
		<?php endif; ?>
	</p>

<?php if ( ( $after_list = yit_get_prop( $order, '_ywraq_request_response_after', true ) ) != '' ): ?>
	<p><?php echo apply_filters( 'ywraq_quote_after_list', nl2br( $after_list ), $order_id ) ?></p>
<?php endif; ?>



	<h2><?php _e( 'Customer\'s details', 'yith-woocommerce-request-a-quote' ); ?></h2>

	<p><strong><?php _e( 'Name:', 'yith-woocommerce-request-a-quote' ); ?></strong> <?php echo $raq_data['user_name'] ?></p>
	<p><strong><?php _e( 'Email:', 'yith-woocommerce-request-a-quote' ); ?></strong>
		<a href="mailto:<?php echo $raq_data['user_email']; ?>"><?php echo $raq_data['user_email']; ?></a></p>

<?php

$billing_address = yit_get_prop( $order, 'ywraq_billing_address', true );
$billing_phone   = yit_get_prop( $order, 'ywraq_billing_phone', true );
$billing_vat     = yit_get_prop( $order, 'ywraq_billing_vat', true );

if ( $billing_address != '' ): ?>
	<p><strong><?php _e( 'Billing Address:', 'yith-woocommerce-request-a-quote' ); ?></strong> <?php echo $billing_address ?></p>
<?php endif;
if ( $billing_phone != '' ): ?>
	<p><strong><?php _e( 'Billing Phone:', 'yith-woocommerce-request-a-quote' ); ?></strong> <?php echo $billing_phone ?></p>
<?php endif;
if ( $billing_vat != '' ): ?>
	<p><strong><?php _e( 'Billing VAT:', 'yith-woocommerce-request-a-quote' ); ?></strong> <?php echo $billing_vat ?></p>
<?php endif; ?>


<?php

$af1 = yit_get_prop( $order, 'ywraq_customer_additional_field', true );
if ( ! empty( $af1 ) ) {
	printf( '<p><strong>%s</strong>: %s</p>', get_option( 'ywraq_additional_text_field_label' ), $af1 );
}

$af2 = yit_get_prop( $order, 'ywraq_customer_additional_field_2', true );
if ( ! empty( $af2 ) ) {
	printf( '<p><strong>%s</strong>: %s</p>', get_option( 'ywraq_additional_text_field_label_2' ), $af2 );
}

$af3 = yit_get_prop( $order, 'ywraq_customer_additional_field_3', true );
if ( ! empty( $af3 ) ) {
	printf( '<p><strong>%s</strong>: %s</p>', get_option( 'ywraq_additional_text_field_label_3' ), $af3 );
}

$af4 = yit_get_prop( $order, 'ywraq_customer_other_email_content', true );
if ( ! empty( $af4 ) ) {
	printf( '<p>%s</p>', $af4 );
}

?>


<?php
do_action( 'woocommerce_email_footer', $email );
?>