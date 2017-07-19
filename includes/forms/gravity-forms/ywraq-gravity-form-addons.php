<?php

GFForms::include_addon_framework();

/**
 * Class YWRAQ_Gravity_Forms_Add_On
 */
class YWRAQ_Gravity_Forms_Add_On extends GFAddOn {

	/**
	 * @var string
	 */
	protected $_version = '1.0.0';
	/**
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.0.6';
	/**
	 * @var string
	 */
	protected $_slug = 'yith-woocommerce-request-a-quote';
	/**
	 * @var string
	 */
	protected $_path = 'ywraq/ywraq.php';
	/**
	 * @var string
	 */
	protected $_full_path = __FILE__;
	/**
	 * @var string
	 */
	protected $_title = 'YITH WooCommerce Request a Quote';
	/**
	 * @var string
	 */
	protected $_short_title = 'YITH WooCommerce Request a Quote';
	/**
	 * @var string
	 */
	protected $_message = '';


	/**
	 * @var
	 */
	protected $lead;

	/**
	 * @var
	 */
	protected $quote;

	/**
	 * @var
	 */
	protected $form;
	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Get an instance of YWRAQ_Gravity_Forms_Add_On
	 *
	 * @return YWRAQ_Gravity_Forms_Add_On
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new YWRAQ_Gravity_Forms_Add_On();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();

		if ( is_admin() ) {
			add_filter( 'gravity_forms_get_contact_forms', array( $this, 'get_forms' ) );
			add_filter( 'gform_custom_merge_tags', array( $this, 'custom_merge_tags' ) );
		}

		add_filter( 'gform_entry_created', array( $this, 'ywraq_gform_notification' ), 9, 2 );
		add_filter( 'ywraq_ajax_create_order_gravity_forms_args', array( $this, 'ywraq_ajax_create_order_args' ), 10, 2 );
		add_filter( 'gform_pre_replace_merge_tags', array( $this, 'pre_replace_merge_tags' ), 10, 7 );
		add_action( 'ywraq_add_order_meta', array( $this, 'register_quote' ) );
		add_action( 'gform_after_email', array( $this, 'reset_list' ) );
	}

	/**
	 * @param $order_id
	 */
	function register_quote( $order_id ) {
		$this->quote = $order_id;
	}

	/**
	 * Add the shortcode {ywraq_quote_table} inside the list of gravity form shortcode
	 *
	 * @param $custom
	 *
	 * @return array
	 */
	public function custom_merge_tags( $custom ) {
		$custom[] = array(
			'tag'   => '{ywraq_quote_table}',
			'label' => esc_html__( 'YITH Quote List', 'yith-woocommerce-request-a-quote' )
		);

		return $custom;
	}

	/**
	 * Replace the table in the email gravity form
	 *
	 * @param $text
	 *
	 * @return mixed
	 */
	public function pre_replace_merge_tags( $text ) {

		$text = str_replace( '{ywraq_quote_table}', $this->_message, $text );

		return $text;
	}

	/**
	 * Filter the arguments after the submit of form
	 *
	 * @param $args
	 * @param $posted
	 *
	 * @return mixed
	 */
	public function ywraq_ajax_create_order_args( $args, $posted ) {

		if ( isset( $posted['gform_submit'] ) ) {
			$form_id = $posted['gform_submit'];

			if ( $form_id == $this->get_selected_form_id() ) {
				$other_email_content  = '';
				$form                 = GFAPI::get_form( $form_id );
				$raq_c                = $form['yith-woocommerce-request-a-quote'];
				$fields_to_exclude    = array();
				$args['user_name']    = '';
				$args['user_email']   = '';
				$args['user_message'] = '';

				//Name
				if ( $raq_c['ywraq_name'] != '' ) {
					$id                  = $raq_c['ywraq_name'];
					$fields_to_exclude[] = $id;
					$ywraq_field         = $this->get_field_by_id( $id, $form );
					if ( $ywraq_field->type == 'name' ) {
						$args['_billing_first_name'] = isset( $this->lead[ $id . '.3' ] ) ? $this->lead[ $id . '.3' ] : '';
						$args['_billing_last_name']  = isset( $this->lead[ $id . '.6' ] ) ? $this->lead[ $id . '.6' ] : '';
					}
					$args['user_name'] = $ywraq_field->get_value_entry_detail( $this->extract_from_lead( $raq_c['ywraq_name'] ) );
				}

				//Email
				if ( $raq_c['ywraq_email'] != '' ) {
					$fields_to_exclude[] = $raq_c['ywraq_email'];
					$ywraq_field         = $this->get_field_by_id( $raq_c['ywraq_email'], $form );
					if ( $ywraq_field->type == 'email' ) {
						$args['user_email'] = $this->lead[ $raq_c['ywraq_email'] ];
					} else {
						$args['user_email'] = $ywraq_field->get_value_entry_detail( $this->extract_from_lead( $raq_c['ywraq_email'] ) );
					}
				}

				//Message

				if ( $raq_c['ywraq_message'] != '' ) {
					$fields_to_exclude[]  = $raq_c['ywraq_message'];
					$ywraq_field          = $this->get_field_by_id( $raq_c['ywraq_message'], $form );
					$args['user_message'] = $ywraq_field->get_value_entry_detail( $this->extract_from_lead( $raq_c['ywraq_message'] ) );
				}

				//Address
				if ( $raq_c['ywraq_billing_address'] != '' ) {
					$id                  = $raq_c['ywraq_billing_address'];
					$fields_to_exclude[] = $id;
					$ywraq_field         = $this->get_field_by_id( $id, $form );
					if ( $ywraq_field->type == 'address' ) {
						$args['_billing_address_1'] = isset( $this->lead[ $id . '.1' ] ) ? $this->lead[ $id . '.1' ] : '';
						$args['_billing_address_2'] = isset( $this->lead[ $id . '.2' ] ) ? $this->lead[ $id . '.2' ] : '';
						$args['_billing_city']      = isset( $this->lead[ $id . '.3' ] ) ? $this->lead[ $id . '.3' ] : '';
						$args['_billing_state']     = isset( $this->lead[ $id . '.4' ] ) ? $this->lead[ $id . '.4' ] : '';
						$args['_billing_postcode']  = isset( $this->lead[ $id . '.5' ] ) ? $this->lead[ $id . '.5' ] : '';
						$args['_billing_country']   = isset( $this->lead[ $id . '.6' ] ) ? $this->lead[ $id . '.6' ] : '';
					}
					$args['billing-address'] = $ywraq_field->get_value_entry_detail( $this->extract_from_lead( $raq_c['ywraq_billing_address'] ) );
				}

				if ( $raq_c['ywraq_billing_phone'] != '' ) {
					$fields_to_exclude[]   = $raq_c['ywraq_billing_phone'];
					$ywraq_field           = $this->get_field_by_id( $raq_c['ywraq_billing_phone'], $form );
					$args['billing-phone'] = $ywraq_field->get_value_entry_detail( $this->extract_from_lead( $raq_c['ywraq_billing_phone'] ) );
				}

				$other_fields = $this->ywraq_get_other_field( $form_id, $posted );

				if ( ! empty( $other_fields ) ) {
					foreach ( $form['fields'] as $index => $field ) {
						if ( ! in_array( $field->id, $fields_to_exclude ) ) {
							$formatted_value = $field->get_value_entry_detail( $this->extract_from_lead( $field->id ) );
							$other_email_content .= sprintf( '<strong>%s</strong>: %s<br>', $field['label'], $formatted_value );
							$other_fields_labelled[ $field->label ] = $formatted_value;
						}
					}

					$args['other_email_content'] = $other_email_content;
					$args['other_email_fields']  = $other_fields_labelled;
				}
			}
		}

		return $args;

	}


	/**
	 * @param $id_field
	 *
	 * @return mixed
	 */
	function extract_from_lead( $id_field ) {

		$lead = $this->lead;

		foreach ( $this->lead as $key => $item ) {
			if ( ! preg_match( "/\A" . $id_field . "\b/i", $key ) && ! preg_match( "/\b" . $id_field . ".\b/i", $key ) ) {
				unset( $lead[ $key ] );
			}
		}

		return count( $lead ) > 1 ? $lead : reset( $lead );
	}

	/**
	 * @param $id
	 * @param $form
	 *
	 * @return mixed
	 */
	function get_field_by_id( $id, $form ) {
		foreach ( $form['fields'] as $item ) {
			if ( $item->id == $id ) {
				return $item;
			}
		}

		return false;
	}

	/**
	 * Add the request a quote table inside the email content
	 *
	 * @param $lead
	 * @param $form
	 *
	 * @return mixed
	 * @internal param $args
	 * @internal param $posted
	 */
	public function ywraq_gform_notification( $lead, $form ) {

		$this->form = $form;
		$this->lead = $lead;

		if ( $form['id'] == $this->get_selected_form_id() && isset( $form['yith-woocommerce-request-a-quote'] ) ) {

			YITH_YWRAQ_Order_Request()->ajax_create_order( false );
			$this->_message = '<div style="max-width:600px">';
			$this->_message .= yith_ywraq_get_email_template( true );
			$this->_message .= '</div>';


		}

		return $lead;
	}

	/**
	 * Filter the fields that should be showed in the quote
	 *
	 * @param $form_id
	 * @param $posted
	 *
	 * @return mixed
	 * @internal param array $exclusion_list
	 *
	 * @internal param $form
	 *
	 * @internal param $args
	 * @internal param $posted
	 */
	public function ywraq_get_other_field( $form_id, $posted ) {

		$selected_form = GFAPI::get_form( $form_id );

		//remove from $posted the fields that are not input fields
		foreach ( $posted as $k => $v ) {
			if ( strpos( $k, 'input_' ) === false ) {
				unset( $posted[ $k ] );
			}
		}

		if ( isset( $selected_form['yith-woocommerce-request-a-quote'] ) ) {
			foreach ( $selected_form['yith-woocommerce-request-a-quote'] as $key => $value ) {
				$key_post = 'input_' . $value;
				if ( isset( $posted[ $key_post ] ) ) {
					unset( $posted[ $key_post ] );
				}
			}
		}

		return $posted;
	}

	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'YITH WooCommerce Request a Quote Settings', 'yith-woocommerce-request-a-quote' ),
				'fields' => array(
					array(
						'name'    => 'ywraq',
						'tooltip' => esc_html__( 'This is the tooltip', 'yith-woocommerce-request-a-quote' ),
						'label'   => esc_html__( 'This is the label', 'yith-woocommerce-request-a-quote' ),
						'type'    => 'text',
						'class'   => 'small',
					)
				)
			)
		);
	}

	/**
	 * Return the list label/value of the fields in the form
	 *
	 * @return array
	 */
	public function get_fields_of_current_form() {

		$current_form = $this->get_current_form();
		$fields       = array();

		if ( isset( $current_form['fields'] ) ) {
			$fields[] = array(
				'label' => esc_html__( 'Choose the field', 'yith-woocommerce-request-a-quote' ),
				'value' => '',
			);
			foreach ( $current_form['fields'] as $index => $field ) {
				$fields[] = array(
					'label' => esc_html__( $field['label'], 'yith-woocommerce-request-a-quote' ),
					'value' => $field['id'],
				);
			}
		}

		return $fields;

	}

	/**
	 * Configures the settings which should be rendered on the Form Settings
	 *
	 * @param $form
	 *
	 * @return array
	 */

	public function form_settings_fields( $form ) {

		$fields = $this->get_fields_of_current_form();

		$settings_fields = array(
			array(
				'title'  => esc_html__( 'YITH WooCommerce Request a Quote Settings', 'yith-woocommerce-request-a-quote' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Name of user *', 'yith-woocommerce-request-a-quote' ),
						'type'    => 'select',
						'name'    => 'ywraq_name',
						'tooltip' => esc_html__( 'Choose what field should be used for the name', 'yith-woocommerce-request-a-quote' ),
						'choices' => $fields,
					),
					array(
						'label'   => esc_html__( 'Email of user *', 'yith-woocommerce-request-a-quote' ),
						'type'    => 'select',
						'name'    => 'ywraq_email',
						'tooltip' => esc_html__( 'Choose what field should be used for the email', 'yith-woocommerce-request-a-quote' ),
						'choices' => $fields,
					),

					array(
						'label'   => esc_html__( 'Message', 'yith-woocommerce-request-a-quote' ),
						'type'    => 'select',
						'name'    => 'ywraq_message',
						'tooltip' => esc_html__( 'Choose what field should be used for the email', 'yith-woocommerce-request-a-quote' ),
						'choices' => $fields,
					),

					array(
						'label'   => esc_html__( 'Phone', 'yith-woocommerce-request-a-quote' ),
						'type'    => 'select',
						'name'    => 'ywraq_billing_phone',
						'tooltip' => esc_html__( 'Choose what field should be used for the Phone', 'yith-woocommerce-request-a-quote' ),
						'choices' => $fields,
					),

					array(
						'label'   => esc_html__( 'Address', 'yith-woocommerce-request-a-quote' ),
						'type'    => 'select',
						'name'    => 'ywraq_billing_address',
						'tooltip' => esc_html__( 'Choose what field should be used for the Address', 'yith-woocommerce-request-a-quote' ),
						'choices' => $fields,
					),
				),
			),
		);

		return apply_filters( 'ywraq_gravity_forms_addon_setting_fields', $settings_fields );
	}


	/**
	 * Return an array for show the list of forms in the RAQ Form Setting Page
	 *
	 * @return array
	 */
	public function get_forms() {
		if ( ! ywraq_gravity_form_installed() ) {
			return array( '' => __( 'Plugin Gravity Forms not activated or not installed', 'yith-woocommerce-request-a-quote' ) );
		}

		$posts = GFAPI::get_forms();
		$array = array();

		foreach ( $posts as $key => $post ) {
			if ( ! $post['is_trash'] ) {
				$array[ $post['id'] ] = $post['title'];
			}
		}

		if ( empty( $array ) ) {
			return array( '' => __( 'No contact form found', 'yith-woocommerce-request-a-quote' ) );
		}

		return $array;
	}

	/**
	 * Get the selected form id in the Form Settings
	 *
	 * @return integer
	 */
	public function get_selected_form_id() {

		if ( function_exists( 'icl_get_languages' ) && class_exists( 'YITH_YWRAQ_Multilingual_Email' ) ) {
			global $sitepress;
			$current_language = $sitepress->get_current_language();
			$gravity_form_id  = get_option( 'ywraq_inquiry_gravity_forms_id_' . $current_language );
		} else {
			$gravity_form_id = get_option( 'ywraq_inquiry_gravity_forms_id' );
		}

		return apply_filters( 'ywraq_inquiry_gravity_form_id', $gravity_form_id );
	}

	/**
	 * Clear the list of request a quote
	 *
	 */
	public function reset_list() {

		//$order_id = isset( $_COOKIE['yith_ywraq_order_id'] ) ? $_COOKIE['yith_ywraq_order_id'] : 0;

		if ( apply_filters( 'ywraq_clear_list_after_send_quote', true ) ) {
			YITH_Request_Quote()->clear_raq_list();
		}
		//wc_setcookie( 'yith_ywraq_order_id', 0, time() - HOUR_IN_SECONDS );

		yith_ywraq_add_notice( ywraq_get_message_after_request_quote_sending( $this->quote ), 'success' );
	}

}


/**
 * Unique access to instance of YWRAQ_Gravity_Forms_Add_On class
 *
 * @return \YWRAQ_Gravity_Forms_Add_On
 */
function YWRAQ_Gravity_Forms_Add_On() {
	return YWRAQ_Gravity_Forms_Add_On::get_instance();
}
