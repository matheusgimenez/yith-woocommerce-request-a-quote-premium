<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of YITH Woocommerce Request A Quote
 *
 * @class   YITH_YWRAQ_Send_Quote
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_YWRAQ_Send_Quote' ) ) {

    /**
     * YITH_YWRAQ_Send_Quote
     *
     * @since 1.0.0
     */
    class YITH_YWRAQ_Send_Quote extends WC_Email {

	    /**
	     * Constructor method, used to return object of the class to WC
	     * @return mixed
	     * @since 1.0.0
	     */
        public function __construct() {
            $this->id          = 'ywraq_send_quote';
            $this->title       = __( 'Email with Quote', 'yith-woocommerce-request-a-quote' );
            $this->description = __( 'This email is sent when an administrator performs the action "Send the quote" from Order Editor', 'yith-woocommerce-request-a-quote' );

            $this->heading = __( 'Our Proposal', 'yith-woocommerce-request-a-quote' );
            $this->subject = __( '[Quote]', 'yith-woocommerce-request-a-quote' );

            $this->template_html  = 'emails/quote.php';
            $this->template_plain = 'emails/plain/quote.php';

            if( $this->enabled == 'no'){
                return;
            }

            // Triggers for this email
            add_action( 'send_quote_mail_notification', array( $this, 'trigger' ), 15, 1 );

            $this->customer_email = true;
            // Call parent constructor
            parent::__construct();

            //$this->recipient = ( isset($this->settings['recipient']) && $this->settings['recipient'] != '' ) ? $this->settings['recipient'] : get_option( 'admin_email' );

            $this->enable_bcc = $this->get_option( 'enable_bcc' );
            $this->enable_bcc = $this->enable_bcc == 'yes';
        }

        /**
         * Method triggered to send email
         *
         * @param $order_id
         *
         * @internal param int $args
         *
         * @since    1.0
         * @author   Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function trigger( $order_id ) {

	        $this->order_id = $order_id;

            if ( $order_id ) {

	            $order      = wc_get_order( $order_id );

	            $order_date = yit_get_prop( $order, '_date_created', true );
	            $exdata     = yit_get_prop( $order, '_ywcm_request_expire', true );
	            $on         = $order->get_order_number();

	            $this->order                   = $order;
	            $this->raq['customer_message'] = yit_get_prop( $order, 'ywraq_customer_message', true );
	            $this->raq['admin_message']    = nl2br( yit_get_prop( $order, '_ywcm_request_response', true ) );
	            $this->raq['user_email']       = yit_get_prop( $order, 'ywraq_customer_email', true );
	            $this->raq['user_name']        = yit_get_prop( $order, 'ywraq_customer_name', true );
	            $this->raq['expiration_data']  = ( $exdata != '' ) ? date_i18n( wc_date_format(), strtotime( $exdata ) ) : '';
	            $this->raq['order-date']       = date_i18n( wc_date_format(), strtotime( $order_date ) );
	            $this->raq['order-id']         = $order_id;
	            $this->raq['order-number']     = ! empty( $on ) ? $on : $order_id;
	            $this->raq['lang']             = yit_get_prop( $order, 'wpml_language', true );

	            $this->recipient               = $this->raq['user_email'];
	            $this->find['quote-number']    = '{quote_number}';
	            $this->replace['quote-number'] = $this->raq['order-number'];

	            $this->send( $this->recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

            }


        }

	    /**
         * @return array
         */
	    public function get_attachments() {
		    $order_id    = $this->order_id;
		    $attachments = array();
		    if ( get_option( 'ywraq_pdf_attachment' ) == 'yes' ) {
			    $attachments[] = YITH_Request_Quote_Premium()->get_pdf_file_path( $order_id );
		    }

		    if ( '' != $optional_upload = yit_get_prop( $this->order, '_ywraq_optional_attachment', true ) ) {
			    $attachment_id = ywraq_get_attachment_id_by_url( $optional_upload );
			    $path          = ( $attachment_id ) ? get_attached_file( $attachment_id ) : null;
			    if ( file_exists( $path ) ) {
				    $attachments[] = $path;
			    }
		    }

		    return apply_filters( 'woocommerce_email_attachments', $attachments, $this->id, $this->object );
	    }

        /**
         * get_headers function.
         *
         * @access public
         * @return string
         */
	    function get_headers() {

		    $cc = ( isset( $this->settings['recipient'] ) && $this->settings['recipient'] != '' ) ? $this->settings['recipient'] : get_option( 'admin_email' );

		    $headers = array();

		    if ( get_option( 'woocommerce_email_from_address' ) != '' ) {
			    $headers[] = "Reply-To: " . $this->get_from_address();
		    }
		    
		    if ( $this->enable_bcc ) {
			    $headers[] = "Bcc: " . $cc . "\r\n";
		    }

		    $headers[] = "Content-Type: " . $this->get_content_type();

		    return apply_filters( 'woocommerce_email_headers', $headers, $this->id, $this->object );
	    }

        /**
         * Get HTML content for the mail
         *
         * @return string HTML content of the mail
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function get_content_html() {
            ob_start();
            wc_get_template( $this->template_html, array(
                'order'             => $this->order,
                'email_heading'     => $this->get_heading(),
                'raq_data'          => $this->raq,
                'email_title'       => $this->get_option( 'email-title' ),
                'email_description' => $this->get_option( 'email-description' ),
                'sent_to_admin'     => true,
                'plain_text'        => false,
                'email'         => $this
            ) );
            return ob_get_clean();
        }

        /**
         * Get plain text content of the mail
         *
         * @return string Plain text content of the mail
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function get_content_plain() {
            ob_start();
            wc_get_template( $this->template_plain, array(
                'order'         => $this->order,
                'email_heading' => $this->get_heading(),
                'raq_data'      => $this->raq,
                'email_title'       => $this->get_option( 'email-title' ),
                'email_description' => $this->get_option( 'email-description' ),
                'sent_to_admin' => true,
                'plain_text'    => true,
                'email'         => $this
            ) );
            return ob_get_clean();
        }

        /**
         * Get from name for email.
         *
         * @return string
         */
        public function get_from_name() {
            $email_from_name = ( isset($this->settings['email_from_name']) && $this->settings['email_from_name'] != '' ) ? $this->settings['email_from_name'] : '';
            return wp_specialchars_decode( esc_html( $email_from_name ), ENT_QUOTES );
        }

        /**
         * Get from email address.
         *
         * @return string
         */
        public function get_from_address() {
            $email_from_email = ( isset($this->settings['email_from_email']) && $this->settings['email_from_email'] != '' ) ? $this->settings['email_from_email'] : '';
            return sanitize_email( $email_from_email );
        }

        /**
         * Init form fields to display in WC admin pages
         *
         * @return void
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'         => __( 'Enable/Disable', 'yith-woocommerce-request-a-quote' ),
                    'type'          => 'checkbox',
                    'label'         => __( 'Enable this email notification', 'yith-woocommerce-request-a-quote' ),
                    'default'       => 'yes'
                ),
                'email_from_name'    => array(
                    'title'       => __( '"From" Name', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'text',
                    'description' => '',
                    'placeholder' => '',
                    'default'     => get_option( 'woocommerce_email_from_name' )
                ),
                'email_from_email'    => array(
                    'title'       => __( '"From" Email Address', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'text',
                    'description' => '',
                    'placeholder' => '',
                    'default'     => get_option( 'woocommerce_email_from_address' )
                ),
                'subject'    => array(
                    'title'       => __( 'Subject', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'This field lets you modify the email subject line. Leave it blank to use the default subject text: <code>%s</code>. You can use {quote_number} as a placeholder that will show the quote number in the quote.', 'yith-woocommerce-request-a-quote' ), $this->subject ),
                    'placeholder' => '',
                    'default'     => ''
                ),
                'recipient'  => array(
                    'title'       => __( 'Bcc Recipient(s)', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'text',
                    'description' => __( 'Enter futher recipients (separated by commas) for this email. By default email to the customer', 'yith-woocommerce-request-a-quote' ),
                    'placeholder' => '',
                    'default'     => ''
                ),
                'enable_bcc'  => array(
                    'title'       => __( 'Send BCC copy', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'checkbox',
                    'description' => __( 'Send a blind carbon copy to the administrator', 'yith-woocommerce-request-a-quote' ),
                    'default'     => 'no'
                ),
                'heading'    => array(
                    'title'       => __( 'Email Heading', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'This field lets you change the main heading in email notification. Leave it blank to use default heading type: <code>%s</code>.', 'yith-woocommerce-request-a-quote' ), $this->heading ),
                    'placeholder' => '',
                    'default'     => ''
                ),
                'email-title'    => array(
                    'title'       => __( 'Email Title', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'text',
                    'placeholder' => '',
                    'default'     =>  __( 'Proposal', 'yith-woocommerce-request-a-quote' )
                ),
                'email-description'    => array(
                    'title'       => __( 'Email Description', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'textarea',
                    'placeholder' => '',
                    'default'     =>  __( 'You have received this email because you sent a quote request to our shop. The response to your request is the following:', 'yith-woocommerce-request-a-quote' )
                ),
                'email_type' => array(
                    'title'       => __( 'Email type', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'select',
                    'description' => __( 'Choose format for the email that has to be sent.', 'yith-woocommerce-request-a-quote' ),
                    'default'     => 'html',
                    'class'       => 'email_type',
                    'options'     => array(
                        'plain'     => __( 'Plain text', 'yith-woocommerce-request-a-quote' ),
                        'html'      => __( 'HTML', 'yith-woocommerce-request-a-quote' ),
                        'multipart' => __( 'Multipart', 'yith-woocommerce-request-a-quote' ),
                    )
                )
            );
        }
    }
}


// returns instance of the mail on file include
return new YITH_YWRAQ_Send_Quote();