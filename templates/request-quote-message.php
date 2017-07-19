<div class="ywraq-question-message">
	<?php
	if( isset($message) && $message != ''): ?>
		<p><?php echo  $message ?></p>
		<?php
	elseif( isset($confirm) && $confirm == 'no'):
		$args =array(
			'status' => 'rejected',
			'raq_nonce' => $raq_nonce,
			'request_quote' => $order_id,
			'confirm' => 'yes'
		);
		?>
		<p><?php printf( __('Are you sure you want to reject quote No. %d?' , 'yith-woocommerce-request-a-quote'), $order_id ) ?></p>
		<p><a class="ywraq-button button" href="<?php echo  esc_url(add_query_arg( $args, YITH_Request_Quote()->get_raq_page_url() ) )?>" ><?php _e('Yes, I want to reject the quote', 'yith-woocommerce-request-a-quote') ?></a> <?php if ( get_option( 'ywraq_show_return_to_shop' ) == 'yes' ):
		$shop_url = apply_filters( 'yith_ywraq_return_to_shop_url', get_option( 'ywraq_return_to_shop_url' ) );
		$label_return_to_shop = apply_filters( 'yith_ywraq_return_to_shop_label', get_option( 'ywraq_return_to_shop_label' ) );
		?>
		<p>
			<a class="ywraq-button button" href="<?php echo apply_filters( 'yith_ywraq_return_to_shop_url', $shop_url ); ?>"><?php echo $label_return_to_shop ?></a>
		</p>
	<?php endif ?></p>
	<?php endif ?>
</div>