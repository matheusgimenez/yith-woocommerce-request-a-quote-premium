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

	'ywraq-layout' => array(


		'layout_general_settings' => array(
			'name' => __( 'Layout settings', 'yith-woocommerce-request-a-quote' ),
			'type' => 'title',
			'id'   => 'ywraq_layout_settings'
		),

		'show_btn_link' => array(
			'name'    => __( 'Button type', 'yith-woocommerce-request-a-quote' ),
			'desc'    => '',
			'id'      => 'ywraq_show_btn_link',
			'type'    => 'select',
			'options' => array(
				'link'   => __( 'Link', 'yith-woocommerce-request-a-quote' ),
				'button' => __( 'Button', 'yith-woocommerce-request-a-quote' ),
			),
			'default' => 'button',
		),

		'show_btn_link_text' => array(
			'name'    => __( 'Button/Link text', 'yith-woocommerce-request-a-quote' ),
			'desc'    => '',
			'id'      => 'ywraq_show_btn_link_text',
			'type'    => 'text',
			'default' => __( 'Add to quote', 'yith-woocommerce-request-a-quote' ),
		),


		'layout_settings_button_bg_color' => array(
			'name'    => __( 'Button background color', 'yith-woocommerce-request-a-quote' ),
			'type'    => 'color',
			'desc'    => '',
			'id'      => 'ywraq_layout_button_bg_color',
			'default' => '#0066b4'
		),

		'layout_settings_button_bg_color_hover' => array(
			'name'    => __( 'Button background color on hover ', 'yith-woocommerce-request-a-quote' ),
			'type'    => 'color',
			'desc'    => '',
			'id'      => 'ywraq_layout_button_bg_color_hover',
			'default' => '#044a80'
		),

		'layout_settings_button_color' => array(
			'name'    => __( 'Button/Link text color', 'yith-woocommerce-request-a-quote' ),
			'type'    => 'color',
			'desc'    => '',
			'id'      => 'ywraq_layout_button_color',
			'default' => '#fff'
		),

		'layout_settings_button_color_hover' => array(
			'name'    => __( 'Button/Link text color hover', 'yith-woocommerce-request-a-quote' ),
			'type'    => 'color',
			'desc'    => '',
			'id'      => 'ywraq_layout_button_color_hover',
			'default' => '#fff'
		),
		//@since 1.6.0
		'show_button_near_add_to_cart' => array(
			'name'    => __( 'Show button next to "Add to cart" in single product page', 'yith-woocommerce-request-a-quote' ),
			'desc'    => __( 'If checked, the button will be showed next to the Add to cart button in the single product page', 'yith-woocommerce-request-a-quote' ),
			'id'      => 'ywraq_show_button_near_add_to_cart',
			'type'    => 'checkbox',
			'default' => 'no'
		),

		'layout_general_settings_end_form' => array(
			'type' => 'sectionend',
			'id'   => 'ywraq_layout_settings_end_form'
		),


		//@since 1.1.6
		'layout_data_settings'             => array(
			'name' => __( 'Data Settings', 'yith-woocommerce-request-a-quote' ),
			'type' => 'title',
			'id'   => 'ywraq_data_settings'
		),

		//@since 1.1.6
		'show_sku'                         => array(
			'name'    => __( 'Show SKU on list table', 'yith-woocommerce-request-a-quote' ),
			'desc'    => __( 'If checked, the sku will be added near the title of product in the request list', 'yith-woocommerce-request-a-quote' ),
			'id'      => 'ywraq_show_sku',
			'type'    => 'checkbox',
			'default' => 'no'
		),

		//@since 1.1.6
		'show_preview'                     => array(
			'name'    => __( 'Show preview thumbnail on email list table', 'yith-woocommerce-request-a-quote' ),
			'desc'    => __( 'If checked, the thumbnail will be added in the table of request and in the proposal email', 'yith-woocommerce-request-a-quote' ),
			'id'      => 'ywraq_show_preview',
			'type'    => 'checkbox',
			'default' => 'no'
		),

		//@since 1.3.0
		'show_old_price'                   => array(
			'name'    => __( 'Show old price on list table', 'yith-woocommerce-request-a-quote' ),
			'desc'    => __( 'If checked, the old price will be showed in the table of request and in the proposal email', 'yith-woocommerce-request-a-quote' ),
			'id'      => 'ywraq_show_old_price',
			'type'    => 'checkbox',
			'default' => 'yes'
		),

		//@since 1.4.9
		'show_return_to_shop'              => array(
			'name'    => __( 'Show "Return to Shop" button', 'yith-woocommerce-request-a-quote' ),
			'desc'    => __( 'If checked, the "Return to Shop" button will be showed in the quote table', 'yith-woocommerce-request-a-quote' ),
			'id'      => 'ywraq_show_return_to_shop',
			'class'   => 'field_with_deps',
			'type'    => 'checkbox',
			'default' => 'yes'
		),

		//@since 1.4.9
		'return_to_shop_label'             => array(
			'name'              => __( '"Return to Shop" button label', 'yith-woocommerce-request-a-quote' ),
			'desc'              => __( '"Return to Shop" label', 'yith-woocommerce-request-a-quote' ),
			'id'                => 'ywraq_return_to_shop_label',
			'class'             => 'regular-input',
			'custom_attributes' => array( 'data-deps' => 'ywraq_show_return_to_shop' ),
			'type'              => 'text',
			'default'           => __( 'Return to Shop', 'yith-woocommerce-request-a-quote' )
		),

		//@since 1.4.9
		'return_to_shop_url'               => array(
			'name'              => __( '"Return to Shop" URL', 'yith-woocommerce-request-a-quote' ),
			'desc'              => __( '"Return to Shop" URL', 'yith-woocommerce-request-a-quote' ),
			'id'                => 'ywraq_return_to_shop_url',
			'class'             => 'regular-input',
			'custom_attributes' => array( 'data-deps' => 'ywraq_show_return_to_shop' ),
			'type'              => 'text',
			'default'           => function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : get_permalink( woocommerce_get_page_id( 'shop' ) ),
		),

		//@since 1.1.6
		'layout_data_settings_end_form'    => array(
			'type' => 'sectionend',
			'id'   => 'ywraq_layout_data_end_form'
		),


	)
);