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


$section1 = array(
    'section_general_settings'     => array(
        'name' => __( 'General settings', 'yith-woocommerce-request-a-quote' ),
        'type' => 'title',
        'id'   => 'ywraq_section_general'
    ),

    'page_id' => array(
        'name'     => __( 'Request a Quote', 'yith-woocommerce-request-a-quote' ),
        'desc'     => __( 'Page contents: [yith_ywraq_request_quote]', 'yith-woocommerce-request-a-quote' ),
        'id'       => 'ywraq_page_id',
        'type'     => 'single_select_page',
        'class'    => 'yith-ywraq-chosen',
        'css'      => 'min-width:300px',
        'desc_tip' => false,
    ),

    'after_click_action' => array(
        'name'    => __( 'Button Action after click', 'yith-woocommerce-request-a-quote' ),
        'desc'    =>  __( 'After click the button "Request a Quote" go to the list page', 'yith-woocommerce-request-a-quote' ),
        'id'      => 'ywraq_after_click_action',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

    'show_btn_single_page' => array(
        'name'    => __( 'Show button in single product page', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_show_btn_single_page',
        'type'    => 'checkbox',
        'default' => 'yes'
    ),

    'show_btn_other_pages' => array(
        'name'    => __( 'Show button in other pages', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_show_btn_other_pages',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

    'show_btn_exclusion' => array(
        'name'    => __( 'Hide button for selected products', 'yith-woocommerce-request-a-quote' ),
        'desc'    =>  __( 'Exclude selected products (See tab "Exclusions")', 'yith-woocommerce-request-a-quote' ),
        'id'      => 'ywraq_show_btn_exclusion',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

    //@since 1.1.6
    'reverse_exclusion' => array(
        'name'    => '',
        'desc'    =>  __( 'Reverse exclusion list (Show button for selected products)', 'yith-woocommerce-request-a-quote' ),
        'id'      => 'ywraq_reverse_exclusion',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

	//@since 1.6.0
    'allow_raq_out_of_stock' => array(
	    'name'    => __( 'Allow customers to request a quote even if the product is out of stock', 'yith-woocommerce-request-a-quote' ),
	    'desc'    => '',
	    'id'      => 'ywraq_allow_raq_out_of_stock',
	    'type'    => 'checkbox',
	    'default' => 'no'
    ),

    //@since 1.3.0
    'show_btn_only_out_of_stock' => array(
        'name'    => __( 'Show button only on out of stock products ', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_show_btn_only_out_of_stock',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

    'section_general_settings_end' => array(
        'type' => 'sectionend',
        'id'   => 'ywraq_section_general_end'
    ),

    'product_settings'     => array(
        'name' => __( 'Product settings', 'yith-woocommerce-request-a-quote' ),
        'type' => 'title',
        'id'   => 'ywraq_product_settings'
    ),

    'hide_add_to_cart' => array(
        'name'    => __( 'Hide "Add to cart" button', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_hide_add_to_cart',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

    'hide_price' => array(
        'name'    => __( 'Hide price', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_hide_price',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

    //@since 1.4.4
    'hide_column_total' => array(
        'name'    => __( 'Hide column total in quote list', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_hide_total_column',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

    'show_total_in_list' => array(
	    'name'    => __( 'Show total in quote list', 'yith-woocommerce-request-a-quote' ),
	    'desc'    => '',
	    'id'      => 'ywraq_show_total_in_list',
	    'type'    => 'checkbox',
	    'default' => 'no'
    ),

    'product_settings_end' => array(
        'type' => 'sectionend',
        'id'   => 'ywraq_product_settings_end'
    ),

    'user_settings'     => array(
        'name' => __( 'User settings', 'yith-woocommerce-request-a-quote' ),
        'type' => 'title',
        'id'   => 'ywraq_user_settings'
    ),

    'user_type' => array(
        'name'    => __( 'Show to', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_user_type',
        'type'    => 'select',
        'options' => array(
            'all' => __('Guests and logged-in users', 'yith-woocommerce-request-a-quote'),
            'guests'=> __('Guests', 'yith-woocommerce-request-a-quote'),
            'customers'=> __('Logged-in users', 'yith-woocommerce-request-a-quote'),
        ),
        'default' => 'all',
    ),

    //1.3.0
    'user_role' => array(
        'name'     => __( 'User Role', 'yith-woocommerce-request-a-quote' ),
        'desc'     => '',
        'id'       => 'ywraq_user_role',
        'type'     => 'multiselect',
        'class'    => 'yith-ywraq-chosen',
        'css'      => 'min-width:300px',
        'default'  => 'all',
        'options' => yith_ywraq_get_roles(),
    ),

    'product_user_settings_end' => array(
        'type' => 'sectionend',
        'id'   => 'ywraq_user_settings_end'
    ),

    'email_settings' => array(
        'name' => __( 'Email Settings', 'yith-woocommerce-request-a-quote' ),
        'type' => 'title',
        'id'   => 'ywraq_email_settings'
    ),

    'email_template' => array(
        'name'     => 'Modelo de template para respostas dos orçamentos',
        'desc'     => 'shortcodes disponiveis: [quote_user_email], [quote_list] (tabela de itens), [quote_date], [quote_user_name], [quote_phone]',
        'type'     => 'editor',
        'id'       => 'ywraq_email_template',
    ),
    'email_settings_end' => array(
        'type' => 'sectionend',
        'id'   => 'ywraq_user_settings_end'
    ),


);


$options = array(
	'settings' => $section1
);

if ( catalog_mode_plugin_enabled() ) {
    unset( $options['settings']['hide_add_to_cart'] );
    unset( $options['settings']['hide_price'] );
}
return $options;