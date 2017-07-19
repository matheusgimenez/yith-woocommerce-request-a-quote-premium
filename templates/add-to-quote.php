<?php
/**
 * Add to Quote button template
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 */

 $data_variations = ( isset( $variations ) && !empty( $variations ) ) ? ' data-variation="'.$variations.'" ' : '';

 ?>

<div class="yith-ywraq-add-to-quote add-to-quote-<?php echo $product_id ?>" <?php echo $data_variations ?>>
    <div class="yith-ywraq-add-button <?php echo ( $exists ) ? 'hide': 'show' ?>" style="display:<?php echo ( $exists ) ? 'none': 'block' ?>" >
        <?php wc_get_template( 'add-to-quote-' . $template_part . '.php', $args, YITH_YWRAQ_DIR, YITH_YWRAQ_DIR );  ?>
    </div>
    <div class="yith_ywraq_add_item_response-<?php echo $product_id ?> yith_ywraq_add_item_response_message <?php echo ( !$exists ) ? 'hide': 'show' ?>" style="display:<?php echo ( !$exists ) ? 'none': 'block' ?>"><?php echo apply_filters( 'ywraq_product_in_list', __('The product is already in quote request list!', 'yith-woocommerce-request-a-quote') )?></div>
    <div class="yith_ywraq_add_item_browse-list-<?php echo $product_id ?> yith_ywraq_add_item_browse_message  <?php echo ( !$exists ) ? 'hide': 'show' ?>" style="display:<?php echo ( !$exists ) ? 'none': 'block' ?>"><a href="<?php echo  $rqa_url ?>"><?php echo $label_browse ?></a></div>
    <div class="yith_ywraq_add_item_product-response-<?php echo $product_id ?> yith_ywraq_add_item_product_message hide" style="display:'none'"></div>
</div>

<div class="clear"></div>
