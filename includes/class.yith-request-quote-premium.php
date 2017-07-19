<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}

use Dompdf\Dompdf;

/**
 * Implements features of YITH Woocommerce Request A Quote Premium
 *
 * @class   YITH_Request_Quote_Premium
 * @package YITH Woocommerce Request A Quote Premium
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_Request_Quote_Premium' ) ) {

	/**
     * Class YITH_Request_Quote_Premium
     */
    class YITH_Request_Quote_Premium extends YITH_Request_Quote {

	    /**
         * @var bool
         */
        private $locale = false;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_Request_Quote_Premium
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

            parent::__construct();

            $this->includes();

            //register widget
            add_action( 'widgets_init', array( $this, 'register_widgets' ) );

            //show button in shop page
            add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_button_shop' ), 15 );


           if( ! catalog_mode_plugin_enabled() && ! is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
              add_filter( 'woocommerce_get_price_html', array( $this, 'show_product_price' ), 0 );
              add_filter( 'woocommerce_get_variation_price_html', array( $this, 'show_product_price' ), 0 );
           }

            add_filter( 'yith_ywraq_hide_price_template', array( $this, 'show_product_price' ), 0, 2 );

	        add_action('init', array($this, 'add_gravity_form_addon'), 1);
            //register metabox to the product editor
            add_action('admin_init', array($this, 'add_metabox'), 1);
            add_action( 'ywraq_exclusions', 'YITH_YWRAQ_Exclusions_Table::output' );

            add_filter('wpcf7_special_mail_tags','yith_ywraq_email_custom_tags', 10, 3);
            add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_emails' ) );

            //check user type
            add_filter( 'yith_ywraq_before_print_button', array( $this, 'must_be_showed' ) );
            add_filter( 'yith_ywraq_before_print_widget', array( $this, 'raq_page_check_user' ) );
            add_filter( 'yith_ywraq_before_print_my_account_my_quotes', array( $this, 'raq_page_check_user' ) );
            add_filter( 'yith_ywraq_before_print_raq_page', array( $this, 'raq_page_check_user' ) );
            add_filter( 'yith_ywraq_raq_page_deniend_access', array( $this, 'raq_page_denied_access' ) );

            if ( get_option('ywraq_enable_pdf', 'yes') ) {
                add_action( 'create_pdf', array( $this, 'generate_pdf' ), 99 );
                add_action( 'yith_ywraq_quote_template_header', array( $this, 'pdf_header' ), 10, 1 );
                add_action( 'yith_ywraq_quote_template_footer', array( $this, 'pdf_footer' ), 10, 1 );
                add_action( 'yith_ywraq_quote_template_content', array( $this, 'pdf_content' ), 10, 1 );
            }

            add_filter( 'plugin_locale', array( $this, 'set_locale_for_pdf' ), 10, 2 );

        }

	    public function add_gravity_form_addon(  ) {
		    if( ywraq_gravity_form_installed() ){
			    YWRAQ_Gravity_Forms_Add_On();
		    }
	    }


        /**
         * Add the quote button in other pages is the product is simple
         *
         * @since   1.0.0
         * @author  Emanuela Castorina
         * @return  boolean|void
         */
	    public function add_button_shop() {

		    $show_button = apply_filters( 'yith_ywraq-btn_other_pages', get_option( 'ywraq_show_btn_other_pages' ) );

		    global $product;

		    $type_in_loop = apply_filters( 'yith_ywraq_show_button_in_loop_product_type', array('simple', 'subscription', 'external' ) );

		    if ( $show_button != 'yes' || ! $product->is_type( $type_in_loop ) ) {
			    return false;
		    }

		    if ( ! function_exists( 'YITH_YWRAQ_Frontend' ) ) {
			    require_once( YITH_YWRAQ_INC . 'class.yith-request-quote-frontend.php' );
			    YITH_YWRAQ_Frontend();
		    }

		    YITH_YWRAQ_Frontend()->print_button( $product );
	    }

        /**
         * Check for which users will not see the price
         *
         * @since   1.0.0
         * @author  Emanuela Castorina
         *
         * @param      $price
         * @param bool $product_id
         *
         * @return string
         */
        public function show_product_price( $price, $product_id = false ) {

            $hide_price = get_option( 'ywraq_hide_price' ) == 'yes';

            if ( catalog_mode_plugin_enabled() ) {
                global $YITH_WC_Catalog_Mode;
                $hide_price = $YITH_WC_Catalog_Mode->check_product_price_single( true, $product_id );
                if ( get_option( 'ywctm_exclude_price_alternative_text' ) != '' ) {
                    $hide_price = ! $hide_price;
                    $price = '';
                }
            }elseif ( $hide_price && current_filter() == 'woocommerce_get_price_html' ) {
		            $price = '';
			}elseif ( $hide_price && !catalog_mode_plugin_enabled() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && current_filter() != 'woocommerce_get_price_html'  ) {
                ob_start();

                $args = array(
                    '.single_variation_wrap .single_variation'
                );

                $classes = implode( ', ', apply_filters( 'ywcraq_catalog_price_classes', $args ) );

                ?>
                <style>
                    <?php echo $classes; ?>
                    {
                        display: none !important
                    }

                </style>
                <?php
                echo ob_get_clean();
            }


            return ( $hide_price ) ? '' : $price;

        }





        /**
         * Add metabox in the product editor
         *
         * @since   1.0.0
         * @author  Emanuela Castorina
         * @return  void
         */
        public function add_metabox() {
            global $pagenow;
            $post = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : ( isset( $_REQUEST['post_ID'] ) ? $_REQUEST['post_ID'] : 0 );
            $post = get_post( $post );

            if ( ( $post && $post->post_type == 'product' ) || ( $pagenow == 'post-new.php' && isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'product' ) ) {
                $args = require_once( YITH_YWRAQ_DIR . 'plugin-options/metabox/ywraq-metabox.php' );
                if ( ! function_exists( 'YIT_Metabox' ) ) {
                    require_once( YITH_YWRAQ_DIR . 'plugin-fw/yit-plugin.php' );
                }
                $metabox = YIT_Metabox( 'yith-ywraq-metabox' );
                $metabox->init( $args );
            }

        }


        /**
         * Files inclusion
         *
         * @since   1.0.0
         * @author  Emanuela Castorina
         * @return  void
         */
        private function includes() {

            if ( is_admin() ) {
                include_once( YITH_YWRAQ_INC.'class.yith-ywraq-custom-table.php' );
                include_once( YITH_YWRAQ_DIR.'templates/admin/exclusions-table.php' );

            }

        }

        /**
         * Register the widgets
         *
         * @since   1.0.0
         * @author  Emanuela Castorina
         * @return  void
         */
        public function register_widgets(){
            register_widget( 'YITH_YWRAQ_List_Quote_Widget' );
            register_widget( 'YITH_YWRAQ_Mini_List_Quote_Widget' );
        }


        /**
         * Refresh the quote list in the widget when a product is added or removed from the list
         *
         * @since   1.0.0
         * @author  Emanuela Castorina
         * @return  void
         */
        public function ajax_refresh_quote_list(){
            $raq_content  = YITH_Request_Quote()->get_raq_return();
            $args         = array(
                'raq_content'      => $raq_content,
                'template_part'    => 'view',
                'title'            => isset( $_POST['title'] ) ? $_POST['title'] : '',
                'item_plural_name' => isset( $_POST['item_plural_name'] ) ? $_POST['item_plural_name'] : '',
                'item_name'        => isset( $_POST['item_name'] ) ? $_POST['item_name'] : '',
                'show_thumbnail'   => isset( $_POST['show_thumbnail'] ) ? $_POST['show_thumbnail'] : 1,
                'show_price'       => isset( $_POST['show_price'] ) ? $_POST['show_price'] : 1,
                'show_quantity'    => isset( $_POST['show_quantity'] ) ? $_POST['show_quantity'] : 1,
                'show_variations'  => isset( $_POST['show_variations'] ) ? $_POST['show_variations'] : 1,
                'widget_type'      => isset( $_POST['widget_type'] ) ? $_POST['widget_type'] : '',
            );
            $args['args'] = $args;

            wp_send_json(
                array(
                    'large' => wc_get_template_html( 'widgets/quote-list.php', $args, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR ),
                    'mini'  => wc_get_template_html( 'widgets/mini-quote-list.php', $args, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR ),
                )
            );

            die();
        }


        /**
         * Loads the inquiry form
         *
         * @param $args
         *
         * @since 1.0
         */
        public function get_inquiry_form($args) {

            $shortcode = '';

            switch ( get_option( 'ywraq_inquiry_form_type' , 'default' ) ) {
                case 'yit-contact-form':
                    $shortcode = '[contact_form name="' . get_option( 'ywraq_inquiry_yit_contact_form_id' ) . '"]';
                    break;
                case 'contact-form-7':
                    if( function_exists('icl_get_languages') && class_exists('YITH_YWRAQ_Multilingual_Email')  ) {
                        global $sitepress;
                        $current_language = $sitepress->get_current_language();
                        $cform7_id = get_option( 'ywraq_inquiry_contact_form_7_id_'.$current_language );

                    }else{
                        $cform7_id = get_option( 'ywraq_inquiry_contact_form_7_id' );
                    }

                    $cform7_id = apply_filters( 'ywraq_inquiry_contact_form_7_id',  $cform7_id );
                    
                    $shortcode = '[contact-form-7 id="' .$cform7_id . '"]';
                    break;
                case 'gravity-forms':
	                if ( ywraq_gravity_form_installed() ) {
		                $gravity_form_id = YWRAQ_Gravity_Forms_Add_On()->get_selected_form_id();
		                $shortcode       = '[gravityform id="' . $gravity_form_id . '" title="true" description="true" ajax="true"]';
	                }
	                break;
                case 'default':
                    wc_get_template( 'request-quote-form.php', $args, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );
                    break;
            }

            echo do_shortcode( $shortcode );

        }

        /**
         * Filters woocommerce available mails, to add wishlist related ones
         *
         * @param $emails array
         *
         * @return array
         * @since 1.0
         */
        public function add_woocommerce_emails( $emails ) {
            $emails['YITH_YWRAQ_Send_Email_Request_Quote'] = include( YITH_YWRAQ_INC . 'emails/class.yith-ywraq-send-email-request-quote.php' );
            $emails['YITH_YWRAQ_Quote_Status'] = include( YITH_YWRAQ_INC . 'emails/class.yith-ywraq-quote-status.php' );
            $emails['YITH_YWRAQ_Send_Quote'] = include( YITH_YWRAQ_INC . 'emails/class.yith-ywraq-send-quote.php' );
            return $emails;
        }

        /**
         * Loads WC Mailer when needed
         *
         * @return void
         * @since 1.0
         */
        public function load_wc_mailer() {
            add_action( 'send_raq_mail', array( 'WC_Emails', 'send_transactional_email' ), 10 );
            add_action( 'send_quote_mail', array( 'WC_Emails', 'send_transactional_email' ), 10 );
            add_action( 'change_status_mail', array( 'WC_Emails', 'send_transactional_email' ), 10 );
        }

        /**
         * Build wishlist page URL.
         *
         * @param string $action
         *
         * @return string
         * @since 1.0.0
         */
	    public function get_raq_url( $action = 'view' ) {
		    $base_url    = '';
		    $raq_page_id = get_option( 'ywraq_page_id' );

		    if ( get_option( 'permalink_structure' ) ) {
			    $raq_page          = get_post( $raq_page_id );
			    $raq_page_slug     = $raq_page->post_name;
			    $raq_page_relative = '/' . $raq_page_slug . '/' . $action . '/';

			    $base_url = trailingslashit( home_url( $raq_page_relative ) );
		    }

		    return $base_url;

	    }


        /**
         * Check if the raq button can be showed
         *
         * @return bool
         * @since 1.0.0
         */
        public function must_be_showed(){

	        global $product;

	        if( ! $product ){
	            global  $post;
		        if (  ! $post || ! is_object( $post ) || ! is_singular() ) {
			        return false;
		        }
		        $product = wc_get_product( $post->ID);
            }

            if( ! is_object( $product ) || ! $this->check_user_type() || ( get_option('ywraq_allow_raq_out_of_stock', 'no') == 'no' && $product && !$product->is_in_stock()) || ( get_option('ywraq_show_btn_only_out_of_stock') == 'yes' && $product &&  $product->is_in_stock() ) ){
                return false;
            }

            return true;
        }

	    /**
	     * Check user
	     *
	     * @return bool
	     * @since 1.0.0
	     */

	    public function check_user() {

		    global $product;

		    if ( ! $product ) {
			    global $post;
			    if ( ! $post || ! is_object( $post ) || ! is_singular() ) {
				    return false;
			    }
			    $product = wc_get_product( $post->ID );
		    }

		    if ( ! is_object( $product ) || ! $this->check_user_type() || ( get_option( 'ywraq_allow_raq_out_of_stock', 'no' ) == 'no' && $product && ! $product->is_in_stock() ) || ( get_option( 'ywraq_show_btn_only_out_of_stock' ) == 'yes' && $product && $product->is_in_stock() ) ) {
			    return false;
		    }

		    return true;
	    }

        /**
         * Check if the raq button can be showed
         *
         * @return bool
         * @since 1.0.0
         */
	    public function raq_page_check_user() {

		    if ( ! $this->check_user_type() ) {
			    return false;
		    }

		    return true;
	    }

        /**
         * Check if the current user is available to send requests
         *
         * @return bool
         * @since 1.0.0
         */
        public function check_user_type(){
            $user_type = get_option( 'ywraq_user_type' );
            $return    = false;

            if ( is_user_logged_in() && ( $user_type == 'customers' || $user_type == 'all' ) ) {
                $rules = (array) get_option( 'ywraq_user_role' );

                if ( empty( $rules ) || ! is_array( $rules ) ) {
                    return false;
                }

                if ( in_array( 'all', $rules ) ) {
                    return true;
                }

                $current_user = wp_get_current_user();
                $intersect    = array_intersect( $current_user->roles, $rules );
                if ( ! empty( $intersect ) ) {
                    return true;
                }
            } else {
                if ( ( ! is_user_logged_in() && $user_type == 'guests' ) || $user_type == 'all' ) {
                    return true;
                }
            }

            return $return;
        }


	    /**
         * @param $message string
         *
         * @return string
         */
	    public function raq_page_denied_access( $message ) {
		    $user_type = get_option( 'ywraq_user_type' );

		    if ( $user_type == 'customers' ) {
			    return __( 'You must be logged in to access this page', 'yith-woocommerce-request-a-quote' );
		    }

		    return $message;
	    }



        /**
         * Generate the template
         *
         * @param $order_id
         *
         * @return int
         */
        public function generate_pdf( $order_id ) {

            ob_start();

            wc_get_template( 'pdf/quote.php', array( 'order_id'=> $order_id ) );

            $html = ob_get_contents();
            ob_end_clean();

	        require_once( YITH_YWRAQ_DOMPDF_DIR . 'autoload.inc.php' );

            $dompdf = new DOMPDF();

            $dompdf->load_html( $html );

            $dompdf->render();

            // The next call will store the entire PDF as a string in $pdf
            $pdf = $dompdf->output();

            $file_path = $this->get_pdf_file_path($order_id, true);

            if( ! file_exists( $file_path ) ){
                $file_path = $this->get_pdf_file_path($order_id, false);
            }else{
                unlink($file_path);
            }

            return file_put_contents( $file_path, $pdf );

        }


	    /**
         * @param $order_id
         *
         * @return string
         */
	    public function get_pdf_file_url( $order_id ) {
		    $path = $this->create_storing_folder( $order_id );

		    return YITH_YWRAQ_SAVE_QUOTE_URL . $path . $this->get_pdf_file_name( $order_id );
	    }

        /**
         * Return the file of pdf
         * @param $order_id
         *
         * @return string
         */
        public function get_pdf_file_name( $order_id ) {
            return apply_filters( 'ywraq_pdf_file_name', 'quote_'.$order_id.'.pdf', $order_id );
        }

	    /**
         * @param      $order_id
         * @param bool $delete_file
         *
         * @return string
         */
        public function get_pdf_file_path( $order_id, $delete_file = false) {
            $path = $this->create_storing_folder($order_id);
            $file = YITH_YWRAQ_DOCUMENT_SAVE_DIR. $path . $this->get_pdf_file_name( $order_id );

            //delete the document if exists
            if( file_exists( $file ) && $delete_file ){
                @unlink( $file );
            }

            return $file;
        }

	    /**
         * @param $order_id
         *
         * @return mixed|string
         */
        public static function create_storing_folder( $order_id ) {

            $order = wc_get_order($order_id);
           /* Create folders for storing documents */
            $folder_pattern = '[year]/[month]/';

	        $order_date = is_callable( array( $order, 'get_date_created' ) ) ? $order->get_date_created() : $order->order_date;

	        $date = getdate( strtotime( $order_date ) );

            $folder_pattern = str_replace(
                array(
                    '[year]',
                    '[month]'
                ),
                array(
                    $date['year'],
                    sprintf( "%02d", $date['mon'] )
                ),
                $folder_pattern );

            if ( ! file_exists( YITH_YWRAQ_DOCUMENT_SAVE_DIR . $folder_pattern ) ) {
                wp_mkdir_p( YITH_YWRAQ_DOCUMENT_SAVE_DIR . $folder_pattern );
            }

            return $folder_pattern;
        }


	    /**
         * @param $order_id
         */
        public function pdf_content( $order_id ){
            $order = wc_get_order($order_id);
            wc_get_template( 'pdf/quote-table.php', array( 'order'=>$order ) );
        }

	    /**
         * @param $order_id
         */
        public function pdf_header( $order_id ){
            $order = wc_get_order($order_id);
            wc_get_template( 'pdf/quote-header.php', array( 'order'=>$order ) );
        }

	    /**
         * @param $order_id
         */
        public function pdf_footer( $order_id ){
            $footer_content  = get_option( 'ywraq_pdf_footer_content' );
            $show_pagination = get_option( 'ywraq_pdf_pagination' );
            wc_get_template( 'pdf/quote-footer.php', array( 'footer' => $footer_content, 'pagination' => $show_pagination, 'order_id' => $order_id ) );
        }

	    /**
         * @param $lang
         */
        public function change_pdf_language( $lang ) {
            global $sitepress, $woocommerce;
            if( is_object($sitepress) ){
                $sitepress->switch_lang( $lang, true );
                $this->locale = $sitepress->get_locale( $lang );
                unload_textdomain( 'yith-woocommerce-request-a-quote' );
                unload_textdomain( 'woocommerce' );
                unload_textdomain( 'default' );

                load_plugin_textdomain( 'yith-woocommerce-request-a-quote', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                $woocommerce->load_plugin_textdomain();
                load_default_textdomain();
            }
        }

        // set correct locale code for emails
	    /**
         * @param $locale
         * @param $domain
         *
         * @return bool
         */
        function set_locale_for_pdf(  $locale, $domain ){

            if( $domain == 'woocommerce' && $this->locale ){
                $locale = $this->locale;
            }

            return $locale;
        }
    }





}

/**
 * Unique access to instance of YITH_Request_Quote_Premium class
 *
 * @return \YITH_Request_Quote_Premium
 */
function YITH_Request_Quote_Premium() {
    return YITH_Request_Quote_Premium::get_instance();
}

