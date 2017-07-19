jQuery(document).ready( function($){
    "use strict";


    var $body = $('body'),
        $add_to_cart_el = $('.add-request-quote-button'),
        $widget = $(document).find('.widget_ywraq_list_quote, .widget_ywraq_mini_list_quote'),
        ajax_loader    = ( typeof ywraq_frontend !== 'undefined' ) ? ywraq_frontend.block_loader : false,
        $remove_item = $('.yith-ywraq-item-remove'),
        $shipping_form = $('.shipping_address'),
        $billing_form = $('.woocommerce-billing-fields');


    /* Variation change */
    $.fn.yith_ywraq_variations = function() {

        var $product_id = $('[name|="product_id"]'),
            product_id = $product_id.val(),
            button = $('.add-to-quote-' + product_id).find('a.add-request-quote-button'),
            $button_wrap = button.parents('.yith-ywraq-add-to-quote'),
            $variation_id = $('[name|="variation_id"]');

        if( ! $variation_id.length ){
            return false;
        }

        button.show().addClass('disabled');

        $variation_id.on('change', function () {

            var  $variation_data =[],
                variations =  '' + $button_wrap.attr('data-variation');

            if( variations != 'undefined' ){
                $variation_data = variations.split(',');
            }

            $('.yith_ywraq_add_item_browse-list-' + $product_id.val()).hide().removeClass('show');
            $('.yith_ywraq_add_item_product-response-' + $product_id.val()).hide().removeClass('show');
            $('.yith_ywraq_add_item_response-' + $product_id.val()).show().removeClass('hide');


            if ( $(this).val() == '') {
                button.show().addClass('disabled');
                button.parent().show().removeClass('hide').removeClass('addedd');

                $('.yith_ywraq_add_item_product-response-' + $product_id.val()).hide().removeClass('show');
                $('.yith_ywraq_add_item_response-' + $product_id.val()).hide().removeClass('show');
                $('.yith_ywraq_add_item_browse-list-' + $product_id.val()).hide().removeClass('show');
            } else {
                if(  $.inArray( $(this).val(), $variation_data ) >= 0 )  {
                    button.hide();
                    $('.yith_ywraq_add_item_response-' + $product_id.val()).show().removeClass('hide');
                    $('.yith_ywraq_add_item_browse-list-' + $product_id.val()).show().removeClass('hide');
                }else{
                    button.show().removeClass('disabled');
                    $('.yith_ywraq_add_item_response-' + $product_id.val()).hide().removeClass('show');
                    $('.yith_ywraq_add_item_browse-list-' + $product_id.val()).hide().removeClass('show');
                }
            }

        });
    };


    $.fn.yith_ywraq_variations();
	$.fn.yith_ywraq_refresh_button = function() {
		var $product_id = $('[name|="product_id"]'),
			product_id = $product_id.val(),
			button = $('.add-to-quote-' + product_id).find('a.add-request-quote-button'),
			$button_wrap = button.parents('.yith-ywraq-add-to-quote'),
			$variation_id = $('[name|="variation_id"]');

            if( ! $variation_id.length ){
                return false;
            }

	}
    $.fn.yith_ywraq_refresh_button();


    var xhr = false;

    /* Add to cart element */
    $(document).on( 'click' ,'.add-request-quote-button', function(e){

        e.preventDefault();


        var $t = $(this),
            $t_wrap = $t.closest('.yith-ywraq-add-to-quote'),
            add_to_cart_info = 'ac',
            $cart_form = '';


        // if( $t.hasClass('disabled') ) {
        //     alert("Please select some product options before adding this product to the list")
        // }

        if( $t.hasClass('disabled') || xhr ) {
            return;
        }

        // find the form
        if( $t.closest( '.cart' ).length ){
            $cart_form = $t.closest( '.cart' );
        }else if( $t_wrap.siblings( '.cart' ).first().length ) {
            $cart_form = $t_wrap.siblings( '.cart' ).first();
        }else if( $('.composite_form').length ){
            $cart_form = $('.composite_form') ;
        }
        else {
            $cart_form = $('.cart');
        }

        if ( typeof $cart_form[0] !== 'undefined' && typeof $cart_form[0].checkValidity === 'function' && !$cart_form[0].checkValidity()) {
            // If the form is invalid, submit it. The form won't actually submit;
            // this will just cause the browser to display the native HTML5 error messages.
            $('<input type="submit">').hide().appendTo($cart_form).click().remove();
            return;
        }

        if ( $t.closest('ul.products').length > 0) {
            var $add_to_cart_el = '',
                $product_id_el = $t.closest('li.product').find('a.add_to_cart_button'),
                $product_id_el_val = $product_id_el.data( 'product_id' );
        }else{
            var $add_to_cart_el = $t.closest('.product').find('input[name="add-to-cart"]'),
                $product_id_el = $t.closest('.product').find('input[name="product_id"]'),
                $product_id_el_val = $product_id_el.length ? $product_id_el.val() : $add_to_cart_el.val();

        }

        var prod_id = ( typeof $product_id_el_val == 'undefined') ? $t.data('product_id') : $product_id_el_val;

        add_to_cart_info = $cart_form.serializefiles();

        add_to_cart_info.append('context','frontend');
        add_to_cart_info.append('action','yith_ywraq_action');
        add_to_cart_info.append('ywraq_action','add_item');
        add_to_cart_info.append('product_id',$t.data('product_id'));
        add_to_cart_info.append('wp_nonce',$t.data('wp_nonce'));
        add_to_cart_info.append('yith-add-to-cart',$t.data('product_id'));



        $(document).trigger( 'yith_ywraq_action_before' );

        if ( typeof yith_wapo_general !== 'undefined' ) {
            if( ! yith_wapo_general.do_submit ) {
                return false;
            }
        }

        xhr = $.ajax({
            type   : 'POST',
            url    : ywraq_frontend.ajaxurl.toString().replace( '%%endpoint%%', 'yith_ywraq_action' ),
            dataType: 'json',
            data   : add_to_cart_info,
            contentType: false,
            processData: false,
            beforeSend: function(){
                $t.after( ' <img src="'+ajax_loader+'" >' );
            },
            complete: function(){
                $t.next().remove();
            },

            success: function (response) {
                    if( response.result == 'true' || response.result == 'exists'){

                        if( ywraq_frontend.go_to_the_list == 'yes' ){
                            window.location.href = response.rqa_url;
                        }else{

                            $('.yith_ywraq_add_item_product-response-' + prod_id).show().removeClass('hide').html( response.message );
                            $('.yith_ywraq_add_item_browse-list-' + prod_id).show().removeClass('hide');
                            $t.parent().hide().removeClass('show').addClass('addedd');
                            $('.add-to-quote-'+ prod_id).attr('data-variation', response.variations );

                            if( $widget.length ){
                                $widget.ywraq_refresh_widget();
                                $widget = $(document).find('.widget_ywraq_list_quote, .widget_ywraq_mini_list_quote');
                            }
                        }

                        $(document).trigger( 'yith_wwraq_added_successfully' );

                    }else if( response.result == 'false' ){
                        $('.yith_ywraq_add_item_response-' + prod_id ).show().removeClass('hide').html( response.message );

                        $(document).trigger( 'yith_wwraq_error_while_adding' );
                    }
                xhr = false;
            }
        });

    });

    $.fn.serializefiles = function() {
        var obj = $(this);
        /* ADD FILE TO PARAM AJAX */
        var formData = new FormData();
        $.each($(obj).find("input[type='file']"), function(i, tag) {
            $.each($(tag)[0].files, function(i, file) {
                formData.append(tag.name, file);
            });
        });
		console.log($(obj));
        var params = $(obj).serializeArray();
		console.log(params);
        var quantity_in = false;
        $.each(params, function (i, val) {
            if( val.name == 'quantity' || val.name.indexOf("quantity")){
                quantity_in = true;
            }

            if( val.name != 'add-to-cart'){
              formData.append(val.name, val.value);
            }
        });

        if( quantity_in === false ){
            formData.append('quantity', 1 );
        }
        return formData;
    };


    /* Refresh the widget */
    $.fn.ywraq_refresh_widget = function () {
        $widget.each(function () {
            var $t = $(this),
                $wrapper_list = $t.find('.yith-ywraq-list-wrapper'),
                $list = $t.find('.yith-ywraq-list'),
                data_widget = $t.find('.yith-ywraq-list-widget-wrapper').data('instance');

            $.ajax({
                type      : 'POST',
                url       : ywraq_frontend.ajaxurl.toString().replace('%%endpoint%%', 'yith_ywraq_action'),
                data      : data_widget + '&ywraq_action=refresh_quote_list&action=yith_ywraq_action&context=frontend',
                beforeSend: function () {
                    $list.css('opacity', 0.5);
                    if ($t.hasClass('widget_ywraq_list_quote')) {
                        $wrapper_list.prepend(' <img src="' + ajax_loader + '" >');
                    }
                },
                complete  : function () {
                    if ($t.hasClass('widget_ywraq_list_quote')) {
                        $wrapper_list.next().remove();
                    }
                    $list.css('opacity', 1);
                },
                success   : function (response) {
                    if ($t.hasClass('widget_ywraq_mini_list_quote')) {
                        $t.find('.yith-ywraq-list-widget-wrapper').html(response.mini);
                    } else {
                        $t.find('.yith-ywraq-list-widget-wrapper').html(response.large);
                    }
                }
            });
        });
    };

     /*Remove an item from rqa list*/
    $(document).on('click', '.yith-ywraq-item-remove', function (e) {

        e.preventDefault();

        var $t = $(this),
            key = $t.data('remove-item'),
            wrapper = $t.parents('.ywraq-wrapper'),
            form = $('#yith-ywraq-form'),
            cf7 = wrapper.find('.wpcf7-form'),
            remove_info = '';

        remove_info = 'context=frontend&action=yith_ywraq_action&ywraq_action=remove_item&key=' + $t.data('remove-item') + '&wp_nonce=' + $t.data('wp_nonce') + '&product_id=' + $t.data('product_id');

        $.ajax({
            type      : 'POST',
            url       : ywraq_frontend.ajaxurl.toString().replace('%%endpoint%%', 'yith_ywraq_action'),
            dataType  : 'json',
            data      : remove_info,
            beforeSend: function () {
                $t.find('.ajax-loading').css('visibility', 'visible');
            },
            complete  : function () {
                $t.siblings('.ajax-loading').css('visibility', 'hidden');
            },
            success: function (response) {
                if (response === 1) {
                    var $row_to_remove = $("[data-remove-item='" + key + "']").parents('.cart_item');

                    //compatibility with WC Composite Products
                    if ($row_to_remove.hasClass('composite-parent')) {
                        var composite_id = $row_to_remove.data('composite-id');
                        $("[data-composite-id='" + composite_id + "']").remove();
                    }

                    //compatibility with YITH WooCommerce Product Bundles Premium
                    if ($row_to_remove.hasClass('bundle-parent')) {
                        var bundle_key = $row_to_remove.data('bundle-key');
                        $("[data-bundle-key='" + bundle_key + "']").remove();
                    }

                    $row_to_remove.remove();

                    if ($('.cart_item').length === 0) {

                        if (cf7.length) {
                            cf7.remove();
                        }

                        $('#yith-ywraq-form, .yith-ywraq-mail-form-wrapper').remove();
                        $('#yith-ywraq-message').html(ywraq_frontend.no_product_in_list);
                    }
                    if ($widget.length) {
                        $widget.ywraq_refresh_widget();
                        $widget = $(document).find('.widget_ywraq_list_quote, .widget_ywraq_mini_list_quote');
                    }

                    $(document).trigger( 'yith_wwraq_removed_successfully' );
                }
                else{
                    $(document).trigger( 'yith_wwraq_error_while_removing' );
                }
            }
        });
    });

    var content_data = '';
    var $cform7 =  $('.wpcf7-submit').closest('.wpcf7');

    if( $cform7.length > 0 ){

        $(document).find('.wpcf7').each( function()
        {
            var $cform7 = $(this);
            var idform = $cform7.find('input[name="_wpcf7"]').val();

            if ( idform == ywraq_frontend.cform7_id ) {

                $cform7.on('wpcf7:mailsent', function () {
                    $.ajax({
                        type    : 'POST',
                        url     : ywraq_frontend.ajaxurl.toString().replace( '%%endpoint%%', 'yith_ywraq_order_action' ),
                        dataType: 'json',
                        data    : {
                            lang:   ywraq_frontend.current_lang,
                            action: 'yith_ywraq_order_action',
                            current_user_id : ywraq_frontend.current_user_id,
                            context: 'frontend',
                            ywraq_order_action: 'mail_sent_order_created'
                        },
                        success: function (response) {
                            if (response.rqa_url != '') {
                                window.location.href = response.rqa_url;
                            }
                        }
                    });
                });
            }
        });
    }


    $('#yith-ywrq-table-list').on('change', '.qty', function() {
        var qty = $(this).val();
        if (qty <= 0) {
            $(this).val(1);
        }
    });

    var reject_buttons = $('.quotes-actions').find('.reject'),
        table = $('.my_account_quotes');

    if( reject_buttons.length ){
        reject_buttons.prettyPhoto({
            hook: 'data-rel',
            social_tools: false,
            theme: 'pp_woocommerce',
            horizontal_padding: 20,
            opacity: 0.8,
            deeplinking: false
        });

        reject_buttons.on('click', function(e){

            e.preventDefault();

            var $t = $(this),
                order_id = $t.parents('.quotes-actions').data('order_id'),
                modal = $('#modal-order-number'),
                request_info = 'context=frontend&action=yith_ywraq_order_action&ywraq_order_action=reject_order&order_id='+order_id+'&lang='+ywraq_frontend.current_lang;

            modal.text(order_id);
            $('.reject-quote-modal-button').attr('data-order_id', order_id);

            reject_buttons.prettyPhoto({
                hook: 'data-rel',
                social_tools: false,
                theme: 'pp_woocommerce',
                horizontal_padding: 20,
                opacity: 0.8,
                deeplinking: false
            });

        });
    }


    $(document).on('click', '.close-quote-modal-button', function(e){
        e.preventDefault();
        $.prettyPhoto.close();
    });

    $(document).on('click', '.reject-quote-modal-button', function(e){

        e.preventDefault();
        var $t = $(this),
            order_id = $t.data('order_id'),
            modal = $('#modal-order-number'),
            table =$t.closest('body').find('.my_account_quotes'),
            row =table.find('[data-order_id="'+order_id+'"]').parent(),
            request_info = 'context=frontend&action=yith_ywraq_order_action&ywraq_order_action=reject_order&order_id='+order_id+'&lang='+ywraq_frontend.current_lang;


        $.ajax({
            type   : 'POST',
            url    : ywraq_frontend.ajaxurl.toString().replace( '%%endpoint%%', 'yith_ywraq_order_action' ),
            dataType: 'json',
            data   : request_info,
            beforeSend: function(){
                row.after( ' <img src="'+ajax_loader+'" >' );
            },
            complete: function(){
                row.next().remove();
            },

            success: function (response) {
                if( response.result !== 0){
                    //window.location.href = response.rqa_url;
                    row.find('.reject').hide();
                    row.find('.accept').hide();
                    row.find('.raq_status').removeClass('pending').addClass('rejected').text(response.status);
                    $.prettyPhoto.close();
                }

            }
        });
    });

	/**
     * To fix the problem of update the quantity automatically when the theme use a different quantity field
     * https://gist.github.com/kreamweb/cede2722b72b1b558ea592b8fbf23413
     */
    var request;
    $(document).on( 'click, change', '.product-quantity input', function(e){

        if( typeof request !== 'undefined' ){
            request.abort();
        }

        var $t = $(this),
            totals =  $t.closest('table').find('.raq-totals'),
            total_inline = $t.closest('tr').find('.product-subtotal'),
            name = $t.attr('name');

        if( typeof name ==   'undefined'){
            var $input_quantity = $t.closest('.product-quantity').find('.input-text.qty'),
                name = $input_quantity.attr('name'),
                value = $input_quantity.val(),
                item_keys = name.match(/[^[\]]+(?=])/g);

            //this is not necessary for some theme like flatsome
            if( $t.hasClass('plus') ){
                value ++;
            }

            if( $t.hasClass('minus') ){
                value --;
            }
            //end

            var request_info = 'context=frontend&action=yith_ywraq_action&ywraq_action=update_item_quantity&quantity='+value+'&key='+item_keys[0];

        }else{
            var value = $t.val(),
                item_keys = name.match(/[^[\]]+(?=])/g),
                request_info = 'context=frontend&action=yith_ywraq_action&ywraq_action=update_item_quantity&quantity='+value+'&key='+item_keys[0];

        }

        request = $.ajax({
            type   : 'POST',
            url    : ywraq_frontend.ajaxurl.toString().replace( '%%endpoint%%', 'yith_ywraq_action' ),
            dataType: 'json',
            data   : request_info,
            success: function ( response ) {
                if( typeof response.total !== 'undefined'  ){
                    total_inline.html( response.line_total );
                    if( totals.length ){
                        totals.html( response.total );
                    }
                }
            }
        });

    });



    /* disable shipping fields */
    if( $shipping_form.length > 0 && ywraq_frontend.lock_shipping == true ){
        $shipping_form.find('input').attr('readonly','readonly');
        $shipping_form.find('select').attr('readonly','readonly');
    }

    if( $billing_form.length > 0 && ywraq_frontend.lock_billing == true ){
        $billing_form.find('input').attr('readonly','readonly');
        $billing_form.find('select').attr('readonly','readonly');
    }
    //Gravity form
    $(document).bind('gform_confirmation_loaded', function(event, formId){
        // code to be trigger when confirmation page is loaded
        window.location.href = ywraq_frontend.rqa_url;
    });

    //to fix the cache of page
    $widget.ywraq_refresh_widget();

});