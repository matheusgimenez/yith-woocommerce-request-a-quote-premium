<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAQ_VERSION' ) ) {
    exit; // Exit if accessed directly
}


if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Displays the exclusions table in YWCTM plugin admin tab
 *
 * @class   YITH_YWRAQ_Exclusions_Table
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 *
 */
class YITH_YWRAQ_Exclusions_Table {

    /**
     * Outputs the exclusions table template with insert form in plugin options panel
     *
     * @since   1.0.0
     * @author  Emanuela Castorina
     * @return  string
     */
    public static function output() {

        global $wpdb;

        $table = new YITH_YWRAQ_Custom_Table( array(
                                                  'singular' => __( 'product', 'yith-woocommerce-request-a-quote' ),
                                                  'plural'   => __( 'products', 'yith-woocommerce-request-a-quote' )
                                              ) );

        $table->options = array(
            'select_table'     => $wpdb->prefix . 'posts a INNER JOIN ' . $wpdb->prefix . 'postmeta b ON a.ID = b.post_id',
            'select_columns'   => array(
                'a.ID',
                'a.post_title',
                'MAX(CASE WHEN b.meta_key = "_ywraq_hide_quote_button" THEN b.meta_value ELSE NULL END) AS hide_add_to_quote'
            ),
            'select_where'     => 'a.post_type = "product" AND  b.meta_key = "_ywraq_hide_quote_button" AND b.meta_value = "1"',
            'select_group'     => 'a.ID',
            'select_order'     => 'a.post_title',
            'select_limit'     => apply_filters( 'ywraq_exclusion_limit', 25 ),
            'count_table'      => '( SELECT COUNT(*) FROM ' . $wpdb->prefix . 'posts a INNER JOIN ' . $wpdb->prefix .
                'postmeta b ON a.ID = b.post_id  WHERE a.post_type = "product" AND b.meta_key = "_ywraq_hide_quote_button" AND b.meta_value="1" GROUP BY a.ID ) AS count_table',
            'count_where'      => '',
            'key_column'       => 'ID',
            'view_columns'     => array(
                'cb'                => '<input type="checkbox" />',
                'product'           => __( 'Product', 'yith-woocommerce-request-a-quote' ),
                'hide_add_to_quote' => __( 'Hide "Add to quote"', 'yith-woocommerce-request-a-quote' )
            ),
            'hidden_columns'   => array(),
            'sortable_columns' => array(
                'product' => array( 'post_title', true )
            ),
            'custom_columns'   => array(
                'column_product'           => function ( $item, $me ) {

                    $edit_query_args = array(
                        'page'   => $_REQUEST['page'],
                        'tab'    => $_REQUEST['tab'],
                        'action' => 'edit',
                        'id'     => $item['ID']
                    );


                    $delete_query_args = array(
                        'page'   => $_REQUEST['page'],
                        'tab'    => $_REQUEST['tab'],
                        'action' => 'delete',
                        'id'     => $item['ID']
                    );
                    $delete_url        = esc_url( add_query_arg( $delete_query_args, admin_url( 'admin.php' ) ) );

                    $product_query_args = array(
                        'post'   => $item['ID'],
                        'action' => 'edit'
                    );
                    $product_url        = esc_url( add_query_arg( $product_query_args, admin_url( 'post.php' ) ) );

                    $actions = array(
'delete' => '<a href="' . $delete_url . '">' . __( 'Remove from exclusions', 'yith-woocommerce-request-a-quote' ) . '</a>',
                    );

                    return sprintf( '<strong><a class="tips" target="_blank" href="%s" data-tip="%s">#%d %s </a></strong> %s', $product_url, __( 'Edit product', 'yith-woocommerce-request-a-quote' ), $item['ID'], $item['post_title'], $me->row_actions( $actions ) );
                },
                'column_hide_add_to_quote' => function ( $item, $me ) {

                    if ( $item['hide_add_to_quote'] == '1' ) {
                        $class = 'show';
                        $tip   = __( 'Yes', 'yith-woocommerce-request-a-quote' );
                    }
                    else {
                        $class = 'hide';
                        $tip   = __( 'No', 'yith-woocommerce-request-a-quote' );
                    }

                    return sprintf( '<mark class="%s tips" data-tip="%s">%s</mark>', $class, $tip, $tip );

                }
            ),
            'bulk_actions'     => array(
                'actions'   => array(
                    'delete' => __( 'Remove from list', 'yith-woocommerce-request-a-quote' )
                ),
                'functions' => array(
                    'function_delete' => function () {
                        global $wpdb;

                        $ids = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : array();
                        if ( is_array( $ids ) ) {
                            $ids = implode( ',', $ids );
                        }

                        if ( !empty( $ids ) ) {
                            $wpdb->query( "UPDATE {$wpdb->prefix}postmeta
                                           SET meta_value='0'
                                           WHERE meta_key = '_ywraq_hide_quote_button' AND post_id IN ( $ids )"
                            );
                        }
                    }
                )
            ),
        );

        $table->prepare_items();

        $message = '';
        $notice  = '';

        $list_query_args = array(
            'page' => $_REQUEST['page'],
            'tab'  => $_REQUEST['tab']
        );

        $list_url = esc_url( add_query_arg( $list_query_args, admin_url( 'admin.php' ) ) );

        if ( 'delete' === $table->current_action() ) {
            $message = sprintf( _n( '%s product removed successfully', '%s products removed successfully', count( $_REQUEST['id'] ), 'yith-woocommerce-request-a-quote' ), count( $_REQUEST['id'] ) );
        }

        if ( !empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], basename( __FILE__ ) ) ) {

            $item_valid = self::validate_fields( $_POST );

            if ( $item_valid !== true ) {

                $notice = $item_valid;

            }
            else {

                $product_ids = array();

                if ( !empty( $_POST['insert'] ) && $_POST['selection_type'] == 'category' ) {
                    $args = array(
                        'post_type'      => 'product',
                        'post_status'    => 'publish',
                        'posts_per_page' => - 1,
                        'tax_query'      => array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field'    => 'id',
                                'terms'    => $_POST['category_ids'],
                            ),
                        )
                    );

                    wp_reset_query();

                    $query = new WP_Query( $args );

                    if ( $query->have_posts() ) {

                        while ( $query->have_posts() ) {

                            $query->the_post();
                            $product_ids[] = $query->post->ID;

                        }

                    }

                    wp_reset_postdata();

                }
                else {

                    $product_ids = explode( ',', $_POST['product_ids'] );

                }

                $hide_quote = isset( $_POST['hide_add_to_quote'] ) ? 1 : 0;

                foreach ( $product_ids as $product_id ) {
                    update_post_meta( $product_id, '_ywraq_hide_quote_button', $hide_quote );

                }

                if ( !empty( $_POST['insert'] ) ) {

                    $message = sprintf( _n( '%s product added successfully', '%s products added successfully', count( $product_ids ), 'yith-woocommerce-request-a-quote' ), count( $product_ids ) );

                }
                elseif ( !empty( $_POST['update'] ) ) {

                    $message = __( 'Product updated successfully', 'yith-woocommerce-request-a-quote' );

                }

            }

        }

        $data_selected = '';
        $value         = '';
        $item          = array(
            'ID'                => 0,
            'hide_add_to_quote' => '',
        );

        if ( isset( $_REQUEST['id'] ) && !empty( $_REQUEST['action'] ) && ( 'edit' == $_REQUEST['action'] ) ) {

            $item = array(
                'ID'                => $_REQUEST['id'],
                'hide_add_to_quote' => get_post_meta( $_REQUEST['id'], '_ywraq_hide_quote_button', true ),
            );

            $product       = wc_get_product( $_REQUEST['id'] );
	        if ( version_compare( WC()->version, '2.7', '<' ) ) {
            	$data_selected = wp_kses_post( $product->get_formatted_name() );
	        }else{
		        $data_selected[$_REQUEST['id']] = wp_kses_post( $product->get_formatted_name() );
			}
            $value         = $_REQUEST['id'];

        }

        ?>
        <div class="wrap">
            <div class="icon32 icon32-posts-post" id="icon-edit"><br /></div>
            <h2><?php _e( 'Exclusion list', 'yith-woocommerce-request-a-quote' );

                if ( empty( $_REQUEST['action'] ) || ( 'insert' !== $_REQUEST['action'] && 'edit' !== $_REQUEST['action'] ) ) : ?>
                    <?php $query_args = array(
                        'page'   => $_REQUEST['page'],
                        'tab'    => $_REQUEST['tab'],
                        'action' => 'insert'
                    );
                    $add_form_url     = esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
                    ?>
                    <a class="add-new-h2" href="<?php echo $add_form_url; ?>"><?php _e( 'Add Products', 'yith-woocommerce-request-a-quote' ); ?></a>
                <?php endif; ?>
            </h2>
            <?php if ( !empty( $notice ) ) : ?>
                <div id="notice" class="error below-h2"><p><?php echo $notice; ?></p></div>
            <?php endif;

            if ( !empty( $message ) ) : ?>
                <div id="message" class="updated below-h2"><p><?php echo $message; ?></p></div>
            <?php endif;

            if ( !empty( $_REQUEST['action'] ) && ( 'insert' == $_REQUEST['action'] || 'edit' == $_REQUEST['action'] ) ) : ?>

                <form id="form" method="POST">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />
                    <table class="form-table">
                        <tbody>

                        <?php if ( 'insert' == $_REQUEST['action'] ) : ?>
                            <tr valign="top" id="ywraq_selection_type">
                                <th scope="row" class="titledesc">
                                    <label for="selection_type"><?php _e( 'Selection type', 'yith-woocommerce-request-a-quote' ) ?></label>
                                </th>
                                <td class="forminp forminp-radio">
                                    <fieldset>
                                        <ul>
                                            <li>
                                                <label>
                                                    <input
                                                        name="selection_type"
                                                        value="product"
                                                        type="radio"
                                                        checked="checked"
                                                        />
                                                    <?php _e( 'Select by product', 'yith-woocommerce-request-a-quote' ) ?>
                                                </label>
                                            </li>
                                            <li>
                                                <label>
                                                    <input
                                                        name="selection_type"
                                                        value="category"
                                                        type="radio"
                                                        />
                                                    <?php _e( 'Select by category', 'yith-woocommerce-request-a-quote' ) ?>
                                                </label>
                                            </li>
                                        </ul>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr valign="top" id="ywraq_category" style="display: none">
                                <th scope="row" class="titledesc">
                                    <label for="category_ids"><?php _e( 'Categories to exclude', 'yith-woocommerce-request-a-quote' ); ?></label>
                                </th>
                                <td class="forminp">
									<?php if ( version_compare( WC()->version, '2.7', '<' ) ) { ?>
                                    <input
                                        type="hidden"
                                        class="wc-product-search"
                                        id="category_ids"
                                        name="category_ids"
                                        data-placeholder="<?php _e( 'Search for a category&hellip;', 'yith-woocommerce-request-a-quote' ) ?>"
                                        data-action="ywraq_json_search_product_categories_ywraq"
                                        data-multiple="true"
                                        data-selected=""
                                        value=""
                                        />
									<?php  }else{
										yit_add_select2_fields(
											array(
												'type'              => 'hidden',
												'class'             => 'wc-product-search',
												'id'                => 'category_ids',
												'name'              => 'category_ids',
												'data-placeholder'  => __( 'Search for a category&hellip;', 'yith-woocommerce-request-a-quote' ),
												'data-allow_clear'  => false,
												'data-selected'     => '',
												'data-multiple'     => true,
												'data-action'       => 'ywraq_json_search_product_categories_ywraq',
												'value'             => '',
												'style'             => 'width:200px',
												'custom-attributes' => array()
											)
										);
									} ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr valign="top" id="ywraq_products">
                            <th scope="row" class="titledesc">
                                <label for="product_ids"><?php _e( 'Products to exclude', 'yith-woocommerce-request-a-quote' ); ?></label>
                            </th>
                            <td class="forminp">
	                            <?php if ( version_compare( WC()->version, '2.7', '<' ) ) { ?>
                                <?php if ( 'edit' == $_REQUEST['action'] ) : ?>
                                    <input id="product_id" name="product_ids" type="hidden" value="<?php echo esc_attr( $item['ID'] ); ?>" />
                                <?php endif; ?>
                                <input
                                    type="hidden"
                                    class="wc-product-search"
                                    id="product_ids"
                                    name="product_ids"
                                    data-placeholder="<?php _e( 'Search for a product&hellip;', 'yith-woocommerce-request-a-quote' ) ?>"
                                    data-action="woocommerce_json_search_products"
                                    data-multiple="<?php echo ( 'edit' == $_REQUEST['action'] ) ? 'false' : 'true'; ?>"
                                    data-selected="<?php echo $data_selected; ?>"
                                    value="<?php echo $value; ?>"
                                    <?php echo ( 'edit' == $_REQUEST['action'] ) ? 'disabled="disabled"' : ''; ?>
                                    />
								<?php }else{
		                            yit_add_select2_fields(
			                            array(
				                            'type'              => 'hidden',
				                            'class'             => 'wc-product-search',
				                            'id'                => 'product_ids',
				                            'name'              => 'product_ids',
				                            'data-placeholder'  => __( 'Search for a product&hellip;', 'yith-woocommerce-request-a-quote' ),
				                            'data-allow_clear'  => false,
				                            'data-selected'     => $data_selected,
				                            'data-multiple'     => ( 'edit' == $_REQUEST['action'] ) ? 'false' : 'true',
				                            'data-action'       => 'woocommerce_json_search_products',
				                            'value'             => $value,
				                            'style'             => 'width:200px',
				                            'custom-attributes' => array()
			                            )
		                            );
								}?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="hide-add-to-quote"><?php _e( 'Hide "Add to quote" button', 'yith-woocommerce-request-a-quote' ); ?></label>
                            </th>
                            <td class="forminp forminp-checkbox">
								<?php $checkd = empty($item['hide_add_to_quote']) ? 1 : $item['hide_add_to_quote'] ?>
                                <input id="hide-add-to-quote" name="hide_add_to_quote" type="checkbox" <?php checked( $checkd, 1 ) ?> />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if ( 'insert' == $_REQUEST['action'] ) : ?>

                        <input type="submit" value="<?php _e( 'Add product to exclusions', 'yith-woocommerce-request-a-quote' ); ?>" id="insert" class="button-primary" name="insert">

                    <?php else : ?>

                        <input type="submit" value="<?php _e( 'Update product exclusion', 'yith-woocommerce-request-a-quote' ); ?>" id="update" class="button-primary" name="update">

                    <?php endif; ?>
                    <a class="button-secondary" href="<?php echo $list_url; ?>"><?php _e( 'Return to exclusion list', 'yith-woocommerce-request-a-quote' ); ?></a>
                </form>
            <?php else : ?>
                <form id="custom-table" method="GET" action="<?php echo $list_url; ?>">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
                    <input type="hidden" name="tab" value="<?php echo $_REQUEST['tab']; ?>" />
                    <?php $table->display(); ?>
                </form>
            <?php endif; ?>
        </div>
        <script>

            (function ($) {

                $(document).ready(function () {
                    //exclusion list
                    $('#ywraq_selection_type input').click(function () {

                        if ($(this).val() == 'product') {

                            $('#ywraq_products').show();
                            $('#ywraq_category').hide();

                        } else {

                            $('#ywraq_category').show();
                            $('#ywraq_products').hide();

                        }

                    });
                });
            })(jQuery);
        </script>
    <?php

    }

    /**
     * Validate input fields
     *
     * @since   1.0.0
     * @author  Emanuela Castorina
     *
     * @param   $item array POST data array
     *
     * @return  bool|string
     */
    static function validate_fields( $item ) {


        $messages = array();

        if ( !empty( $item['insert'] ) ) {

            if ( empty( $item['product_ids'] ) && $item['selection_type'] == 'product' ) {
                $messages[] = __( 'Select at least one product', 'yith-woocommerce-request-a-quote' );
            }

            if ( empty( $item['category_ids'] ) && $item['selection_type'] == 'category' ) {
                $messages[] = __( 'Select at least one category', 'yith-woocommerce-request-a-quote' );
            }

        }

        if ( empty( $item['hide_add_to_quote'] ) ) {
            $messages[] = __( 'Select the option', 'yith-woocommerce-request-a-quote' );
        }

        if ( empty( $messages ) ) {
            return true;
        }
        return implode( '<br />', $messages );
    }

}

if ( !function_exists( 'json_search_product_categories_ywraq' ) ) {

    /**
     * Get category name
     *
     * @since   1.2.3
     *
     * @param   $x
     * @param   $taxonomy_types
     *
     * @return  string
     * @author  Alberto Ruggiero
     */
    function json_search_product_categories_ywraq( $x = '', $taxonomy_types = array( 'product_cat' ) ) {

        global $wpdb;

        $term = (string) urldecode( stripslashes( strip_tags( $_REQUEST['term'] ) ) );
        $term = '%' . $term . '%';

        $query_cat = $wpdb->prepare( "SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name, {$wpdb->terms}.slug
                                   FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                                   WHERE {$wpdb->term_taxonomy}.taxonomy IN (%s) AND {$wpdb->terms}.slug LIKE %s", implode( ',', $taxonomy_types ), $term );

        $product_categories = $wpdb->get_results( $query_cat );

        $to_json = array();

        foreach ( $product_categories as $product_category ) {

            $to_json[$product_category->term_id] = sprintf( '#%s &ndash; %s', $product_category->term_id, $product_category->name );

        }

        wp_send_json( $to_json );

    }

    add_action( 'wp_ajax_ywraq_json_search_product_categories_ywraq', 'json_search_product_categories_ywraq', 10 );

}

