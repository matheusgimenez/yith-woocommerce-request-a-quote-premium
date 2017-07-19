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

	'pdf' => array(

		//@since 1.3.0
		'layout_general_settings'     => array(
			'name' => __( 'PDF Quote settings', 'yith-woocommerce-request-a-quote' ),
			'type' => 'title',
			'id'   => 'ywraq_pdf_quote_settings'
		),

		'enable_pdf' => array(
			'name'    => __( 'Allow creating PDF documents', 'yith-woocommerce-request-a-quote' ),
			'desc'    => '',
			'id'      => 'ywraq_enable_pdf',
			'type'    => 'checkbox',
			'default' => 'yes'
		),


		//@since 1.3.0
		'pdf_in_myaccount' => array(
			'name'    => __( 'Allow PDF document download in My Account page', 'yith-woocommerce-request-a-quote' ),
			'desc'    => __( 'If checked, a button "Download PDF" will be added in the quote detail page', 'yith-woocommerce-request-a-quote' ),
			'id'      => 'ywraq_pdf_in_myaccount',
			'type'    => 'checkbox',
			'default' => 'no'
		),

		//@since 1.0.2
		'pdf_logo'         => array(
			'name'              => __( 'Logo', 'yith-woocommerce-request-a-quote' ),
			'desc'              => __( 'Upload the logo you want to show in the PDF document', 'yith-woocommerce-request-a-quote' ),
			'id'                => 'ywraq_pdf_logo',
			'default'           => YITH_YWRAQ_ASSETS_URL.'/images/logo.jpg',
			'type'              => 'ywraq_upload'
		),

		//@since 1.0.2
		'pdf_info'         => array(
			'name'              => __( 'Sender Info in PDF file', 'yith-woocommerce-request-a-quote' ),
			'desc_tip'              => __( 'Add sender information that have to be shown in the PDF document', 'yith-woocommerce-request-a-quote' ),
			'id'                => 'ywraq_pdf_info',
			'default'           => get_bloginfo('name'),
			'css'				=> 'width:50%;height:100px',
			'type'              => 'textarea'
		),

		//@since 1.3.0
		'pdf_footer_content'         => array(
			'name'              => __( 'Add general text on the footer of pdf document', 'yith-woocommerce-request-a-quote' ),
			'desc_tip'              => '',
			'id'                => 'ywraq_pdf_footer_content',
			'default'           => '',
			'css'				=> 'width:50%;height:100px',
			'type'              => 'textarea'
		),

		//@since 1.3.0
		'pdf_pagination' => array(
			'name'    => __( 'Show pagination', 'yith-woocommerce-request-a-quote' ),
			'desc'    => '',
			'id'      => 'ywraq_pdf_pagination',
			'type'    => 'checkbox',
			'default' => 'yes'
		),

		//@since 1.0.2
		'pdf_attachment' => array(
			'name'    => __( 'Attach PDF quote to the email', 'yith-woocommerce-request-a-quote' ),
			'desc'    => __( 'If checked, the quote will be sent as PDF attachment', 'yith-woocommerce-request-a-quote' ),
			'id'      => 'ywraq_pdf_attachment',
			'type'    => 'checkbox',
			'default' => 'no'
		),


		//@since 1.3.0
		'pdf_link' => array(
			'name'    => __( 'Show Link  Accept/Reject', 'yith-woocommerce-request-a-quote' ),
			'desc'    => '',
			'id'      => 'ywraq_pdf_link',
			'type'    => 'checkbox',
			'default' => 'no'
		),

		//@since 1.0.2
		'hide_table_is_pdf_attachment' => array(
			'name'    => __( 'Remove the list with products from the email', 'yith-woocommerce-request-a-quote' ),
			'desc'    => __( 'Hide quote in the email if it has been sent as PDF attachment', 'yith-woocommerce-request-a-quote' ),
			'id'      => 'ywraq_hide_table_is_pdf_attachment',
			'type'    => 'checkbox',
			'default' => 'no'
		),


		'layout_pdf_settings_end_form'             => array(
			'type'              => 'sectionend',
			'id'                => 'ywraq_pdf_quote_settings_end_form'
		),
	)
);