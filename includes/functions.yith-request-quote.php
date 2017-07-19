<?php
if ( !defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Implements helper functions for YITH Woocommerce Request A Quote
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */

if ( !function_exists( 'yith_ywraq_locate_template' ) ) {
    /**
     * Locate the templates and return the path of the file found
     *
     * @param string $path
     * @param array  $var
     *
     * @return string
     * @since 1.0.0
     */
    function yith_ywraq_locate_template( $path, $var = NULL ) {

        if ( function_exists( 'WC' ) ) {
            $woocommerce_base = WC()->template_path();
        }
        elseif ( defined( 'WC_TEMPLATE_PATH' ) ) {
            $woocommerce_base = WC_TEMPLATE_PATH;
        }
        else {
            $woocommerce_base = WC()->plugin_path() . '/templates/';
        }

        $template_woocommerce_path = $woocommerce_base . $path;
        $template_path             = '/' . $path;
        $plugin_path               = YITH_YWRAQ_DIR . 'templates/' . $path;

        $located = locate_template( array(
            $template_woocommerce_path, // Search in <theme>/woocommerce/
            $template_path,             // Search in <theme>/
            $plugin_path                // Search in <plugin>/templates/
        ) );

        if ( !$located && file_exists( $plugin_path ) ) {
            return apply_filters( 'yith_ywraq_locate_template', $plugin_path, $path );
        }

        return apply_filters( 'yith_ywraq_locate_template', $located, $path );
    }
}

if ( !function_exists( 'yith_ywraq_get_product_meta' ) ) {
	/**
     * @param      $raq
     * @param bool $echo
     *
     * @return string
     */
    function yith_ywraq_get_product_meta( $raq, $echo = true, $show_price = true ) {
        /**
         * Return the product meta in a varion product
         *
         * @param array $raq
         * @param bool  $echo
         *
         * @return string
         * @since 1.0.0
         */
        $item_data = array();


        // Variation data
        if ( !empty( $raq['variation_id'] ) && is_array( $raq['variations'] ) ) {

            foreach ( $raq['variations'] as $name => $value ) {

                if ( '' === $value ) {
                    continue;
                }

                $taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

                // If this is a term slug, get the term's nice name
                if ( taxonomy_exists( $taxonomy ) ) {
                    $term = get_term_by( 'slug', $value, $taxonomy );
                    if ( !is_wp_error( $term ) && $term && $term->name ) {
                        $value = $term->name;
                    }
                    $label = wc_attribute_label( $taxonomy );

                }else {
                    if( strpos($name, 'attribute_') !== false ) {
                        $custom_att = str_replace( 'attribute_', '', $name );
                        if ( $custom_att != '' ) {
                            $label = wc_attribute_label( $custom_att );
                        }
                        else {
                            $label = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $name ), $name );
                           // $label = $name;
                        }
                    }
                }

                $item_data[] = array(
                    'key'   => $label,
                    'value' => $value
                );


            }
        }

	    $item_data = apply_filters( 'ywraq_item_data', $item_data, $raq, $show_price );

	    $carrets = apply_filters('ywraq_meta_data_carret', "\n" );

	    $out = $echo ? $carrets : "";

	    // Output flat or in list format
	    if ( sizeof( $item_data ) > 0 ) {
		    foreach ( $item_data as $data ) {
			    if ( $echo ) {
				    $out .= esc_html(  $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . $carrets;
			    }
			    else {
				    $out .= ' - ' . esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . ' ';
			    }
		    }
	    }

	    if ( $echo ) {
		    echo $out;
	    }else{
		    return $out;
	    }

	    return '';
    }


}

if ( !function_exists( 'yith_ywraq_get_product_meta_from_order_item' ) ) {
	/**
     * @param      $item_meta
     * @param bool $echo
     *
     * @return string
     */
    function yith_ywraq_get_product_meta_from_order_item( $item_meta, $echo = true ) {
        /**
         * Return the product meta in a varion product
         *
         * @param array $raq
         * @param bool  $echo
         *
         * @return string
         * @since 1.0.0
         */
        $item_data = array();

        // Variation data
        if ( !empty( $item_meta ) ) {

            foreach ( $item_meta as $name => $val ) {

                if ( empty( $val ) ) {
                    continue;
                }

                if ( in_array( $name, apply_filters( 'woocommerce_hidden_order_itemmeta', array(
                    '_qty',
                    '_tax_class',
                    '_product_id',
                    '_variation_id',
                    '_line_subtotal',
                    '_line_subtotal_tax',
                    '_line_total',
                    '_line_tax',
                    '_parent_line_item_id',
                    '_commission_id',
                    '_woocs_order_rate',
                    '_woocs_order_base_currency',
                    '_woocs_order_currency_changed_mannualy'
                ) ) ) ) {
                    continue;
                }

                // Skip serialised meta
                if ( is_serialized( $val[0] ) ) {
                    continue;
                }


                $taxonomy = $name;
              
                // If this is a term slug, get the term's nice name
                if ( taxonomy_exists( $taxonomy ) ) {
                    $term = get_term_by( 'slug', $val[0], $taxonomy );
                    if ( !is_wp_error( $term ) && $term && $term->name ) {
                        $value = $term->name;
                    }else {
                    	$value = $val[0];
                    }
                    $label = wc_attribute_label( $taxonomy );

                } else {
                    $label =  apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $name ), $name );
                    $value = $val[0];
                }

                if( $label!= '' && $val[0] != ''){
                    $item_data[] = array(
                        'key'   => $label,
                        'value' => $value
                    );
                }
            }
        }


        $item_data = apply_filters( 'ywraq_item_data', $item_data );
        $out = "";
        // Output flat or in list format
        if ( sizeof( $item_data ) > 0 ) {
            foreach ( $item_data as $data ) {
                if ( $echo ) {
                    echo esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . "\n";
                }
                else {
                    $out .= ' - ' . esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . ' ';
                }
            }
        }

        return $out;

    }
}

if ( !function_exists( 'yith_ywraq_notice_count' ) ) {
	/****** NOTICES *****/
	/**
	 * Get the count of notices added, either for all notices (default) or for one
	 * particular notice type specified by $notice_type.
	 *
	 * @since 2.1
	 *
	 * @param string $notice_type The name of the notice type - either error, success or notice. [optional]
	 *
	 * @return int
	 */
	function yith_ywraq_notice_count( $notice_type = '' ) {
		$session      = YITH_Request_Quote()->session_class;
		$notice_count = 0;
		$all_notices  = $session->get( 'yith_ywraq_notices', array() );

		if ( isset( $all_notices[ $notice_type ] ) ) {
			$notice_count = absint( sizeof( $all_notices[ $notice_type ] ) );
		} elseif ( empty( $notice_type ) ) {
			$notice_count += absint( sizeof( $all_notices ) );
		}

		return $notice_count;
	}
}

if ( !function_exists( 'yith_ywraq_add_notice' ) ) {
	/**
	 * Add and store a notice
	 *
	 * @since 2.1
	 *
	 * @param string $message The text to display in the notice.
	 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
	 */
	function yith_ywraq_add_notice( $message, $notice_type = 'success' ) {

		$session = YITH_Request_Quote()->session_class;
		$notices = $session->get( 'yith_ywraq_notices', array() );

		// Backward compatibility
		if ( 'success' === $notice_type ) {
			$message = apply_filters( 'yith_ywraq_add_message', $message );
		}

		$notices[ $notice_type ][] = apply_filters( 'yith_ywraq_add_' . $notice_type, $message );

		$session->set( 'yith_ywraq_notices', $notices );

	}
}

if ( !function_exists( 'yith_ywraq_print_notices' ) ) {
	/**
	 * Prints messages and errors which are stored in the session, then clears them.
	 *
	 * @since 2.1
	 */
	function yith_ywraq_print_notices() {

		if ( get_option( 'ywraq_activate_thank_you_page' ) == 'yes' ) {
			return '';
		}

		$session      = YITH_Request_Quote()->session_class;
		$all_notices  = $session->get( 'yith_ywraq_notices', array() );
		$notice_types = apply_filters( 'yith_ywraq_notice_types', array( 'error', 'success', 'notice' ) );

		foreach ( $notice_types as $notice_type ) {
			if ( yith_ywraq_notice_count( $notice_type ) > 0 ) {
				if ( count( $all_notices ) > 0 && $all_notices[ $notice_type ] ) {
					wc_get_template( "notices/{$notice_type}.php", array(
						'messages' => $all_notices[ $notice_type ]
					) );
				}
			}
		}

		yith_ywraq_clear_notices();
	}
}

if ( !function_exists( 'yith_ywraq_clear_notices' ) ) {
	/**
	 * Unset all notices
	 *
	 * @since 2.1
	 */
	function yith_ywraq_clear_notices() {
		$session = YITH_Request_Quote()->session_class;
		$session->set( 'yith_ywraq_notices', null );
	}
}

/****** PREMIUM FUNCTIONS *****/
if ( !function_exists( 'ywraq_get_order_status_tag' ) ) {
	/*
	 * @param $status
	 */
	function ywraq_get_order_status_tag( $status ) {
		switch ( $status ) {
			case 'ywraq-new':
				echo '<span class="raq_status new">' . __( 'new', 'yith-woocommerce-request-a-quote' ) . '</span>';
				break;
			case 'ywraq-pending':
				echo '<span class="raq_status pending">' . __( 'pending', 'yith-woocommerce-request-a-quote' ) . '</span>';
				break;
			case 'ywraq-expired':
				echo '<span class="raq_status expired">' . __( 'expired', 'yith-woocommerce-request-a-quote' ) . '</span>';
				break;
			case 'ywraq-rejected':
				echo '<span class="raq_status rejected">' . __( 'rejected', 'yith-woocommerce-request-a-quote' ) . '</span>';
				break;
			case 'pending':
				echo '<span class="raq_status accepted">' . __( 'accepted', 'yith-woocommerce-request-a-quote' ) . '</span>';
				break;
			default:
				echo '<span class="raq_status accepted">' . __( 'accepted', 'yith-woocommerce-request-a-quote' ) . '</span>';
		}
	}
}

if ( ! function_exists( 'catalog_mode_plugin_enabled' ) ) {
	/**
	 * @return bool
	 */
	function catalog_mode_plugin_enabled() {
		return defined( 'YWCTM_PREMIUM' ) && YWCTM_PREMIUM && get_option( 'ywctm_enable_plugin ' ) == 'yes';
	}
}

/****** HOOKS *****/
if ( !function_exists( 'yith_ywraq_show_button_in_single_page' ) ) {
	function yith_ywraq_show_button_in_single_page() {
		global $product;
		$hide_quote_button = yit_get_prop( $product, '_ywraq_hide_quote_button', true );
		$general_show_btn  = get_option( 'ywraq_show_btn_single_page' );
		$reverse_show_btn  = get_option( 'ywraq_reverse_exclusion' );

		if ( $reverse_show_btn == 'yes' ) {
			$hide_quote_button = ! $hide_quote_button;
		}

		if ( $general_show_btn == 'yes' ) {  //check if the product is in exclusion list
			if ( $hide_quote_button == 1 ) {
				return 'no';
			}
		}

		return $general_show_btn;
	}
}

if ( !function_exists( 'yith_ywraq_show_button_in_other_pages' ) ) {
	/**
	 * @param $setting_option
	 *
	 * @return string
	 */
	function yith_ywraq_show_button_in_other_pages( $setting_option ) {

		if ( $setting_option == 'no' ) {
			return $setting_option;
		}

		global $product;
		$hide_quote_button = yit_get_prop( $product, '_ywraq_hide_quote_button', true );
		$general_show_btn  = get_option( 'ywraq_show_btn_exclusion' );
		$reverse_show_btn  = get_option( 'ywraq_reverse_exclusion' );

		if ( $reverse_show_btn == 'yes' ) {
			$hide_quote_button = ! $hide_quote_button;
		}

		if ( $general_show_btn == 'yes' ) {  //check if the product is in exclusion list
			if ( $hide_quote_button == 1 ) {
				return 'no';
			}
		}

		return 'yes';
	}
}

if ( ! function_exists( 'yith_ywraq_get_contact_forms' ) ) {
	/**
	 * Get list of forms by YIT Contact Form plugin
	 * @return array
	 * @internal param array $array
	 * @since    1.0.0
	 * @author   Emanuela Castorina
	 */
	function yith_ywraq_get_contact_forms() {
		if ( ! function_exists( 'YIT_Contact_Form' ) ) {
			return array( '' => __( 'Plugin not activated or not installed', 'yith-woocommerce-request-a-quote' ) );
		}

		$array = array();

		$posts = get_posts( array(
			'post_type' => YIT_Contact_Form()->contact_form_post_type
		) );

		foreach ( $posts as $post ) {
			$array[ $post->post_name ] = $post->post_title;
		}

		if ( $array == array() ) {
			return array( '' => __( 'No contact form found', 'yith-woocommerce-request-a-quote' ) );
		}

		return $array;
	}
}

if ( !function_exists( 'yith_ywraq_wpcf7_get_contact_forms' ) ) {
	/**
	 * Get list of forms by Contact Form 7 plugin
	 *
	 * @since   1.0.0
	 * @author  Emanuela Castorina
	 * @return  array
	 */
	function yith_ywraq_wpcf7_get_contact_forms() {
		if ( ! function_exists( 'wpcf7_contact_form' ) ) {
			return array( '' => __( 'Plugin not activated or not installed', 'yith-woocommerce-request-a-quote' ) );
		}

		$posts = WPCF7_ContactForm::find();

		$array = array();
		foreach ( $posts as $post ) {
			$array[ $post->id() ] = $post->title();
		}


		if ( empty( $array ) ) {
			return array( '' => __( 'No contact form found', 'yith-woocommerce-request-a-quote' ) );
		}

		return $array;
	}
}

if ( !function_exists( 'ywraq_get_current_contact_form_7' ) ) {
	/**
	 * Get current contact form selected by Contact Form 7 plugin
	 *
	 * @since   1.5.6
	 * @author  Emanuela Castorina
	 * @return mixed|void
	 */
	function ywraq_get_current_contact_form_7() {
		$cform7_id = '';

		if ( get_option( 'ywraq_inquiry_form_type' ) == 'contact-form-7' ) {
			if ( function_exists( 'icl_get_languages' ) && class_exists( 'YITH_YWRAQ_Multilingual_Email' ) ) {
				global $sitepress;
				$current_language = $sitepress->get_current_language();
				$cform7_id        = get_option( 'ywraq_inquiry_contact_form_7_id_' . $current_language );
			} else {
				$cform7_id = get_option( 'ywraq_inquiry_contact_form_7_id' );
			}
		}

		return apply_filters('ywraq_inquiry_contact_form_7_id', $cform7_id );
	}
}

if ( !function_exists( 'yith_ywraq_get_roles' ) ) {
	/**
	 * Return the roles of users
	 *
	 * @return array
	 * @since 1.3.0
	 */
	function yith_ywraq_get_roles(){
        global $wp_roles;
        return array_merge( array( 'all' => __( 'All', 'yith-woocommerce-request-a-quote' ) ), $wp_roles->get_names() );
    }
}

if ( !function_exists( 'yith_ywraq_email_custom_tags' ) ) {
	/**
	 * @param $text
	 * @param $tag
	 * @param $html
	 *
	 * @return string
	 */
	function yith_ywraq_email_custom_tags( $text, $tag, $html ) {

		if ( $tag == 'yith-request-a-quote-list' ) {
			return yith_ywraq_get_email_template( $html );
		}
	}
}

if ( !function_exists( 'yith_ywraq_get_email_template' ) ) {
	/**
	 * @param $html
	 *
	 * @return string
	 */
	function yith_ywraq_get_email_template( $html ) {

		$raq_data['order_id']    = WC()->session->raq_new_order;
		$raq_data['raq_content'] = YITH_Request_Quote()->get_raq_return();

		ob_start();
		if ( $html ) {
			wc_get_template( 'emails/request-quote-table.php', array(
				'raq_data' => $raq_data
			) );
		} else {
			wc_get_template( 'emails/plain/request-quote-table.php', array(
				'raq_data' => $raq_data
			) );
		}

		return ob_get_clean();
	}
}

if ( ! function_exists( 'yith_ywraq_quote_list_shortcode' ) ) {
	/**
	 * @param $shortcodes
	 *
	 * @return mixed
	 */
	function yith_ywraq_quote_list_shortcode( $shortcodes ) {
		$shortcodes['%yith-request-a-quote-list%'] = yith_ywraq_get_email_template( true );

		return $shortcodes;
	}

	add_filter( 'yit_contact_form_shortcodes', 'yith_ywraq_quote_list_shortcode' );
}

if ( ! function_exists( 'ywraq_get_token' ) ) {
	/**
	 * @param $action
	 * @param $order_id
	 * @param $email
	 *
	 * @return string
	 */
	function ywraq_get_token( $action, $order_id, $email ) {
		return wp_hash( $action . '|' . $order_id . '|' . $email, 'yith-woocommerce-request-a-quote' );
	}
}

if ( !function_exists( 'ywraq_verify_token' ) ) {
	/**
	 * @param $token
	 * @param $action
	 * @param $order_id
	 * @param $email
	 *
	 * @return int
	 */
	function ywraq_verify_token( $token, $action, $order_id, $email ) {
		$expected = wp_hash( $action . '|' . $order_id . '|' . $email, 'yith-woocommerce-request-a-quote' );
		if ( hash_equals( $expected, $token ) ) {
			return 1;
		}

		return 0;
	}
}

if ( !function_exists( 'ywraq_get_list_empty_message' ) ) {
	/**
	 * @return mixed|void
	 */
	function ywraq_get_list_empty_message() {
		$label_return_to_shop = apply_filters( 'yith_ywraq_return_to_shop_label', get_option( 'ywraq_return_to_shop_label' ) );
		$empty_list_message   = sprintf( '<p class="ywraq_list_empty_message">%s<p>', __( 'Your list is empty, add products to the list to send a request', 'yith-woocommerce-request-a-quote' ) );
		$shop_url             = apply_filters( 'yith_ywraq_return_to_shop_url', get_option( 'ywraq_return_to_shop_url' ) );
		$empty_list_message .= sprintf( '<p class="return-to-shop"><a class="button wc-backward" href="%s">%s</a><p>', $shop_url, $label_return_to_shop );

		return apply_filters( 'ywraq_get_list_empty_message', $empty_list_message );
	}
}

if ( !function_exists( 'ywraq_get_browse_list_message' ) ) {
	/**
	 * @return mixed|void
	 */
	function ywraq_get_browse_list_message() {
		return apply_filters( 'ywraq_product_added_view_browse_list', __( 'Browse the list', 'yith-woocommerce-request-a-quote' ) );
	}
}



if ( !function_exists( 'ywraq_get_attachment_id_by_url' ) ) {
	/**
	 * Return an ID of an attachment by searching the database with the file URL.
	 *
	 * First checks to see if the $url is pointing to a file that exists in
	 * the wp-content directory. If so, then we search the database for a
	 * partial match consisting of the remaining path AFTER the wp-content
	 * directory. Finally, if a match is found the attachment ID will be
	 * returned.
	 *
	 * @param string $url The URL of the image (ex: http://mysite.com/wp-content/uploads/2013/05/test-image.jpg)
	 *
	 * @return mixed $attachment Returns an attachment ID, or null if no attachment is found
	 */
	function ywraq_get_attachment_id_by_url( $url ) {
		// Split the $url into two parts with the wp-content directory as the separator
		$parsed_url = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

		// Get the host of the current site and the host of the $url, ignoring www
		$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
		$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

		// Return nothing if there aren't any $url parts or if the current host and $url host do not match
		if ( ! isset( $parsed_url[1] ) || empty( $parsed_url[1] ) || ( $this_host != $file_host ) ) {
			return;
		}

		// Now we're going to quickly search the DB for any attachment GUID with a partial path match
		// Example: /uploads/2013/05/test-image.jpg
		global $wpdb;

		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parsed_url[1] ) );

		// Returns null if no attachment is found
		return $attachment[0];
	}
}

if ( ! function_exists( 'ywraq_get_message_after_request_quote_sending' ) ) {
	/**
	 * Return the message after that the request quote sending
	 *
	 * @param $new_order
	 *
	 * @return string
	 */
	function ywraq_get_message_after_request_quote_sending( $new_order ) {

		if ( get_option( 'ywraq_activate_thank_you_page' ) == 'yes' ) {
			return '';
		}

		$ywraq_message_after_sent_the_request = get_option( 'ywraq_message_after_sent_the_request' );
		$ywraq_message_to_view_details        = get_option( 'ywraq_message_to_view_details' );

		$quote_number = apply_filters( 'ywraq_quote_number', $new_order );
		if ( is_user_logged_in() && ( get_option( 'ywraq_enable_link_details' ) == "yes" && get_option( 'ywraq_enable_order_creation', 'yes' ) == 'yes' ) ) {
			$message = sprintf( __( '%s %s <a href="%s">#%s</a>', 'yith-woocommerce-request-a-quote' ), $ywraq_message_after_sent_the_request, $ywraq_message_to_view_details, YITH_YWRAQ_Order_Request()->get_view_order_url( $new_order ), $quote_number );
		} else {
			$message = $ywraq_message_after_sent_the_request;
		}

		return $message;
	}
}

if ( ! function_exists( 'ywraq_get_label' ) ) {
	/**
	 * Return or print a label from a specific $key
	 *
	 * @param      $key
	 * @param bool $echo
	 *
	 * @return string|void
	 */
	function ywraq_get_label( $key, $echo = false ) {

		$label = '';
		switch ( $key ) {
			case 'accept' :
				$label = get_option( 'ywraq_accept_link_label', __( 'Accept', 'yith-woocommerce-request-a-quote' ) );
				break;
			case 'reject' :
				$label = get_option( 'ywraq_reject_link_label', __( 'Reject', 'yith-woocommerce-request-a-quote' ) );
				break;
		}

		$label = apply_filters( 'ywraq_get_label', $label, $key );

		if ( $echo ) {
			echo $label;
		} else {
			return $label;
		}

	}
}

if ( ! function_exists( 'wc_get_template_html' ) && function_exists( 'wc_get_template' ) ) {
	/**
     * add the function wc_get_template_html if woocommerce version is < 2.5
	 * @param $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return string
	 */
	function wc_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
        ob_start();
        wc_get_template( $template_name, $args, $template_path, $default_path );

        return ob_get_clean();
    }
}


//todo wc 27
if ( !function_exists( 'ywraq_formatted_line_total' ) ) {

	/**
	 * Gets line subtotal - formatted for display.
	 *
	 * @param array  $item
	 * @param string $tax_display
	 * @return string
	 */
	function ywraq_formatted_line_total( $order, $item, $tax_display = '' ) {

        if ( ! $tax_display ) {
            $tax_display = yit_get_prop( $order, 'tax_display_cart', true );
        }

        if ( ! isset( $item['line_total'] ) || ! isset( $item['line_total'] ) || ! isset( $item['line_subtotal'] ) || ! isset( $item['line_subtotal'] ) ) {
            return '';
        }

        $show_old_price = get_option('ywraq_show_old_price', false);

        $show_discount = apply_filters('ywraq_show_discount_in_line_total', $show_old_price=='yes' && $item['line_subtotal'] > $item['line_total'], $item );

		$currency = method_exists( $order,  'get_currency') ? $order->get_currency() :  $order->get_order_currency;

        if ( 'excl' == $tax_display ) {
            $ex_tax_label = yit_get_prop( $order, '_prices_include_tax', true ) ? 1 : 0;

            $line_total = wc_price( $order->get_line_total( $item ), array( 'ex_tax_label' => $ex_tax_label, 'currency' => $currency ) );
            $line_subtotal = wc_price( $order->get_line_subtotal( $item ), array( 'ex_tax_label' => $ex_tax_label, 'currency' => $currency ) );

        } else {
            $line_total = wc_price( $order->get_line_total( $item, true ), array('currency' => $currency ) );
            $line_subtotal = wc_price( $order->get_line_subtotal( $item, true ), array('currency' =>  $currency ) );
        }

        if( $show_discount ){
            $line_total = '<small><del>'.$line_subtotal.'</del></small> '.$line_total;
        }
        return apply_filters( 'ywraq_formatted_line_total', $line_total, $item, $order );
    }
}

/* GRAVITY FORMS */
if ( !function_exists( 'ywraq_gravity_form_installed' ) ) {
	/**
	 * @return bool
	 */
	function ywraq_gravity_form_installed() {
		return apply_filters('ywraq_gravity_form_installation', class_exists( 'GFForms' ) ) ;
	}
}

/* POLYLANG */
if( defined('POLYLANG_VERSION') ){

	if ( !function_exists( 'ywraq_pll_getLanguageEntity' ) ) {
		/**
		 * @param $slug
		 *
		 * @return bool
		 */
		function ywraq_pll_getLanguageEntity( $slug ) {
			global $polylang;

			$langs = $polylang->model->get_languages_list();

			foreach ( $langs as $lang ) {
				if ( $lang->slug == $slug ) {
					return $lang;
				}
			}

			return false;
		}
	}

	if ( !function_exists( 'ywraq_pll_refresh_email_lang' ) ) {
		/**
		 * @param $order_id
		 *
		 * @return mixed
		 */
		function ywraq_pll_refresh_email_lang( $order_id ) {

			global $polylang;
			$order  = wc_get_order( $order_id );
			$lang   = yit_get_prop( $order, 'wpml_language', true );
			$entity = ywraq_pll_getLanguageEntity( $lang );

			if ( $entity ) {
				$polylang->curlang = $polylang->model->get_language( $entity->locale );

				$GLOBALS['text_direction'] = $entity->is_rtl ? 'rtl' : 'ltr';
				if ( class_exists( 'WP_Locale' ) ) {
					$GLOBALS['wp_locale'] = new \WP_Locale();
				}

				return $entity->locale;
			}

		}

		add_action( 'send_quote_mail_notification', 'ywraq_pll_refresh_email_lang', 10 );
	}

}

/* FLATSOME */
if ( !function_exists( 'show_wraq_product_lightbox' ) ) {
	function show_wraq_product_lightbox() {

		if ( ! function_exists( 'YITH_YWRAQ_Frontend' ) ) {
			return;
		}

		global $product;

		if ( ! $product ) {
			global $post;
			if ( ! $post || ! is_object( $post ) || ! is_singular() ) {
				return;
			}
			$product = wc_get_product( $post->ID );
		}

		if ( get_option( 'ywraq_show_button_near_add_to_cart', 'no' ) == 'yes' && $product->is_in_stock() ) {
			add_action( 'woocommerce_after_add_to_cart_button', array(
				YITH_YWRAQ_Frontend(),
				'add_button_single_page'
			) );
		} else {
			add_action( 'woocommerce_single_product_lightbox_summary', array(
				YITH_YWRAQ_Frontend(),
				'add_button_single_page'
			), 35 );
		}
	}

	add_action( 'wc_quick_view_before_single_product', 'show_wraq_product_lightbox' );
}

//todo:dev163
/* CONTACT FORM 7 */

//if ( !function_exists( 'ywraq_wpcf7_editor_panels' ) ) {
//	/**
//	 * @param $panels
//	 *
//	 * @return mixed
//	 */
//	function ywraq_wpcf7_editor_panels( $panels ) {
//		$panels['ywraq-panel'] = array(
//			'title'    => __( 'YITH WooCommerce Request a Quote', 'yith-woocommerce-request-a-quote' ),
//			'callback' => 'wpcf7_editor_panel_ywraq',
//		);
//
//		return $panels;
//	}
//
//	add_filter( 'wpcf7_editor_panels', 'ywraq_wpcf7_editor_panels' );
//}

if ( !function_exists( 'ywraq_wpcf7_contact_form_properties' ) ) {
	/**
	 * @param $properties
	 *
	 * @return mixed
	 */
	function ywraq_wpcf7_contact_form_properties( $properties ) {
		$properties['ywraq-options'] = array();

		return $properties;
	}

	add_filter( 'wpcf7_contact_form_properties', 'ywraq_wpcf7_contact_form_properties' );
}

if ( !function_exists( 'wpcf7_editor_panel_ywraq' ) ) {
	/**
	 * @param $posted
	 */
	function wpcf7_editor_panel_ywraq( $posted ) {
		?>
        <input type="text" id="ywraq-test" name="ywraq-test" value=""/>
		<?php
	}

}

if ( !function_exists( 'ywraq_wpcf7_save_contact_form' ) ) {
	/**
	 * @param $contact_form
	 */
	function ywraq_wpcf7_save_contact_form( $contact_form ) {
		$ywraq      = array();
		$properties = $contact_form->get_properties();

		if ( isset( $_POST['ywraq-test'] ) ) {

			$ywraq['test'] = trim( $_POST['ywraq-test'] );
		}
		$properties['ywraq-options'] = array( 'ciao' => 'ciao' );

		$contact_form->set_properties( $properties );
	}
	add_action('wpcf7_save_contact_form', 'ywraq_wpcf7_save_contact_form');
}
/* END CONTACT FORM 7 */



if ( ! function_exists( 'ywraq_adjust_type' ) ) {
	function ywraq_adjust_type( $attr, $value ) {
		if ( version_compare( WC()->version, '2.7.0', '<' ) ) {
			switch ( $attr ) {
				case 'date_created':
					$value = strtotime( $value );
					break;
				default:
			}
		}else{
			switch ( $attr ) {
				case 'date_created':
					$value = strtotime( $value );
					break;
				default:
			}
		}



		return $value;
	}
}

/* END CONTACT FORM 7 */


/**
 * Gets line subtotal - formatted for display.
 *
 * @param array  $item
 * @param string $tax_display
 * @return string
 */

if ( ! function_exists( 'ywraq_get_quote_total' ) ) {
    function ywraq_get_quote_total( $raq ) {
        $total = 0;

        foreach ( $raq as $key => $raq_item ) {
            $_product = wc_get_product( ( isset( $raq_item['variation_id'] ) && $raq_item['variation_id'] != '' ) ? $raq_item['variation_id'] : $raq_item['product_id'] );

            if ( ! $_product ) {
                continue;
            }

            $total += apply_filters( 'yith_ywraq_product_price', $_product->get_display_price( '', $raq_item['quantity'] ), $_product, $raq_item );
        }

        return wc_price( $total );
    }
}

if ( ! function_exists( 'ywraq_get_quote_line_total' ) ) {
    function ywraq_get_quote_line_total( $key, $raq ) {
        $total = 0;

        if ( ! isset( $raq[$key] ) ){
            return $total;
        }

        $raq_item = $raq[ $key ];
        $_product = wc_get_product( ( isset( $raq_item['variation_id'] ) && $raq_item['variation_id'] != '' ) ? $raq_item['variation_id'] : $raq_item['product_id'] );

        if ( ! $_product ) {
            return $total;
        }

        $total = apply_filters( 'yith_ywraq_product_price', $_product->get_display_price( '', $raq_item['quantity'] ), $_product, $raq_item );

        return wc_price( $total );
    }
}

if( ! function_exists( 'ywraq_convert_date_format' ) ){
    
    function ywraq_convert_date_format( $format ) {

        $keys = array(
            'd' => '%d', //Day of the month, 2 digits with leading zeros (01 to 31)
            'D' => '%a', //A textual representation of a day, three letters (Mon through Sun)
            'j' => '%e', //Day of the month without leading zeros (1 to 31)
            'l' => '%A', //A full textual representation of the day of the week (Sunday through Saturday)
            'F' => '%B', //A full textual representation of a month, such as January or March
            'm' => '%m', //Numeric representation of a month, with leading zeros (01 through 12)
            'M' => '%b', //A short textual representation of a month, three letters (Jan through Dec)
            'n' => '%m', //Numeric representation of a month, without leading zeros (01 through 12)
            'Y' => '%Y', //A full numeric representation of a year, 4 digits (Examples: 1999 or 2003)
            'y' => '%y', //A two digit representation of a year (Examples: 99 or 03)
        );

        return str_replace( array_keys( $keys ), $keys, $format );

    }
    
}

if ( ! function_exists( 'ywraq_get_date_format' ) ) {
	function ywraq_get_date_format( $language ) {
		$date_format = wc_date_format();
		if ( isset( $language ) ) {
			$args_accept['lang'] = $language;
			$args_reject['lang'] = $language;

			global $sitepress;

			if ( $sitepress ) {

				$lang = $sitepress->get_locale( $language );

				setlocale( LC_ALL, $lang . '.UTF-8' );

				$local_formats = apply_filters( 'ywraq_date_local_formats', array() );

				if ( ! empty( $local_formats ) ) {

					if ( isset( $local_formats[ $lang ] ) ) {

						$date_format = $local_formats[ $lang ];

					}
				}
			}
		}

		return $date_format;
	}
}