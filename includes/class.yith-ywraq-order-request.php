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
     * Array with Quote List data
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
	private $status_changed = false;

	/**
	 * @var bool
	 */
	private $quote_sent = false;

	/**
	 * @var bool
	 */
	private $billing_man = false;
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

	    //Order Customization *******************************
        add_action( 'init', array( $this, 'register_order_status' ) );
        add_filter( 'wc_order_statuses', array( $this, 'add_custom_status_to_order_statuses' ) );
        add_filter( 'wc_order_is_editable', array( $this, 'order_is_editable' ), 10, 2 );
        //add custom metabox
	    add_action( 'admin_init', array( $this, 'add_metabox' ), 10 );
	    add_action( 'woocommerce_before_order_object_save', array( $this, 'save_quote' ) );
	    add_action( 'wp_insert_post', array( $this, 'raq_order_action' ), 100, 2 );

	    if ( get_option( 'ywraq_enable_order_creation', 'yes' ) == 'yes' ) {
		    add_action( 'ywraq_process', array( $this, 'create_order' ), 10, 1 );
	    }

	    //Backend Quotes *******************************
	    add_filter( 'views_edit-shop_order', array( $this, 'show_add_new_quote' ) );
	    add_action( 'add_meta_boxes_shop_order', array($this, 'set_new_quote') );
	    add_action( 'woocommerce_before_order_object_save', array( $this, 'save_new_quote' ) );

	    //Ajax Action *******************************
        add_action( 'wc_ajax_yith_ywraq_order_action', array( $this, 'ajax' ) );

	    //My Account *******************************
	    add_action( 'init', array( $this, 'add_endpoint' ) );
	    add_action( 'template_redirect', array( $this, 'load_view_quote_page' ) );
	    add_action( 'ywraq_view_quote', array( $this, 'check_permission_view_quote_page' ) );
	    //my account list quotes
	    add_filter( 'woocommerce_my_account_my_orders_query', array( $this, 'my_account_my_orders_query' ) );
	    add_action( 'woocommerce_before_my_account', array( $this, 'my_account_my_quotes' ) );

	    //User Action *******************************
	    add_filter( 'nonce_user_logged_out', array( $this, 'wpnonce_filter' ), 10, 2 );
	    add_action( 'wp_loaded', array( $this, 'change_order_status' ) );
	    add_action( 'ywraq_raq_message', array( $this, 'print_message' ), 10 );

        //Quote Accepted *******************************
	    add_filter( 'woocommerce_add_cart_item', array( $this, 'set_new_product_price' ), 20 );
	    add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'set_new_product_price' ), 20  );
        add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_cart_fee' ) );
        add_action( 'woocommerce_checkout_order_processed', array( $this, 'checkout_save_billing_shipping' ), 10, 3 );

        //coupons
	    add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'label_coupon' ), 10, 2 );
	    add_filter( 'woocommerce_coupon_message', array( $this, 'manage_coupon_message' ), 10, 3 );
        //shipping
        add_action( 'woocommerce_review_order_before_shipping', array( $this, 'set_shipping_methods' ) );
        add_filter( 'woocommerce_package_rates', array( $this, 'set_package_rates' ) );
	    //add the cart_hash as post meta of order to process the same order
	    add_filter( 'woocommerce_create_order', array( $this, 'set_cart_hash' ), 1 );
	    //if the customer cancel the order empty the cart
	    add_action('woocommerce_cancelled_order', array( $this, 'empty_cart') );
	    //override the checkout fields if this option is enabled
	    add_action( 'template_redirect', array( $this, 'checkout_fields_manage' ) );
	    //check if a customer is paying a quote
	    add_action( 'wp_loaded', array( $this, 'check_quote_in_cart' ) );
	    //remove meta of quote after order processed
	    add_action( 'woocommerce_checkout_order_processed', array( $this, 'raq_processed' ), 10, 2 );

        //Contact Form 7 *******************************
        add_action( 'wpcf7_before_send_mail', array( $this, 'create_order_before_mail_cf7' ) );

        //Gravity Form *******************************
	    if( get_option( 'ywraq_inquiry_form_type', 'default' ) == 'gravity-forms' && get_option( 'ywraq_activate_thank_you_page' ) == 'yes' ){
			    $gravity_form = get_option('ywraq_inquiry_gravity_forms_id');
			    add_action( 'gform_after_submission_'.$gravity_form, array( $this, 'redirect_after_submission_mail_gravityform'), 10, 2);
	    }

	    //YITH Contact Form *******************************
	    if ( get_option( 'ywraq_inquiry_form_type' ) == 'yit-contact-form' ) {
		    add_action( 'init', array( $this, 'yit_contact_form_before_sending_email' ), 9 );
		    add_action( 'yit-sendmail-success', array( $this, 'yit_contact_form_after_sent_email' ) );
	    }

     }

     function redirect_after_submission_mail_gravityform(){
		wp_safe_redirect( YITH_Request_Quote()->get_redirect_page_url() );
     }
	/**
	 * Save billing and shipping in the order
	 * @param $order_id
	 * @param $data
	 * @param $order
	 */
	public function checkout_save_billing_shipping( $order_id, $data, $order ) {
		$order = wc_get_order( $order_id );
		foreach ( $data as $key => $value ) {
			if ( is_callable( array( $order, "set_{$key}" ) ) ) {
				$order->{"set_{$key}"}( $value );

				// Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
			} elseif ( 0 === stripos( $key, 'billing_' ) || 0 === stripos( $key, 'shipping_' ) ) {
				$order->update_meta_data( '_' . $key, $value );
			}
		}

		$order->save();
	}

	/**
	 * Manage the checkout fields when a quote is accepted. Override the shipping or billing info in the checkout page.
	 * Called by the hook 'template_redirect'
	 *
	 * @return void
	 * @since 1.6.3
	 */
	public function checkout_fields_manage( ) {

		if ( ! is_checkout() ) {
			return;
		}

		$order_id = $this->get_current_order_id();

		if ( ! $order_id ) {
			return;
		}

		$order                                     = wc_get_order( $order_id );
		$checkout_info                             = yit_get_prop( $order, '_ywraq_checkout_info', true );
		$this->order_payment_info['order_id']      = $order_id;
		$this->order_payment_info['checkout_info'] = $checkout_info;

		if ( $this->order_payment_info['checkout_info'] == 'both' || $this->order_payment_info['checkout_info'] == 'billing' ) {
			foreach ( WC()->countries->get_address_fields( '', 'billing_' ) as $key => $value ) {
				$this->order_payment_info[ $key ] = get_post_meta( $order_id, '_' . $key, true );
				add_filter( 'woocommerce_customer_get_' . $key, array( $this, 'checkout_fields_override' ) );
			}

			if ( apply_filters( 'ywraq_lock_editing_billing', true ) && yit_get_prop( $order, '_ywraq_lock_editing', true ) == 'yes' ) {
				add_filter( 'yith_ywraq_frontend_localize', array( $this, 'lock_billing' ) );
			}
		}

		if ( $this->order_payment_info['checkout_info'] == 'both' || $this->order_payment_info['checkout_info'] == 'shipping' ) {
			foreach ( WC()->countries->get_address_fields( '', 'shipping_' ) as $key => $value ) {
				$this->order_payment_info[ $key ] = get_post_meta( $order_id, '_' . $key, true );
				add_filter( 'woocommerce_customer_get_' . $key, array( $this, 'checkout_fields_override' ) );
			}

			if ( apply_filters( 'ywraq_lock_editing_shipping', true ) && yit_get_prop( $order, '_ywraq_lock_editing', true ) == 'yes' ) {
				add_filter( 'yith_ywraq_frontend_localize', array( $this, 'lock_shipping' ) );
				add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true' );
			}
		}
	}

	/**
	 * Called by WC_Customer filter for change the field in the checkout page
	 * @param $value
	 *
	 * @return mixed
	 * @since 1.7.0
	 */
	public function checkout_fields_override( $value ) {
		$current_filter = current_filter();
		$key            = str_replace( 'woocommerce_customer_get_', '', $current_filter );
		if ( isset( $this->order_payment_info[ $key ] ) ) {
			return $this->order_payment_info[ $key ];
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

			WC()->session->set( 'order_awaiting_payment', 0 );
			WC()->session->set( 'chosen_shipping_methods', array() );
		}
	}


	/**
	 * Save the quote in backend
	 * @param $order
	 */
	public function save_quote( $order ){
		if ( ! isset( $_POST['yit_metaboxes'] ) || ! isset( $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] )  ) {
			return;
		}

		$metaboxes = $_REQUEST['yit_metaboxes'];
		$metaboxes ['_ywraq_lock_editing'] = isset( $_REQUEST['_ywraq_lock_editing']) ? 'yes' : 'no';
		$metaboxes ['_ywraq_disable_shipping_method'] = isset( $_REQUEST['_ywraq_disable_shipping_method']) ? 'yes' : 'no';

		yit_set_prop( $order, $metaboxes );

	}

	/**
	 * Save new quote from backend
	 *
	 * @param $order WC_Order
	 *
	 * @internal param $post_id
	 * @internal param $post
	 * @since 1.7.0
	 */
	public function save_new_quote( $order ) {

		if ( ! isset( $_REQUEST['yit_metaboxes'] ['ywraq_customer_name']) ) {
			return;
		}

		$metaboxes = $_REQUEST['yit_metaboxes'];
		$props     = array( 'ywraq_raq' => 'yes' );

		if ( $ov_field = apply_filters( 'ywraq_override_order_billing_fields', true ) ) {
			$props['_billing_first_name'] = $metaboxes['ywraq_customer_name'];
			if ( is_email( $metaboxes['ywraq_customer_email'] ) ) {
				$props['_billing_email'] = $metaboxes['ywraq_customer_email'];
			}
		}

		yit_set_prop( $order, $props );
	}

	/**
	 * Set the new status of an order to New Quote Request status
	 *
	 * @since 1.7.0
	 */
	public function  set_new_quote(){
		global $post;

		if(  isset( $_REQUEST['new_quote'] ) && $_REQUEST['new_quote'] &&  $_REQUEST['post_type'] == 'shop_order' ) {
			$order = wc_get_order( $post->ID );
			$order->set_status( 'ywraq-new' );
			wp_cache_set( 'order-' . $order->get_id(), $order, 'orders' );
		}

	}

	/**
	 * Add post meta _cart_hash to the order in waiting payment
	 *
	 * @since 1.3.0
	 *
	 * @param $value string
	 *
	 * @return string
	 */
	public function set_cart_hash( $value ) {
		$order_id = $this->get_current_order_id();
		$order    = wc_get_order( $order_id );

		if ( $this->is_quote( $order_id ) ) {
			$hash = md5( json_encode( wc_clean( WC()->cart->get_cart_for_session() ) ) . WC()->cart->total );
			yit_save_prop( $order, 'cart_hash', $hash, false, true);
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
                WC()->cart->add_fee( $fee->get_name(), $fee->get_total(), $fee->get_tax_status(), $fee->get_tax_class() );
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
	    $data = false;

        if ( isset( $this->_data[$property] ) ) {
	        $data = $this->_data[$property];
        }

        return $data;
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
		if ( ! is_user_logged_in() ) {
			wc_get_template( 'myaccount/form-login.php' );
		} else {
			$view_quote = get_option( 'woocommerce_myaccount_view_quote_endpoint', 'view-quote' );
			$order_id   = $wp->query_vars[ $view_quote ];
			wc_get_template( 'myaccount/view-quote.php',
				array(
					'order_id'     => $order_id,
					'current_user' => get_user_by( 'id', get_current_user_id() )
				), YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );
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
     * @param $order WC_Order
     *
     * @return bool
     * @since  1.0
     * @author Emanuela Castorina
     */
    public function order_is_editable( $editable, $order ) {

	    $accepted_statuses = apply_filters( 'ywraq_quote_accepted_statuses_edit', array( 'ywraq-new', 'ywraq-accepted', 'ywraq-pending', 'ywraq-expired', 'ywraq-rejected' ) );

	    if ( in_array( $order->get_status(), $accepted_statuses ) ) {
            return true;
        }
        return $editable;
    }

    /**
     * Create order from Request a quote list
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

	    // Ensure shipping methods are loaded early
	    WC()->shipping();

		if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}

        $order = wc_create_order( $args = array(
            'status'      => 'ywraq-new',
            'customer_id' => apply_filters( 'ywraq_customer_id', $customer_id )
        ) );

		$order_id = $order->get_id();

		yit_save_prop( $order, '_current_user', $customer_id, false, true);

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

	    if ( get_option( 'ywraq_allow_raq_out_of_stock', 'no' ) == 'yes' ) {
		    add_filter( 'woocommerce_variation_is_in_stock', '__return_true' );
		    add_filter( 'woocommerce_product_is_in_stock', '__return_true' );
		    add_filter( 'woocommerce_product_backorders_allowed', '__return_true' );
	    }

	    add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 99 );

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

	    remove_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 99 );

        $new_cart->calculate_totals();  // fix tax

        foreach ( $new_cart->get_cart() as $cart_item_key => $values ) {

            $args = array();

	        $args['variation'] =  (  ! empty( $values['variation'] ) ) ? $values['variation'] : array();

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

		        $new_cart->calculate_shipping();
		        $packages = WC()->shipping->get_packages();
		        $shipping_method = apply_filters( 'ywraq_filter_shipping_methods', WC()->session->get( 'chosen_shipping_methods' ) );

		        // Store shipping for all packages
		        foreach ( $packages as $package_key => $package ) {
			        if ( isset( $package['rates'][ $shipping_method [ $package_key ] ] ) ) {

				        $item = new WC_Order_Item_Shipping();
				        $item->set_shipping_rate( $package['rates'][ $shipping_method[ $package_key ] ] );
				        $item_id = $item->save();

				        if ( ! $item_id ) {
					        throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'yith-woocommerce-request-a-quote' ), 404 ) );
				        }

				        $order->add_item( $item );
				        // Allows plugins to add order item meta to shipping
				        do_action( 'woocommerce_add_shipping_order_item', $order_id, $item_id, $package_key );
			        } else {
				        throw new Exception( __( 'Sorry, invalid shipping method.', 'yith-woocommerce-request-a-quote' ) );
			        }
		        }
	        }


        }

	    $order->calculate_taxes();
	    $order->calculate_totals();

	    WC()->cart = $cart;
        WC()->cart->set_session();
        WC()->cart->calculate_totals();

        WC()->session->set( 'raq_new_order', $order_id );

        do_action( 'ywraq_after_create_order', $order_id, $_POST, $raq );

        return $order_id;

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

		$attr = array();
		$order_id = yit_get_prop( $order, 'id', true);

		$ov_field = apply_filters( 'ywraq_override_order_billing_fields', true );
		//save the customer message

		$attr['ywraq_customer_message'] = $raq['user_message'];
		$attr['ywraq_customer_email']   = $raq['user_email'];
		$attr['ywraq_customer_name']    = $raq['user_name'];

		if ( $ov_field ) {
			// override the email address
			$attr['_billing_email'] = $raq['user_email'];

			//override first name and last name
			if ( isset( $raq['_billing_first_name'] ) ) {
				$attr['_billing_first_name'] = $raq['_billing_first_name'];
				$attr['_billing_last_name']  = $raq['_billing_last_name'];
			} else {
				$attr['_billing_first_name'] = $raq['user_name'];
			}
		}

		if ( isset( $raq['user_additional_field'] ) ) {
			$attr['ywraq_customer_additional_field'] = $raq['user_additional_field'];
			if ( $meta = get_option( 'ywraq_additional_text_field_meta' ) ) {
				$attr[ $meta ] = $raq['user_additional_field'];
			}
		}

		if ( isset( $raq['other_email_content'] ) ) {
			$attr['ywraq_other_email_content'] = $raq['other_email_content'];
		}

		if ( isset( $raq['user_additional_field_2'] ) ) {
			$attr['ywraq_customer_additional_field_2'] = $raq['user_additional_field_2'];
			if ( $meta = get_option( 'ywraq_additional_text_field_meta_2' ) ) {
				$attr[ $meta ] = $raq['user_additional_field_2'];
			}
		}

		if ( isset( $raq['user_additional_field_3'] ) ) {
			$attr['ywraq_customer_additional_field_3'] = $raq['user_additional_field_3'];
			if ( $meta = get_option( 'ywraq_additional_text_field_meta_3' ) ) {
				$attr[ $meta ] = $raq['user_additional_field_3'];
			}
		}

		if ( isset( $raq['attachment'] ) ) {
			$attr['ywraq_customer_attachment'] = $raq['attachment'];
		}

		if ( isset( $raq['other_email_fields'] ) ) {
			$attr['ywraq_other_email_fields'] = $raq['other_email_fields'];
		}

		if ( isset( $raq['billing-address'] ) ) {
			if ( $ov_field ) {
				if ( isset( $raq['_billing_address_1'] ) ) {
					$attr['_billing_address_1'] = $raq['_billing_address_1'];
					$attr['_billing_address_2'] = $raq['_billing_address_2'];
					$attr['_billing_city']      = $raq['_billing_city'];
					$attr['_billing_state']     = $raq['_billing_state'];
					$attr['_billing_postcode']  = $raq['_billing_postcode'];
					$attr['_billing_country']   = $raq['_billing_country'];
				} else {
					$attr['_billing_address_1'] = $raq['billing-address'];
				}
			}
			$attr['ywraq_billing_address'] = $raq['billing-address'];
		}

		if ( isset( $raq['billing-phone'] ) ) {
			$attr['ywraq_billing_phone'] = $raq['billing-phone'];
			if ( $ov_field ) {
				$attr['_billing_phone'] = $raq['billing-phone'];
			}
		}

		if ( isset( $raq['billing-vat'] ) ) {
			$attr['ywraq_billing_vat'] = $raq['billing-vat'];
		}

		$attr['ywraq_raq_status'] = 'pending';
		$attr['ywraq_raq']        = 'yes';

		if ( isset( $raq['lang'] ) ) {
			$attr['wpml_language'] = $raq['lang'];
		}

		do_action( 'ywraq_add_order_meta', $order_id, $raq );

		yit_save_prop( $order, $attr, false, false, true );

	}

    /**
     * Add to cart the products in the request, add also a coupon with the discount applied
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

        if ( ! $order ) {
            return;
        }

        do_action( 'ywraq_before_order_accepted', $order_id );

        $order->update_status( 'pending' );

        $prop = array( 'ywraq_raq_status' => 'accepted' );

	    add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ) , 99);
        // Copy products from the order to the cart
        foreach ( $order->get_items() as $item ) {

			$product_id   = (int) apply_filters( 'woocommerce_add_to_cart_product_id', $item->get_product_id() );
			$quantity     = $item->get_quantity();
			$variation_id = (int) $item->get_variation_id();
            $variations   = array();

			//todo:check 3.0.8
            foreach ( $item['item_meta'] as $meta_name => $meta_value ) {
                if ( taxonomy_is_product_attribute( $meta_name ) ) {
                    $variations[ $meta_name ] = $meta_value;
                } elseif ( meta_is_product_attribute( $meta_name, $meta_value[0], $product_id ) ) {
                    $variations[ $meta_name ] = $meta_value;
                }
            }




            if ( function_exists( 'YITH_WCTM' ) ) {
                remove_filter( 'woocommerce_add_to_cart_validation', array( YITH_WCTM(), 'avoid_add_to_cart' ), 10 );
            }

            $cart_item_data = apply_filters( 'woocommerce_order_again_cart_item_data', array(), $item, $order );
            $cart_item_data = apply_filters( 'ywraq_order_cart_item_data', $cart_item_data, $item, $order );

	        if ( $quantity ) {
		        if ( get_option( 'woocommerce_prices_include_tax', 'no' ) == 'yes' ) {
			        $cart_item_data['ywraq_price'] = ( $item['line_subtotal'] + $item['line_subtotal_tax'] ) / $quantity;
		        } else {
			        $cart_item_data['ywraq_price'] = ( $item['line_subtotal'] ) / $quantity;
		        }
	        }

            if ( ! apply_filters( 'ywraq_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations, $cart_item_data ) ) {
                continue;
            }

            // Add to cart validation
	        remove_filter( 'woocommerce_add_to_cart_validation', array( $this, 'cart_validation'), 10);

            if ( ! apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations, $cart_item_data ) ) {
                continue;
            }

            WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations, $cart_item_data );

        }

        $fees = $order->get_fees();
        WC()->session->set( 'request_quote_fee', $fees );

	    $tax_display = $order->get_prices_include_tax('edit');
	    $order_discount              = $order->get_total_discount( ! $tax_display  );

	    if ( $order_discount > 0 ) {

		    $coupon = new WC_Coupon( $this->label_coupon . '_' . $order_id );

		    if ( $coupon->is_valid() ) {
			    $coupon->set_amount( $order_discount );
		    } else {
		    	$args = array( 'id'                         => false,
			                   'discount_type'              => 'fixed_cart',
			                   'amount'                     => $order_discount,
			                   'individual_use'             => false,
			                   'usage_limit'                => '1',
			    );

			    $coupon->read_manual_coupon( $this->label_coupon . '_' . $order_id, $args );
		    }

		    $coupon->save();

		    WC()->session->set( 'request_quote_discount', $coupon );
		    WC()->cart->add_discount( $this->label_coupon . '_' . $order_id );
	    }


	    remove_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 99 );

        do_action( 'ywraq_after_order_accepted', $order_id );

        do_action( 'change_status_mail', array( 'order' => $order, 'status' => 'accepted' ) );

	    WC()->cart->calculate_totals();

	    yit_save_prop( $order, $prop, false, false, true );

    }

	/**
	 * Update the price in the cart session
	 * @param $cart_item
	 * @return array
	 * @internal param $cart_item_data
	 */
	public function set_new_product_price( $cart_item ) {
		if ( isset( $cart_item['ywraq_price'] ) ) {
			$cart_item['data']->set_price( $cart_item['ywraq_price'] );
		}

		return $cart_item;
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

	    if ( ( isset( $_GET['new_quote'] ) && $_GET['new_quote'] ) || isset( $_REQUEST['yit_metaboxes'] ) || ( $post && $post->post_type == 'shop_order' && $this->is_quote( $post->ID ) ) ) {
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
	 *
	 * @param $view
	 *
	 * @return string
	 */
	public function  show_add_new_quote( $view ) {

		$link = esc_url(  admin_url( 'post-new.php?post_type=shop_order&new_quote=1' ) );

		printf( __( '<p><a href="%s">Add a new Quote</a></p>', 'yith-woocommerce-request-a-quote'), $link );

		return $view;

	}

    /**
     * Send the quote to the customer or create a psd preview
     *
     * @since   1.0.0
     * @author  Emanuela Castorina
     *
     * @param $post_id
     * @param $post
     */
    public function raq_order_action( $post_id, $post ) {

	    if ( $this->quote_sent || !isset( $_POST['yit_metaboxes'] ) || !isset( $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] ) || empty( $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] ) ) {
		    return;
	    }

	    $this->quote_sent = true;

        $order = wc_get_order( $post_id );
        

        switch( $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] ){
	        case 'send_quote':
		        $order->update_status('ywraq-pending');

		        if( get_option('ywraq_enable_pdf', 'yes') ){
			        do_action( 'create_pdf', $post_id );
		        }

		        do_action( 'send_quote_mail', $post_id );
		        break;
	        case 'create_preview_pdf':
		        do_action( 'create_pdf', $post_id );
		        break;
	        default:
        }
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

    if ( ! isset( $_POST['yit_metaboxes'] ) || ! isset( $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] ) || $_POST['yit_metaboxes']['_ywraq_safe_submit_field'] != 'send_quote' || $this->status_changed ) {
            return;
        }

        $order = wc_get_order( $post_id );

        if( $order  ){
			$order->update_status( 'ywraq-pending' );
			//check if the status has changed
	        $this->status_changed = true;
        }

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

	    if ( ! empty( $_REQUEST['current_user_id'] ) ) {
		    $order = wc_get_order( $order_id );
		    yit_save_prop( $order, '_customer_id', $_REQUEST['current_user_id'] );
	    }

	    wc_setcookie( 'yith_ywraq_order_id', 0, time() - HOUR_IN_SECONDS );
	    yith_ywraq_add_notice( ywraq_get_message_after_request_quote_sending( $order_id ), 'success' );

	    if ( apply_filters( 'ywraq_clear_list_after_send_quote', true ) ) {
		    YITH_Request_Quote()->clear_raq_list();
	    }

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
			'lang',
			'your-email',
			'your-subject',
			'your-message',
			'action',
			'ywraq_order_action',
			'billing-address',
			'billing-phone',
			'billing-vat',
			'g-recaptcha-response',
			'_wpcf7_is_ajax_call'
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
            $accepted_page_id = YITH_Request_Quote()->get_accepted_page();
            $checkout_page_id = get_option( 'woocommerce_checkout_page_id' );
            $cart_page_id = get_option( 'woocommerce_cart_page_id' );

	        $order_id = $_POST['order_id'];
	        $order = wc_get_order( $order_id );

	        if( $order ){

		        if ( $accepted_page_id == $checkout_page_id || $accepted_page_id == $cart_page_id ) {
			        $this->order_accepted( $order_id );
			        $url = $accepted_page_id == $cart_page_id  ? wc_get_cart_url() : wc_get_checkout_url();
			        $result = array(
				        'result'  => 1,
				        'rqa_url' => apply_filters( 'ywraq_accepted_redirect_url', $url , $order_id ) ,
			        );

		        } else {
			        $this->accept_reject_order( 'accepted', $order_id );

			        $result = array(
				        'result'  => 1,
				        'rqa_url' => apply_filters( 'ywraq_accepted_redirect_url', get_permalink( $accepted_page_id ), $order_id ),
			        );
		        }
	        }else{
		        $message = __( 'An error occurred. Please, contact site administrator', 'yith-woocommerce-request-a-quote' );
		        $result  = array(
			        'result'  => 0,
			        'message' => $message,
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
		yit_save_prop( $order, 'ywraq_raq_status', $status, false, true );

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
		$order = wc_get_order( $order_id );
		yit_delete_prop( $order, 'ywraq_raq_status' );
	}

	/**
	 * Change the label of coupon
	 *
	 * @since    1.0.0
	 * @author   Emanuela Castorina
	 *
	 * @param $string
	 * @param $code
	 *
	 * @return string
	 * @internal param $coupon
	 *
	 */
    public function label_coupon( $string, $code ) {

        //change the label if the order is generated from a quote
	    if ( strpos( $this->label_coupon, $code->get_code() ) !== false ) {
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
	    $user_email = yit_get_prop( $order, 'ywraq_customer_email', true );

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
	            $accepted_page_id = YITH_Request_Quote()->get_accepted_page();
                if ( YITH_Request_Quote()->enabled_checkout() ) {
                    $this->order_accepted( $order_id );
                } else {
                    $this->accept_reject_order( 'accepted', $order_id );

                }

	            $redirect = get_permalink( $accepted_page_id );

	            wp_safe_redirect( apply_filters('ywraq_accepted_redirect_url', $redirect, $order_id ) );
	            wp_safe_redirect( $redirect );

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

                        $this->accept_reject_order( 'rejected', $order_id );

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

        if ( ! $this->get_current_order_id() ) {
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

    	$order = wc_get_order( $order_id );

    	return yit_get_prop( $order, 'ywraq_raq', true ) == 'yes';
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
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		$current_status = $order->get_status();
		$ex_opt         = yit_get_prop( $order, '_ywcm_request_expire', true );

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
     * @return  void
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
     * @return  void
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

	    $order_id = $this->get_current_order_id();

	    if ( ! $order_id   ) {
		    return;
	    }

        $shipping_items = $this->get_shipping_items();

        if( ! empty( $shipping_items ) ){

            foreach ( $shipping_items as $shipping_item ) {
                $chosen_shipping_methods[] = $shipping_item->get_method_id();  //$shipping_item['method_id'];
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

		$order             = wc_get_order( $order_id );
		$override_shipping = yit_get_prop( $order, '_ywraq_disable_shipping_method', true );
		$new_rates         = array();
		$shipping_items    = $this->get_shipping_items();

		if( $override_shipping == 'yes' ) {

			$new_rates = array();
			$cost      = 0;
			$label  = '';
			$method_id = '';
			$taxes_value = array();
			if( $shipping_items ){
				foreach ( $shipping_items as $shipping_item ) {
					$method_id = $shipping_item->get_method_id();
					$cost += $shipping_item->get_total();
					$comma = empty( $label ) ? '' : ', ';
					$label .= $comma . $shipping_item->get_name();
					$shipping_item['name'];

					$tt = $shipping_item->get_taxes();
					if ( ! empty( $tt['total'] ) ) {
						foreach ( $tt['total'] as $key => $t ) {
							if ( isset( $taxes_value[ $key ] ) ) {
								$taxes_value[ $key ] = $taxes_value[ $key ] + $t;
							} else {
								$taxes_value[ $key ] = $t;
							}
						}
					}
				}

				foreach ( $rates as $key => $rate ) {
					if ( $rate->id == $method_id || $rate->method_id == $method_id ) {
						$new_rates[ $key ]        = $rates[ $key ];
						$new_rates[ $key ]->cost  = $cost;
						$new_rates[ $key ]->label = $label;
						$taxes_value = array_filter( $taxes_value );
						if ( is_array( $taxes_value ) && ! empty( $taxes_value ) ) {
							$new_rates[ $key ]->taxes = $taxes_value;
						}

						break;
					}
				}
			}

		}else{
			$new_rates = $rates;
			foreach ( $rates as $key => $rate ) {
				if( $shipping_items ){
					foreach ( $shipping_items as $shipping_item ) {
						$method_id = $shipping_item->get_method_id();
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
								$new_rates[ $key ]->taxes  = apply_filters( $new_rates[ $key ]->taxes );

							} else {
								$new_rates[ $key ]        = $rates[ $key ];
								$new_rates[ $key ]->cost  = $shipping_item['cost'];
								$new_rates[ $key ]->label = $shipping_item['name'];
								$new_rates[ $key ]->taxes = maybe_unserialize( $shipping_item['taxes'] );
								$new_rates[ $key ]->taxes  = apply_filters( $new_rates[ $key ]->taxes );

							}
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
	public function get_current_order_id() {

		$order_id = absint( WC()->session->get('order_awaiting_payment') );

		if ( $order_id && ! $this->is_quote( $order_id ) ) {
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
			add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 99 );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'cart_validation' ), 10, 2 );
		} else {
			remove_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 99 );
			remove_filter( 'woocommerce_add_to_cart_validation', array( $this, 'cart_validation' ), 10);
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