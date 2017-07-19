<?php
/**
 * Form to Request a quote
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 1.0.0
 * @author  Yithemes
 */
$current_user = array();
if ( is_user_logged_in() ) {
    $current_user = get_user_by( 'id', get_current_user_id() );
}

$user_name = ( ! empty( $current_user ) ) ?  $current_user->display_name : '';
$user_email = ( ! empty( $current_user ) ) ?  $current_user->user_email : '';

$optional_form_text_field            = ( get_option( 'ywraq_additional_text_field' ) == 'yes' ) ? true : false;
$optional_form_text_field_required   = ( get_option( 'ywraq_additional_text_field_required' ) == 'yes' ) ? 'required' : '';
$optional_form_text_field_2          = ( get_option( 'ywraq_additional_text_field_2' ) == 'yes' ) ? true : false;
$optional_form_text_field_required_2 = ( get_option( 'ywraq_additional_text_field_required_2' ) == 'yes' ) ? 'required' : '';
$optional_form_text_field_3          = ( get_option( 'ywraq_additional_text_field_3' ) == 'yes' ) ? true : false;
$optional_form_text_field_required_3 = ( get_option( 'ywraq_additional_text_field_required_3' ) == 'yes' ) ? 'required' : '';
$optional_form_upload_field          = ( get_option( 'ywraq_additional_upload_field' ) == 'yes' ) ? true : false;
$force_user_to_register              = ( get_option( 'ywraq_force_user_to_register' ) == 'yes' ) ? 'required' : '';
?>
<div class="yith-ywraq-mail-form-wrapper">
    <h3><?php _e( 'Send the request', 'yith-woocommerce-request-a-quote' ) ?></h3>

    <form id="yith-ywraq-mail-form" name="yith-ywraq-mail-form" action="<?php echo esc_url( YITH_Request_Quote()->get_raq_page_url() ) ?>" method="post" enctype="multipart/form-data">

        <p class="form-row form-row-wide validate-required" id="rqa_name_row">
            <label for="rqa-name" class=""><?php _e( 'Name', 'yith-woocommerce-request-a-quote' ) ?>
                <abbr class="required" title="required">*</abbr></label>
            <input type="text" class="input-text " name="rqa_name" id="rqa-name" placeholder="" value="<?php echo $user_name ?>" required>
        </p>

        <p class="form-row form-row-wide validate-required" id="rqa_email_row">
            <label for="rqa-email" class=""><?php _e( 'Email', 'yith-woocommerce-request-a-quote' ) ?>
                <abbr class="required" title="required">*</abbr></label>
            <input type="email" class="input-text " name="rqa_email" id="rqa-email" placeholder="" value="<?php echo $user_email ?>" required>
        </p>

        <?php if( $optional_form_text_field ): ?>
            <p class="form-row form-row-wide validate-required" id="rqa_text_field_row">
                <label for="rqa_text_field_row" class=""><?php echo get_option('ywraq_additional_text_field_label') ?>
                    <?php if ( $optional_form_text_field_required == 'required' ) : ?>
                       <abbr class="required" title="required">*</abbr></label>
                    <?php endif ?>
                <input type="text" class="input-text " name="rqa_text_field" id="rqa-text-field" placeholder="" value="" <?php echo $optional_form_text_field_required ?>>
            </p>
        <?php endif ?>

        <?php if( $optional_form_text_field_2 ): ?>
            <p class="form-row form-row-wide validate-required" id="rqa_text_field_row_2">
                <label for="rqa_text_field_row_2"><?php echo get_option('ywraq_additional_text_field_label_2') ?>
                    <?php if ( $optional_form_text_field_required_2 == 'required' ) : ?>
                        <abbr class="required" title="required">*</abbr></label>
                    <?php endif ?>
                <input type="text" class="input-text " name="rqa_text_field_2" id="rqa_text_field_row_2" placeholder="" value="" <?php echo $optional_form_text_field_required_2 ?>>
            </p>
        <?php endif ?>


        <?php if( $optional_form_text_field_3 ): ?>
            <p class="form-row form-row-wide validate-required" id="rqa_text_field_row_3">
                <label for="rqa_text_field_row_3"><?php echo get_option('ywraq_additional_text_field_label_3') ?>
                    <?php if ( $optional_form_text_field_required_3 == 'required' ) : ?>
                        <abbr class="required" title="required">*</abbr></label>
                    <?php endif ?>
                <input type="text" class="input-text " name="rqa_text_field_3" id="rqa_text_field_row_3" placeholder="" value="" <?php echo $optional_form_text_field_required_3 ?>>
            </p>
        <?php endif ?>

        <?php if( $optional_form_upload_field ): ?>
            <p class="form-row form-row-wide" id="rqa_upload_field_row">
                <label for="rqa-upload-field" class=""><?php echo get_option('ywraq_additional_upload_field_label') ?>
                <input type="file" class="input-text input-upload" name="rqa_upload_field" id="rqa-upload-field">
            </p>
        <?php endif ?>
        <p class="form-row" id="rqa_message_row">
            <label for="rqa-message" class=""><?php _e( 'Message', 'yith-woocommerce-request-a-quote' ) ?></label>
            <textarea name="rqa_message" class="input-text " id="rqa-message" placeholder="<?php _e( 'Notes on your request...', 'yith-woocommerce-request-a-quote' ) ?>" rows="5" cols="5" ></textarea>
        </p>
        <?php if( ! is_user_logged_in() && get_option('ywraq_add_user_registration_check') == 'yes' ): ?>
            <input class="input-checkbox" id="createaccount" type="checkbox" name="createaccount" value="1" <?php echo $force_user_to_register ?>/> <label for="createaccount" class="checkbox"><?php _e( 'Create an account?', 'yith-woocommerce-request-a-quote' ); ?></label>
        <?php endif ?>
        <p class="form-row">
            <input type="hidden" id="raq-mail-wpnonce" name="raq_mail_wpnonce" value="<?php echo wp_create_nonce( 'send-request-quote' ) ?>">
            <input class="button raq-send-request" type="submit" value="<?php _e( 'Send Your Request', 'yith-woocommerce-request-a-quote' ) ?>">
        </p>
        <?php if ( defined( 'ICL_LANGUAGE_CODE' ) ): ?>
            <input type="hidden" class="lang_param" name="lang" value="<?php echo( ICL_LANGUAGE_CODE ); ?>" />
        <?php endif ?>

    </form>
</div>