<?php
/**
 * Plugin Name: WpBooking
 * Plugin URI: wpbooking
 * Description: All in one Booking System
 * Version: 1.3
 * Author: wpbooking
 * Author URI: http://www.wpbooking.org
 * Requires at least: 4.1
 * Tested up to: 4.3
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: wpbooking
 * Domain Path: languages/
 *
 * @package wpbooking
 * @author shinetheme
 * @since 1.0
 */

 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('WPBooking_System') and !function_exists('WPBooking')) {
    class WPBooking_System
    {
        static $_inst = FALSE;

        private $_version = 1.0;

        /**
         * Get and Access Global Variable
         * @var array
         */
        protected $global_values = array();
        protected $_dir_path = FALSE;
        protected $_dir_url = FALSE;
        public $API_URL = 'https://wpbooking.org/wp-admin/admin-ajax.php';

        /**
         * @since 1.0
         */
        function __construct()
        {
            do_action('wpbooking_before_plugin_init');

            $this->_dir_path = plugin_dir_path(__FILE__);
            $this->_dir_url = plugin_dir_url(__FILE__);

            add_action('init', array($this, '_init'));
            add_action('admin_menu', array($this, '_admin_init_menu_page'));
            add_action('plugins_loaded', array($this, '_load_cores'));

            add_action('admin_enqueue_scripts', array($this, '_admin_default_scripts'));
            add_action('wp_enqueue_scripts', array($this, '_frontend_scripts'));

            do_action('wpbooking_after_plugin_init');
            add_action('activated_plugin', array($this, '_activation_redirect'));
        }


        function _activation_redirect($plugin)
        {
            if ($plugin == plugin_basename(__FILE__)) {
                $is_setup_demo = get_option("wpbooking_setup_demo", 'true');
                if ($is_setup_demo == "true") {
                    exit(wp_redirect(add_query_arg(array('page' => 'wpbooking_setup_page_settings'), admin_url("admin.php"))));
                }
            }
        }


        function _frontend_scripts()
        {
            /**
             * Css
             */
            wp_enqueue_style('font-awesome', wpbooking_assets_url('fa4.5/css/font-awesome.min.css'), FALSE, '4.5.0');
            wp_enqueue_style('fotorama', wpbooking_assets_url('fotorama4.6.4/fotorama.css'));
            /**
             * WPBooking Icon
             */
            wp_enqueue_style('wpbooking-icon', wpbooking_assets_url('my-icons-collection/font/flaticon.css'));
            /**
             * Magnific
             */
            wp_register_script('magnific', wpbooking_assets_url('magnific/jquery.magnific-popup.min.js'), array('jquery'), null, true);
            wp_register_style('magnific', wpbooking_assets_url('magnific/magnific-popup.css'));

            /**
             * Fotorama
             */
            wp_register_script('fotorama', wpbooking_assets_url('fotorama4.6.4/fotorama.js'), array('jquery'), null, true);
            wp_register_style('fotorama', wpbooking_assets_url('fotorama4.6.4/fotorama.css'));


            /**
             * OwlCarousel
             */
            wp_register_script('owlcarousel', wpbooking_assets_url('owl.carousel/owl.carousel.min.js'), array('jquery'), null, true);
            wp_register_style('owlcarousel', wpbooking_assets_url('owl.carousel/assets/owl.carousel.css'));

            /**
             * Select2 CSS
             */
            wp_enqueue_style('wpbooking-select2', wpbooking_assets_url('select2/css/select2.min.css'));
            wp_enqueue_style('jquery-ui-datepicker', wpbooking_assets_url('css/datepicker.css'));
            if (is_singular('wpbooking_service')) {
                wp_enqueue_style('magnific');
                wp_enqueue_script('magnific');
                wp_enqueue_style('fororama');
                wp_enqueue_script('fotorama');
            }
            wp_enqueue_style('wpbooking', wpbooking_assets_url('css/wpbooking-booking.css'), array( 'owlcarousel', 'wpbooking-icon'));

            /**
             * Ion RangeSlider for Price Search Field
             * @author dungdt
             * @since 1.0
             */
            wp_register_script('ion-range-slider', wpbooking_assets_url('ion-range-slider/js/ion.rangeSlider.min.js'), array('jquery'), null, true);
            wp_register_style('ion-range-slider', wpbooking_assets_url('ion-range-slider/css/ion.rangeSlider.css'));
            wp_register_style('ion-range-slider-flatui', wpbooking_assets_url('ion-range-slider/css/ion.rangeSlider.skinFlat.css'));
            wp_register_style('ion-range-slider-html5', wpbooking_assets_url('ion-range-slider/css/ion.rangeSlider.skinHTML5.css'));


            /**
             * Javascripts
             */
            wp_enqueue_script('fotorama-js', wpbooking_assets_url('fotorama4.6.4/fotorama.js'), array('jquery'), null, true);
            $google_api_key = wpbooking_get_option('google_api_key','AIzaSyAwXoW3vyBK0C5k2G-0l1D3n10UJ3LwZ3k');
            wp_enqueue_script('google-map-js', '//maps.googleapis.com/maps/api/js?libraries=places&sensor=false&key='.$google_api_key, array('jquery'), null, true);
            wp_enqueue_script('gmap3.min-js', wpbooking_assets_url('js/gmap3.min.js'), array('jquery'), null, true);


            /**
             * Moment Js
             */
            wp_register_script('moment', wpbooking_admin_assets_url('js/moment.min.js'), array(), null, true);

            /**
             * Select2 Jquery
             */
            wp_enqueue_script('wpbooking-select2', wpbooking_assets_url('select2/js/select2.full.min.js'), array('jquery'), null, true);

            wp_enqueue_script('wpbooking-nicescroll', wpbooking_assets_url('js/nicescroll.js'), array('jquery'), null, true);

            wp_enqueue_script('wpbooking-booking', wpbooking_assets_url('js/wpbooking-booking.js'), array('jquery', 'jquery-ui-datepicker', 'owlcarousel', 'moment'), null, true);


            wp_localize_script('jquery', 'wpbooking_params', array(
                'ajax_url'              => admin_url('admin-ajax.php'),
                'wpbooking_security'    => wp_create_nonce('wpbooking-nonce-field'),
                'select_comment_review' => esc_html__('Please rate the criteria of this accommodation.', 'wpbooking'),
                'currency_symbol'=>WPBooking_Currency::get_current_currency('symbol'),
                'currency_position'=> WPBooking_Currency::get_current_currency('position'),
                'thousand_separator'=> WPBooking_Currency::get_current_currency('thousand_sep'),
                'decimal_separator'=> WPBooking_Currency::get_current_currency('decimal_sep'),
                'currency_precision'=>WPBooking_Currency::get_current_currency('decimal'),
            ));

            wp_localize_script('jquery', 'wpbooking_hotel_localize', array(
                'booking_required_adult'          => esc_html__('Please select the number of adults ', 'wpbooking'),
                'booking_required_children'       => esc_html__('Please select the number of children ', 'wpbooking'),
                'booking_required_adult_children' => esc_html__('Please select the number of adults and children', 'wpbooking'),
                'is_not_select_date'              => esc_html__('To see price details, please select check-in and check-out date.', 'wpbooking'),
                'is_not_select_check_in_date'     => esc_html__('Please select check-in date.', 'wpbooking'),
                'is_not_select_check_out_date'    => esc_html__('Please select check-out date.', 'wpbooking'),
                'loading_url'     => admin_url('/images/wpspin_light.gif'),
            ));
        }

        /**
         * Load default CSS and Javascript for admin
         * @since 1.0
         */
        function _admin_default_scripts()
        {

            /**
             * WPBooking Icon
             */
            wp_enqueue_style('wpbooking-icon', wpbooking_assets_url('my-icons-collection/font/flaticon.css'));

            /**
             * JQuery Sticky
             *
             */
            wp_register_script('sticky',wpbooking_assets_url('admin/js/jquery.sticky.js'),array('jquery'),null,true);
            /**
             * Code Flask
             */
            wp_register_style('prismjs', wpbooking_assets_url('codeflask/themes/prism.css'));
            wp_register_style('codeflask', wpbooking_assets_url('codeflask/codeflask.css'),array('prismjs'));
            wp_register_script('prismjs', wpbooking_assets_url('codeflask/prism.js'), array(), null, true);
            wp_register_script('codeflask', wpbooking_assets_url('codeflask/codeflask.js'), array('prismjs'), null, true);
            wp_register_script('bootstrap', wpbooking_assets_url('bootstrap/js/bootstrap.min.js'), array('jquery'), null, true);

            /**
             * Icon Picker
             */
            wp_register_script('iconpicker', wpbooking_assets_url('iconpicker/js/fontawesome-iconpicker.min.js'), array('jquery'), null, true);



            /**
             * Select2 Jquery
             */
            wp_enqueue_script('wpbooking-select2', wpbooking_assets_url('select2/js/select2.full.min.js'), array('jquery'), null, true);
            wp_enqueue_style('wpbooking-select2', wpbooking_assets_url('select2/css/select2.min.css'));

            /**
             * wbCalendar
             */
            wp_enqueue_script('wbCalendar', wpbooking_assets_url('js/wb-calendar.js'), array('jquery'), null, true);
            wp_enqueue_style('wbCalendar', wpbooking_assets_url('css/wb-calendar.css'));

            /**
             * Chart Report
             */
            wp_register_script('chart', wpbooking_assets_url('js/Chart.min.js'), array('jquery'), null, true);

            /**
             * Js Color
             */
            wp_enqueue_script('colorpicker-master', wpbooking_assets_url('colorpicker-master/colors.js'), array('jquery'), null, true);

            wp_enqueue_style('colorpicker-master', wpbooking_assets_url('colorpicker-master/mod.css'));


            /**
             * Flag icon
             * @since 1.0
             * @author dungdt
             *
             */

            wp_enqueue_script('wpbooking-admin', wpbooking_admin_assets_url('js/wpbooking-admin.js'), array('jquery', 'bootstrap', 'jquery-ui-core', 'iconpicker', 'jquery-ui-datepicker', 'jquery-ui-accordion','wpbooking-calendar-room','sticky'), null, true);
            wp_enqueue_script('wpbooking-admin-form-build', wpbooking_admin_assets_url('js/wpbooking-admin-form-build.js'), array('jquery'), null, true);

            wp_enqueue_script('moment-js', wpbooking_admin_assets_url('js/moment.min.js'), array('jquery'), null, true);

            wp_enqueue_script('full-calendar', wpbooking_admin_assets_url('js/fullcalendar.min.js'), array('jquery', 'moment-js'), null, true);

            wp_enqueue_script('fullcalendar-lang', wpbooking_admin_assets_url('/js/lang-all.js'), array('jquery'), null, true);

            wp_enqueue_script('wpbooking-calendar-room', wpbooking_admin_assets_url('js/wpbooking-calendar-room.js'), array('jquery', 'jquery-ui-datepicker'), null, true);


            //Popover
            wp_register_style('popover', wpbooking_assets_url('bootstrap/less/popovers.css'));


            // Admin Fonts
            $fonts = add_query_arg(array(
                'family' => 'Open+Sans:700,800',
                'subset' => 'vietnamese',
            ), 'https://fonts.googleapis.com/css');

            wp_enqueue_style('open-sans-bold', $fonts);
            wp_enqueue_style('iconpicker', wpbooking_assets_url('iconpicker/css/fontawesome-iconpicker.min.css'));
            wp_enqueue_style('full-calendar', wpbooking_admin_assets_url('/css/fullcalendar.min.css'), FALSE, '1.1.6');

            wp_enqueue_style('font-awesome', wpbooking_assets_url('fa4.5/css/font-awesome.min.css'), FALSE, '4.5.0');
            wp_enqueue_style('wpbooking-admin', wpbooking_admin_assets_url('css/admin.css'), array('wpbooking-icon'));
            wp_enqueue_style('wpbooking-admin-form-build', wpbooking_admin_assets_url('css/wpbooking-admin-form-build.css'));


            wp_localize_script('jquery', 'wpbooking_params', array(
                'ajax_url'           => admin_url('admin-ajax.php'),
                'api_url'            => $this->API_URL,
                'wpbooking_security' => wp_create_nonce('wpbooking-nonce-field'),
                'delete_confirm'     => esc_html__('Do you want to delete?', 'wpbooking'),
                'delete_string'      => esc_html__('delete', 'wpbooking'),
                'delete_gallery'      => esc_html__('Do you want to delete all image?', 'wpbooking'),
                'room' => esc_html__('room','wpbooking'),
                'rooms' => esc_html__('rooms','wpbooking'),
                'delete_permanently_image' => esc_html__('You want to delete this image permanently?','wpbooking'),
                'next' => esc_html__('Next','wpbooking'),
                'prev' => esc_html__('Prev','wpbooking'),
                'read_more' => esc_html__('Read More','wpbooking'),
                'room_name'=>esc_html__('Room Name:','wpbooking')
            ));

        }

        function _load_cores()
        {
            $files = array(
                'cores/config',
                'cores/model',
                'cores/controllers',
                'cores/loader',
            );
            $this->load($files);

        }

        /**
         * @since 1.0
         */

        function _init()
        {
            load_plugin_textdomain('wpbooking', FALSE, plugin_basename(dirname(__FILE__)) . '/languages');


        }

        /**
         * @since 1.0
         */
        function _admin_init()
        {
            $plugin = get_plugin_data(__FILE__);
            $this->_version = $plugin['Version'];

        }

        /**
         * Get Version Plugin
         *
         * @since 1.2
         * @author quandq
         *
         * @return mixed
         */
        function get_version_plugin()
        {
            $plugin = get_plugin_data(__FILE__);
            return $plugin['Version'];
        }

        function _admin_init_menu_page()
        {

            $menu_page = $this->get_menu_page();
            add_menu_page(
                $menu_page['page_title'],
                $menu_page['menu_title'],
                $menu_page['capability'],
                $menu_page['menu_slug'],
                $menu_page['function'],
                $menu_page['icon_url'],
                $menu_page['position']
            );
        }

        /**
         * @since 1.0
         * @param $file
         * @param bool|FALSE $include_once
         */
        function load($file, $include_once = FALSE)
        {
            if (is_array($file)) {
                if (!empty($file)) {
                    foreach ($file as $value) {
                        $this->load($value, $include_once);
                    }
                }
            } else {
                $file = $this->get_dir('shinetheme/' . $file . '.php');
                if (!$file) {

                }
                if (file_exists($file)) {
                    if ($include_once) include_once($file);
                    include($file);
                }
            }

        }

        /**
         * @since 1.0
         * @param bool|FALSE $file
         * @return string
         */
        function get_dir($file = FALSE)
        {
            return $this->_dir_path . $file;
        }

        /**
         * @since 1.0
         * @param bool|FALSE $file
         * @return string
         */
        function get_url($file = FALSE)
        {
            return $this->_dir_url . $file;
        }

        function get_menu_page()
        {
            $page = apply_filters('wpbooking_menu_page_args', array(
                'page_title' => esc_html__("WPBooking", 'wpbooking'),
                'menu_title' => esc_html__("WPBooking", 'wpbooking'),
                'capability' => 'manage_options',
                'menu_slug'  => 'wpbooking',
                'function'   => array($this, '_show_default_page'),
                'icon_url'   => 'dashicons-analytics',
                'position'   => 55
            ));

            return $page;

        }

        function _show_default_page()
        {
            do_action('wpbooking_default_menu_page');
        }

        function set_admin_message($message, $type = 'information')
        {
            $_SESSION['message']['admin'] = array(
                'content' => $message,
                'type'    => $type
            );
        }

        function set_message($message, $type = 'information')
        {
            $_SESSION['message']['frontend'] = array(
                'content' => $message,
                'type'    => $type
            );
        }

        function get_message($clear_message = TRUE)
        {
            $message = isset($_SESSION['message']['frontend']) ? $_SESSION['message']['frontend'] : FALSE;
            if ($clear_message) $_SESSION['message']['frontend'] = array();

            return $message;
        }

        function get_admin_message($clear_message = TRUE)
        {
            $message = isset($_SESSION['message']['admin']) ? $_SESSION['message']['admin'] : FALSE;
            if ($clear_message) $_SESSION['message']['admin'] = array();

            return $message;
        }

        /**
         * Set Global Variable
         *
         * @since 1.0
         * @param $name
         * @param $value
         */
        function set($name, $value)
        {
            $this->global_values[$name] = $value;
        }

        /**
         * Get Global Variable
         *
         * @since 1.0
         * @param $name
         * @param bool|FALSE $default
         * @return bool
         */
        function get($name, $default = FALSE)
        {
            return isset($this->global_values[$name]) ? $this->global_values[$name] : $default;
        }

        /**
         * @return WPBooking_System
         */
        static function inst()
        {

            if (!self::$_inst) {
                self::$_inst = new self();
            }

            return self::$_inst;
        }
    }

    /**
     * @since 1.0
     */
    function WPBooking()
    {
        return WPBooking_System::inst();
    }

    WPBooking();
}