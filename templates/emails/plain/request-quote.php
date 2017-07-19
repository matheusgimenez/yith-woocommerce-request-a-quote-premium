<?php
/**
 * Plain Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @version 1.0.0
 * @since   1.0.0
 * @author  Yithemes
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

echo $email_heading . "\n\n";

echo $email_description . "\n\n";

//Include table

wc_get_template( 'emails/plain/request-quote-table.php', array(
    'raq_data'      => $raq_data
) );


if( ! empty( $raq_data['user_message']) ){

    echo __( 'Customer\'s message', 'yith-woocommerce-request-a-quote' ) . "\n";

    echo $raq_data['user_message']. "\n\n";
}

echo __( 'Customer\'s details', 'yith-woocommerce-request-a-quote' ) . "\n";

echo __( 'Name:', 'yith-woocommerce-request-a-quote' ); echo $raq_data['user_name'] . "\n";
echo __( 'Email:', 'yith-woocommerce-request-a-quote' ); echo $raq_data['user_email'] . "\n";

if( ! empty( $raq_data['user_additional_field']) ){
    echo __( 'Customer\'s additional field:', 'yith-woocommerce-request-a-quote' ); echo $raq_data['user_additional_field'] . "\n";
}

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );