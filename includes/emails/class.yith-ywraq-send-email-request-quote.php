<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of YITH Woocommerce Request A Quote
 *
 * @class   YITH_YWRAQ_Send_Email_Request_Quote
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_YWRAQ_Send_Email_Request_Quote' ) ) {

    /**
     * YITH_YWRAQ_Send_Email_Request_Quote
     *
     * @since 1.0.0
     */
    class YITH_YWRAQ_Send_Email_Request_Quote extends WC_Email {

        /**
         * Constructor method, used to return object of the class to WC
         *
         * @return \YITH_YWRAQ_Send_Email_Request_Quote
         * @since 1.0.0
         */
        public function __construct() {
            $this->id          = 'ywraq_email';
            $this->title       = __( 'Email to request a quote', 'yith-woocommerce-request-a-quote' );
            $this->description = __( 'This email is sent when a user clicks on "Request a quote" button', 'yith-woocommerce-request-a-quote' );

            $this->heading = __( 'Request a quote', 'yith-woocommerce-request-a-quote' );
            $this->subject = __( '[Request a quote]', 'yith-woocommerce-request-a-quote' );

            $this->template_html  = 'emails/request-quote.php';
            $this->template_plain = 'emails/plain/request-quote.php';

            // Triggers for this email
            add_action( 'send_raq_mail_notification', array( $this, 'trigger' ), 15, 1 );

            // Call parent constructor
            parent::__construct();

            if( $this->enabled == 'no'){
                return;
            }
            
            // Other settings
            $this->recipient = $this->get_option( 'recipient' );

            if ( !$this->recipient ) {
                $this->recipient = get_option( 'admin_email' );
            }

            $this->enable_cc = $this->get_option( 'enable_cc' );

            $this->enable_cc = $this->enable_cc == 'yes';

        }

        /**
         * Method triggered to send email
         *
         * @param int $args
         *
         * @return void
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function trigger( $args ) {

            $new_order = WC()->session->raq_new_order;
            $this->raq                = $args;
            $this->raq['order_id']    = is_null( $new_order ) ? 0 : $new_order;
            $this->raq['raq_content'] = YITH_Request_Quote()->get_raq_return();

            $this->find['quote-number']      = '{quote_number}';
            $this->replace['quote-number'] = apply_filters( 'ywraq_quote_number', $this->raq['order_id'] );

            $recipients = (array) $this->get_recipient();
            $recipients = implode( ',', $recipients );
            
            if( class_exists('YWRAQ_Multivendor') ){
                $return = apply_filters( 'ywraq_multivendor_email', false, $args, $this);
            }else{
                $return = $this->send( $recipients, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments( $args['attachment'] ) );
            }

            if ( $return ) {
                if( apply_filters('ywraq_clear_list_after_send_quote', true ) ){
                    YITH_Request_Quote()->clear_raq_list();
                }

                yith_ywraq_add_notice( ywraq_get_message_after_request_quote_sending( $new_order ), 'success' );
                
            }
            else {
                yith_ywraq_add_notice( __( 'There was a problem sending your request. Please try again.', 'yith-woocommerce-request-a-quote' ), 'error' );
            }
        }

        /**
         * get_headers function.
         *
         * @access public
         * @return string
         */
          public function get_headers() {
            $headers = "Reply-to: " . $this->raq['user_email'] . "\r\n";

            if ( $this->enable_cc ) {
                $headers .= "Cc: " . $this->raq['user_email'] . "\r\n";
            }

            $headers .= "Content-Type: " . $this->get_content_type() . "\r\n";

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
			    'raq_data'          => $this->raq,
			    'email_heading'     => $this->get_heading(),
			    'email_description' => $this->get_option( 'email-description' ),
			    'sent_to_admin'     => true,
			    'plain_text'        => false,
			    'email'             => $this
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
			    'raq_data'          => $this->raq,
			    'email_heading'     => $this->get_heading(),
			    'email_description' => $this->get_option( 'email-description' ),
			    'sent_to_admin'     => true,
			    'plain_text'        => false,
			    'email'             => $this
		    ) );

		    return ob_get_clean();
	    }


	    /**
	     * @param string $file
	     *
	     * @return array
	     */
	    public function get_attachments( $file = ''){
            $attachments = array();
            if( !empty($file) && file_exists( $file['file'] ) ){
                $attachments[] = $file['file'];
            }
		    return apply_filters( 'woocommerce_email_attachments', $attachments, $this->id, $this->object );
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
                    'description' => sprintf( __( 'This field lets you edit email subject line. Leave it blank to use default subject text: <code>%s</code>. You can use {quote_number} as a placeholder that will show the quote number in the quote.', 'yith-woocommerce-request-a-quote' ), $this->subject ),
                    'placeholder' => '',
                    'default'     => ''
                ),
                'recipient'  => array(
                    'title'       => __( 'Recipient(s)', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'Enter recipients (separated by commas) for this email. Defaults to <code>%s</code>', 'yith-woocommerce-request-a-quote' ), esc_attr( get_option( 'admin_email' ) ) ),
                    'placeholder' => '',
                    'default'     => ''
                ),
                'enable_cc'  => array(
                    'title'       => __( 'Send CC copy', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'checkbox',
                    'description' => __( 'Send a carbon copy to the user', 'yith-woocommerce-request-a-quote' ),
                    'default'     => 'no'
                ),
                'heading'    => array(
                    'title'       => __( 'Email Heading', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'This field lets you change the main heading in email notification. Leave it blank to use default heading type: <code>%s</code>.', 'yith-woocommerce-request-a-quote' ), $this->heading ),
                    'placeholder' => '',
                    'default'     => ''
                ),

                'email-description'    => array(
                    'title'       => __( 'Email Description', 'yith-woocommerce-request-a-quote' ),
                    'type'        => 'textarea',
                    'placeholder' => '',
                    'default'     =>  __( 'You have received a request for a quote. The request is the following:', 'yith-woocommerce-request-a-quote')
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
return new YITH_YWRAQ_Send_Email_Request_Quote();