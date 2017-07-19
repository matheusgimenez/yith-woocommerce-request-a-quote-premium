<?php
$sidebar_action_hide_class = $this->is_collapsed() ? '' : 'hide-on-click';
$sidebar_action_hide_title = $this->is_collapsed() ? __( 'Show sidebar', 'yith-plugin-fw' ) : __( 'Hide sidebar', 'yith-plugin-fw' );
$sidebar_class             = $this->is_collapsed() ? 'yith-panel-sidebar-hidden' : '';
?>


<div id="yit-panel-sidebar" class="<?php echo $sidebar_class; ?>">
    <div id="yit-panel-sidebar-actions">
        <div id="yit-panel-sidebar-action-hide" class="<?php echo $sidebar_action_hide_class; ?>">
            <span class="yit-panel-sidebar-action-title"><?php echo $sidebar_action_hide_title; ?></span>
            <span class="yit-panel-sidebar-action-hide-icon dashicons dashicons-arrow-left"></span>
        </div>
    </div>
    <div id="yit-panel-sidebar-widgets-container">
        <?php
        $this->print_panel_sidebar_widgets();
        ?>
    </div>
</div>
