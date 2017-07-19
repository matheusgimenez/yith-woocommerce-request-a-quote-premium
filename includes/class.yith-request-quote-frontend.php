<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of FREE version of YITH Woocommerce Request A Quote
 *
 * @class   YITH_YWRAQ_Frontend
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_YWRAQ_Frontend' ) ) {

	/**
     * Class YITH_YWRAQ_Frontend
     */
    class YITH_YWRAQ_Frontend {

        /**
         * Single instance of the class
         *
         * @var \YITH_YWRAQ_Frontend
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_YWRAQ_Frontend
         * @since 1.0.0
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor
         *
         * Initialize plugin and registers actions and filters to be used
         *
         * @since  1.0
         * @author Emanuela Castorina
         */
        public function __construct() {

            //start the session
            if ( !session_id() ) {
                session_start();
            }

            add_action( 'wp_loaded', array( $this, 'update_raq_list' ) );
	        //show button in single page
	        add_action( 'woocommerce_before_single_product', array( $this, 'show_button_single_page' ) );

            //custom styles and javascripts
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

            add_filter('yith_ywraq-show_btn_single_page', 'yith_ywraq_show_button_in_single_page');
            add_filter('yith_ywraq-btn_other_pages', 'yith_ywraq_show_button_in_other_pages', 10);

	        add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'hide_add_to_cart_loop' ), 99, 2);

            new YITH_YWRAQ_Shortcodes();

        }


	    public function show_button_single_page() {
		    global $product;

		    if( ! $product ){
			    global  $post;
			    if (  ! $post || ! is_object( $post ) || ! is_singular() ) {
				    return;
			    }
			    $product = wc_get_product( $post->ID);
		    }

		    if( get_option('ywraq_show_button_near_add_to_cart','no') == 'yes' && $product->is_in_stock() && $product->get_price() !== '' ){
			    if( $product->is_type('variable')  ){
				    add_action( 'woocommerce_after_single_variation', array(  $this, 'add_button_single_page' ),15 );
			    }else{
				    add_action( 'woocommerce_after_add_to_cart_button', array(  $this, 'add_button_single_page' ),15 );
			    }
		    }else{
			    add_action( 'woocommerce_single_product_summary', array( $this, 'add_button_single_page' ), 35 );
		    }
        }

        /**
         * Hide add to cart in single page
         *
         * Hide the button add to cart in the single product page
         *
         * @since  1.0
         * @author Emanuela Castorina
         */
	    public function hide_add_to_cart_single() {

		    if ( catalog_mode_plugin_enabled() ) {
			    return;
		    }

		    global $post;

		    if (  ! $post || ! is_object( $post ) || ! is_singular() ) {
		    	return;
		    }

		    $product = wc_get_product( $post->ID);
		    if( ! $product || apply_filters('ywraq_hide_add_to_cart_single', false, $product ) ){
		    	return;
		    }
		    if ( get_option( 'ywraq_hide_add_to_cart' ) == 'yes' ||  $product->get_price() == '' ) {
			    if ( isset( $product ) && $product && $product->is_type('variable') ) {
				    $css = ".single_variation_wrap .variations_button button{
	                 display:none!important;
	                }";
				    wp_add_inline_style( 'yith_ywraq_frontend', $css );
			    } else {
				    $css = ".cart button.single_add_to_cart_button{
	                 display:none!important;
	                }";
			    }
			    wp_add_inline_style( 'yith_ywraq_frontend', $css );
		    }


	    }

        /**
         * Hide add to cart in loop
         *
         * Hide the button add to cart in the shop page
         *
         * @since  1.0
         * @author Emanuela Castorina
         *
         * @param $link
         * @param WC_Product $product
         *
         * @return string
         */
        public function hide_add_to_cart_loop( $link , $product) {

	        if ( ! catalog_mode_plugin_enabled() && get_option( 'ywraq_hide_add_to_cart' ) == 'yes'){

                if( ! $product->is_type('variable' ) ) {

	                if ( get_option( 'yith-wccl-enable-in-loop' ) == 'yes' || apply_filters( 'hide_add_to_cart_loop', true, $link, $product ) ) {
		                $link = '';
	                }
                }
	        }



            return $link;
        }

        /**
         * Enqueue Scripts and Styles
         *
         * @return void
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function enqueue_styles_scripts() {



            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            wp_register_script( 'yith_ywraq_frontend', YITH_YWRAQ_ASSETS_URL . '/js/frontend' . $suffix . '.js', array( 'jquery' ), YITH_YWRAQ_VERSION, true );

            $assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

	        if( ! is_single() ) {
		        // Prettyphoto for modal questions
		        wp_enqueue_style( 'ywraq_prettyPhoto_css', $assets_path . 'css/prettyPhoto.css' );

		        wp_enqueue_script( 'ywraq-prettyPhoto', $assets_path . 'js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), false, true );
	        }

			$cform7_id = ywraq_get_current_contact_form_7();


            //WPML Compatibility
            global $sitepress, $polylang;
            $current_language = '';
            if( function_exists('icl_get_languages') && class_exists('YITH_YWRAQ_Multilingual_Email')  ) {
                $current_language = $sitepress->get_current_language();
            }elseif( $polylang && isset( $polylang->curlang->slug ) ){
                $current_language = $polylang->curlang->slug;
            }


            $go_to_the_list = ( get_option( 'ywraq_after_click_action' ) == 'yes' ) ? 'yes' : 'no';
	        $localize_script_args =  array(
		        'ajaxurl'            => version_compare( WC()->version, '2.4.0', '<' ) ? WC()->ajax_url() : WC_AJAX::get_endpoint( "%%endpoint%%" ),
		        'cform7_id'          => apply_filters( 'ywraq_inquiry_contact_form_7_id', $cform7_id ),
		        'current_lang'       => $current_language,
		        'no_product_in_list' => ywraq_get_list_empty_message(),
		        'block_loader'       => YITH_YWRAQ_ASSETS_URL . '/images/ajax-loader.gif',
		        'go_to_the_list'     => $go_to_the_list,
		        'rqa_url'            => YITH_Request_Quote()->get_redirect_page_url(),
		        'current_user_id'    => is_user_logged_in() ?  get_current_user_id() : ''
	        );

            wp_localize_script( 'yith_ywraq_frontend', 'ywraq_frontend', apply_filters( 'yith_ywraq_frontend_localize', $localize_script_args ) );

            wp_enqueue_style( 'yith_ywraq_frontend', YITH_YWRAQ_ASSETS_URL . '/css/frontend.css' );
            wp_enqueue_script( 'yith_ywraq_frontend' );

            if( defined('YITH_YWRAQ_PREMIUM') ){
                $custom_css = require_once(YITH_YWRAQ_TEMPLATE_PATH.'/layout/css.php');
                wp_add_inline_style( 'yith_ywraq_frontend', $custom_css );
            }


	        if( function_exists('Woo_Bulk_Discount_Plugin_t4m') ){

		        remove_filter( 'woocommerce_cart_product_subtotal', array( Woo_Bulk_Discount_Plugin_t4m(), 'filter_cart_product_subtotal' ), 10, 3 );
	        }

            $this->hide_add_to_cart_single();
        }

        /**
         * Check if the button can be showed in single page
         *
         * @return void
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function add_button_single_page() {

            $show_button = apply_filters('yith_ywraq-show_btn_single_page', 'yes' );

            if( $show_button != 'yes' ){
                return false;
            }

            $this->print_button();
        }

	    /**
	     * @param bool $product_id
	     *
	     * @internal param bool $product
	     */
        public function print_button( $product_id = false ){

	        if ( ! $product_id ) {
		        global $product;
	        } else {
		        $product = wc_get_product( $product_id );
	        }

	        if ( ! apply_filters( 'yith_ywraq_before_print_button', true, $product ) ) {
		        return;
	        }

	        $style_button = ( get_option( 'ywraq_show_btn_link' ) == 'button' ) ? 'button' : 'ywraq-link';
	        $product_id   = yit_get_prop( $product, 'id', true );

            $args         = array(
                'class'         => 'add-request-quote-button ' . $style_button,
                'wpnonce'       => wp_create_nonce( 'add-request-quote-' . $product_id ),
                'product_id'    => $product_id,
                'label'         => apply_filters( 'ywraq_product_add_to_quote' , get_option('ywraq_show_btn_link_text') ),
                'label_browse'  => apply_filters( 'ywraq_product_added_view_browse_list' , __( 'Browse the list', 'yith-woocommerce-request-a-quote' ) ),
                'template_part' => 'button',
                'rqa_url'       => YITH_Request_Quote()->get_raq_page_url(),
                'exists'        => ( $product->is_type('variable') ) ? false : YITH_Request_Quote()->exists( $product_id ),
            );

            if( $product->is_type('variable')){
                $args['variations'] = implode( ',', YITH_Request_Quote()->raq_variations );
            }

            $args['args'] = $args;
            
            $template_button = 'add-to-quote.php';
            
            if( class_exists( 'YITH_WAPO_Type' ) && !is_product() ){
                
                $has_addons = YITH_WAPO_Type::getAllowedGroupTypes( $product_id );
                
                if( !empty( $has_addons ) ){
                    $template_button = 'add-to-quote-addons.php';
                }
            }

            wc_get_template( $template_button, apply_filters('ywraq_add_to_quote_args', $args), YITH_YWRAQ_DIR, YITH_YWRAQ_DIR);

        }

        /**
         * Update the Request Quote List
         *
         * @return void
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function update_raq_list() {



            if ( isset( $_POST['update_raq_wpnonce'] ) && isset( $_POST['raq'] ) && wp_verify_nonce( $_POST['update_raq_wpnonce'], 'update-request-quote-quantity' ) ) {

                foreach ( $_POST['raq'] as $key => $value ) {

                    if ( $value['qty'] != 0 ) {

                        YITH_Request_Quote()->update_item( $key, 'quantity', $value['qty'] );
                    }
                    else {
                        YITH_Request_Quote()->remove_item( $key );
                    }
                }
            }
        }

    }

    /**
     * Unique access to instance of YITH_YWRAQ_Frontend class
     *
     * @return \YITH_YWRAQ_Frontend
     */
    function YITH_YWRAQ_Frontend() {
        return YITH_YWRAQ_Frontend::get_instance();
    }
}