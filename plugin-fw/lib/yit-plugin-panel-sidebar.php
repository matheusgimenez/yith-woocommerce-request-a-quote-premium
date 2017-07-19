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

if ( !class_exists( 'YIT_Plugin_Panel_Sidebar' ) ) {
    /**
     * YIT Plugin Panel Sidebar
     *
     * @class      YIT_Plugin_Panel_Sidebar
     * @package    Yithemes
     * @since      1.0
     * @author     Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YIT_Plugin_Panel_Sidebar {
        /**
         * @var string version of class
         */
        public $version = '1.0.1';


        /**
         * @var array array of widgets
         */
        public $widgets = array();

        /**
         * @type string
         */
        protected $_remote_widget_xml = 'http://bit.ly/1UJ83xN';

        /**
         * default priority for Remote Widgets
         *
         * @type int
         */
        public $default_remote_widget_priority = 40;

        /**
         * parent panel
         *
         * @var YIT_Plugin_Panel
         */
        public $panel;

        private $_is_collapsed;

        /**
         * @type string
         */
        public static $transient_remote_widgets = 'yit_panel_sidebar_remote_widgets';

        /**
         * @type string
         */
        public static $transient_updated_remote_widgets = 'yit_panel_sidebar_remote_widgets_update';

        /**
         * @type string
         */
        public static $collapse_option = 'yith_plugin_panel_sidebar_collapse';

        /**
         * Constructor
         *
         * @param YIT_Plugin_Panel $panel the parent panel
         *
         * @since      1.0
         * @author     Leanza Francesco <leanzafrancesco@gmail.com>
         */
        private function __construct( $panel ) {
            $this->panel = $panel;

            /* load and sort default widgets and remote widgets */
            $this->load_widgets();

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_filter( 'yit_admin_panel_content_class', array( $this, 'filter_admin_panel_content_class' ) );
        }

        /**
         * get instance
         *
         * @static
         * @return YIT_Plugin_Panel_Sidebar
         *
         * @since  1.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public static function instance( $panel ) {
            return new self( $panel );
        }

        /**
         * return true if is collapsed by option
         *
         * @return bool
         */
        public function is_collapsed() {
            if ( !isset( $this->_is_collapsed ) ) {
                $this->_is_collapsed = get_option( self::$collapse_option, 'no' ) === 'yes';
            }

            return $this->_is_collapsed;
        }

        /**
         * if is collapsed add a class to panel wrapper
         *
         * @param $class
         *
         * @return string
         */
        public function filter_admin_panel_content_class( $class ) {
            if ( $this->is_collapsed() ) {
                $class .= ' yit-admin-panel-content-wrap-full';
            }

            return $class;
        }

        /**
         * Add one or more widgets to $this->widgets
         *
         * @param array $widgets
         */
        public function add_widgets( $widgets ) {
            $this->widgets = array_merge( $this->widgets, $widgets );
        }

        /**
         * delete transients
         */
        public static function delete_transients() {
            delete_transient( self::$transient_remote_widgets );
            delete_transient( self::$transient_updated_remote_widgets );
        }

        /**
         * filter and sort widgets
         */
        private function _filter_and_sort_widgets() {
            /* filter widgets */
            $page_name     = isset( $this->panel->settings[ 'page' ] ) ? $this->panel->settings[ 'page' ] : '';
            $this->widgets = apply_filters( 'yit_plugin_panel_sidebar_widgets', $this->widgets, $page_name );

            /*sort widgets*/
            uasort( $this->widgets, array( $this, 'sort_widgets' ) );
        }

        /**
         * get Remote Widget by XML from YIThemes
         *
         * @return array
         *
         * @since      1.0
         * @author     Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function get_remote_widgets() {
            $load_remote_widgets = apply_filters( 'yit_panel_sidebar_load_remote_widgets', true );
            if ( !$load_remote_widgets )
                return array();

            $remote_widgets = get_transient( self::$transient_remote_widgets );
            $updated        = get_transient( self::$transient_updated_remote_widgets );
            $is_debug       = defined( 'YIT_FW_REMOTE_WIDGETS_DEBUG' ) && YIT_FW_REMOTE_WIDGETS_DEBUG;

            if ( $is_debug || $updated === false || $remote_widgets === false ) {
                $remote_widgets = array();
            } else {
                return $remote_widgets;
            }

            $expiration         = 1 * DAY_IN_SECONDS;
            $updated_expiration = DAY_IN_SECONDS; // update frequency

            $remote_xml = wp_remote_get( $this->_remote_widget_xml );
            if ( !is_wp_error( $remote_xml ) && isset( $remote_xml[ 'response' ][ 'code' ] ) && '200' == $remote_xml[ 'response' ][ 'code' ] && class_exists( 'SimpleXmlElement' ) ) {
                try {
                    // suppress all XML errors when loading the document
                    libxml_use_internal_errors( true );

                    $xml_data           = new SimpleXmlElement( $remote_xml[ 'body' ] );
                    $xml_remote_widgets = isset( $xml_data->widget ) ? $xml_data->widget : array();

                    $enabled_args = array(
                        'title',
                        'icon',
                        'content',
                        'class',
                        'title_class',
                        'badge',
                        'badge_text',
                        'image',
                        'image_class',
                        'priority',
                        'starting',
                        'expiration',
                    );

                    $last_remote_priority = $this->default_remote_widget_priority;

                    foreach ( $xml_remote_widgets as $xml_widget ) {
                        if ( !isset( $xml_widget->id ) )
                            continue;

                        $widget_id    = (string) $xml_widget->id;
                        $widget_array = array();
                        foreach ( $enabled_args as $key ) {
                            if ( isset( $xml_widget->$key ) ) {
                                $widget_array[ $key ] = (string) $xml_widget->$key;
                            } else {
                                if ( $key == 'priority' ) {
                                    $widget_array[ $key ] = $last_remote_priority;
                                    $last_remote_priority += 10;
                                }
                            }
                        }
                        $remote_widgets[ $widget_id ] = $widget_array;
                    }

                    $xml_expiration = isset( $xml_data->expiration ) ? (string) $xml_data->expiration : '';
                    if ( !empty( $xml_expiration ) ) {
                        $expiration = strtotime( $xml_expiration ) - strtotime( 'now' );
                        // if the XML is expired removes widgets
                        if ( $expiration < 1 )
                            $remote_widgets = array();

                        $is_urgent = isset( $xml_data->urgent ) ? !!$xml_data->urgent : false;
                        $is_urgent = true;
                        if ( !$is_urgent ) {
                            $four_days_random = mt_rand( 0, 4 * DAY_IN_SECONDS );
                            $expiration += $four_days_random;
                        } else {
                            /**
                             * - - - - - U R G E N T - - - - -
                             * it will be updated the exact day, BUT in different time! :)
                             * [to prevent too many request at the same time]
                             */
                            $one_day_random = mt_rand( 0, DAY_IN_SECONDS );
                            $expiration += $one_day_random;
                        }
                    }

                    $four_days_random = mt_rand( 0, 4 * DAY_IN_SECONDS );

                    /* to prevent multiple request if it's expired */
                    if ( $expiration < 1 ) {
                        $expiration = 1 * DAY_IN_SECONDS + $four_days_random;
                    }
                } catch ( Exception $e ) {

                }

                //$updated_expiration = 30 * DAY_IN_SECONDS + $four_days_random;
            }

            set_transient( self::$transient_remote_widgets, $remote_widgets, $expiration );
            set_transient( self::$transient_updated_remote_widgets, true, $updated_expiration );

            return $remote_widgets;
        }

        /**
         * load and sort default widgets and remote widgets
         *
         * @since      1.0
         * @author     Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function load_widgets() {
            /* get static widgets */
            $this->widgets = include( YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/sidebar/widgets/widgets.php' );

            /* get remote widgets */
            $remote_widgets = $this->get_remote_widgets();
            $this->add_widgets( $remote_widgets );
        }

        /**
         * Print the panel sidebar
         *
         * @return void
         *
         * @since    1.0
         * @author   Leanza Francesco      <leanzafrancesco@gmail.com>
         */
        public function print_panel_sidebar() {
            do_action( 'yit_panel_before_sidebar' );

            include( YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/sidebar/sidebar.php' );

            do_action( 'yit_panel_after_sidebar' );
        }

        /**
         * Print the panel sidebar widgets
         *
         * @return void
         *
         * @since    1.0
         * @author   Leanza Francesco      <leanzafrancesco@gmail.com>
         */
        public function print_panel_sidebar_widgets() {
            $basename = YIT_CORE_PLUGIN_PATH;
            $path     = '/panel/sidebar/widget.php';

            $default_args = array(
                'id'                 => '',
                'title'              => '',
                'icon'               => '',
                'content'            => '',
                'class'              => '',
                'title_class'        => '',
                'template'           => '',
                'badge'              => '',
                'badge_text'         => '',
                'image'              => '',
                'image_class'        => '',
                'args'               => array(),
                'hide_if_empty_args' => '',
                'priority'           => 10,
                'starting'           => '',
                'expiration'         => '',
            );

            $this->_filter_and_sort_widgets();

            foreach ( $this->widgets as $widget_id => $widget ) {
                $args = array_merge( $widget, array( 'id' => $widget_id ) );
                $args = wp_parse_args( $args, $default_args );

                $is_started = empty( $args[ 'starting' ] ) || strtotime( $args[ 'starting' ] . ' midnight' ) <= strtotime( 'midnight' );
                $is_expired = !empty( $args[ 'expiration' ] ) && strtotime( $args[ 'expiration' ] . ' midnight' ) < strtotime( 'midnight' );

                if ( $is_expired || !$is_started )
                    continue;

                if ( !empty( $args[ 'hide_if_empty_args' ] ) ) {
                    $hide_if_empty_args = $args[ 'hide_if_empty_args' ];
                    $continue           = false;
                    foreach ( $hide_if_empty_args as $hide_if_empty_arg ) {
                        if ( empty( $args[ 'args' ][ $hide_if_empty_arg ] ) ) {
                            $continue = true;
                            break;
                        }
                    }
                    if ( $continue )
                        continue;
                }

                do_action( 'yit_panel_sidebar_before_widget', $widget_id, $widget );

                yit_plugin_get_template( $basename, $path, $args );

                do_action( 'yit_panel_sidebar_after_widget', $widget_id, $widget );
            }
        }

        /**
         * set transient for first activation
         * to prevent too many calls to YIThemes
         */
        public static function set_transient_for_first_activation() {
            $remote_widgets = get_transient( self::$transient_remote_widgets );
            $updated        = get_transient( self::$transient_updated_remote_widgets );

            $first_activation = $updated === false && $remote_widgets === false;
            if ( $first_activation ) {
                $seven_days_random = mt_rand( 0, 7 * DAY_IN_SECONDS );
                $expiration        = 1 * DAY_IN_SECONDS + $seven_days_random;

                set_transient( self::$transient_remote_widgets, array(), $expiration );
                set_transient( self::$transient_updated_remote_widgets, true, $expiration );
            }
        }

        /**
         * Sort widgets by priority
         *
         * @param $a
         * @param $b
         *
         * @return bool
         *
         * @since      1.0
         * @author     Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function sort_widgets( $a, $b ) {
            $priority_a = isset( $a[ 'priority' ] ) ? intval( $a[ 'priority' ] ) : $this->default_remote_widget_priority;
            $priority_b = isset( $b[ 'priority' ] ) ? intval( $b[ 'priority' ] ) : $this->default_remote_widget_priority;
            if ( $priority_a == $priority_b ) {
                return 0;
            } elseif ( $priority_a > $priority_b ) {
                return 1;
            } else {
                return -1;
            }
        }

        /**
         * Add Admin WC Style and Scripts
         *
         * @return void
         *
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com
         */
        public function admin_enqueue_scripts() {
            $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            wp_enqueue_style( 'opensans-font', '//fonts.googleapis.com/css?family=Open+Sans:400,500,600,700,800,100,200,300,900' );
            wp_enqueue_style( 'yit-plugin-sidebar-style', YIT_CORE_PLUGIN_URL . '/assets/css/yit-plugin-panel-sidebar.css', $this->version );
            wp_enqueue_script( 'yit-plugin-sidebar-js', YIT_CORE_PLUGIN_URL . '/assets/js/yit-plugin-panel-sidebar' . $min . '.js', array( 'jquery' ), $this->version, true );
            wp_localize_script( 'yit-plugin-sidebar-js', 'sidebar_labels', array(
                'hide_sidebar'  => __( 'Hide sidebar', 'yith-plugin-fw' ),
                'show_sidebar'  => __( 'Show sidebar', 'yith-plugin-fw' ),
                'wrapper_class' => 'yit-admin-panel-content-wrap',
            ) );
        }
    }
}