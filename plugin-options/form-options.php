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


$no_form_plugin     = '';
$inquiry_form       = '';
$yit_contact_form   = '';
$contact_form_7     = '';
$gravity_forms      = '';

$active_plugins     = array(
    'default' => __( 'Default', 'yith-woocommerce-request-a-quote' ),
);

if ( function_exists( 'YIT_Contact_Form' ) ) {
    $active_plugins['yit-contact-form'] = __( 'YIT Contact Form', 'yith-woocommerce-request-a-quote' );
}

if ( function_exists( 'wpcf7_contact_form' ) ) {
    $active_plugins['contact-form-7'] = __( 'Contact Form 7', 'yith-woocommerce-request-a-quote' );
}

if ( ywraq_gravity_form_installed() ) {
    $active_plugins['gravity-forms'] = __( 'Gravity Forms', 'yith-woocommerce-request-a-quote' );
}



if ( !empty( $active_plugins ) || function_exists( 'YIT_Contact_Form' ) || function_exists( 'wpcf7_contact_form' ) || ywraq_gravity_form_installed() ) {


    $inquiry_form       = array(
        'name'              => __( 'Request form', 'yith-woocommerce-request-a-quote' ),
        'type'              => 'select',
        'desc'              => __( 'Choose one. You can also add forms from YIT Contact Form or Contact Form 7 that must be installed and activated.', 'yith-woocommerce-request-a-quote' ),
        'options'           => $active_plugins,
        'default'           => 'none',
        'id'                => 'ywraq_inquiry_form_type'
    );

    $yit_contact_form   = array(
        'name'              => '',
        'type'              => 'select',
        'desc'              => __( 'Choose the form to display', 'yith-woocommerce-request-a-quote' ),
        'options'           => apply_filters( 'yit_get_contact_forms', array() ),
        'id'                => 'ywraq_inquiry_yit_contact_form_id',
        'class'             => 'yit-contact-form'
    );


    if( function_exists('icl_get_languages') && class_exists('YITH_YWRAQ_Multilingual_Email')  ){
        $langs = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');

        if( is_array( $langs ) ) {
            foreach ( $langs as $key => $lang ) {
                $contact_form_7['contact_form_7_' . $key] = array(
                    'name' => sprintf( '%s:', $lang['native_name'] ),
                    'type' => 'select',
                    'desc' => __( 'Choose the form to display', 'yith-woocommerce-request-a-quote' ),
                    'options' => apply_filters( 'wpcf7_get_contact_forms', array() ),
                    'id' => 'ywraq_inquiry_contact_form_7_id_' . $key,
                    'class' => 'contact-form-7'
                );
            }


            foreach ( $langs as $key => $lang ) {
                $gravity_forms['gravity_forms_' . $key] = array(
                    'name' => sprintf( '%s:', $lang['native_name'] ),
                    'type' => 'select',
                    'desc' => __( 'Choose the form to display', 'yith-woocommerce-request-a-quote' ),
                    'options' => apply_filters( 'gravity_forms_get_contact_forms', array() ),
                    'id' => 'ywraq_inquiry_gravity_forms_id_' . $key,
                    'class' => 'gravity-forms'
                );
            }
        }

    }else{
        $contact_form_7    = array(
            'name'              => '',
            'type'              => 'select',
            'desc'              => __( 'Choose the form to display', 'yith-woocommerce-request-a-quote' ),
            'options'           => apply_filters( 'wpcf7_get_contact_forms', array() ),
            'id'                => 'ywraq_inquiry_contact_form_7_id',
            'class'             => 'contact-form-7'
        );

	    $gravity_forms    = array(
            'name'              => '',
            'type'              => 'select',
            'desc'              => __( 'Choose the form to display', 'yith-woocommerce-request-a-quote' ),
            'options'           => apply_filters( 'gravity_forms_get_contact_forms', array() ),
            'id'                => 'ywraq_inquiry_gravity_forms_id',
            'class'             => 'gravity-forms'
        );
    }

} else {

    $no_form_plugin     =  __( 'To use this feature, YIT Contact Form or Contact Form 7 must be installed and activated.', 'yith-woocommerce-request-a-quote' );

}

$section1 = array(
    'section_form_settings'     => array(
        'name' => __( 'Form settings', 'yith-woocommerce-request-a-quote' ),
        'type' => 'title',
        'id'   => 'ywraq_section_form'
    ),



    'inquiry_form'                 => $inquiry_form,
    'yit_contact_form'             => $yit_contact_form,
);

$section2 = array();
if ( is_array( $contact_form_7 ) ) {
    foreach ( $contact_form_7 as $k => $cf ) {
        if( ! is_array( $cf ) ){
            $section2['contact_form_7'] = $contact_form_7;
            break;
        }
        $section2[$k] = $cf;
    }
}

if ( is_array( $gravity_forms ) ) {
	foreach ( $gravity_forms as $k => $cf ) {
		if( ! is_array( $cf ) ){
			$section2['gravity_forms'] = $gravity_forms;
			break;
		}
		$section2[$k] = $cf;
	}
}

$section3 = array(
    'additional_text_field' => array(
        'name'              => __( 'Additional text field for default form', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'class'             => 'field_with_deps',
        'id'                => 'ywraq_additional_text_field',
        'custom_attributes' => array( 'data-form-type' => 'default' ),
        'type'              => 'checkbox',
        'default'           => 'no',
    ),

    'additional_text_field_label' => array(
        'name'              => __( 'Label', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_additional_text_field_label',
        'type'              => 'text',
        'class'             => 'regular-input',
        'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field', 'data-form-type' => 'default' ),
        'default'           => '',
    ),

    'additional_text_field_required' => array(
        'name'              => __( 'Required Field', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_additional_text_field_required',
        'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field', 'data-form-type' => 'default' ),
        'type'              => 'checkbox',
        'default'           => 'yes',
    ),

    'additional_text_field_meta' => array(
	    'name'              => __( 'Meta', 'yith-woocommerce-request-a-quote' ),
	    'desc'              => __( 'For example add "_billing_address_1" if you want this field to be the billing address 1 after the quote is sent', 'yith-woocommerce-request-a-quote' ),
	    'id'                => 'ywraq_additional_text_field_meta',
	    'type'              => 'text',
	    'class'             => 'regular-input',
	    'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field', 'data-form-type' => 'default' ),
	    'default'           => '',
    ),

    //@since 1.3.4
    'additional_text_field_2'        => array(
        'name'              => __( 'Additional text field for default form', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'class'             => 'field_with_deps',
        'id'                => 'ywraq_additional_text_field_2',
        'custom_attributes' => array( 'data-form-type' => 'default'),
        'type'              => 'checkbox',
        'default'           => 'no',
    ),

    'additional_text_field_label_2' => array(
        'name'              => __( 'Label', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_additional_text_field_label_2',
        'type'              => 'text',
        'class'             => 'regular-input',
        'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field_2', 'data-form-type' => 'default' ),
        'default'           => '',
    ),

    'additional_text_field_required_2' => array(
        'name'              => __( 'Required Field', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_additional_text_field_required_2',
        'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field_2', 'data-form-type' => 'default' ),
        'type'              => 'checkbox',
        'default'           => 'yes',
    ),

    'additional_text_field_meta_2' => array(
	    'name'              => __( 'Meta', 'yith-woocommerce-request-a-quote' ),
	    'desc'              => __( 'For example add "_billing_address_1" if you want this field to be the billing address 1 after the quote is sent', 'yith-woocommerce-request-a-quote' ),
	    'id'                => 'ywraq_additional_text_field_meta_2',
	    'type'              => 'text',
	    'class'             => 'regular-input',
	    'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field_2', 'data-form-type' => 'default' ),
	    'default'           => '',
    ),

    'additional_text_field_3' => array(
        'name'              => __( 'Additional text field for default form', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_additional_text_field_3',
        'class'             => 'field_with_deps',
        'custom_attributes' => array( 'data-form-type' => 'default' ),
        'type'              => 'checkbox',
        'default'           => 'no',
    ),

    'additional_text_field_label_3' => array(
        'name'              => __( 'Additional text field label for default form', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field_3', 'data-form-type' => 'default' ),
        'id'                => 'ywraq_additional_text_field_label_3',
        'class'             => 'regular-input',
        'type'              => 'text',
        'default'           => '',
    ),

    'additional_text_field_required_3' => array(
        'name'              => __( 'Required Field', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_additional_text_field_required_3',
        'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field_3', 'data-form-type' => 'default' ),
        'type'              => 'checkbox',
        'default'           => 'yes',
    ),

    'additional_text_field_meta_3' => array(
	    'name'              => __( 'Meta', 'yith-woocommerce-request-a-quote' ),
	    'desc'              => __( 'For example add "_billing_address_1" if you want this field to be the billing address 1 after the quote is sent', 'yith-woocommerce-request-a-quote' ),
	    'id'                => 'ywraq_additional_text_field_meta_3',
	    'type'              => 'text',
	    'class'             => 'regular-input',
	    'custom_attributes' => array( 'data-deps' => 'ywraq_additional_text_field_3', 'data-form-type' => 'default' ),
	    'default'           => '',
    ),


    'additional_upload_field' => array(
        'name'              => __( 'Additional upload file field for default form', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'class'             => 'field_with_deps',
        'id'                => 'ywraq_additional_upload_field',
        'custom_attributes' => array( 'data-form-type' => 'default' ),
        'type'              => 'checkbox',
        'default'           => 'no'
    ),

    'additional_upload_field_label' => array(
        'name'              => __( 'Additional upload field label for default form', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_additional_upload_field_label',
        'custom_attributes' => array( 'data-deps' => 'ywraq_additional_upload_field', 'data-form-type' => 'default' ),
        'type'              => 'text',
        'class'             => 'regular-input',
        'default'           => __( 'Upload a file', 'yith-woocommerce-request-a-quote' ),
    ),

    //@since 1.1.6
    'add_user_registration_check'   => array(
        'name'              => __( 'Enable registration on the "Request a Quote" page', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'class'             => 'field_with_deps',
        'id'                => 'ywraq_add_user_registration_check',
        'custom_attributes' => array( 'data-form-type' => 'default' ),
        'type'              => 'checkbox',
        'default'           => 'no'
    ),

    'force_user_to_register' => array(
        'name'              => __( 'Required Field', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_force_user_to_register',
        'custom_attributes' => array( 'data-deps' => 'ywraq_add_user_registration_check', 'data-form-type' => 'default' ),
        'type'              => 'checkbox',
        'default'           => 'yes',
    ),


    'section_end_form' => array(
        'type' => 'sectionend',
        'id'   => 'ywraq_premium_end_form'
    ),

    //@since 1.4.4
    'section_after_submit_action'     => array(
        'name' => __( 'Actions', 'yith-woocommerce-request-a-quote' ),
        'type' => 'title',
        'id'   => 'ywraq_after_submit_action'
    ),


    'message_after_sent_the_request' => array(
        'name'    => __( 'Show this message after a quote request is sent', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_message_after_sent_the_request',
        'class'   => 'field_with_deps regular-input',
        'type'    => 'text',
        'default' => __('Your request has been sent successfully.', 'yith-woocommerce-request-a-quote'),
    ),

    'enable_link_details' => array(
        'name'    => __( 'Show request details after it has been submitted', 'yith-woocommerce-request-a-quote' ),
        'desc'    => __( 'If checked, the link of the quote details will be showed', 'yith-woocommerce-request-a-quote' ),
        'id'      => 'ywraq_enable_link_details',
        'class'   => 'field_with_deps',
        'type'    => 'checkbox',
        'default' => 'yes'
    ),

    'message_to_view_details' => array(
        'name'              => __( 'Show this text to lead users to Details page', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_message_to_view_details',
        'class'             => 'regular-input',
        'type'              => 'text',
        'custom_attributes' => array( 'data-deps' => 'ywraq_enable_link_details' ),
        'default'           => __( 'You can see details at:', 'yith-woocommerce-request-a-quote' ),
    ),

    'activate_thank_you_page' => array(
        'name'    => __( 'Activate Thank-you page', 'yith-woocommerce-request-a-quote' ),
        'desc'    => __( 'If checked, after submitting a request, customers will be redirected to the Thank-you page', 'yith-woocommerce-request-a-quote' ),
        'id'      => 'ywraq_activate_thank_you_page',
        'class'   => 'field_with_deps',
        'type'    => 'checkbox',
        'default' => 'no'
    ),

    'thank_you_page' => array(
        'name'              => __( 'Select your Thank-you page', 'yith-woocommerce-request-a-quote' ),
        'desc'              => '',
        'id'                => 'ywraq_thank_you_page',
        'type'              => 'single_select_page',
        'default'           => '',
        'class'             => 'yith-ywraq-chosen',
        'css'               => 'min-width:300px',
        'desc_tip'          => false,
    ),

    //@since 1.4.4
    'section_after_submit_action_end'             => array(
        'type'              => 'sectionend',
        'id'                => 'ywraq_after_submit_action_end'
    ),

    //@since 1.8
    'section_email_settings'     => array(
        'name' => __( 'Email settings', 'yith-woocommerce-request-a-quote' ),
        'type' => 'title',
        'id'   => 'ywraq_section_email'
    ),

    //@since 1.8
    'quote_detail_link' => array(
        'name'    => __( 'Link to quote request details to be shown in "Request a Quote" email', 'yith-woocommerce-request-a-quote' ),
        'desc'    => '',
        'id'      => 'ywraq_quote_detail_link',
        'type'    => 'select',
        'options' => array(
            'myaccount' => __( 'Quote request details', 'yith-woocommerce-request-a-quote' ),
            'editor'    => __( 'Quote creation page (admin)', 'yith-woocommerce-request-a-quote' ),
        ),
        'default' => 'myaccount'
    ),

    //@since 1.8
    'section_email_end'             => array(
        'type'              => 'sectionend',
        'id'                => 'ywraq_email_end'
    ),
);


$options = array(
	'form' => array_merge( $section1, $section2, $section3)
);

return $options;