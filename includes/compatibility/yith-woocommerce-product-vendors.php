<?php

if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit; // Exit if accessed directly
}


/**
 * YWRAQ_Multivendor class to add compatibility with YITH WooCommerce Multivendor
 *
 * @class   YWRAQ_Multivendor
 * @package YITH WooCommerce Request A Quote
 * @since   1.3.0
 * @author  Yithemes
 */
if ( ! class_exists( 'YWRAQ_Multivendor' ) ) {

	/**
	 * Class YWRAQ_Multivendor
	 */
	class YWRAQ_Multivendor {

		/**
		 * Single instance of the class
		 *
		 * @var \YWRAQ_Multivendor
		 */
		protected static $instance;


		/**
		 * @var string
		 */
		protected $current_order = '';


		/**
		 * Returns single instance of the class
		 *
		 * @return \YWRAQ_Multivendor
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
		 * Initialize class and registers actions and filters to be used
		 *
		 * @since  1.3.0
		 * @author Emanuela Castorina
		 */
		public function __construct() {

			//Send request quote compaibility
			add_filter( 'ywraq_multivendor_email', array( $this, 'trigger_email_send_request' ), 15, 3 );

            add_action( 'ywraq_after_create_order', array( $this, 'create_suborder' ), 10, 3 );
			add_filter( 'woocommerce_new_order_data' , array( $this, 'change_status_to_suborder'));

			//Send request quote compatibility
			
			add_filter( 'woocommerce_order_get_items', array( $this, 'filter_order_items'), 10, 2);
			add_filter( 'woocommerce_admin_order_data_after_order_details', array( $this, 'update_order_totals'), 10);

		}

		/**
		 * @param $items
		 * @param $order WC_Order
		 *
		 * @return array
		 */
		public function filter_order_items( $items, $order ) {
            $raq_status          = yit_get_prop( $order, 'ywraq_raq_status', true );
			$is_quote 	         = ! empty( $raq_status ) ? true : false;

			$parent_order        = get_post_field( 'post_parent', yit_get_prop( $order, 'id' ) );
			$is_parent_order     = $parent_order == 0;
			$is_create_raq_order = defined('DOING_CREATE_RAQ_ORDER' ) &&  DOING_CREATE_RAQ_ORDER;

			if( $is_quote && $is_parent_order && ! $is_create_raq_order ){
				$new_items = array();

				if( ! empty( $items ) ){
					foreach ( $items as $key => $item ) {
						if( isset( $item['product_id'] ) ){
							$vendor = yith_get_vendor( $item['product_id'], 'product' );
							if ( ! $vendor->is_valid() ) {
								$new_items[ $key ] = $item;
							}
						}else{
							$new_items[ $key ] = $item;
						}
					}
				}
                $items = $new_items;
            }
            return $items;
        }

		/**
		 * @param $order WC_Order
		 */
		public function update_order_totals( $order ) {
			$order->calculate_totals();
		}

		/**
		 * Create suborders for vendors
		 *
		 * @param $order_id
		 * @param $posted
         * @param $raq
		 *
		 * @since  1.3.0
		 * @author Emanuela Castorina
		 */
        public function create_suborder( $order_id, $posted, $raq ) {
            $this->current_order = $order_id;
			
            $suborder_ids        = YITH_Vendors()->orders->check_suborder( $this->current_order, $posted, true );

	        if ( ! empty( $suborder_ids ) ) {
		        foreach ( $suborder_ids as $suborder_id ) {
			        $suborder = wc_get_order( $suborder_id );

			        if ( $suborder instanceof WC_Order ) {
				        YITH_YWRAQ_Order_Request()->add_order_meta( $suborder, $raq );
				        YITH_Commissions()->register_commissions( $suborder_id );
			        }
		        }
	        }

        }

		/**
		 * Set the status "New quote Request" to suborders
         *
		 * @param array $args
		 *
		 * @return mixed
		 */
		public function change_status_to_suborder( $args ) {
			if ( $this->current_order && isset( $args['post_parent'] ) && $this->current_order == $args['post_parent'] ) {
				$args['post_status'] = 'wc-ywraq-new';
			}

			return $args;
		}


		/**
		 * Switch the products of the request to each vendors that are owner, or to administrator
		 *
		 * @param $return
		 * @param $args
		 * @param $email_class YITH_YWRAQ_Send_Email_Request_Quote
		 *
		 * @return mixed
		 *
		 * @since  1.3.0
		 * @author Emanuela Castorina
		 */
		public function trigger_email_send_request( $return, $args, $email_class ) {

			$vendors_list 	= array();
			$admin_list 	= array();
			$parent_raq_id 	= $email_class->raq['order_id'];
			$sub_raqs 		= YITH_Vendors()->orders->get_suborder($parent_raq_id);

			$vendor_raqs = array();

			if ( ! empty( $email_class->raq['raq_content'] ) ) {
				foreach ( $email_class->raq['raq_content'] as $raq_item => $item ) {
					$vendor = yith_get_vendor( $item['product_id'], 'product' );
					if ( $vendor->is_valid() ) {
						$vendors_list[$vendor->id][$raq_item] = $email_class->raq['raq_content'][$raq_item];
                    }
                    else {
						$admin_list[$raq_item] = $email_class->raq['raq_content'][$raq_item];
					}
				}
			}

			/**
			 * Check for vendor raq
			 */
			foreach( $sub_raqs as $sub_raq ){
				$raq = wc_get_order( $sub_raq );
				if( $raq ){
					if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
						$tmp_vendor = yith_get_vendor( get_post_field( 'post_author', yit_get_prop( $raq, 'id' ) ), 'user' );
					}else{
						$tmp_vendor = yith_get_vendor( $raq->post->post_author, 'user' );
					}
					if( $tmp_vendor->is_valid() ){
						$vendor_raqs[ $tmp_vendor->id ] = $sub_raq;
					}
				}
			}


			if ( ! empty( $admin_list ) ) {
				$email_class->raq['order_id'] 	 = $parent_raq_id;
				$email_class->raq['raq_content'] = $admin_list;
				$return                          = $email_class->send( $email_class->get_recipient(), $email_class->get_subject(), $email_class->get_content(), $email_class->get_headers(), $email_class->get_attachments( $args['attachment'] ) );
			}

			if ( ! empty( $vendors_list ) ) {
				foreach ( $vendors_list as $vendor_id => $raq_vendor ) {

					$email_class->raq['order_id'] = $vendor_raqs[ $vendor_id ];
					$email_class->raq['raq_content'] = $raq_vendor;
					$vendor                          = yith_get_vendor( $vendor_id, 'vendor' );

					if ( isset( $vendor->store_email ) && $vendor->store_email ) {
						$email_class->recipient = $vendor->store_email;
                    }
                    else {
						$owner_id = $vendor->get_owner();
						if ( ! empty( $owner_id ) ) {
							$owner                      = get_user_by( 'id', $owner_id );
							$email_class->recipient = $owner->user_email;
						}
					}
					$return = $email_class->send( $email_class->get_recipient(), $email_class->get_subject(), $email_class->get_content(), $email_class->get_headers(), $email_class->get_attachments( $args['attachment'] ) );

				}
			}



			return $return;
		}

		/**
		 * Add hidden order itemmeta from Quote Email
		 * @param $itemmeta
		 *
		 * @return array
		 */
		public function add_hidden_order_itemmeta( $itemmeta  ) {
			$itemmeta[] = '_parent_line_item_id';
			return $itemmeta;
		}

		
	}

}

/**
 * Unique access to instance of YWRAQ_Multivendor class
 *
 * @return \YWRAQ_Multivendor
 */
function YWRAQ_Multivendor() {
	return YWRAQ_Multivendor::get_instance();
}

YWRAQ_Multivendor();