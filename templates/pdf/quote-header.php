<?php

if( function_exists('icl_get_languages') ) {
    global $sitepress;
    $lang = yit_get_prop( $order, 'wpml_language', true );
    YITH_Request_Quote_Premium()->change_pdf_language( $lang );
}

$order_id           = yit_get_prop( $order, 'id', true );
$logo_attachment_id = get_option( 'ywraq_pdf_logo-yith-attachment-id' );
$logo               = $logo_attachment_id  ? get_attached_file( $logo_attachment_id ) : get_option( 'ywraq_pdf_logo' );
$logo               = apply_filters( 'ywraq_pdf_logo', $logo );
$user_email         = yit_get_prop( $order, 'ywraq_customer_email', true );
$user_name          = yit_get_prop( $order, 'ywraq_customer_name', true );
$billing_company    = yit_get_prop( $order, '_billing_company', true );
$billing_address_1  = yit_get_prop( $order, '_billing_address_1', true );
$billing_address_2  = yit_get_prop( $order, '_billing_address_2', true );
$billing_phone      = yit_get_prop( $order, 'ywraq_billing_phone', true );
$billing_vat        = yit_get_prop( $order, 'ywraq_billing_vat', true );
$exdata             = yit_get_prop( $order, '_ywcm_request_expire', true );
$expiration_data    = ( $exdata != '' ) ? date_i18n( wc_date_format(), strtotime( $exdata ) ) : '';
$order_date         = ywraq_adjust_type( 'date_created', yit_get_prop( $order, 'date_created', true ) );
$order_date         = date_i18n( wc_date_format(), strtotime( $order_date ) );

?>
<div class="logo">
   <img src="<?php echo $logo ?>" style="max-width: 300px;" >
</div>
<div class="admin_info right">
    <table>
        <tr>
            <td valign="top" class="small-title"><?php echo __( 'From', 'yith-woocommerce-request-a-quote' ) ?></td>
            <td valign="top" class="small-info">
                <p><?php echo nl2br( get_option( 'ywraq_pdf_info' ) ) ?></p>
            </td>
        </tr>
        <tr>
            <td valign="top" class="small-title"><?php echo __( 'Customer', 'yith-woocommerce-request-a-quote' ) ?></td>
            <td valign="top" class="small-info">
                <p><strong><?php echo $user_name ?></strong><br>
                    <?php echo $user_email ?><br>
                    <?php

                    if( $billing_company != ''){
                        echo $billing_company.'<br>';
                    }

                    if( $billing_address_1 != ''){
                        echo $billing_address_1.'<br>';
                    }

                    if( $billing_address_2 != ''){
                        echo $billing_address_2.'<br>';
                    }

                    if( $billing_phone != ''){
                        echo $billing_phone.'<br>' ;
                    }

                    if( $billing_vat != ''){
                        echo $billing_vat.'<br>' ;
                    } ?>
                </p>
            </td>
        </tr>
        <?php if ( $expiration_data != '' ): ?>
            <tr>
                <td valign="top" class="small-title"><?php echo __( 'Expiration date', 'yith-woocommerce-request-a-quote' ) ?></td>
                <td valign="top" class="small-info">
                    <p><strong><?php echo $expiration_data ?></strong></p>
                </td>
            </tr>
        <?php endif ?>
    </table>
</div>
<div class="clear"></div>
<div class="quote-title">
    <h2><?php printf( __( 'Quote #%s', 'yith-woocommerce-request-a-quote' ), apply_filters( 'ywraq_quote_number', $order_id ) ) ?></h2>
</div>