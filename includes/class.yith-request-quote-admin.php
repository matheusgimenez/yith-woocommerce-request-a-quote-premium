<?php
if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements features of FREE version of YITH Woocommerce Request A Quote
 *
 * @class   YITH_YWRAQ_Admin
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */
if ( ! class_exists( 'YITH_YWRAQ_Admin' ) ) {

	/**
	 * Class YITH_YWRAQ_Admin
	 */
	class YITH_YWRAQ_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_YWRAQ_Admin
		 */

		protected static $instance;

		/**
		 * @var $_panel YIT_Plugin_Panel_WooCommerce
		 */
		protected $_panel;

		/**
		 * @var $_premium string Premium tab template file name
		 */
		protected $_premium = 'premium.php';

		/**
		 * @var string Premium version landing link
		 */
		protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/';

		/**
		 * @var string Panel page
		 */
		protected $_panel_page = 'yith_woocommerce_request_a_quote';

		/**
		 * @var string List of messages
		 */
		protected $messages = array();

		/**
		 * @var string Doc Url
		 */
		public $doc_url = 'https://yithemes.com/docs-plugins/yith-woocommerce-request-a-quote/';


		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_YWRAQ_Admin
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
		 * @since  1.0
		 * @author Emanuela Castorina
		 */
		public function __construct() {

			// register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			$this->create_menu_items();

			//Add action links
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWRAQ_DIR . '/' . basename( YITH_YWRAQ_FILE ) ), array(
				$this,
				'action_links'
			) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

			add_action( 'init', array( $this, 'add_page' ) );
			add_action( 'admin_notices', array( $this, 'check_coupon' ) );


			add_filter( 'yit_get_contact_forms', 'yith_ywraq_get_contact_forms' );
			add_filter( 'wpcf7_get_contact_forms', 'yith_ywraq_wpcf7_get_contact_forms' );
			add_filter( 'wpcf7_collect_mail_tags', array( $this, 'add_tags_to_contact_form7' ) );



			//custom styles and javascripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

		}

		/**
		 * Enqueue styles and scripts
		 *
		 * @access public
		 * @return void
		 * @since  1.0.0
		 */
		public function enqueue_styles_scripts() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'yith_ywraq_admin', YITH_YWRAQ_ASSETS_URL . '/js/yith-ywraq-admin' . $suffix . '.js', array( 'jquery' ), YITH_YWRAQ_VERSION, true );

			if ( defined( 'YITH_YWRAQ_PREMIUM' ) ) {
				wp_register_style( 'yith_ywraq_backend', YITH_YWRAQ_ASSETS_URL . '/css/backend.css' );
			}

			//load the script in selected pages
			global $pagenow;
			$post = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : ( isset( $_REQUEST['post_ID'] ) ? $_REQUEST['post_ID'] : 0 );
			$post = get_post( $post );

			if ( ( $pagenow=='admin.php' && isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'yith_woocommerce_request_a_quote' ) || ( $post && $post->post_type == 'shop_order' ) || ( $pagenow == 'post-new.php' && isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'shop_order' ) ) {
				wp_enqueue_script( 'yith_ywraq_admin' );
				wp_enqueue_style( 'yith_ywraq_backend');
			}



		}

		/**
		 * Create Menu Items
		 *
		 * Print admin menu items
		 *
		 * @since  1.0
		 * @author Emanuela Castorina
		 */
		private function create_menu_items() {

			// Add a panel under YITH Plugins tab
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'yith_woocommerce_request_a_quote', array( $this, 'premium_tab' ) );
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'settings' => __( 'Settings', 'yith-woocommerce-request-a-quote' )
			);

			if ( defined( 'YITH_YWRAQ_FREE_INIT' ) ) {
				$admin_tabs['premium'] = __( 'Premium Version', 'yith-woocommerce-request-a-quote' );
			} else {
				$admin_tabs['ywraq-layout'] = __( 'Layout', 'yith-woocommerce-request-a-quote' );
				$admin_tabs['form']         = __( 'Form Settings', 'yith-woocommerce-request-a-quote' ); //@since 1.4.5
				$admin_tabs['quote']        = __( 'Quote Settings', 'yith-woocommerce-request-a-quote' ); //@since 1.4.5
				$admin_tabs['pdf']          = __( 'PDF Quote', 'yith-woocommerce-request-a-quote' );
				$admin_tabs['exclusions']   = __( 'Exclusion List', 'yith-woocommerce-request-a-quote' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => __( 'Request a Quote', 'yith-woocommerce-request-a-quote' ),
				'menu_title'       => __( 'Request a Quote', 'yith-woocommerce-request-a-quote' ),
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_YWRAQ_DIR . '/plugin-options'
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once( YITH_YWRAQ_DIR . '/plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

			add_action( 'woocommerce_admin_field_ywraq_upload', array( $this->_panel, 'yit_upload' ), 10, 1 );

		}

		/**
		 * Add a page "Request a Quote".
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_page() {
			global $wpdb;

			$option_value = get_option( 'ywraq_page_id' );

			if ( $option_value > 0 && get_post( $option_value ) ) {
				return;
			}

			$page_found = $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'request-quote' LIMIT 1;" );
			if ( $page_found ) :
				if ( ! $option_value ) {
					update_option( 'ywraq_page_id', $page_found );
				}

				return;
			endif;

			$page_data = array(
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => esc_sql( _x( 'request-quote', 'page_slug', 'yit' ) ),
				'post_title'     => __( 'Request a Quote', 'yit' ),
				'post_content'   => '[yith_ywraq_request_quote]',
				'post_parent'    => 0,
				'comment_status' => 'closed'
			);
			$page_id   = wp_insert_post( $page_data );

			update_option( 'ywraq_page_id', $page_id );
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_YWRAQ_TEMPLATE_PATH . '/admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once( $premium_tab_template );
			}
		}

		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $links | links plugin array
		 *
		 * @return   mixed Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return mixed
		 * @use      plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {

			$links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-request-a-quote' ) . '</a>';
			if ( defined( 'YITH_YWRAQ_FREE_INIT' ) ) {
				$links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith-woocommerce-request-a-quote' ) . '</a>';
			}

			return $links;
		}

		/**
		 * plugin_row_meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $plugin_meta
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $status
		 *
		 * @return   array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

			if ( defined( 'YITH_YWRAQ_INIT' ) && YITH_YWRAQ_INIT == $plugin_file ) {
				$plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-request-a-quote' ) . '</a>';
			}

			return $plugin_meta;
		}

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return  string The premium landing link
		 */
		public function get_premium_landing_uri() {
			return defined( 'YITH_REFER_ID' ) ? $this->get_premium_landing_uri() . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
		}

		/**
		 * Display Admin Notice if coupons are enabled
		 *
		 * @access public
		 * @return void
		 *
		 * @since  1.3.0
		 */
		public function check_coupon() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( get_option( 'woocommerce_enable_coupons' ) != 'yes' ) { ?>
				<div id="message" class="error ywraq_different_url">
					<p>
						<strong><?php _e( 'YITH WooCommerce Request a Quote', 'yith-woocommerce-request-a-quote' ); ?></strong>
					</p>

					<p>
						<?php _e( 'WooCommerce coupon system has been disabled. In order to make YITH WooCommerce Request a Quote work correctly, you have to enable coupons.', 'yith-woocommerce-request-a-quote' ); ?>
					</p>

					<p>
						<a href="<?php echo admin_url( "admin.php?page=wc-settings&tab=checkout" ) ?>"><?php echo __( 'Enable the use of coupons', 'yith-woocommerce-request-a-quote' ) ?></a>
					</p>
				</div>

				<?php
			}
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */

		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_YWRAQ_DIR . 'plugin-fw/licence/lib/yit-licence.php';
				require_once YITH_YWRAQ_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YITH_YWRAQ_INIT, YITH_YWRAQ_SECRET_KEY, YITH_YWRAQ_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */

		public function register_plugin_for_updates() {
			if( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_YWRAQ_DIR.'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YITH_YWRAQ_SLUG, YITH_YWRAQ_INIT );
		}


		/**
		 * Add the tags [yith-request-a-quote-list] to the contact form 7 legend
		 *
		 * @params $tags
		 * @param $tags
		 *
		 * @return array
		 * @since 1.4.9
		 */
		public function add_tags_to_contact_form7( $tags ) {
			$tags[] = 'yith-request-a-quote-list';

			return $tags;
		}

	}

}

/**
 * Unique access to instance of YITH_YWRAQ_Admin class
 *
 * @return \YITH_YWRAQ_Admin
 */
function YITH_YWRAQ_Admin() {
	return YITH_YWRAQ_Admin::get_instance();
}

YITH_YWRAQ_Admin();