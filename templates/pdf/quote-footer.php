<?php
if( function_exists('icl_get_languages') && class_exists('YITH_YWRAQ_Multilingual_Email')  ) {
	global $sitepress;
	$lang = get_post_meta( $order_id, 'wpml_language', true );
	YITH_Request_Quote_Premium()->change_pdf_language( $lang );
}
?>
<div class="footer">
	<?php if ( $footer != '' ): ?>
		<div class="footer-content"><?php echo $footer ?></div>
	<?php endif; ?>
	<?php if ( $pagination != '' ): ?>
		<div class="page"><?php echo __( 'Page', 'yith-woocommerce-request-a-quote' ) ?> <span class="pagenum"></span>
		</div>
	<?php endif ?>
</div>