<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of YITH Woocommerce Request A Quote
 *
 * @class   YWRAQ_WooCommerce_Product_Addon
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YWRAQ_WooCommerce_Product_Addon' ) ) {

    class YWRAQ_WooCommerce_Product_Addon {

        /**
         * Single instance of the class
         *
         * @var \YWRAQ_WooCommerce_Product_Addon
         */

        protected static $instance;

        /**
         * Session object
         */
        public $session_class;


        /**
         * Content of session
         */
        public $raq_content = array();


        /**
         * Returns single instance of the class
         *
         * @return \YWRAQ_WooCommerce_Product_Addon
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
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function __construct() {

            add_filter( 'ywraq_ajax_add_item_prepare', array( $this, 'ajax_add_item' ), 10, 2 );
            add_filter( 'ywraq_add_item', array( $this, 'add_item' ), 10, 2 );

            add_filter( 'ywraq_request_quote_view_item_data', array( $this, 'request_quote_view' ), 10, 3 );
            add_action( 'ywraq_order_adjust_price', array( $this, 'adjust_price'), 10, 2);
            add_action( 'ywraq_from_cart_to_order_item', array( $this, 'add_order_item_meta'), 10, 3);
            add_action( 'ywraq_item_data', array( $this, 'add_raq_item_meta'), 10, 2);

            add_filter( 'ywraq_add_to_cart', array( $this, 'add_to_cart'), 10, 2);

            add_filter( 'ywraq_exists_in_list', array( $this, 'exists_in_list'), 10 , 5);
            add_filter( 'ywraq_quote_item_id', array( $this, 'quote_item_id'), 10 , 2);

            add_action( 'ywraq_before_add_to_cart_in_order_accepted', array( $this, 'remove_action' ) );


        }


        public function ajax_add_item(  $postdata, $product_id  ){
            global $Product_Addon_Cart;

            if( empty( $postdata ) ){
                $postdata = array();
            }

            $postdata['add-to-cart'] = $product_id;

            $t = $Product_Addon_Cart->add_cart_item_data( null, $product_id, $postdata );
            if( !empty( $t ) ) {
                $postdata = array_merge( $t, $postdata);
            }

            return $postdata;
        }

        public function add_item( $product_raq, $raq ) {
            if ( isset( $product_raq['addons'] ) ) {
                $raq['addons'] = $product_raq['addons'];
            }

            return $raq;
        }

        public function request_quote_view( $item_data, $raq, $_product ) {
            if ( isset( $raq['addons'] ) ) {
                foreach ( $raq['addons'] as $_r ) {
                    $item_data[] = array(
                        'key'   => $_r['name'],
                        'value' => $_r['value']
                    );
                    $_product->adjust_price( $_r['price'] );
                }
            }

            return $item_data;
        }

        public function add_raq_item_meta( $item_data, $raq = array() ) {
            if ( isset( $raq['addons'] ) ) {
                foreach ( $raq['addons'] as $_r ) {
                    $item_data[] = array(
                        'key'   => $_r['name'],
                        'value' => $_r['value']
                    );
                }
            }

            return $item_data;
        }

        public function adjust_price( $values, $_product ) {
            if ( isset( $values['addons'] ) ) {
                foreach ( $values['addons'] as $_r ) {
                    $_product->adjust_price( $_r['price'] );
                }
            }

        }

        public function add_to_cart( $cart_item_data, $item  ) {
            if ( isset( $item['item_meta']['_ywraq_wc_addons'] ) ) {
                $addons = maybe_unserialize( $item['item_meta']['_ywraq_wc_addons'] );
                if( ! empty( $addons ) ){
                    $ad = maybe_unserialize($addons[0]);
                    $cart_item_data['addons'] =  $ad;
                    $cart_item_data['add-to-cart'] =  $item['product_id'];
                }
            }

            return $cart_item_data;

        }


        public function add_order_item_meta( $values, $cart_item_key, $item_id ) {

            if ( !empty( $values['addons'] ) ) {
                foreach ( $values['addons'] as $addon ) {
                    $name = $addon['name'];
                    if ( $addon['price'] > 0 ) {
                        $name .= ' (' . strip_tags( wc_price( $addon['price'] ) ) . ')';
                    }
                    wc_add_order_item_meta( $item_id, $name, $addon['value'] );
                }
                wc_add_order_item_meta( $item_id, '_ywraq_wc_addons', $values['addons'] );
            }
        }

        public function exists_in_list( $return, $product_id, $variation_id, $postdata, $raqdata ){

            if( $postdata ){

                $this->ajax_add_item( $postdata, $product_id );
                if( isset( $postdata['addons']) && !empty( $postdata['addons'] )){
                    $str = '';
                    foreach( $postdata['addons'] as $ad ){
                        $str .=  $ad['name'].$ad['value'];
                    }

                    if( $variation_id ){
                        $key_to_find = md5($product_id.$variation_id.$str);
                    }else{
                        $key_to_find = md5($product_id.$str);
                    }

                   

                    if ( array_key_exists( $key_to_find, $raqdata ) ) {
                        $return = true;
                    }
                }
            }else{
                $addons = get_product_addons( $product_id );
                if( !empty($addons)){
                    $return = false;
                }
            }


            return $return;
        }

        public function quote_item_id( $item_id, $product_raq ){
            $str = '';
            $addons = get_product_addons( $product_raq['product_id'] );

            if( !empty( $addons ) && isset( $product_raq['addons'] ) && !empty( $product_raq['addons'] ) ){

                foreach( $product_raq['addons'] as $ad ){
                    $str .=  $ad['name'].$ad['value'];
                }
                if ( isset( $product_raq['variation_id'] ) ) {
                    $item_id = md5( $product_raq['product_id'] . $product_raq['variation_id'] . $str );
                }
                else {
                    $item_id = md5( $product_raq['product_id'] . $str );
                }

            }

            return $item_id;
        }

        public function remove_action(){
            global $Product_Addon_Cart;
            remove_filter( 'woocommerce_add_cart_item_data', array( $Product_Addon_Cart, 'add_cart_item_data' ), 10 );
        }

    }

    /**
     * Unique access to instance of YWRAQ_WooCommerce_Product_Addon class
     *
     * @return \YWRAQ_WooCommerce_Product_Addon
     */
    function YWRAQ_WooCommerce_Product_Addon() {
        return YWRAQ_WooCommerce_Product_Addon::get_instance();
    }

    if ( class_exists( 'WC_Product_Addons' ) ) {
        YWRAQ_WooCommerce_Product_Addon();
    }

}