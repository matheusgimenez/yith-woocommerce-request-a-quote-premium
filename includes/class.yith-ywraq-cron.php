<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements the YITH_YWRAQ_Cron class
 *
 *
 * @class    YITH_YWRAQ_Cron
 * @package  YITH Woocommerce Request A Quote
 * @since    1.4.9
 * @author   Yithemes
 */
class YITH_YWRAQ_Cron {

    /**
     * Single instance of the class
     *
     * @var \YITH_YWRAQ_Cron
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_YWRAQ_Cron
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

        add_action( 'wp_loaded', array( $this, 'ywraq_set_cron' ) );

        if( get_option( 'ywraq_automate_send_quote' ) == 'yes' ){
            add_filter( 'cron_schedules',  array( $this, 'cron_schedule'), 50 );
            add_action( 'ywraq_automatic_quote', array( $this, 'send_automatic_quote' ) );
        }


        add_action( 'ywraq_clean_cron', array( $this, 'clean_session') );
        add_action( 'ywraq_time_validation', array( $this, 'time_validation' ) );

     }

    

    /**
     *
     */
    public function ywraq_set_cron(){

        if( ! wp_next_scheduled( 'ywraq_time_validation' ) ){
            $ve = get_option( 'gmt_offset' ) > 0 ? '+' : '-';
            wp_schedule_event( strtotime( '00:00 tomorrow ' . $ve . get_option( 'gmt_offset' ) . ' HOURS'), 'daily', 'ywraq_time_validation' );
        }

        if ( !wp_next_scheduled( 'ywraq_clean_cron' ) ) {
            wp_schedule_event( time(), 'daily', 'ywraq_clean_cron' );
        }

        if ( ! wp_next_scheduled( 'ywraq_automatic_quote' ) &&  get_option( 'ywraq_automate_send_quote' ) == 'yes'  ) {
            wp_schedule_event( current_time('timestamp', 1), 'ywrac_gap', 'ywraq_automatic_quote' );
        }
    }

    /**
     * Cron Schedule
     *
     * Add new schedules to wordpress
     *
     *
     * @since  1.0.0
     * @author Emanuela Castorina
     */
    public function cron_schedule( $schedules ){

        $interval = 0;
        $cron_type = get_option( 'ywraq_cron_time_type' );
        $cron_time = get_option( 'ywraq_cron_time' );

        if ( $cron_type == 'hours' ) {
            $interval = 60 * 60 * $cron_time;
        }
        elseif ( $cron_type == 'days' ) {
            $interval = 24 * 60 * 60 * $cron_time;
        }
        elseif ( $cron_type == 'minutes' ) {
            $interval = 60 * $cron_time;
        }

        $schedules['ywrac_gap'] = array(
            'interval' => $interval,
            'display' => __( 'YITH WooCommerce Request a Quote Cron', 'yith-woocommerce-request-a-quote' )
        );

        return $schedules;
    }

    /**
     *Clean the session on database
     */
    public function clean_session(){
        global $wpdb;

        $wpdb->query("DELETE FROM ". $wpdb->prefix ."options  WHERE option_name LIKE '_yith_ywraq_session_%'");

    }

    /**
     * Function called from Cron to swich in
     * ywraq-expired order status the request expired
     *
     * @since   1.4.9
     * @author  Emanuela Castorina
     * @return  void
     */
    public function time_validation() {
    	//todo:replace get_posts with wc_get_orders
        $orders = get_posts( array(
            'numberposts' => - 1,
            'meta_query'  => array(
                array(
                    'key'     => '_ywcm_request_expire',
                    'value'   => '',
                    'compare' => '!='
                )
            ),
            'post_type'   => 'shop_order',
            'post_status' => array( 'wc-ywraq-pending' )
        ) );

        foreach ( $orders as $order ) {
            $expired_data = strtotime( get_post_meta( $order->ID, '_ywcm_request_expire', true ) );
            $expired_data += ( 24 * 60 * 60 ) - 1;
            if ( $expired_data < time() ) {
                wp_update_post( array( 'ID' => $order->ID, 'post_status' => 'wc-ywraq-expired' ) );
            }
        }
    }

    /**
     * Send automatic quote
     *
     * @since   1.4.9
     * @author  Emanuela Castorina
     * @return  void
     */
    public function send_automatic_quote(){

	    $orders = wc_get_orders(
	        array(
		        'numberposts' => - 1,
		        'status' => array( 'wc-ywraq-new' )
	        )
        );

        if( $orders ){
            foreach ( $orders as $order ) {
            	$order_id = yit_get_prop( $order, 'id', true );
                do_action( 'create_pdf', $order_id );
                do_action( 'send_quote_mail', $order_id );
                $order->update_status( 'ywraq-pending' );
            }
        }

    }
}


/**
 * Unique access to instance of YITH_YWRAQ_Cron class
 *
 * @return \YITH_YWRAQ_Cron
 */
function YITH_YWRAQ_Cron() {
    return YITH_YWRAQ_Cron::get_instance();
}

YITH_YWRAQ_Cron();