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

<div class="yith-ywraq-add-to-quote add-to-quote-addons-<?php echo $product_id ?>" <?php echo $data_variations ?>>
    <a class="add-request-quote-button-addons button" style="display:<?php echo ( $exists ) ? 'none': 'block' ?>" href="<?php echo get_the_permalink( $product_id );?>" ><?php echo $label;?></a>

</div>

<div class="clear"></div>
