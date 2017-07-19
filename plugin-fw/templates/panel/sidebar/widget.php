<?php
/**
 * @var string $id
 * @var string $title
 * @var string $content
 * @var string $class
 * @var string $title_class
 * @var string $template
 * @var string $badge
 * @var string $badge_text
 * @var string $image
 * @var array  $args
 * @var string $icon
 *
 */

if ( !empty( $template ) ) {
    ob_start();
    $basename = YIT_CORE_PLUGIN_PATH;
    $path     = '/panel/sidebar/widgets/widget-' . $template . '.php';
    yit_plugin_get_template( $basename, $path, $args );
    $content = ob_get_clean();
}

if ( !empty( $icon ) ) {
    $title_class .= ' yit-panel-sidebar-widget-icon ' . $icon . '-icon';
}

?>

<div id="yit-panel-sidebar-<?php echo $id ?>-widget" class="yit-panel-sidebar-widget-wrapper <?php echo $class; ?>">
    <div class="yit-panel-sidebar-widget-container">
        <?php if ( !empty( $title ) ): ?>
            <div class="yit-panel-sidebar-widget-title <?php echo $title_class; ?>">
                <?php echo $title; ?>
            </div>
        <?php endif; ?>
        <div class="yit-panel-sidebar-widget-content"><?php echo $content; ?></div>
        <?php
        if ( !empty( $image ) ) {
            $path = YIT_CORE_PLUGIN_URL . '/assets/images/widgets/' . $image;
            echo "<img class='yit-panel-sidebar-widget-image $image_class' src='{$path}' alt='' width='100%'/>";
        }
        ?>
    </div>
    <?php
    if ( !empty( $badge ) ) {
        $basename = YIT_CORE_PLUGIN_PATH;
        $path     = '/panel/sidebar/widgets/badges/badge.php';
        yit_plugin_get_template( $basename, $path, array( 'text' => $badge_text, 'type' => $badge ) );
    }
    ?>
</div>
