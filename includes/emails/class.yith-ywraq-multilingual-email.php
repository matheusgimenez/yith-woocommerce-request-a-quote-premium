<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements features of YITH Woocommerce Request A Quote
 *
 * @class   YITH_YWRAQ_Quote_Status
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_YWRAQ_Multilingual_Email' ) ) {

	/**
	 * YITH_YWRAQ_Multilingual_Email
	 *
	 * @since 1.0.0
	 */
	class YITH_YWRAQ_Multilingual_Email extends WCML_Emails {

		/**
		 * YITH_YWRAQ_Multilingual_Email constructor.
		 */
		function __construct(  ) {

			global $woocommerce_wpml, $sitepress;
			// Call parent constructor
			parent::__construct($woocommerce_wpml, $sitepress);

			add_action( 'send_quote_mail_notification', array( $this, 'refresh_email_lang'), 10, 1 );
		}

		/**
		 * @param $order_id
		 */
		function refresh_email_lang( $order_id ){
			global $sitepress;
			if ( is_array( $order_id ) ) {
				if ( isset($order_id['order_id']) ) {
					$order_id = $order_id['order_id'];
				} else {
					return;
				}

			}

			$order = wc_get_order( $order_id );
			$lang = yit_get_prop($order, 'wpml_language', TRUE);
			if ( ! empty( $lang ) ) {
				$sitepress->switch_lang($lang,true);
			}

		}
	}

	// returns instance of the mail on file include
	return new YITH_YWRAQ_Multilingual_Email();
}

