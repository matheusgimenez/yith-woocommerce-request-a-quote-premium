<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


return array(
    'label'    => __( 'Single Product Settings for quote requests', 'yith-woocommerce-request-a-quote' ),
    'pages'    => 'product', //or array( 'post-type1', 'post-type2')
    'context'  => 'normal', //('normal', 'advanced', or 'side')
    'priority' => 'high',
    'tabs'     => array(
        'settings' => array(
            'label'  => __( 'Settings', 'yith-woocommerce-request-a-quote' ),
            'fields' => apply_filters( 'ywraq_product_metabox', array(
                    'ywraq_hide_quote_button' => array(
                        'label' => __( '"Add to quote" button', 'yith-woocommerce-request-a-quote' ),
                        'desc'  => __( 'Exclude this product from showing "Add to quote" button', 'yith-woocommerce-request-a-quote' ),
                        'type'  => 'checkbox',
                        'std'   => 0 ),
                )
            )
        )
    )
);