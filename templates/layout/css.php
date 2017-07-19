<?php
if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit; // Exit if accessed directly
}

$ywraq_layout_button_bg_color       = get_option( 'ywraq_layout_button_bg_color' );
$ywraq_layout_button_bg_color_hover = get_option( 'ywraq_layout_button_bg_color_hover' );
$ywraq_layout_button_color          = get_option( 'ywraq_layout_button_color' );
$ywraq_layout_button_color_hover    = get_option( 'ywraq_layout_button_color_hover' );

$css = ".woocommerce .add-request-quote-button.button, .woocommerce .add-request-quote-button-addons.button{
    background-color: {$ywraq_layout_button_bg_color};
    color: {$ywraq_layout_button_color};
}
.woocommerce .add-request-quote-button.button:hover,  .woocommerce .add-request-quote-button-addons.button:hover{
    background-color: {$ywraq_layout_button_bg_color_hover};
    color: {$ywraq_layout_button_color_hover};
}
.woocommerce a.add-request-quote-button{
    color: {$ywraq_layout_button_color};
}

.woocommerce a.add-request-quote-button:hover{
    color: {$ywraq_layout_button_color_hover};
}
";

if ( get_option( 'ywraq_show_button_near_add_to_cart', 'no' ) == 'yes' ) {
	$css .= ".woocommerce button.single_add_to_cart_button.button {margin-right: 5px;}";
}

return apply_filters( 'ywraq_custom_css', $css );