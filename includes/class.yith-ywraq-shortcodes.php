<?php
if ( !defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * YITH_YWRAQ_Shortcodes add shortcodes to the request quote list
 *
 * @class 	YITH_YWRAQ_Shortcodes
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */
class YITH_YWRAQ_Shortcodes {


    /**
     * Constructor for the shortcode class
     *
     */
    public function __construct() {

        add_shortcode( 'yith_ywraq_request_quote', array( $this, 'request_quote_page' ) );
        add_shortcode( 'yith_ywraq_myaccount_quote_list', array( $this, 'my_account_raq_shortcode' ) );
        add_shortcode( 'yith_ywraq_single_view_quote', array( $this, 'single_view_quote' ) );
        
        add_shortcode( 'yith_ywraq_myaccount_quote', array( $this, 'raq_shortcode_account' ) );
        add_shortcode( 'yith_ywraq_widget_quote', array( $this, 'widget_quote' ) );
        add_shortcode( 'yith_ywraq_button_quote', array( $this, 'button_quote' ) );

	    add_shortcode('yith_ywraq_number_items', array( $this, 'ywraq_number_items' ));

    }

	/**
     * @return string
     */
    public function raq_shortcode_account() {
        
        global $wp;

        $view_quote = get_option( 'woocommerce_myaccount_view_quote_endpoint', 'view-quote' );
        
        if( empty( $wp->query_vars[$view_quote] ) ) {
            return WC_Shortcodes::shortcode_wrapper( array( YITH_YWRAQ_Order_Request(), 'view_quote_list' ) );
        }
        else {
            return WC_Shortcodes::shortcode_wrapper( array( YITH_YWRAQ_Order_Request(), 'view_quote' ) );
        }
    }

	/**
     * @param      $atts
     * @param null $content
     *
     * @return string
     */
    public function request_quote_page( $atts, $content = null ) {

        $raq_content  = YITH_Request_Quote()->get_raq_return();

        $args = shortcode_atts( array(
            'raq_content'   => $raq_content,
            'template_part' => 'view',
            'show_form'     => 'yes'
        ), $atts );

        $args['args'] = $args;

        ob_start();

        wc_get_template('request-quote.php', $args, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );

        return ob_get_clean();
    }

	/**
	 * @param      $atts
	 * @param null $content
	 *
	 * @return string
	 */
	public function button_quote( $atts, $content = null ) {

		$args = shortcode_atts( array(
			'product'   => false
		), $atts );

		ob_start();
		YITH_YWRAQ_Frontend()->print_button( $args['product'] );

		return ob_get_clean();
	}
	/**
	 * @param      $atts
	 * @param null $content
	 *
	 * @return string
	 */
	public function ywraq_number_items( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'class'            => 'ywraq_number_items',
			'show_url'         => 'yes',
			'item_name'        => __( 'item', 'yith-woocommerce-request-a-quote' ),
			'item_plural_name' => __( 'items', 'yith-woocommerce-request-a-quote' ),
		), $atts );

		$num_items = YITH_Request_Quote()->get_raq_item_number();
		$raq_url   = esc_url( YITH_Request_Quote()->get_raq_page_url() );
		if ( $atts['show_url'] == 'yes' ) {
			$div = sprintf( '<div class="%s"><a href="%s">%d %s</a></div>', $atts['class'], $raq_url, $num_items, _n( $atts['item_name'], $atts['item_plural_name'], $num_items, 'yith-woocommerce-request-a-quote' ) );
		} else {
			$div = sprintf( '<div class="%s">%d %s</div>', $atts['class'], $num_items, _n( $atts['item_name'], $atts['item_plural_name'], $num_items, 'yith-woocommerce-request-a-quote' ) );
		}

		return $div;
	}

    /**
     * Add Quotes section to my-account page
     *
     * @since   1.0.0
     * @return  void
     */
    public function my_account_raq_shortcode(){

        ob_start();
        wc_get_template( 'myaccount/my-quotes.php', null, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );
        return ob_get_clean();
    }


	/**
     * @param      $atts
     * @param null $content
     *
     * @return string
     */
    public function single_view_quote( $atts, $content = null ) {

        $args = shortcode_atts( array(
            'order_id'   => 0,
        ), $atts );


        ob_start();
        wc_get_template( 'myaccount/view-quote.php',
            array( 'order_id'     => $args['order_id'],
                   'current_user' => get_user_by( 'id', get_current_user_id() ) ), YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );
        return ob_get_clean();
    }


    /**
     * @param      $atts
     * @param null $content
     *
     * @return string
     */
    public function widget_quote( $atts, $content = null ) {

        $args = shortcode_atts( array(
            'title'           => __( 'Quote List', 'yith-woocommerce-request-a-quote' ),
            'show_thumbnail'  => 1,
            'show_price'      => 1,
            'show_quantity'   => 1,
            'show_variations' => 1,
        ), $atts );

        $args['args'] = $args;

        ob_start();

        the_widget('YITH_YWRAQ_List_Quote_Widget', $args);

        return ob_get_clean();
    }
}

