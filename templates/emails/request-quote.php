<?php
/**
 * HTML Template Email Request a Quote
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 1.3.4
 * @author  Yithemes
 */

$order_id = $raq_data['order_id'];
$order    = wc_get_order( $order_id );
$customer = yit_get_prop( $order, '_customer_user', true );
$page_detail_admin = ( get_option('ywraq_quote_detail_link') == 'editor' ) ? true : false;
$quote_number = apply_filters( 'ywraq_quote_number', $raq_data['order_id'] );
do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p><?php echo $email_description ?></p>


<?php
    wc_get_template( 'emails/request-quote-table.php', array(
        'raq_data'      => $raq_data
    ) );
?>
<p></p>

<?php if(  ( $customer != 0 && ( get_option( 'ywraq_enable_link_details' ) == "yes" && get_option( 'ywraq_enable_order_creation', 'yes' ) == 'yes' ) ) || ( $page_detail_admin &&  get_option( 'ywraq_enable_order_creation', 'yes' ) == 'yes' )): ?>
    <p><?php printf( __( 'You can see details here: <a href="%s">#%s</a>', 'yith-woocommerce-request-a-quote' ), YITH_YWRAQ_Order_Request()->get_view_order_url($order_id, $page_detail_admin), $quote_number ); ?></p>
<?php endif ?>


<?php if( ! empty( $raq_data['user_message']) ): ?>
<h2><?php _e( 'Customer\'s message', 'yith-woocommerce-request-a-quote' ); ?></h2>
    <p><?php echo $raq_data['user_message'] ?></p>
<?php endif ?>
<h2><?php _e( 'Customer\'s details', 'yith-woocommerce-request-a-quote' ); ?></h2>

<p><strong><?php _e( 'Name:', 'yith-woocommerce-request-a-quote' ); ?></strong> <?php echo $raq_data['user_name'] ?></p>
<p><strong><?php _e( 'Email:', 'yith-woocommerce-request-a-quote' ); ?></strong> <a href="mailto:<?php echo $raq_data['user_email']; ?>"><?php echo $raq_data['user_email']; ?></a></p>

<?php if( ! empty( $raq_data['user_additional_field']) || ! empty( $raq_data['user_additional_field_2']) || ! empty( $raq_data['user_additional_field_3']) ): ?>
<h2><?php _e( 'Customer\'s additional fields', 'yith-woocommerce-request-a-quote' ); ?></h2>

<?php if( ! empty( $raq_data['user_additional_field']) ): ?>
    <p><?php printf( '<strong>%s</strong>: %s', get_option('ywraq_additional_text_field_label'), $raq_data['user_additional_field'] ) ?></p>
<?php endif ?>

<?php if( ! empty( $raq_data['user_additional_field_2']) ): ?>
        <p><?php printf( '<strong>%s</strong>: %s', get_option('ywraq_additional_text_field_label_2'), $raq_data['user_additional_field_2'] ) ?></p>
<?php endif ?>

<?php if( ! empty( $raq_data['user_additional_field_3']) ): ?>
    <p><?php printf( '<strong>%s</strong>: %s', get_option('ywraq_additional_text_field_label_3'), $raq_data['user_additional_field_3'] ) ?></p>
<?php endif ?>

<?php endif ?>
<?php
echo get_option( 'ywraq_email_template', 'arrrrroz');
do_action( 'woocommerce_email_footer', $email );

?>
