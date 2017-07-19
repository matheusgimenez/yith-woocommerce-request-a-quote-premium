<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * @var array $links
 */
$links = !empty( $links ) ? $links : array();

$link_default_args = array(
    'url'    => '',
    'title'  => '',
    'target' => '_blank'
);
?>

<ul class="yit-panel-sidebar-links-list">
    <?php foreach ( $links as $link ) {
        $link = wp_parse_args( $link, $link_default_args );
        $link = (object)$link;
        echo "<li><a href='$link->url' target='$link->target'>$link->title</a></li>";
    }
    ?>
</ul>
