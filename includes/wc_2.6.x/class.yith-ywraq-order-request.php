<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements the YITH_YWRAQ_Order_Request class
 *
 *
 * @class    YITH_YWRAQ_Order_Request
 * @package  YITH Woocommerce Request A Quote
 * @since    1.0.0
 * @author   Yithemes
 */
class YITH_YWRAQ_Order_Request {


	/**
	 * Array with Quote List datas
	 */
	protected $_data = array();

	/**
	 * Name of dynamic coupon
	 */
	protected $label_coupon = 'quotediscount';

	/**
	 * @var array
	 */
	protected $yit_contact_form_post = array();

	/**
	 * @var array
	 */
	protected $order_payment_info = array();

	/**
	 * @var array
	 */
	private $args_message = array();

	/**
	 * @var bool
	 */
	private $quote_sent = false;

	/**
	 * Single instance of the class
	 *
	 * @var \YITH_YWRAQ_Order_Request
	 */
	protected static $instance;

	/**
	 * Returns single instance of the class
	 *
	 * @return \YITH_YWRAQ_Order_Request
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

		$this->_data['raq_order_status'] = array(
			'wc-ywraq-new',
			'wc-ywraq-pending',
			'wc-ywraq-expired',
			'wc-ywraq-rejected',
			'wc-ywraq-accepted'
		);

		add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'label_coupon' ), 10, 3 );
		add_filter( 'woocommerce_coupon_error', array( $this, 'manage_coupon_errors' ), 10, 3 );
		add_filter( 'woocommerce_coupon_message', array( $this, 'manage_coupon_message' ), 10, 3 );

		//set new order status
		add_action( 'init', array( $this, 'register_order_status' ) );
		add_filter( 'wc_order_statuses', array( $this, 'add_custom_status_to_order_statuses' ) );
		add_filter( 'wc_order_is_editable', array( $this, 'order_is_editable' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'raq_processed' ), 10, 2 );

		//add custom metabox
		add_action( 'admin_init', array( $this, 'add_metabox' ), 1 );
		add_action( 'save_post', array( $this, 'change_status_quote' ), 2, 2 );

		//backend orders
		add_filter( 'views_edit-shop_order', array( $this, 'show_add_new_quote' ) );
		add_action( 'save_post', array( $this, 'save_new_quote' ), 1, 2 );

		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'send_quote' ), 100, 2 );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'create_preview_pdf' ), 100, 2 );

		if ( get_option( 'ywraq_enable_order_creation', 'yes' ) == 'yes' ) {
			add_action( 'ywraq_process', array( $this, 'create_order' ), 10, 1 );
		}

		add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'create_coupon_cart_discount' ), 10, 2 );

		//myaccount list quotes
		add_filter( 'woocommerce_my_account_my_orders_query', array( $this, 'my_account_my_orders_query' ) );
		add_action( 'woocommerce_before_my_account', array( $this, 'my_account_my_quotes' ) );

		/* ajax action */
		if ( version_compare( WC()->version, '2.4.0', '<' ) ) {
			add_action( 'wp_ajax_yith_ywraq_order_action', array( $this, 'ajax' ) );
			add_action( 'wp_ajax_nopriv_yith_ywraq_order_action', array( $this, 'ajax' ) );
		} else {
			add_action( 'wc_ajax_yith_ywraq_order_action', array( $this, 'ajax' ) );
		}
		add_filter( 'nonce_user_logged_out', array( $this, 'wpnonce_filter' ), 10, 2 );
		add_action( 'wp_loaded', array( $this, 'change_order_status' ) );
		add_action( 'ywraq_raq_message', array( $this, 'print_message' ), 10 );

		//if yit_contact_form is used
		if ( get_option( 'ywraq_inquiry_form_type' ) == 'yit-contact-form' ) {
			add_action( 'init', array( $this, 'yit_contact_form_before_sending_email' ), 9 );
			add_action( 'yit-sendmail-success', array( $this, 'yit_contact_form_after_sent_email' ) );
		}

		//change price on cart
		//add_filter( 'woocommerce_add_cart_item', array( $this, 'change_price' ), 10, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 11, 2 );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_cart_fee' ) );
		add_action( 'woocommerce_review_order_before_shipping', array( $this, 'set_shipping_methods' ) );
		add_filter( 'woocommerce_package_rates', array( $this, 'set_package_rates' ) );

		//add endpoint view-quote
		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( 'template_redirect', array( $this, 'load_view_quote_page' ) );
		add_action( 'ywraq_view_quote', array( $this, 'check_permission_view_quote_page' ) );

		// contact form 7
		add_action( 'wpcf7_before_send_mail', array( $this, 'create_order_before_mail_cf7' ) );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'set_new_product_price' ), 20, 3 );

		//add the cart_hash as post meta of order
		add_filter( 'woocommerce_create_order', array( $this, 'set_cart_hash' ), 10);

		//if the customer cancell the order empty the cart
		add_action('woocommerce_cancelled_order', array( $this, 'empty_cart') );

		//override the checkout fields if this option is enabled
		add_action( 'template_redirect', array( $this, 'checkout_fields_manage' ) );

		//check if a customer is paying a quote
		add_action( 'wp_loaded', array( $this, 'check_quote_in_cart' ) );

	}

	/**
	 * Manage the checkout fields when a quote is accepted. Override the shipping or billing info in the checkout page.
	 * Called by the hook 'template_redirect'
	 *
	 * @return void
	 * @since 1.6.3
	 */
	public function checkout_fields_manage( ) {
		if( !is_checkout() ){
			return;
		}

		$order_id = WC()->session->order_awaiting_payment;
		if ( ! $order_id && !$this->is_quote( $order_id ) ) {
			return;
		}

		$checkout_info = get_post_meta( $order_id, '_ywraq_checkout_info', true );
		$this->order_payment_info['order_id'] = $order_id;
		$this->order_payment_info['checkout_info'] = $checkout_info;

		if( $this->order_payment_info['checkout_info'] == 'both' ||  $this->order_payment_info['checkout_info'] == 'billing' ){
			foreach ( WC()->countries->get_address_fields( '', 'billing_' ) as $key => $value ) {
				$this->order_payment_info[$key] = get_post_meta( $order_id, '_'.$key, true);
			}

			if( apply_filters('ywraq_lock_editing_billing', true ) && get_post_meta( $order_id, '_ywraq_lock_editing', true ) == 'yes'){
				add_filter('yith_ywraq_frontend_localize', array( $this, 'lock_billing' ) );
			}
		}

		if( $this->order_payment_info['checkout_info'] == 'both' || $this->order_payment_info['checkout_info'] == 'shipping' ){
			foreach ( WC()->countries->get_address_fields( '', 'shipping_' ) as $key => $value ) {
				$this->order_payment_info[$key] = get_post_meta( $order_id, '_'.$key, true);
			}

			if( apply_filters('ywraq_lock_editing_shipping', true ) && get_post_meta( $order_id, '_ywraq_lock_editing', true ) == 'yes'){
				add_filter('yith_ywraq_frontend_localize', array( $this, 'lock_shipping' ) );
				add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true' );
			}

		}

		if( $checkout_info != ''){
			add_filter('get_user_metadata', array( $this, 'checkout_fields_override'), 100, 4);
		}
	}

	/**
	 * Override the shipping or billing info in the checkout page.
	 * Called by the hook 'get_user_metadata' in the method checkout_fields_manage()
	 *
	 * @param $value
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return mixed|void
	 * @since 1.6.3
	 */
	public function checkout_fields_override( $value, $object_id, $meta_key, $single ) {
		if( !$this->order_payment_info) {
			return;
		}

		if( isset( $this->order_payment_info[$meta_key])){
			return $this->order_payment_info[$meta_key];
		}

		return $value;
	}

	/**
	 * If the customer cancel the order empty the cart
	 * @param $order_id
	 */
	public function empty_cart( $order_id ){
		if( $this->is_quote( $order_id ) && ! is_admin() ){
			WC()->cart->empty_cart();
			$order = wc_get_order($order_id);
			if( $order ){
				$order->update_status( 'ywraq-accepted' );
			}
			WC()->session->order_awaiting_payment = 0;
		}
	}

	public function save_quote( ){

	}

	/**
	 * @param $post_id
	 * @param $post
	 */
	public function save_new_quote( $post_id, $post ) {


		if ( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != 'shop_order'  ){
			return;
		}
		if( isset( $_REQUEST['new_quote'] ) && $_REQUEST['new_quote'] &&  $_REQUEST['post_type'] == 'shop_order' ) {
			update_post_meta( $post_id, 'ywraq_raq', 'yes' );

			$order = wc_get_order( $post_id );
			if ( $order ) {
				$order->update_status( 'ywraq-new' );
			}


		}else {

			$ov_field = apply_filters('ywraq_override_order_billing_fields', true );

			if( $ov_field ){

				if( isset($_REQUEST['ywraq_customer_email'])  ){
					// override the email address
					update_post_meta( $post_id, '_billing_email', $_REQUEST['ywraq_customer_email'] );
				}

				if( isset($_REQUEST['ywraq_customer_name'])  ){
					// override the email address
					update_post_meta( $post_id, '_billing_first_name', $_REQUEST['ywraq_customer_name'] );
				}
			}

		}


	}

	/**
	 * Add post meta _cart_hash to the order in waiting payment
	 *
	 * @since 1.3.0
	 * @return void
	 */
	function set_cart_hash( $value ){
		$order_id = absint( WC()->session->order_awaiting_payment );
		if( $this->is_quote( $order_id ) ){
			$hash = md5( json_encode( wc_clean( WC()->cart->get_cart_for_session() ) ) . WC()->cart->total );
			update_post_meta( $order_id, '_cart_hash', $hash);
		}
		return $value;
	}

	/**
	 * Add fee into cart after that the request was accepted
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public function add_cart_fee() {
		$fees = WC()->session->get( 'request_quote_fee' );
		if ( $fees ) {
			foreach ( $fees as $fee ) {
				$taxable = ( $fee['line_subtotal_tax'] ) ? true : false;
				WC()->cart->add_fee( $fee['name'], $fee['line_total'], $taxable, $fee['tax_class'] );
			}

		}
	}

	/**
	 * Filter the wpnonce
	 *
	 * @param int    $uid
	 * @param string $action
	 *
	 * @since 1.3.0
	 * @return string
	 */
	public function wpnonce_filter( $uid, $action ) {
		if ( strpos( 'accept-request-quote-', $action ) || strpos( 'reject-request-quote-', $action ) ) {
			return '';
		}

		return $uid;
	}

	/**
	 * Return a $property defined in this class
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $property
	 *
	 * @return mixed
	 */
	public function __get( $property ) {

		if ( isset( $this->_data[$property] ) ) {
			return $this->_data[$property];
		}

	}

	/**
	 * Add the endpoint for the page in my account to manage the order view
	 *
	 * @since 1.0.0
	 */
	public function add_endpoint() {
		$view_quote = get_option( 'woocommerce_myaccount_view_quote_endpoint', 'view-quote' );
		$do_flush   = get_option( 'yith-ywraq-flush-rewrite-rules', 1 );

		add_rewrite_endpoint( $view_quote, EP_ROOT | EP_PAGES );

		if ( $do_flush ) {
			// change option
			update_option( 'yith-ywraq-flush-rewrite-rules', 0 );
			// the flush rewrite rules
			flush_rewrite_rules();
		}
	}

	/**
	 * Load the page of saved cards
	 *
	 * @since 1.0.0
	 */
	public function load_view_quote_page() {
		global $wp, $post;

		$view_quote = get_option( 'woocommerce_myaccount_view_quote_endpoint', 'view-quote' );

		if ( ! is_page( wc_get_page_id( 'myaccount' ) ) || ! isset( $wp->query_vars[$view_quote] ) ) {
			return;
		}

		$order_id           = $wp->query_vars[$view_quote];
		//   apply_filters( 'ywraq_quote_number', $this->raq['order_id'] );
		$post->post_title   = sprintf( __( 'Quote #%s', 'yith-woocommerce-request-a-quote' ), apply_filters( 'ywraq_quote_number', $order_id ) );
		$post->post_content = WC_Shortcodes::shortcode_wrapper( array( $this, 'view_quote' ) );

		remove_filter( 'the_content', 'wpautop' );
	}

	/**
	 * Show the quote detail
	 *
	 * @since 1.0.0
	 */
	public function view_quote() {
		global $wp;
		if ( !is_user_logged_in() ) {
			wc_get_template( 'myaccount/form-login.php' );
		}
		else {
			$view_quote = get_option( 'woocommerce_myaccount_view_quote_endpoint', 'view-quote' );
			$order_id = $wp->query_vars[$view_quote];
			wc_get_template( 'myaccount/view-quote.php',
				array( 'order_id'     => $order_id,
				       'current_user' => get_user_by( 'id', get_current_user_id() ) ), YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );
		}
	}

	/**
	 * Show the quote list
	 *
	 * @since   1.0.0
	 */
	public function view_quote_list(){
		wc_get_template( 'myaccount/my-quotes.php', null, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );
	}

	/**
	 * Register Order Status
	 *
	 * @return void
	 * @since  1.0
	 * @author Emanuela Castorina
	 */
	public function register_order_status() {
		register_post_status( 'wc-ywraq-new', array(
			'label'                     => __( 'New Quote Request', 'yith-woocommerce-request-a-quote' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'New Quote Request <span class="count">(%s)</span>', 'New Quote Requests <span class="count">(%s)</span>', 'yith-woocommerce-request-a-quote' )
		) );

		register_post_status( 'wc-ywraq-pending', array(
			'label'                     => __( 'Pending Quote', 'yith-woocommerce-request-a-quote' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Pending Quote <span class="count">(%s)</span>', 'Pending Quote <span class="count">(%s)</span>','yith-woocommerce-request-a-quote' )
		) );

		register_post_status( 'wc-ywraq-expired', array(
			'label'                     => __( 'Expired Quote', 'yith-woocommerce-request-a-quote' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Expired Quote <span class="count">(%s)</span>', 'Expired Quotes <span class="count">(%s)</span>','yith-woocommerce-request-a-quote' )
		));

		register_post_status( 'wc-ywraq-accepted', array(
			'label'                     => __( 'Accepted Quote', 'yith-woocommerce-request-a-quote' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Accepted Quote <span class="count">(%s)</span>', 'Accepted Quote <span class="count">(%s)</span>','yith-woocommerce-request-a-quote' )
		));

		register_post_status( 'wc-ywraq-rejected', array(
			'label'                     => __( 'Rejected Quote', 'yith-woocommerce-request-a-quote' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Rejected Quote <span class="count">(%s)</span>', 'Rejected Quote <span class="count">(%s)</span>','yith-woocommerce-request-a-quote' )
		));

	}

	/**
	 * Add Custom Order Status to WC_Order
	 *
	 * @param $order_statuses
	 *
	 * @return array
	 * @since  1.0
	 * @author Emanuela Castorina
	 */
	public function add_custom_status_to_order_statuses( $order_statuses ) {
		$order_statuses['wc-ywraq-new']      = __( 'New Quote Request', 'yith-woocommerce-request-a-quote' );
		$order_statuses['wc-ywraq-pending']  = __( 'Pending Quote', 'yith-woocommerce-request-a-quote' );
		$order_statuses['wc-ywraq-expired']  = __( 'Expired Quote', 'yith-woocommerce-request-a-quote' );
		$order_statuses['wc-ywraq-accepted'] = __( 'Accepted Quote', 'yith-woocommerce-request-a-quote' );
		$order_statuses['wc-ywraq-rejected'] = __( 'Rejected Quote', 'yith-woocommerce-request-a-quote' );

		return $order_statuses;
	}

	/**
	 * @return array
	 */
	public function get_quote_order_status() {
		return array(
			'wc-ywraq-new' => __( 'New Quote Request', 'yith-woocommerce-request-a-quote' ),
			'wc-ywraq-pending'  => __( 'Pending Quote', 'yith-woocommerce-request-a-quote' ),
			'wc-ywraq-expired'  => __( 'Expired Quote', 'yith-woocommerce-request-a-quote' ),
			'wc-ywraq-accepted' => __( 'Accepted Quote', 'yith-woocommerce-request-a-quote' ),
			'wc-ywraq-rejected' => __( 'Rejected Quote', 'yith-woocommerce-request-a-quote' )
		);
	}

	/**
	 * Set custom status order editable
	 *
	 * @param $editable
	 * @param $order
	 *
	 * @return bool
	 * @since  1.0
	 * @author Emanuela Castorina
	 */
	public function order_is_editable( $editable, $order ) {

		$accepted_statuses = apply_filters( 'ywraq_quote_accepted_statuses_edit', array( 'ywraq-new', 'ywraq-accepted', 'ywraq-pending', 'ywraq-expired', 'ywraq-rejected' ) );

		if ( in_array( $order->get_status(), $accepted_statuses  ) ) {
			return true;
		}
		return $editable;
	}

	/**
	 * Set custom status order editable
	 *
	 * @param $raq
	 *
	 * @return int
	 * @throws Exception
	 * @since  1.0.0
	 * @author Emanuela Castorina
	 */
	public function create_order( $raq ) {

		if ( ! defined( 'DOING_CREATE_RAQ_ORDER' ) ) {
			define( 'DOING_CREATE_RAQ_ORDER', true );
		}


		$raq_content = $raq['raq_content'];

		if ( empty( $raq_content ) ) {
			return false;
		}

		if ( isset( $raq['customer_id'] ) ) {
			$customer_id = $raq['customer_id'];
		} else {
			$customer_id = get_current_user_id();
		}

		if ( class_exists( 'WC_Subscriptions_Coupon' ) ) {
			remove_filter( 'woocommerce_get_discounted_price', 'WC_Subscriptions_Coupon::apply_subscription_discount_before_tax', 10 );
			remove_filter( 'woocommerce_get_discounted_price', 'WC_Subscriptions_Coupon::apply_subscription_discount', 10 );
		}

		do_action( 'ywraq_before_create_order', $raq );

		WC()->session->__unset( 'chosen_shipping_methods' );
		// Ensure shipping methods are loaded early
		WC()->shipping();

		if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}

		$order = wc_create_order( $args = array(
			'status'      => 'ywraq-new',
			'customer_id' => apply_filters( 'ywraq_customer_id', $customer_id )
		) );


		add_post_meta( $order->id, '_current_user', $customer_id );

		//Add order meta to new RAQ order
		$this->add_order_meta( $order, $raq );

		$cart = WC()->cart;

		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		$new_cart = new WC_Cart();
		WC()->cart = $new_cart;
		WC()->cart->empty_cart();
		WC()->cart->set_session();


		if( get_option('ywraq_allow_raq_out_of_stock', 'no') == 'yes'){
			add_filter('woocommerce_variation_is_in_stock','__return_true');
			add_filter('woocommerce_product_is_in_stock','__return_true');
			add_filter( 'woocommerce_product_backorders_allowed', '__return_true' );
		}

		add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ) , 99);
		foreach ( $raq_content as $item => $values ) {

			$new_cart_item_key = $new_cart->add_to_cart(
				$values['product_id'],
				$values['quantity'],
				( isset( $values['variation_id'] ) ? $values['variation_id'] : '' ),
				( isset( $values['variations'] ) ? $values['variations'] : '' ),
				$values
			);

			$new_cart = apply_filters('ywraq_add_to_cart_from_request', $new_cart, $values, $item , $new_cart_item_key );

		}
		remove_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ) , 99);

		$new_cart->calculate_totals();  // fix tax

		foreach ( $new_cart->get_cart() as $cart_item_key => $values ) {

			$args = array();
			if ( isset( $values['variation'] ) ) {
				$args['variation'] =  $values['variation'];
			}
			if( isset( $values['line_subtotal'] ) ){
				$args['totals']['subtotal'] = $values['line_subtotal'];
			}
			if( isset( $values['line_total'] ) ){
				$args['totals']['total'] = $values['line_total'];
			}
			if( isset( $values['line_subtotal_tax'] ) ){
				$args['totals']['subtotal_tax'] = $values['line_subtotal_tax'];
			}
			if( isset( $values['line_tax'] ) ){
				$args['totals']['tax'] = $values['line_tax'];
			}
			if( isset( $values['line_tax_data'] ) ){
				$args['totals']['tax_data'] = $values['line_tax_data'];
			}

			$values['quantity'] = ( $values['quantity'] <= 0 ) ? 1 : $values['quantity'];

			$args = apply_filters( 'ywraq_cart_to_order_args', $args, $cart_item_key, $values, $new_cart );

			$item_id = $order->add_product(
				$values['data'],
				$values['quantity'],
				$args
			);

			do_action('ywraq_from_cart_to_order_item', $values, $cart_item_key, $item_id );
		}

		$calculate_shipping = apply_filters('ywraq_calculate_shipping_from_request', get_option('ywraq_calculate_default_shipping_quote', 'no') );

		if ( $calculate_shipping == 'yes' ) {

			if ( $new_cart->needs_shipping() ) {

				$packages        = WC()->shipping->get_packages();
				$shipping_method = apply_filters( 'ywraq_filter_shipping_methods', WC()->session->get( 'chosen_shipping_methods' ) );

				// Store shipping for all packages
				foreach ( $packages as $package_key => $package ) {

					if ( isset( $package['rates'][ $shipping_method [ $package_key ] ] ) ) {

						$item_id = $order->add_shipping( $package['rates'][ $shipping_method[ $package_key ] ] );

						if ( ! $item_id ) {
							throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'yith-woocommerce-one-click-checkout' ), 404 ) );
						}

						// Allows plugins to add order item meta to shipping
						do_action( 'woocommerce_add_shipping_order_item', $order->id, $item_id, $package_key );
					}
					else {
						if( apply_filters( 'ywraq_throw_shipping_exception', is_user_logged_in() ) ){
							throw new Exception( __( 'Sorry, invalid shipping method.', 'yith-woocommerce-one-click-checkout' ) );
						}
					}
				}
			}

			$order->set_total( $new_cart->tax_total, 'tax' );
			$order->set_total( $new_cart->shipping_total, 'shipping' );
			$order->set_total( $new_cart->shipping_tax_total, 'shipping_tax' );
			$order->set_total( $new_cart->total );

		}else{
			$order->set_total( $new_cart->tax_total - $new_cart->shipping_tax_total, 'tax' );
			$order->set_total( $new_cart->total - $new_cart->shipping_total - $new_cart->shipping_tax_total );
		}


		$order->set_total( $new_cart->get_cart_discount_total(), 'cart_discount' );
		$order->set_total( $new_cart->get_cart_discount_tax_total(), 'cart_discount_tax' );
		$order->calculate_taxes();
		$order->calculate_totals();


		WC()->cart = $cart;
		WC()->cart->set_session();
		WC()->cart->calculate_totals();

		WC()->session->set( 'raq_new_order', $order->id );

		do_action( 'ywraq_after_create_order', $order->id, $_POST, $raq );

		return $order->id;

	}

	/**
	 * Add the order meta to new RAQ order
	 *
	 * @param             WC_Order
	 * @param mixed|array Request  a quote args
	 *
	 * @since  1.4.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 * @return void
	 */
	public function add_order_meta( $order, $raq ) {

		$ov_field = apply_filters('ywraq_override_order_billing_fields', true );
		//save the customer message
		add_post_meta( $order->id, 'ywraq_customer_message', $raq['user_message'] );
		add_post_meta( $order->id, 'ywraq_customer_email', $raq['user_email'] );
		add_post_meta( $order->id, 'ywraq_customer_name', $raq['user_name'] );

		if( $ov_field ){
			// override the email address
			update_post_meta( $order->id, '_billing_email', $raq['user_email'] );

			//override first name and last name
			if ( isset( $raq['_billing_first_name'] ) ) {
				update_post_meta( $order->id, '_billing_first_name', $raq['_billing_first_name'] );
				update_post_meta( $order->id, '_billing_last_name', $raq['_billing_last_name'] );
			}else{
				update_post_meta( $order->id, '_billing_first_name', $raq['user_name'] );
			}

		}

		if ( isset( $raq['user_additional_field'] ) ) {
			add_post_meta( $order->id, 'ywraq_customer_additional_field', $raq['user_additional_field'] );
			if ( $meta = get_option( 'ywraq_additional_text_field_meta' ) ) {
				add_post_meta( $order->id, $meta, $raq['user_additional_field'] );
			}
		}

		if ( isset( $raq['other_email_content'] ) ) {
			add_post_meta( $order->id, 'ywraq_other_email_content', $raq['other_email_content'] );
		}

		if ( isset( $raq['user_additional_field_2'] ) ) {
			add_post_meta( $order->id, 'ywraq_customer_additional_field_2', $raq['user_additional_field_2'] );
			if ( $meta = get_option( 'ywraq_additional_text_field_meta_2' ) ) {
				add_post_meta( $order->id, $meta, $raq['user_additional_field_2'] );
			}
		}

		if ( isset( $raq['user_additional_field_3'] ) ) {
			add_post_meta( $order->id, 'ywraq_customer_additional_field_3', $raq['user_additional_field_3'] );
			if ( $meta = get_option( 'ywraq_additional_text_field_meta_3' ) ) {
				add_post_meta( $order->id, $meta, $raq['user_additional_field_3'] );
			}
		}

		if ( isset( $raq['attachment'] ) ) {
			add_post_meta( $order->id, 'ywraq_customer_attachment', $raq['attachment'] );
		}

		if ( isset( $raq['other_email_fields'] ) ) {
			add_post_meta( $order->id, 'ywraq_other_email_fields', $raq['other_email_fields'] );
		}

		if ( isset( $raq['billing-address'] ) ) {
			if( $ov_field ){
				if ( isset( $raq['_billing_address_1'] ) ) {
					update_post_meta( $order->id, '_billing_address_1', $raq['_billing_address_1'] );
					update_post_meta( $order->id, '_billing_address_2', $raq['_billing_address_2'] );
					update_post_meta( $order->id, '_billing_city', $raq['_billing_city'] );
					update_post_meta( $order->id, '_billing_state', $raq['_billing_state'] );
					update_post_meta( $order->id, '_billing_postcode', $raq['_billing_postcode'] );
					update_post_meta( $order->id, '_billing_country', $raq['_billing_country'] );
				}else{
					update_post_meta( $order->id, '_billing_address_1', $raq['billing-address'] );
				}
			}

			add_post_meta( $order->id, 'ywraq_billing_address', $raq['billing-address'] );
		}

		if ( isset( $raq['billing-phone'] ) ) {
			add_post_meta( $order->id, 'ywraq_billing_phone', $raq['billing-phone'] );
			if( $ov_field ){
				update_post_meta( $order->id, '_billing_phone', $raq['billing-phone'] );
			}
		}

		if ( isset( $raq['billing-vat'] ) ) {
			add_post_meta( $order->id, 'ywraq_billing_vat', $raq['billing-vat'] );
		}

		add_post_meta( $order->id, 'ywraq_raq_status', 'pending' );
		add_post_meta( $order->id, 'ywraq_raq', 'yes' );

		if ( isset( $raq['lang'] ) ) {
			update_post_meta( $order->id, 'wpml_language', $raq['lang'] );
		}

		do_action('ywraq_add_order_meta', $order->id, $raq );
	}

	/**
	 * Add to cart the products in the request, add also a coupoun with the discount applied
	 *
	 * @param $order_id
	 *
	 * @since  1.0.0
	 * @author Emanuela Castorina
	 */
	public function order_accepted( $order_id ) {
		if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}

		// Clear current cart
		WC()->cart->empty_cart( true );
		WC()->cart->get_cart_from_session();
		WC()->session->set( 'order_awaiting_payment', $order_id );

		// Load the previous order - Stop if the order does not exist
		$order = wc_get_order( $order_id );

		if ( empty( $order->id ) ) {
			return;
		}

		do_action( 'ywraq_before_order_accepted', $order_id );
		$order->update_status( 'pending' );

		update_post_meta( $order->id, 'ywraq_raq_status', 'accepted' );

		$minimum_amount = $order->get_subtotal();
		add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ) , 99);
		// Copy products from the order to the cart
		foreach ( $order->get_items() as $item ) {

			// Load all product info including variation data
			$product_id   = (int) apply_filters( 'woocommerce_add_to_cart_product_id', $item['product_id'] );
			$quantity     = $item['qty'];
			$variation_id = (int) $item['variation_id'];
			$variations   = array();

			$pr = wc_get_product( ($variation_id ) ? $variation_id : $product_id);

			foreach ( $item['item_meta'] as $meta_name => $meta_value ) {
				if ( taxonomy_is_product_attribute( $meta_name ) ) {
					$variations[ $meta_name ] = $meta_value[0];
				} elseif ( meta_is_product_attribute( $meta_name, $meta_value[0], $product_id ) ) {
					$variations[ $meta_name ] = $meta_value[0];
				}
			}

			if ( function_exists( 'YITH_WCTM' ) ) {
				remove_filter( 'woocommerce_add_to_cart_validation', array( YITH_WCTM(), 'avoid_add_to_cart' ), 10 );
			}

			$cart_item_data = apply_filters( 'woocommerce_order_again_cart_item_data', array(), $item, $order );
			$cart_item_data = apply_filters( 'ywraq_order_cart_item_data', $cart_item_data, $item, $order );

			if( $quantity ){
				if( get_option( 'woocommerce_prices_include_tax', 'no' ) == 'yes' ){
					$cart_item_data['ywraq_price'] = ($item['line_subtotal']+$item['line_subtotal_tax'])/ $quantity;
				}else{
					$cart_item_data['ywraq_price'] = ($item['line_subtotal'])/ $quantity;
				}
			}

			if ( ! apply_filters( 'ywraq_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations, $cart_item_data ) ) {
				continue;
			}

			// Add to cart validation
			remove_filter( 'woocommerce_add_to_cart_validation', array( $this, 'cart_validation'), 10, 2);

			if ( ! apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations, $cart_item_data ) ) {
				continue;
			}

			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations, $cart_item_data );

		}

		$fees = $order->get_fees();

		WC()->session->set( 'request_quote_fee', $fees );
		WC()->session->set( 'request_shipping_tax', $order->get_shipping_tax() );

		$order_discount = $order->get_total_discount( $order->tax_display_cart === 'excl' && $order->display_totals_ex_tax );

		if ( $order_discount > 0 ) {
			$coupon = apply_filters( 'ywraq_accepted_order_coupon', array(
				'coupon_amount'  => $order_discount,
				'minimum_amount' => $minimum_amount,
				'discount_type'  => 'fixed_cart'
			));

			WC()->session->set( 'request_quote_discount', $coupon );
			WC()->cart->add_discount( $this->label_coupon );
		}

		WC()->cart->calculate_totals();

		remove_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ) , 99);
		do_action( 'ywraq_after_order_accepted', $order_id );

		do_action( 'change_status_mail', array( 'order' => $order, 'status' => 'accepted' ) );

	}

	/**
	 * @param $cart_item_data
	 * @param $cart_item_key
	 *
	 * @return mixed
	 */
	public function set_new_product_price( $cart_item_data, $cart_item_key ){
		if ( isset( $cart_item_data['ywraq_price'] ) ) {
			$cart_item_data['data']->price       = $cart_item_data['ywraq_price'];
		}

		return $cart_item_data;
	}

	/**
	 * Update the price in the cart session
	 *
	 * @param array $cart_item
	 * @param array $values
	 *
	 * @return array
	 * @since  1.3.0
	 * @author Emanuela Castorina
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {

		if ( ! isset( $cart_item['ywraq_price'] ) ) {
			return $cart_item;
		}

		$cart_item['data']->price = $cart_item['ywraq_price'];

		return $cart_item;
	}

	/**
	 * Create Coupon
	 *
	 * @param $args
	 * @param $code
	 *
	 * @return array
	 * @since  1.0.0
	 * @author Emanuela Castorina
	 */
	public function create_coupon_cart_discount( $args, $code ) {
		if ( $code == apply_filters( 'woocommerce_coupon_code', $this->label_coupon ) && isset( WC()->session->request_quote_discount ) ) {
			$args = WC()->session->request_quote_discount;
		}

		return $args;
	}

	/**
	 * Add actions to WC Order Editor
	 *
	 * @param $actions
	 *
	 * @return array
	 * @since  1.0.0
	 * @author Emanuela Castorina
	 */
	public function add_order_actions( $actions ) {
		$actions['ywraq-send-quote'] = __( 'Send the Quote', 'yith-woocommerce-request-a-quote' );
		return $actions;
	}

	/**
	 * Add metabox in the order editor
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  void
	 */
	public function  add_metabox() {

		$post = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : ( isset( $_REQUEST['post_ID'] ) ? $_REQUEST['post_ID'] : 0 );
		$post = get_post( $post );

		if ( ( isset( $_GET['new_quote'] ) && $_GET['new_quote'] ) || ( $post && $post->post_type == 'shop_order' && $this->is_quote( $post->ID ) ) ) {
			$args = require_once( YITH_YWRAQ_DIR . 'plugin-options/metabox/ywraq-metabox-order.php' );
			if ( ! function_exists( 'YIT_Metabox' ) ) {
				require_once( YITH_YWRAQ_DIR . 'plugin-fw/yit-plugin.php' );
			}
			$metabox = YIT_Metabox( 'yith-ywraq-metabox-order' );
			$metabox->init( $args );
		}

	}

	/**
	 * Add a button add to quote in the orders list
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  void
	 */
	public function  show_add_new_quote( $view ) {

		$link = esc_url(  admin_url( 'post-new.php?post_type=shop_order&new_quote=1' ) );

		printf( __( '<p><a href="%s">Add a new Quote</a></p>', 'yith-woocommerce-request-a-quote'), $link );

		return $view;

	}

	/**
	 * Send the quote to the customer
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function send_quote( $post_id, $post ) {

		if ( $this->quote_sent || !isset( $_POST['yit_metaboxes'] ) || !isset( $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] ) || $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] != 'send_quote' ) {
			return;
		}

		$this->quote_sent = true;

		$order = wc_get_order( $post_id );
		if( get_option('ywraq_enable_pdf', 'yes') ){
			do_action( 'create_pdf', $order->id );
		}

		do_action( 'send_quote_mail', $order->id );

	}

	/**
	 * Create PDF of the quote
	 *
	 * @since   1.6.0
	 * @author  Emanuela Castorina
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function create_preview_pdf( $post_id, $post ) {

		if ( !isset( $_POST['yit_metaboxes'] ) || !isset( $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] ) || $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] != 'create_preview_pdf' ) {
			return;
		}

		$order = wc_get_order( $post_id );

		do_action( 'create_pdf', $order->id );

	}

	/**
	 * Change the status of the quote
	 *
	 * @param int $post_id
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function change_status_quote( $post_id ) {

		if ( ! isset( $_POST['yit_metaboxes'] ) || ! isset( $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] ) || $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] != 'send_quote'  ) {
			return;
		}

		$order = wc_get_order( $post_id );

		if( $order  ){
			$order->update_status( 'ywraq-pending' );
		}

		return;

	}

	/**
	 * Remove Quotes from Order query
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function my_account_my_orders_query( $args ) {

		if ( version_compare( WC()->version, '2.6.0', '<') ){
			$args['meta_query'] = array(
				array(
					'key'     => 'ywraq_raq_status',
					'compare' => 'NOT EXISTS',
				)
			);

			$args['post_status'] = array_diff( $args['post_status'], $this->_data['raq_order_status'] );
		}else{
			$args['status'] = array_keys(array_diff( wc_get_order_statuses(), $this->get_quote_order_status() ));
		}


		return $args;
	}

	/**
	 * Add quotes list to my-account page
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  void
	 */
	public function my_account_my_quotes() {
		wc_get_template( 'myaccount/my-quotes.php', null, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );
	}

	/**
	 * Switch a ajax call
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  void
	 */
	public function ajax() {
		if ( isset( $_POST['ywraq_order_action'] ) ) {
			if ( method_exists( $this, 'ajax_' . $_POST['ywraq_order_action'] ) ) {
				$s = 'ajax_' . $_POST['ywraq_order_action'];
				$this->$s();
			}
		}
	}

	/**
	 * @param $cf
	 */
	public function create_order_before_mail_cf7( $cf ) {

		$form_id = ywraq_get_current_contact_form_7();

		if( isset( $_REQUEST['_wpcf7'] ) && $_REQUEST['_wpcf7'] == $form_id ) {
			$this->ajax_create_order( false );
		}
	}

	/**
	 *
	 */
	public function ajax_mail_sent_order_created() {

		$order_id = isset( $_COOKIE['yith_ywraq_order_id'] ) ? $_COOKIE['yith_ywraq_order_id'] : 0;

		if( ! $order_id ){
			yith_ywraq_add_notice( __( 'An error as occurred creating your request. Please try again.', 'yith-woocommerce-request-a-quote' ), 'error' );

			wp_send_json(
				array(
					'rqa_url' => YITH_Request_Quote()->get_raq_page_url(),
				)
			);
		}

		wc_setcookie( 'yith_ywraq_order_id', 0, time() - HOUR_IN_SECONDS );

		if ( ! empty( $_REQUEST['current_user_id'] ) ) {
			update_post_meta( $order_id, '_customer_id', $_REQUEST['current_user_id'] );
		}

		if ( apply_filters( 'ywraq_clear_list_after_send_quote', true ) ) {
			YITH_Request_Quote()->clear_raq_list();
		}

		yith_ywraq_add_notice( ywraq_get_message_after_request_quote_sending( $order_id ), 'success' );

		wp_send_json(
			array(
				'rqa_url' => YITH_Request_Quote()->get_redirect_page_url(),
			)
		);
	}

	/**
	 * Called to create an order from a request sended with contact form 7
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param bool $mail_sent_order_created
	 *
	 * @throws Exception
	 */
	public function ajax_create_order( $mail_sent_order_created = true ) {

		$exclude_params = apply_filters( 'ywraq_other_fields_exclusion_list', array(
			'_wpcf7',
			'_wpcf7_version',
			'_wpcf7_locale',
			'_wpcf7_unit_tag',
			'_wpnonce',
			'your-name',
			'your-email',
			'your-subject',
			'your-message',
			'lang',
			'action',
			'ywraq_order_action',
			'billing-address',
			'billing-phone',
			'billing-vat',
			'g-recaptcha-response',
			'_wpcf7_is_ajax_call',
			'current_user_id'
		) );

		$other_email_content = '';
		$other_fields        = array();

		$raq_content = YITH_Request_Quote()->get_raq_return();

		$args = array(
			'other_email_content' => $other_email_content,
			'raq_content'         => $raq_content
		);

		switch ( get_option( 'ywraq_inquiry_form_type', 'default' ) ) {
			case 'gravity-forms':
				$args = apply_filters( 'ywraq_ajax_create_order_gravity_forms_args', $args, $_POST );

				break;
			default:

				if ( ! empty( $_POST ) ) {
					foreach ( $_POST as $key => $value ) {
						if ( ! in_array( $key, $exclude_params ) ) {
							$other_email_content .= sprintf( '<strong>%s</strong>: %s<br>', $key, $value );
							$other_fields[ $key ] = $value;
						}
					}
				}

				$args_cf7 = array(
					'user_name'    => $_POST['your-name'],
					'user_email'   => $_POST['your-email'],
					'user_message' => $_POST['your-message'],
					'other_email_fields'  => $other_fields,
					'other_email_content' => $other_email_content,
				);

				$args = array_merge( $args, $args_cf7 );

				if ( isset( $_POST['billing-address'] ) ) {
					$args['billing-address'] = $_POST['billing-address'];
				}

				if ( isset( $_POST['billing-phone'] ) ) {
					$args['billing-phone'] = $_POST['billing-phone'];
				}

				if ( isset( $_POST['billing-vat'] ) ) {
					$args['billing-vat'] = $_POST['billing-vat'];
				}

				if ( isset( $_REQUEST['lang'] ) ) {
					$args['lang'] = $_REQUEST['lang'];
				}

				if ( ! isset( $args['lang'] )  && isset( $_POST['_wpcf7_locale'] ) ) {
					$lang         = explode( '_', $_POST['_wpcf7_locale'] );
					$args['lang'] = $lang[0];
				}

				$current_customer_id = 0;

				if ( is_user_logged_in() ) {
					$current_customer_id = get_current_user_id();
				} elseif ( $current_customer = get_user_by( 'email', $_POST['your-email'] ) ) {
					$current_customer_id = $current_customer->ID;
				}



				$args['customer_id'] = $current_customer_id;
		}

		$new_order = $this->create_order( $args );

		if ( $new_order ) {
			wc_setcookie( 'yith_ywraq_order_id', $new_order, 0 );
		}


		if ( $mail_sent_order_created ) {
			$this->ajax_mail_sent_order_created();
		}
	}

	/**
	 * Change the status of Quote in 'accepted'
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  void
	 */
	public function ajax_accept_order() {
		if ( ! isset( $_POST['order_id'] ) ) {
			$message = __( 'An error occurred. Please, contact site administrator', 'yith-woocommerce-request-a-quote' );
			$result  = array(
				'result'  => 0,
				'message' => $message,
			);
		} else {

			$accepted_page_id = get_option( 'ywraq_page_accepted' );
			$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );
			$cart_page_id = get_option( 'woocommerce_cart_page_id' );



			$order = wc_get_order( $_POST['order_id'] );
			if ( $accepted_page_id == $checkout_page_id || $accepted_page_id == $cart_page_id ) {
				$this->order_accepted( $order->id );
				$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : WC()->cart->get_checkout_url();
				$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : WC()->cart->get_cart_url();
				$redirect = $accepted_page_id == $cart_page_id  ? $cart_url : $checkout_url;

				$result = array(
					'result'  => 1,
					'rqa_url' => apply_filters( 'ywraq_accepted_redirect_url', $redirect, $order->id) ,
				);

			} else {
				$this->accept_reject_order( 'accepted', $_POST['order_id'] );

				$result = array(
					'result'  => 1,
					'rqa_url' => apply_filters( 'ywraq_accepted_redirect_url', get_permalink( $accepted_page_id ), $order->id ),
				);
			}

		}

		wp_send_json(
			$result
		);
	}

	/**
	 * Reject the quote
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  void
	 */
	public function ajax_reject_order() {
		if ( !isset( $_POST['order_id'] ) ) {
			$message = __( 'An error occurred. Please, contact site administrator', 'yith-woocommerce-request-a-quote' );
			$result  = array(
				'result'  => 0,
				'message' => $message,
			);
		}
		else {
			$this->accept_reject_order( 'rejected', $_POST['order_id'] );

			$result = array(
				'result'  => 1,
				'status'  => __( 'rejected', 'yith-woocommerce-request-a-quote' ),
				'rqa_url' => '',
			);
		}

		wp_send_json(
			$result
		);
	}

	/**
	 * Change the status of the quote
	 * @param $status
	 * @param $order_id
	 */
	public function accept_reject_order( $status, $order_id ){

		$order = wc_get_order( $order_id );
		update_post_meta( $order_id, 'ywraq_raq_status', $status );

		//return if the status is the same
		if ( $order->get_status() == 'ywraq-' . $status ) {
			return;
		}

		$order->update_status( 'ywraq-' . $status );
		$args = array(
			'order'  => $order,
			'status' => $status
		);

		do_action( 'change_status_mail', $args );
	}

	/**
	 * Delete post meta ywraq_status
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $order_id
	 */
	public function raq_processed( $order_id ) {
		delete_post_meta( $order_id, 'ywraq_raq_status' );
	}

	/**
	 * Change the label of coupon
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $string
	 * @param $coupon
	 *
	 * @return string
	 */
	public function label_coupon( $string, $coupon ) {

		//change the label if the order is generated from a quote
		if ( $coupon->code != $this->label_coupon ) {
			return $string;
		}

		$label = esc_html( __( 'Discount:', 'yith-woocommerce-request-a-quote' ) );
		return $label;
	}

	/**
	 * Manage the request from the email of customer
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  void
	 */
	public function change_order_status() {

		if ( !isset( $_GET['raq_nonce'] ) || !isset( $_GET['status'] ) || !isset( $_GET['request_quote'] ) ) {
			return;
		}

		$status   = $_GET['status'];
		$order_id = $_GET['request_quote'];

		$order      = wc_get_order( $_GET['request_quote'] );
		$user_email = get_post_meta( $order_id, 'ywraq_customer_email', true );
		$this->is_expired( $order_id );
		$current_status = $order->get_status();

		$args = array(
			'message' => '',
		);
		if ( ! ywraq_verify_token( $_GET['raq_nonce'], 'accept-request-quote', $order_id, $user_email ) && ! ywraq_verify_token( $_GET['raq_nonce'], 'reject-request-quote', $order_id, $user_email ) ) {
			$args['message']    = sprintf( __( 'You do not have permission to read the quote', 'yith-woocommerce-request-a-quote' ), $order_id );
			$this->args_message = $args;
			return;
		}


		if ( $status == 'accepted' && ywraq_verify_token( $_GET['raq_nonce'], 'accept-request-quote', $order_id, $user_email ) ) {

			if ( in_array( $current_status, array( 'ywraq-pending', 'pending', 'ywraq-accepted') ) ) {

				if ( YITH_Request_Quote()->enabled_checkout() ) {
					$this->order_accepted( $order_id );
					$accepted_page_id = get_option( 'ywraq_page_accepted' );
					$cart_page_id = get_option( 'woocommerce_cart_page_id' );
					$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : WC()->cart->get_checkout_url();
					$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : WC()->cart->get_cart_url();
					$redirect = $accepted_page_id == $cart_page_id  ? $cart_url : $checkout_url;
				} else {
					$this->accept_reject_order( 'accepted', $order_id );
					$redirect = get_permalink( get_option( 'ywraq_page_accepted' ));
				}

				wp_safe_redirect( apply_filters('ywraq_accepted_redirect_url', $redirect, $order_id ) );
				exit;
			} else {
				switch ( $current_status ) {
					case 'ywraq-rejected':
						$args['message'] = sprintf( __( 'Quote n. %d has been rejected and is not available', 'yith-woocommerce-request-a-quote' ), $order_id );
						break;
					case 'ywraq-expired':
						$args['message'] = sprintf( __( 'Quote n. %d has expired and is not available', 'yith-woocommerce-request-a-quote' ), $order_id );
						break;
					default:
						$args['message'] = sprintf( __( 'Quote n. %d can\'t be accepted because its status is: %s', 'yith-woocommerce-request-a-quote' ), $order_id, $current_status );
						break;
				}
			}
		}
		else {
			if ( $current_status == 'ywraq-rejected' && $status == 'rejected' ) {
				$args['message'] = sprintf( __( 'Quote n. %d has been rejected', 'yith-woocommerce-request-a-quote' ), $order_id );
			}
			elseif ( $current_status == 'ywraq-expired' ) {
				$args['message'] = sprintf( __( 'Quote n. %d has expired and is not available', 'yith-woocommerce-request-a-quote' ), $order_id );
			}
			elseif ( $current_status != 'ywraq-pending' && $current_status != 'pending' ) {
				$args['message'] = sprintf( __( 'Quote n. %d can\'t be rejected because its status is: %s', 'yith-woocommerce-request-a-quote' ), $order_id, $current_status );
			}
			else {
				if ( !isset( $_GET['raq_confirm'] ) && !isset( $_GET['confirm'] ) && $status == 'rejected' && ywraq_verify_token( $_GET['raq_nonce'], 'reject-request-quote', $order_id, $user_email ) ) {
					$args = array(
						'status'        => $status,
						'raq_nonce'     => $_GET['raq_nonce'],
						'request_quote' => $order_id,
						'raq_confirm'   => 'no'
					);

					wp_safe_redirect( add_query_arg( $args, YITH_Request_Quote()->get_raq_page_url() ) );
					exit;
				}
				else {

					if ( !isset( $_GET['confirm'] ) ) {
						$args = array(
							'status'    => 'rejected',
							'raq_nonce' => $_GET['raq_nonce'],
							'order_id'  => $_GET['request_quote'],
							'confirm'   => 'no'
						);

					}
					else {

						$this->accept_reject_order( 'rejected', $order->id);

						$args['message'] = sprintf( __( 'The quote n. %d has been rejected', 'yith-woocommerce-request-a-quote' ), $order_id );
					}
				}
			}
		}

		$this->args_message = $args;
	}

	/**
	 * Print message in Request a Quote after that function change_order_status is called
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  void
	 */
	public function print_message() {
		wc_get_template( 'request-quote-message.php', $this->args_message, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );
	}

	/**
	 * Manage coupon errors
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $err
	 * @param $err_code
	 * @param $obj
	 *
	 * @return string
	 */
	public function manage_coupon_errors( $err, $err_code, $obj ) {

		$order_id = WC()->session->order_awaiting_payment;

		if ( ! $this->is_quote( $order_id ) ) {
			return $err;
		}

		if ( $err_code == 101 ) {
			$err = apply_filters( 'ywraq_coupon_error', __( 'Sorry, you have changed content of your cart. Discount has now been removed from your order', 'yith-woocommerce-request-a-quote' ), $err_code, $obj );
		}

		return $err;
	}

	/**
	 * Manage coupon message
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $msg
	 * @param $msg_code
	 * @param $obj
	 *
	 * @return string
	 */
	public function manage_coupon_message( $msg, $msg_code, $obj ) {
		$order_id = WC()->session->order_awaiting_payment;

		if ( ! $this->is_quote( $order_id ) ) {
			return $msg;
		}

		$msg = '';
		if ( $msg_code == 200 ) {
			$msg = __( 'Discount applied successfully.', 'yith-woocommerce-request-a-quote' );
		}
		elseif ( $msg_code == 201 ) {
			$msg = __( 'Discount removed successfully.', 'yith-woocommerce-request-a-quote' );
		}

		return $msg;
	}

	/**
	 * Check if an order is created from a request quote
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function is_quote( $order_id ) {
		if ( get_post_meta( $order_id, 'ywraq_raq', true ) == 'yes' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if an order is created from a request quote
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function is_expired( $order_id ) {
		$order          = wc_get_order( $order_id );

		if( ! $order ) {
			return false;
		}

		$current_status = $order->get_status();
		$ex_opt         = get_post_meta( $order_id, '_ywcm_request_expire', true );

		if ( $current_status == 'ywraq-expired' || $ex_opt == '' ) {
			return true;
		}

		//check if expired
		$expired_data = strtotime( $ex_opt ) + ( 24 * 60 * 60 ) - 1;
		if ( $expired_data < time() ) {
			$order->update_status( 'ywraq-expired' );

			return true;
		}

		return false;
	}

	/**
	 * Function called if yit-contact-form in used to send the request
	 * grab the post array ad save it in $yit_contact_form_post
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  bool
	 */
	public function yit_contact_form_before_sending_email() {
		if ( isset( $_POST['yit_contact'] ) ) {
			$this->yit_contact_form_post = $_POST['yit_contact'];
		}
	}

	/**
	 * Function called if yit-contact-form in used to send the request
	 * after the email is sent to administrator
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  bool
	 */
	public function yit_contact_form_after_sent_email() {

		if ( !empty( $this->yit_contact_form_post ) && isset( $this->yit_contact_form_post['name'] ) && isset( $this->yit_contact_form_post['email'] ) ) {

			$args = array(
				'user_name'    => $this->yit_contact_form_post['name'],
				'user_email'   => $this->yit_contact_form_post['email'],
				'user_message' => isset( $this->yit_contact_form_post['message'] ) ? $this->yit_contact_form_post['message'] : '',
				'raq_content'  => YITH_Request_Quote()->get_raq_return()
			);

			$new_order = $this->create_order( $args );

			if ( apply_filters( 'ywraq_clear_list_after_send_quote', true ) ) {
				YITH_Request_Quote()->clear_raq_list();
			}

			yith_ywraq_add_notice( ywraq_get_message_after_request_quote_sending( $new_order ), 'success' );

			wp_redirect( YITH_Request_Quote()->get_redirect_page_url(), 301 );
			exit;
		}
		else {
			yith_ywraq_add_notice( __( 'An error has occurred. Please, contact site administrator.', 'yith-woocommerce-request-a-quote' ), 'error' );
		}

	}

	/**
	 * Return the quote detail page
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 *
	 * @param      $order_id
	 * @param bool $admin
	 *
	 * @return string
	 */
	public function get_view_order_url( $order_id , $admin = false ) {
		if( $admin ){
			$view_order_url =  admin_url( 'post.php?post=' . $order_id . '&action=edit' );
		}
		else {
			$view_quote = get_option( 'woocommerce_myaccount_view_quote_endpoint', 'view-quote' );
			$view_order_url = wc_get_endpoint_url( $view_quote, $order_id, wc_get_page_permalink( 'myaccount' ) );
		}

		return apply_filters( 'ywraq_get_quote_order_url', $view_order_url, $order_id );
	}

	/**
	 * Set the shipping method as choosen
	 *
	 * @return void
	 * @since 1.4.4
	 */
	public function set_shipping_methods() {

		if ( isset( $_POST['shipping_method'] ) ) {
			return;
		}

		$shipping_items = $this->get_shipping_items();

		if( !empty( $shipping_items ) ){

			foreach ( $shipping_items as $shipping_item ) {
				$chosen_shipping_methods[] = $shipping_item['method_id'];
			}

			WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
		}
	}

	/**
	 * Change cost to shipping
	 * @param $rates
	 *
	 * @return mixed
	 * @since 1.4.4
	 */
	public function set_package_rates( $rates ) {

		$order_id = $this->get_current_order_id();

		if ( ! $order_id   ) {
			return $rates;
		}

		$shipping_items = $this->get_shipping_items();
		$override_shipping = get_post_meta($order_id, '_ywraq_disable_shipping_method', true);

		$new_rates = array();

		if( $override_shipping == 'yes' ) {
			$new_rates = array();
			$cost      = 0;
			$label  = '';
			$method_id = '';
			$taxes_value = array();
			foreach ( $shipping_items as $shipping_item ) {
				$method_id = $shipping_item['method_id'];
				$cost += $shipping_item['cost'];
				$comma = empty( $label ) ? '' : ', ';
				$label .= $comma . $shipping_item['name'];

				if( isset( $shipping_item['taxes']  ) ){
					$tt = maybe_unserialize( $shipping_item['taxes'] );
					if( $tt ){

						foreach ( $tt as $key => $t ) {

							if( isset( $taxes_value[ $key ] ) ){
								$taxes_value[ $key ] = $taxes_value[ $key ] + $t;
							}else{
								$taxes_value[ $key ] = $t;
							}
						}
					}
				}
			}
			foreach ( $rates as $key => $rate ) {
				if ( $rate->id == $method_id ||  $rate->method_id == $method_id) {
					$new_rates[ $key ]        = $rates[ $key ];
					$new_rates[ $key ]->cost  = $cost;
					$new_rates[ $key ]->label = $label;
					if( $taxes_value ){
						$new_rates[ $key ]->taxes = $taxes_value;
					}

					break;
				}
			}
		}else{
			$new_rates = $rates;
			foreach ( $rates as $key => $rate ) {
				foreach ( $shipping_items as $shipping_item ) {
					$method_id = $shipping_item['method_id'];
					if ( $rate->method_id == $method_id ) {
						if ( isset( $new_rates[ $key ] ) ) {
							if ( get_option( 'ywraq_sum_multiple_shipping_costs' ) == 'yes' ) {
								$new_rates[ $key ]->cost += $shipping_item['cost'];
								$new_rates[ $key ]->label .= ',' . $shipping_item['name'];
							} else {
								$new_rates[ $key ]->cost  = $shipping_item['cost']; //$new_rates[ $key ]->cost += $shipping_item['cost']; (original code)
								$new_rates[ $key ]->label = $shipping_item['name']; //$new_rates[ $key ]->label .= ',' . $shipping_item['name']; (original code)
							}
							$new_rates[ $key ]->taxes = maybe_unserialize( $shipping_item['taxes'] ) + $new_rates[ $key ]->taxes;
						} else {
							$new_rates[ $key ]        = $rates[ $key ];
							$new_rates[ $key ]->cost  = $shipping_item['cost'];
							$new_rates[ $key ]->label = $shipping_item['name'];
							$new_rates[ $key ]->taxes = maybe_unserialize( $shipping_item['taxes'] );
						}
					}
				}
			}
		}

		return $new_rates;

	}

	/**
	 * Return the shipping items of the order in awaiting payment
	 * @return array
	 * @since 1.4.4
	 */
	public function get_shipping_items() {

		$order_id = $this->get_current_order_id();

		if ( ! $order_id   ) {
			return array();
		}

		$order = wc_get_order( $order_id );
		$shipping_items = $order->get_items( 'shipping' );

		return $shipping_items;
	}

	/**
	 * Callable in frontend return the order-quote that is in the cart
	 *
	 * @return bool|mixed
	 */
	public function get_current_order_id(){

		$order_id = WC()->session->order_awaiting_payment;

		if ( ! $this->is_quote( $order_id )   ) {
			return false;
		}

		return $order_id;

	}

	/**
	 * Add a variable to localize script to lock the shipping fields in the checkout page
	 * @param $localize_args
	 *
	 * @return mixed
	 * @since 1.6.3
	 */
	function lock_billing( $localize_args ) {
		$localize_args['lock_billing'] = true;
		return $localize_args;
	}

	/**
	 * Add a variable to localize script to lock the shipping fields in the checkout page
	 * @param $localize_args
	 *
	 * @return mixed
	 * @since 1.6.3
	 */
	function lock_shipping( $localize_args ) {
		$localize_args['lock_shipping'] = true;
		return $localize_args;
	}

	/**
	 * Check if the quote is in the cart
	 * @since 1.6.3
	 *
	 * @return void
	 */
	function check_quote_in_cart(){
		if ( is_admin() ) {
			return;
		}
		$order = $this->get_current_order_id();
		if ( $order ) {
			add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ) , 99);
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'cart_validation' ), 10, 2 );
		} else {
			remove_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 99 );
			remove_filter( 'woocommerce_add_to_cart_validation', array( $this, 'cart_validation' ), 10, 2 );
		}
	}

	/**
	 * Disallow the add to cart when the order-quote is in the cart
	 *
	 * @since 1.6.3
	 * @param $result
	 * @param $product_id
	 *
	 * @return bool
	 */
	public function cart_validation( $result, $product_id ) {
		$order_id = WC()->session->order_awaiting_payment;
		if ( ! $order_id && !$this->is_quote( $order_id ) ) {
			return $result;
		}else{
			return false;
		}
	}

	/**
	 * If the product is without price, for catalog sites
	 *
	 * @since 1.6.3
	 * @return bool
	 */
	function is_purchasable(){
		return true;
	}
}


/**
 * Unique access to instance of YITH_YWRAQ_Order_Request class
 *
 * @return \YITH_YWRAQ_Order_Request
 */
function YITH_YWRAQ_Order_Request() {
	return YITH_YWRAQ_Order_Request::get_instance();
}

YITH_YWRAQ_Order_Request();