<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * @var YIT_Plugin_Panel_Sidebar $this
 */

$widgets = array(
    'membership' => array(
        'title'       => __( 'Join the club', 'yit' ),
        'title_class' => 'orange',
        'icon'        => 'box-white',
        'template'    => 'membership',
        'priority'    => 10,
    ),
    'despacho'   => array(
        'title'       => __( 'Despacho Theme - 100% FREE', 'yit' ),
        'icon'        => 'info',
        'template'    => 'despacho',
        'badge'       => 'gift-tape',
        'badge_text'  => __( 'FREE!', 'yit' ),
        'image'       => 'despacho.png',
        'image_class' => 'yit-panel-sidebar-widget-despacho-image',
        'priority'    => 20,
    ),
    'links'      => array(
        'title'              => __( 'Important Links', 'yit' ),
        'icon'               => 'link',
        'template'           => 'links',
        'args'               => array( 'links' => $this->panel->links ),
        'hide_if_empty_args' => array( 'links' ),
        'priority'           => 30,
    ),
);
return apply_filters( 'yit_panel_widgets_array', $widgets );