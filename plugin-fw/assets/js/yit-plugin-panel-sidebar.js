/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */


(function ( $ ) {
    var hide_sidebar_btn      = $( '#yit-panel-sidebar-action-hide' ),
        hide_sidebar_text     = hide_sidebar_btn.find( '.yit-panel-sidebar-action-title' ),
        sidebar               = $( '#yit-panel-sidebar' ),
        panel_wrapper         = $( '.' + sidebar_labels.wrapper_class ),
        ajax_hide_option_save_call,
        ajax_hide_option_save = function ( option ) {
            if ( ajax_hide_option_save_call ) {
                ajax_hide_option_save_call.abort();
            }

            var post_data = {
                option: option,
                action: 'yith_plugin_panel_sidebar_set_collapse_option'
            };

            ajax_hide_option_save_call = $.ajax( {
                type: "POST",
                data: post_data,
                url:  ajaxurl
            } );
        },
        hide_sidebar          = function () {
            hide_sidebar_btn.removeClass( 'hide-on-click' );
            hide_sidebar_text.html( sidebar_labels.show_sidebar );
            sidebar.addClass( 'yith-panel-sidebar-hidden' );
            panel_wrapper.addClass( 'yit-admin-panel-content-wrap-full' );
            ajax_hide_option_save( 'yes' );
        },
        show_sidebar          = function () {
            hide_sidebar_btn.addClass( 'hide-on-click' );
            hide_sidebar_text.html( sidebar_labels.hide_sidebar );
            sidebar.removeClass( 'yith-panel-sidebar-hidden' );
            panel_wrapper.removeClass( 'yit-admin-panel-content-wrap-full' );
            ajax_hide_option_save( 'no' );
        };

    hide_sidebar_btn.on( 'click', function () {
        if ( $( this ).is( '.hide-on-click' ) ) {
            hide_sidebar();
        } else {
            show_sidebar();
        }
    } );

})( jQuery );