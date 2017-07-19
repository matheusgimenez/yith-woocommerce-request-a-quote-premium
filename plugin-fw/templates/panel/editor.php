<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
*  Example to call this template
*
*  'section_general_settings_videobox'         => array(
*      'name' => __( 'Title of box', 'yith-plugin-fw' ),
*      'type' => 'videobox',
*      'default' => array(
*          'plugin_name'        => __( 'Plugin Name', 'yith-plugin-fw' ),
*          'title_first_column' => __( 'Title first column', 'yith-plugin-fw' ),
*          'description_first_column' => __('Lorem ipsum ... ', 'yith-plugin-fw'),
*          'video' => array(
*              'video_id'           => 'vimeo_code',
*              'video_image_url'    => '#',
*              'video_description'  => __( 'Lorem ipsum dolor sit amet....', 'yith-plugin-fw' ),
*          ),
*          'title_second_column' => __( 'Title first column', 'yith-plugin-fw' ),
*          'description_second_column' => __('Lorem ipsum dolor sit amet.... ', 'yith-plugin-fw'),
*          'button' => array(
*              'href' => 'http://www.yithemes.com',
*              'title' => 'Get Support and Pro Features'
*          )
*      ),
*      'id'   => 'yith_wcas_general_videobox'
*  ),
*/
$value = get_option( $id, '' );
$editor_args = array(
    'wpautop'       => true, // use wpautop?
    'media_buttons' => true, // show insert/upload button(s)
    'textarea_name' => $id, // set the textarea name to something different, square brackets [] can be used here
    'textarea_rows' => 20, // rows="..."
    'tabindex'      => '',
    'editor_css'    => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
    'editor_class'  => '', // add extra class(es) to the editor textarea
    'teeny'         => false, // output the minimal editor config used in Press This
    'dfw'           => false, // replace the default fullscreen with DFW (needs specific DOM elements and css)
    'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
    'quicktags'     => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
);
?>
<div id="<?php echo $id ?>-container" <?php if ( isset( $deps ) ): ?>data-field="<?php echo $id ?>" data-dep="<?php echo $deps['ids'] ?>" data-value="<?php echo $deps['values'] ?>" <?php endif ?> >
    <?php if ( ! empty( $title ) ) : ?><label for="<?php echo $id ?>"><?php echo $title ?></label><?php endif; ?>
    <div class="editor"><?php wp_editor( $value, $id, $editor_args ); ?></div>
    <p><span class="desc"><?php echo $desc ?></span></p>
</div>