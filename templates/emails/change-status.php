<?php
/**
 * HTML Template Email Request a Quote
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.1.6
 * @version 1.1.8
 * @author  Yithemes
 */
$order_id = yit_get_prop( $order, 'id', true );

$quote_number = apply_filters( 'ywraq_quote_number',  $order_id );

do_action( 'woocommerce_email_header', $email_heading, $email );

?>


<?php if( $status == 'accepted'): ?>
    <p><?php printf( __('The Proposal #%s has been accepted', 'yith-woocommerce-request-a-quote'), $quote_number ) ?></p>
<?php else: ?>
    <p><?php printf( __('The Proposal #%s has been rejected', 'yith-woocommerce-request-a-quote'), $quote_number ) ?></p>
<?php endif ?>
    <p></p>
    <p><?php printf( __( 'You can see details here: <a href="%s">#%s</a>', 'yith-woocommerce-request-a-quote' ),  admin_url( 'post.php?post='.$order_id.'&action=edit'), $quote_number ) ?></p>

<?php
do_action( 'woocommerce_email_footer', $email );
?>