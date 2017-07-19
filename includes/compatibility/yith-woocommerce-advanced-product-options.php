<?php
if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements features of YITH Woocommerce Request A Quote
 *
 * @class   YWRAQ_Avanced_Product_Options
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */
if ( ! class_exists( 'YWRAQ_Avanced_Product_Options' ) ) {

	/**
	 * Class YWRAQ_Avanced_Product_Options
	 */
	class YWRAQ_Avanced_Product_Options {

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

			add_filter( 'ywraq_request_quote_view_item_data', array( $this, 'request_quote_view' ), 10, 4 );
			add_action( 'ywraq_order_adjust_price', array( $this, 'adjust_price' ), 10, 2 );
			add_action( 'ywraq_from_cart_to_order_item', array( $this, 'add_order_item_meta' ), 10, 3 );
			add_action( 'ywraq_item_data', array( $this, 'add_raq_item_meta' ), 10, 3 );
			add_filter( 'ywraq_add_to_cart', array( $this, 'add_to_cart' ), 10, 2 );

			// email front end price
			add_filter( 'yith_ywraq_hide_price_template', array( $this, 'ywraq_hide_price_template' ), 10, 3 );
			add_filter( 'ywraq_exists_in_list', array( $this, 'exists_in_list' ), 10, 5 );
			add_filter( 'ywraq_quote_item_id', array( $this, 'quote_item_id' ), 10, 2 );
			add_filter( 'ywraq_order_cart_item_data', array( $this, 'remove_price' ), 90, 3 );

		}


		/**
		 * @param $postdata
		 * @param $product_id
		 *
		 * @return array
		 */
		public function ajax_add_item( $postdata, $product_id ) {
			$yith_wapo_frontend = YITH_WAPO()->frontend;


			if ( empty( $postdata ) ) {
				$postdata = array();
			}

			$postdata['add-to-cart'] = $product_id;

			$t1 = $yith_wapo_frontend->add_cart_item_data( null, $product_id, $postdata );


            if( defined( 'YITH_WAPO_PREMIUM' ) ) {
                $t2 = $yith_wapo_frontend->add_cart_item_data( null, $product_id, $postdata, 1 );
                $t['yith_wapo_options'] = array_merge( $t1['yith_wapo_options'], $t2['yith_wapo_options'] );
                $t['yith_wapo_sold_individually'] = empty( $t2['yith_wapo_options'] ) ? '' : 1;
            }else{
                $t['yith_wapo_options'] = $t1['yith_wapo_options'];
            }



			if ( ! empty( $t ) ) {
				$postdata = array_merge( $t, $postdata );
			}

			return $postdata;
		}

		/**
		 * @param $product_raq
		 * @param $raq
		 *
		 * @return mixed
		 */
		public function add_item( $product_raq, $raq ) {

			if ( isset( $product_raq['yith_wapo_options'] ) ) {
				$raq['yith_wapo_options'] = $product_raq['yith_wapo_options'];
			}

			return $raq;
		}

		/**
		 * @param $item_data
		 * @param $raq
		 * @param $_product WC_Product
		 * @param bool $show_price
		 *
		 * @return array
		 */
		public function request_quote_view( $item_data, $raq, $_product, $show_price = true ) {


			if ( isset( $raq['yith_wapo_options'] ) ) {
				foreach ( $raq['yith_wapo_options'] as $_r ) {

					$price = '';

					if ( $show_price && $_r['price'] > 0 ) {
						$price = ' ( ' . strip_tags( wc_price( $_r['price'] ) ) . ' ) ';
					}

					$item_data[] = array(
						'key'   => $_r['name'],
						'value' => $_r['value'] . $price
					);
					//wc 2.7
					if(version_compare( WC()->version, '2.7.0', '<' ) ){

						$_product->adjust_price( $_r['price'] );
					}else{
						$_product->set_price( $_r['price'] + $_product->get_price() );
					}

				}
			}

			return $item_data;
		}

		/**
		 * @param $item_data
		 * @param null $raq
		 * @param bool $show_price
		 *
		 * @return array
		 */
		public function add_raq_item_meta( $item_data, $raq = null, $show_price = true ) {

			if ( isset( $raq['yith_wapo_options'] ) ) {
				foreach ( $raq['yith_wapo_options'] as $_r ) {

					$price = '';

					if ( $show_price && $_r['price'] > 0) {
						$price = ' ( ' . strip_tags( wc_price( $_r['price'] ) ) . ' ) ';
					}

					$item_data[] = array(
						'key'   => $_r['name'],
						'value' => $_r['value'] . $price
					);

				}
			}

			return $item_data;
		}

		/**
		 * @param $values
		 * @param $_product
		 */
		public function adjust_price( $values, $_product ) {
			if ( isset( $values['yith_wapo_options'] ) ) {

				foreach ( $values['yith_wapo_options'] as $_r ) {
					//wc 2.7
                    if(version_compare( WC()->version, '2.7.0', '<' ) ){
						$_product->adjust_price( $_r['price'] );
					}else{
						$_product->set_price( $_r['price'] + $_product->get_price() );
					}
				}
			}

		}

		/**
		 * @param $cart_item_data
		 * @param $item
		 *
		 * @return mixed
		 */
		public function add_to_cart( $cart_item_data, $item ) {
			if ( isset( $item['item_meta']['_ywraq_wc_ywapo'] ) ) {
				$addons = maybe_unserialize( $item['item_meta']['_ywraq_wc_ywapo'] );
				if ( ! empty( $addons ) ) {
					$ad                                  = maybe_unserialize( $addons[0] );
					$cart_item_data['yith_wapo_options'] = $ad;
					$cart_item_data['add-to-cart']       = $item['product_id'];
				}
			}

			return $cart_item_data;

		}

		/**
		 * @param $product_total
		 * @param $_product_id
		 * @param null $item
		 *
		 * @return string
		 */
		public function ywraq_hide_price_template( $product_total, $_product_id, $item = null ) {

			if ( isset( $item ) && is_array( $item ) && !empty( $item['yith_wapo_options'] ) ) {

				if( isset( $item['variation_id']) && $item['variation_id'] ){
					$_product_id =  $item['variation_id'];
				}

				$product = wc_get_product( $_product_id );
				$this->adjust_price( $item, $product );
				$product_total = WC()->cart->get_product_subtotal( $product, $item['quantity'] );
			}

			return $product_total;
		}

		/**
		 * @param $new_cart
		 * @param $values
		 * @param $item
		 * @param $new_cart_item_key
		 *
		 * @return mixed
		 */
		public function add_to_cart_from_request( $new_cart, $values, $item, $new_cart_item_key ) {

			$cart =  &$new_cart->cart_contents;

			if ( isset( $cart[ $new_cart_item_key ] ) && isset( $values['yith_wapo_options'] ) ) {
				$cart[ $new_cart_item_key ]['yith_wapo_options'] = $values['yith_wapo_options'];
				$ywapo_frontend                                  = YITH_WAPO()->frontend;
				$ywapo_frontend->cart_adjust_price( $cart[ $new_cart_item_key ] );
			}

			return $new_cart;

		}

		/**
		 * @param $values
		 * @param $cart_item_key
		 * @param $item_id
		 */
		public function add_order_item_meta( $values, $cart_item_key, $item_id ) {

			if ( ! empty( $values['yith_wapo_options'] ) ) {
				foreach ( $values['yith_wapo_options'] as $addon ) {
					$name = $addon['name'];
					if ( $addon['price'] > 0 ) {
						$name .= ' (' . strip_tags( wc_price( $addon['price'] ) ) . ')';
					}
					wc_add_order_item_meta( $item_id, $name, $addon['value'] );
				}
				wc_add_order_item_meta( $item_id, '_ywraq_wc_ywapo', $values['yith_wapo_options'] );
			}

		}

		/**
		 * @param $args
		 * @param $cart_item_key
		 * @param $values
		 * @param $new_cart
		 *
		 */
		public function cart_to_order_args( $args, $cart_item_key, $values, $new_cart ) {

			$product = $values['data'];
			if ( isset( $product ) && is_object( $product ) ) {
				if ( function_exists( 'wc_get_price_excluding_tax' ) ) {
					$total = wc_get_price_excluding_tax( $product, array( 'qty' => $values['quantity'] ) );
				} else {
					$total = $product->get_price_excluding_tax( $values['quantity'] );
				}

				$args['totals']['subtotal'] = $total;
				$args['totals']['total']    = $total;
			}


		}

		/**
		 * @param $return
		 * @param $product_id
		 * @param $variation_id
		 * @param $postdata
		 * @param $raqdata
		 *
		 * @return bool
		 */
		public function exists_in_list( $return, $product_id, $variation_id, $postdata, $raqdata ) {

			if ( $postdata ) {

				$this->ajax_add_item( $postdata, $product_id );
				if ( isset( $postdata['yith_wapo_options'] ) && ! empty( $postdata['yith_wapo_options'] ) ) {
					$str = '';
					foreach ( $postdata['yith_wapo_options'] as $ad ) {
						$str .= $ad['name'] . $ad['value'];
					}

					if ( $variation_id ) {
						$key_to_find = md5( $product_id . $variation_id . $str );
					} else {
						$key_to_find = md5( $product_id . $str );
					}


					if ( array_key_exists( $key_to_find, $raqdata ) ) {
						$return = true;
					}
				}
			} else {
				$addons = YITH_WAPO_Type::getAllowedGroupTypes( $product_id );
				if ( ! empty( $addons ) ) {
					$return = false;
				}
			}


			return $return;
		}

		/**
		 * @param $item_id
		 * @param $product_raq
		 *
		 * @return string
		 */
		public function quote_item_id( $item_id, $product_raq ) {
			$str    = '';
			$addons = YITH_WAPO_Type::getAllowedGroupTypes( $product_raq['product_id'] );

			if ( ! empty( $addons ) && isset( $product_raq['yith_wapo_options'] ) && ! empty( $product_raq['yith_wapo_options'] ) ) {

				foreach ( $product_raq['yith_wapo_options'] as $ad ) {
					$str .= $ad['name'] . $ad['value'];
				}
				if ( isset( $product_raq['variation_id'] ) ) {
					$item_id = md5( $product_raq['product_id'] . $product_raq['variation_id'] . $str );
				} else {
					$item_id = md5( $product_raq['product_id'] . $str );
				}

			}

			return $item_id;
		}

		/**
		 *
		 */
		public function remove_action() {
			$yith_wapo_frontend = YITH_WAPO()->frontend;
			remove_filter( 'woocommerce_add_cart_item_data', array( $yith_wapo_frontend, 'add_cart_item_data' ), 10 );
		}

		/**
		 * @param $cart_item
		 * @param $item
		 * @param $order
		 *
		 * @return array
		 */
		public function remove_price( $cart_item, $item, $order ) {
			if ( isset( $cart_item['yith_wapo_options'] ) ) {
				$new_cart_item = array();
				foreach ( $cart_item['yith_wapo_options'] as $k => $opt ) {
					if ( isset( $opt['price_original'] ) ) {
						$opt['price_original']                    = 0;
						//$opt['price']                             = 0;
						$new_cart_item['yith_wapo_options'][ $k ] = $opt;
					}
				}

				$cart_item = $new_cart_item;
			}

			return $cart_item;
		}

	}

	/**
	 * Unique access to instance of YWRAQ_WooCommerce_Product_Addon class
	 *
	 * @return \YWRAQ_WooCommerce_Product_Addon
	 */
	function YWRAQ_Avanced_Product_Options() {
		return YWRAQ_Avanced_Product_Options::get_instance();
	}

	if ( class_exists( 'YITH_WAPO' ) ) {
		YWRAQ_Avanced_Product_Options();
	}

}