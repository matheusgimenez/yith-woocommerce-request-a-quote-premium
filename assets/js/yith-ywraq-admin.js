/**
* Javascript functions to administrator pane
*
* @package YITH Woocommerce Request A Quote
* @since   1.0.0
* @version 1.0.0
* @author  Yitheme
*/
jQuery(document).ready(function($) {
    "use strict";

    var select          = $( document).find( '.yith-ywraq-chosen' );

    select.each( function() {
        $(this).chosen({
            width: '350px',
            disable_search: true,
            multiple: true
        })
    });
    

    //Contact form selection
    var yit_contact_form   = $( 'select.yit-contact-form' ).parent().parent(),
        contact_form_7     = $( 'select.contact-form-7' ).parent().parent(),
        gravity_forms     = $( 'select.gravity-forms' ).parent().parent();

    $( 'select#ywraq_inquiry_form_type' ).change(function(){

        var option = $( 'option:selected', this ).val();

        switch (option) {
            case "yit-contact-form":
                yit_contact_form.show();
                contact_form_7.hide();
                gravity_forms.hide();
                break;
            case "contact-form-7":
                yit_contact_form.hide();
                gravity_forms.hide();
                contact_form_7.show();
                break;
            case "gravity-forms":
                yit_contact_form.hide();
                contact_form_7.hide();
                gravity_forms.show();
                break;
            default:
                yit_contact_form.hide();
                contact_form_7.hide();
                gravity_forms.hide();
        }

    }).change();


    //Order functions

    $('#ywraq_submit_button').on('click', function(e){
        e.preventDefault();
        $('#_ywraq_safe_submit_field').val('send_quote');

       $(this).closest('form').submit();
    });

    //Order functions

    $('#ywraq_pdf_button').on('click', function(e){
        e.preventDefault();
        $('#_ywraq_safe_submit_field').val('create_preview_pdf');

        $(this).closest('form').submit();
    });

    //datepicker

        if( $('.metaboxes-tab #_ywcm_request_expire-container .panel-datepicker').length > 0){
            $('.metaboxes-tab #_ywcm_request_expire-container .panel-datepicker').each( function() {
                $.datepicker.setDefaults({
                    gotoCurrent: true,
                    dateFormat: 'yy-mm-dd'
                });
                $(this).datepicker('option','minDate',"1d");

            });
        }


    //Field with deps
    $( '.field_with_deps' ).on('change', function(){
        var $t = $(this),
            id = $t.attr('id');
        if( $t.is(':checked')) {
            if ($t.attr('id') == 'ywraq_show_accept_link') {
                $('#ywraq_page_accepted, #ywraq_accept_link_label').closest('tr').show().addClass('sub-option');
            }else if( $t.attr('id') == 'ywraq_activate_thank_you_page' ){
                $('#ywraq_thank_you_page').closest('tr').show().addClass('sub-option');
            } else {
                $('[data-deps="' + id + '"]').each(function () {
                    $(this).closest('tr').show().addClass('sub-option');
                });
            }
        } else{
            if ($t.attr('id') == 'ywraq_show_accept_link') {
                $('#ywraq_page_accepted, #ywraq_accept_link_label').closest('tr').hide();
            } else if( $t.attr('id') == 'ywraq_activate_thank_you_page' ){
                $('#ywraq_thank_you_page').closest('tr').hide();
            } else {
                $('[data-deps="'+id+'"]').each(function(){
                    $(this).closest('tr').hide();
                });
            }
        }
    }).change();


    $('#ywraq_inquiry_form_type').on('change', function(){
        var $t = $(this),
            form_type = $t.val();


        $('input[data-form-type]').each(function(){
            $(this).closest('tr').hide();
        });

        $('input[data-form-type="'+form_type+'"]').each(function(){
            $(this).closest('tr').show();
            $( '.field_with_deps' ).trigger('change');
        });


    }).change();

    $('#ywraq_pdf_file').attr('disabled','disabled');


});