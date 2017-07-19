<?php
/*
Plugin Name: YITH Woocommerce Request A Quote Premium
Description: The YITH Woocommerce Request A Quote plugin lets your customers ask for an estimate of a list of products they are interested into.
Version: 1.7.7
Author: YITHEMES
Author URI: http://yithemes.com/
Text Domain: yith-woocommerce-request-a-quote
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
 * @package YITH Woocommerce Request A Quote Premium
 * @since   1.0.0
 * @author  YITHEMES
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Define constants ________________________________________
if ( ! defined( 'YITH_YWRAQ_DIR' ) ) {
    define( 'YITH_YWRAQ_DIR', plugin_dir_path( __FILE__ ) );
}

if ( defined( 'YITH_YWRAQ_VERSION' ) ) {
    return;
}else{
    define( 'YITH_YWRAQ_VERSION', '1.7.7' );
}

if ( ! defined( 'YITH_YWRAQ_PREMIUM' ) ) {
    define( 'YITH_YWRAQ_PREMIUM', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWRAQ_FILE' ) ) {
    define( 'YITH_YWRAQ_FILE', __FILE__ );
}

if ( ! defined( 'YITH_YWRAQ_URL' ) ) {
    define( 'YITH_YWRAQ_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YITH_YWRAQ_ASSETS_URL' ) ) {
    define( 'YITH_YWRAQ_ASSETS_URL', YITH_YWRAQ_URL . 'assets' );
}

if ( ! defined( 'YITH_YWRAQ_TEMPLATE_PATH' ) ) {
    define( 'YITH_YWRAQ_TEMPLATE_PATH', YITH_YWRAQ_DIR . 'templates' );
}

if ( ! defined( 'YITH_YWRAQ_INIT' ) ) {
    define( 'YITH_YWRAQ_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWRAQ_INC' ) ) {
    define( 'YITH_YWRAQ_INC', YITH_YWRAQ_DIR . '/includes/' );
}

if ( ! defined( 'YITH_YWRAQ_SLUG' ) ) {
    define( 'YITH_YWRAQ_SLUG', 'yith-woocommerce-request-a-quote' );
}

if ( ! defined( 'YITH_YWRAQ_SECRET_KEY' ) ) {
    define( 'YITH_YWRAQ_SECRET_KEY', 'vT6zK6QAp0DD2H2d9NoE' );
}

$wp_upload_dir = wp_upload_dir();

if ( ! defined( 'YITH_YWRAQ_DOCUMENT_SAVE_DIR' ) ) {
    define( 'YITH_YWRAQ_DOCUMENT_SAVE_DIR', $wp_upload_dir['basedir'] . '/yith_ywraq/' );
}

if ( ! defined( 'YITH_YWRAQ_SAVE_QUOTE_URL' ) ) {
    define( 'YITH_YWRAQ_SAVE_QUOTE_URL', $wp_upload_dir['baseurl'] . '/yith_ywraq/' );
}

// Free version deactivation if installed __________________
if( ! function_exists( 'yit_deactive_free_version' ) ) {
    require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_YWRAQ_FREE_INIT', plugin_basename( __FILE__ ) );


// Yith jetpack deactivation if installed __________________
if ( function_exists( 'yith_deactive_jetpack_module' ) ) {
    global $yith_jetpack_1;
    yith_deactive_jetpack_module( $yith_jetpack_1, 'YITH_YWRAQ_PREMIUM', plugin_basename( __FILE__ ) );
}

/* Plugin Framework Version Check */
if( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_YWRAQ_DIR . 'plugin-fw/init.php' ) ) {
    require_once( YITH_YWRAQ_DIR . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_YWRAQ_DIR  );

if( ! function_exists('yith_ywraq_install_woocommerce_admin_notice') ){
    function yith_ywraq_install_woocommerce_admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'YITH Woocommerce Request A Quote is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-request-a-quote' ); ?></p>
        </div>
        <?php
    }
}

if ( ! function_exists( 'yith_ywraq_premium_install' ) ) {
    function yith_ywraq_premium_install() {
        if ( !function_exists( 'WC' ) ) {
            add_action( 'admin_notices', 'yith_ywraq_install_woocommerce_admin_notice' );
        } else {
            do_action( 'yith_ywraq_init' );
        }
    }

    add_action( 'plugins_loaded', 'yith_ywraq_premium_install', 12 );
}

if ( ! function_exists( 'yith_ywraq_premium_constructor' ) ) {

    function yith_ywraq_premium_constructor() {
        // Load required classes and functions

        // Woocommerce installation check _________________________
        if ( ! function_exists( 'WC' ) ) {
            add_action( 'admin_notices', 'yith_ywraq_install_woocommerce_admin_notice' );

            return;
        }

        // Load ywraq text domain ___________________________________
        load_plugin_textdomain( 'yith-woocommerce-request-a-quote', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        if ( ! class_exists( 'WC_Session' ) ) {
            include_once( WC()->plugin_path() . '/includes/abstracts/abstract-wc-session.php' );
        }

        if ( ! defined( 'YITH_YWRAQ_DOMPDF_DIR' ) ) {
            define( 'YITH_YWRAQ_DOMPDF_DIR', YITH_YWRAQ_DIR . 'lib/dompdf/' );
        }

        if ( class_exists( 'WC_Product_Addons' ) ) {
            require_once( YITH_YWRAQ_INC . 'compatibility/class.woocommerce-product-addon.php' );
        }

        require_once( YITH_YWRAQ_INC . 'functions.yith-request-quote.php' );
        require_once( YITH_YWRAQ_INC . 'class.yith-ywraq-session.php' );
        require_once( YITH_YWRAQ_INC . 'class.yith-ywraq-shortcodes.php' );
        require_once( YITH_YWRAQ_INC . 'class.yith-request-quote.php' );
        require_once( YITH_YWRAQ_INC . 'class.yith-request-quote-premium.php' );
	    if ( version_compare( WC()->version, '2.7.0', '<' ) ) {
		    require_once( YITH_YWRAQ_INC . '/wc_2.6.x/class.yith-ywraq-order-request.php' );
        }else{
		    require_once( YITH_YWRAQ_INC . 'class.yith-ywraq-order-request.php' );
        }


        require_once( YITH_YWRAQ_INC . 'widgets/class.yith-ywraq-list-quote-widget.php' );
        require_once( YITH_YWRAQ_INC . 'widgets/class.yith-ywraq-mini-list-quote-widget.php' );

        if( defined( 'WCML_VERSION') ){
            require_once( YITH_YWRAQ_INC . 'emails/class.yith-ywraq-multilingual-email.php' );
        }

        if ( class_exists( 'YITH_Vendors' ) ) {
            require_once( YITH_YWRAQ_INC . 'compatibility/yith-woocommerce-product-vendors.php' );
        }

        if ( class_exists( 'YITH_WAPO' ) ) {
            require_once( YITH_YWRAQ_INC . 'compatibility/yith-woocommerce-advanced-product-options.php' );
        }

        if ( class_exists( 'YITH_WCP' ) ) {
            require_once( YITH_YWRAQ_INC . 'compatibility/yith-woocommerce-composite-products.php' );
        }

        if ( ywraq_gravity_form_installed() ) {
            require_once( YITH_YWRAQ_INC . 'forms/gravity-forms/ywraq-gravity-form-addons.php' );
        }

        $is_frontend_call = isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend';
        $is_frontend_action = isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'flatsome_quickview' ) );

        if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX && ( $is_frontend_call || $is_frontend_action ) ) ) {
            require_once( YITH_YWRAQ_INC . 'class.yith-request-quote-admin.php' );
        } else {
            require_once( YITH_YWRAQ_INC . 'class.yith-request-quote-frontend.php' );
            YITH_YWRAQ_Frontend();
        }

        require_once( YITH_YWRAQ_INC . 'class.yith-ywraq-cron.php' );

        YITH_Request_Quote_Premium();
    }

    add_action( 'yith_ywraq_init', 'yith_ywraq_premium_constructor' );


}
