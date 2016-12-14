<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 8/10/2016
 * Time: 3:47 PM
 */
if (!class_exists('WPBooking_Tour_Service_Type') and class_exists('WPBooking_Abstract_Service_Type')) {
    class WPBooking_Tour_Service_Type extends WPBooking_Abstract_Service_Type
    {
        static $_inst = false;

        protected $type_id = 'tour';

        function __construct()
        {
            $this->type_info = array(
                'label' => __("Tour", 'wpbooking'),
                'desc'  => esc_html__('Tour Booking', 'wpbooking')
            );

            $this->settings = array(

                array(
                    'id'    => 'title',
                    'label' => __('Layout', 'wpbooking'),
                    'type'  => 'title',
                ),
                array(
                    'id'    => 'posts_per_page',
                    'label' => __("Item per page", 'wpbooking'),
                    'type'  => 'number',
                    'std'   => 10
                ),
                array(
                    'id'    => "thumb_size",
                    'label' => __("Thumb Size", 'travel-booking'),
                    'type'  => 'image-size'
                ),
                array(
                    'id'    => "gallery_size",
                    'label' => __("Gallery Size", 'travel-booking'),
                    'type'  => 'image-size'
                ),
            );

            parent::__construct();


            add_filter('wpbooking_archive_loop_image_size', array($this, '_apply_thumb_size'), 10, 3);


            /**
             * Register metabox fields
             *
             * @since 1.0
             * @author dungdt
             */
            add_action('init', array($this, '_register_meta_fields'));


            /**
             * Register Tour Type
             *
             * @since 1.0
             * @author dungdt
             */
            add_action('init', array($this, '_register_tour_type'));

            /**
             * Change Base Price Format
             *
             * @since 1.0
             * @author dungdt
             */
            add_action('wpbooking_service_base_price_html_' . $this->type_id, array($this, '_edit_price'), 10, 4);

            /**
             * Filter to Validate Add To Cart
             *
             * @since 1.0
             * @author dungdt
             */
            add_filter('wpbooking_add_to_cart_validate_' . $this->type_id, array($this, '_add_to_cart_validate'), 10, 4);

            /**
             * Show More info in Cart Total Box
             *
             * @since 1.0
             * @author dungdt
             */
            add_action('wpbooking_check_total_item_information_' . $this->type_id, array($this, '_add_total_box_info'));


            /**
             * Show More info in Order Total Box
             *
             * @since 1.0
             * @author dungdt
             *
             */
            add_action('wpbooking_order_detail_total_item_information_' . $this->type_id, array($this, '_add_order_total_box_info'));

            /**
             * Filter to Change Cart Params
             *
             * @since 1.0
             * @author dungdt
             */
            add_filter('wpbooking_cart_item_params_' . $this->type_id, array($this, '_change_cart_params'), 10, 2);


            /**
             * Change Cart Total Price
             *
             * @since 1.0
             * @author dungdt
             */
            add_action('wpbooking_get_cart_total_' . $this->type_id, array($this, '_change_cart_total'), 10, 4);

            /**
             * Show Tour Info
             *
             * @since 1.0
             * @author dungdt
             */
            add_action('wpbooking_review_after_address_' . $this->type_id, array($this, '_show_review_tour_info'));

            /**
             * Show Order Info after Address
             *
             * @since 1.0
             * @author dungdt
             */
            add_action('wpbooking_order_detail_after_address_' . $this->type_id, array($this, '_show_order_info_after_address'));


            /**
             * Show More Order Info for Email
             *
             * @since 1.0
             * @author dungdt
             */
            add_action('wpbooking_email_order_after_address_' . $this->type_id, array($this, '_show_email_order_info_after_address'));

        }

        /**
         * Show More info in Cart Total Box
         *
         * @since 1.0
         * @author dungdt
         *
         * @param array $cart
         */
        public function _add_total_box_info($cart)
        {
            if ($cart['price']) {
                echo '<span class="total-title">' . esc_html__('Tour Price:', 'wpbooking') . '</span>
                      <span class="total-amount">' . WPBooking_Currency::format_money($cart['price']) . '</span>';

            }
        }

        /**
         * Show More info in Order Total Box
         *
         * @since 1.0
         * @author dungdt
         *
         * @param array $order_data
         */
        public function _add_order_total_box_info($order_data)
        {
            if (!empty($order_data['raw_data'])) {
                $raw_data = json_decode($order_data['raw_data'], true);
                if ($raw_data) {
                    $raw_data['price'] = $order_data['price'] - $order_data['tax_total'];
                    $this->_add_total_box_info($raw_data);
                }
            }
        }

        /**
         * Change Cart Total Price
         *
         * @since 1.0
         * @author dungdt
         *
         * @param $price
         * @param $cart
         *
         * @return float
         */
        public function _change_cart_total($price, $cart)
        {

            $cart = wp_parse_args($cart, array(
                'pricing_type'    => '',
                'adult_number'    => '',
                'children_number' => '',
                'infant_number'   => '',
                'calendar'        => array()
            ));

            switch ($cart['pricing_type']) {
                case "per_unit":
                    if (!empty($cart['calendar']['calendar_price'])) {
                        $price = $cart['calendar']['calendar_price'];
                    }
                    break;
                case "per_person":
                default:
                    if (!empty($cart['calendar']) and is_array($cart['calendar'])) {
                        $calendar = wp_parse_args($cart['calendar'], array(
                            'adult_price'  => '',
                            'child_price'  => '',
                            'infant_price' => ''
                        ));
                        $price = 0;
                        if (!empty($cart['adult_number'])) {
                            $price += $calendar['adult_price'] * $cart['adult_number'];
                        }
                        if (!empty($cart['children_number'])) {
                            $price += $calendar['child_price'] * $cart['children_number'];
                        }
                        if (!empty($cart['infant_number'])) {
                            $price += $calendar['infant_price'] * $cart['infant_number'];
                        }
                    }
                    break;
                    break;
            }


            return $price;
        }

        /**
         * Show Order Info after Address
         *
         * @since 1.0
         * @author dungdt
         *
         * @param $order_data
         */
        public function _show_order_info_after_address($order_data)
        {
            if (!empty($order_data['raw_data'])) {
                $raw_data = json_decode($order_data['raw_data'], true);
                if ($raw_data) {
                    $raw_data['price'] = 0;
                    $this->show_review_tour_info($raw_data, false);
                }
            }
        }

        /**
         * Show More Order Info for Email
         *
         * @since 1.0
         * @author dungdt
         *
         * @param $order_data
         */
        public function _show_email_order_info_after_address($order_data)
        {
            if (!empty($order_data['raw_data'])) {
                $raw_data = json_decode($order_data['raw_data'], true);
                if ($raw_data) {
                    $raw_data['price'] = 0;
                    $this->show_review_tour_info($raw_data, false);
                }
            }
        }

        /**
         * To show Tour More information
         * @param $cart
         */
        protected function show_review_tour_info($cart, $is_checkout = true)
        {

            // Price
            if (!empty($cart['price'])) {
                printf('<span class="review-order-item-price tour-price">%s</span>', WPBooking_Currency::format_money($cart['price']));
            }


            $contact_meta = array(
                'contact_number' => 'fa-phone',
                'contact_email'  => 'fa-envelope',
                'website'        => 'fa-home',
            );
            $html = '';
            foreach ($contact_meta as $key => $val) {
                if ($value = get_post_meta($cart['post_id'], $key, true)) {
                    switch ($key) {
                        case 'contact_number':
                            $value = sprintf('<a href="tel:%s">%s</a>', esc_html($value), esc_html($value));
                            break;

                        case 'contact_email':
                            $value = sprintf('<a href="mailto:%s">%s</a>', esc_html($value), esc_html($value));
                            break;
                        case 'website';
                            $value = '<a target=_blank href="' . $value . '">' . $value . '</a>';
                            break;
                    }
                    $html .= '<li class="wb-meta-contact">
                                    <i class="fa ' . $val . ' wb-icon-contact"></i>
                                    <span>' . $value . '</span>
                                </li>';
                }
            }
            if (!empty($html)) {
                echo '<ul class="wb-contact-list">' . $html . '</ul>';
            }

            // From
            if (!empty($cart['check_in_timestamp'])) {
                $from_detail = date_i18n(get_option('date_format'), $cart['check_in_timestamp']);
                if (!empty($cart['duration'])) {
                    $from_detail .= ' (' . $cart['duration'] . ')';
                }
                printf('<div class="from-detail"><span class="head-item">%s:</span> <span class="from-detail-duration">%s</span></div>', esc_html__('From', 'wpbooking'), $from_detail);
            }
            switch ($cart['pricing_type']) {
                case "per_unit":
                    if (!empty($cart['adult_number'])) {
                        printf('<div class="people-price-item"><span class="head-item">%s:</span> <span class="price-item">%d</span></div>', esc_html__('Adult(s)', 'wpbooking'), $cart['adult_number']);
                    }
                    if (!empty($cart['children_number'])) {
                        printf('<div class="people-price-item"><span class="head-item">%s:</span> <span class="price-item">%d</span></div>', esc_html__('Children', 'wpbooking'), $cart['children_number']);
                    }
                    if (!empty($cart['infant_number'])) {
                        printf('<div class="people-price-item"><span class="head-item">%s:</span> <span class="price-item">%d</span></div>', esc_html__('Infant(s)', 'wpbooking'), $cart['infant_number']);
                    }
                    break;
                case "per_person":
                default:
                    if (!empty($cart['calendar'])) {
                        $calendar = wp_parse_args($cart['calendar'], array(
                            'adult_price'  => '',
                            'child_price'  => '',
                            'infant_price' => ''
                        ));
                        if (!empty($cart['adult_number'])) {
                            printf('<div class="people-price-item"><span class="head-item">%s:</span> <span class="price-item">%d x %s = %s</span></div>', esc_html__('Adult(s)', 'wpbooking'), $cart['adult_number'], WPBooking_Currency::format_money($calendar['adult_price']), WPBooking_Currency::format_money($calendar['adult_price'] * $cart['adult_number']));
                        }
                        if (!empty($cart['children_number'])) {
                            printf('<div class="people-price-item"><span class="head-item">%s:</span> <span class="price-item">%d x %s = %s</span></div>', esc_html__('Children', 'wpbooking'), $cart['children_number'], WPBooking_Currency::format_money($calendar['child_price']), WPBooking_Currency::format_money($calendar['child_price'] * $cart['children_number']));
                        }
                        if (!empty($cart['infant_number'])) {
                            printf('<div class="people-price-item"><span class="head-item">%s:</span> <span class="price-item">%d x %s = %s</span></div>', esc_html__('Infant(s)', 'wpbooking'), $cart['infant_number'], WPBooking_Currency::format_money($calendar['infant_price']), WPBooking_Currency::format_money($calendar['infant_price'] * $cart['infant_number']));
                        }
                    }
                    break;
            }

            if ($is_checkout) {
                $url_change_date = add_query_arg(array(
                    'start_date' => $cart['check_in_timestamp'],
                ), get_permalink($cart['post_id']));
                ?>
                <small><a href="<?php echo esc_url($url_change_date) ?>"
                          class="change-date"><?php esc_html_e("Change Date", "wpbooking") ?></a></small>
                <?php
            }
        }

        /**
         * Callback to show Tour Info
         *
         * @since 1.0
         * @author dungdt
         */
        public function _show_review_tour_info($cart)
        {
            $cart['price'] = WPBooking_Checkout_Controller::inst()->get_cart_total();

            $this->show_review_tour_info($cart);
        }

        /**
         * Callback to Change Cart Params
         *
         * @since 1.0
         * @author dungdt
         *
         * @param $cart_params
         * @param $post_id
         * @return mixed
         */
        public function _change_cart_params($cart_params, $post_id)
        {

            $cart_params['check_in_timestamp'] = $this->post('wb-departure-date');
            $cart_params['adult_number'] = $this->post('adult_number');
            $cart_params['children_number'] = $this->post('children_number');
            $cart_params['infant_number'] = $this->post('infant_number');
            $cart_params['pricing_type'] = get_post_meta($post_id, 'pricing_type', true);
            $cart_params['duration'] = get_post_meta($post_id, 'duration', true);

            $cart_params['calendar'] = $this->get_available_data($post_id, $cart_params['check_in_timestamp']);

            return $cart_params;
        }

        /**
         * Filter to Validate Add To Cart
         *
         * @since 1.0
         * @author dungdt
         *
         * @param $is_validated
         * @param $service_type
         * @param $post_id
         * @param $cart_params
         * @return mixed
         */
        public function _add_to_cart_validate($is_validated, $service_type, $post_id, $cart_params)
        {
            $service = wpbooking_get_service($post_id);
            $start = $cart_params['check_in_timestamp'];
            $calendar = WPBooking_Calendar_Model::inst();
            global $wpdb;

            switch ($service->get_meta('pricing_type')) {
                case "per_unit":
                    $query = $calendar->select($wpdb->prefix . 'wpbooking_availability.id,
	' . $wpdb->prefix . 'wpbooking_service.max_guests,calendar_minimum,calendar_maximum,SUM(adult_number + children_number + infant_number) AS total_people_booked,start,calendar_price')
                        ->join('wpbooking_service', "wpbooking_service.post_id = wpbooking_availability.post_id")
                        ->join('wpbooking_order', "wpbooking_order.post_id = wpbooking_availability.post_id AND check_in_timestamp = `start` and wpbooking_order.STATUS NOT IN ('cancelled','refunded','trash','payment_failed')", 'left')
                        ->where(array(
                            $wpdb->prefix . 'wpbooking_availability.post_id' => $post_id,
                            $wpdb->prefix . 'wpbooking_availability.status'  => 'available',
                            'start'                                          => $start,
                        ))
                        ->groupby($wpdb->prefix . 'wpbooking_availability.id')
                        ->having(' total_people_booked IS NULL OR total_people_booked < max_guests')
                        ->get()->row();
                    if (!$query) {
                        $is_validated = false;
                        wpbooking_set_message(esc_html__('Sorry! This tour is not available at your selected time', 'wpbooking'), 'error');
                    } else {
                        $total_people = $cart_params['adult_number'] + $cart_params['children_number'] + $cart_params['infant_number'];


                        if (!empty($total_people)) {
                            $is_validated = true;
                            wpbooking_set_message(esc_html__('This tour require at least 1 person', 'wpbooking'), 'error');
                        } else {
                            // Check Slot(s) Remain
                            // Check Slot(s) Remain
                            if ($total_people + $query['total_people_booked'] > $query['max_guests']) {
                                $is_validated = false;
                                wpbooking_set_message(sprintf(esc_html__('This tour only remain availability for %d people', 'wpbooking'), $query['max_guests'] - $query['total_people_booked']), 'error');
                            } else {
                                // Check Max, Min
                                $min = (int)$query['calendar_minimum'];
                                $max = (int)$query['calendar_maximum'];
                                if ($min <= $max) {
                                    if ($min) {
                                        if ($total_people < $min) {
                                            $is_validated = false;
                                            wpbooking_set_message(sprintf(esc_html__('Minimum Travelers must be %d', 'wpbooking'), $min), 'error');
                                        }
                                    }
                                    if ($max) {
                                        if ($total_people > $max) {
                                            $is_validated = false;
                                            wpbooking_set_message(sprintf(esc_html__('Maximum Travelers must be %d', 'wpbooking'), $max), 'error');
                                        }
                                    }
                                }
                            }
                        }


                    }
                    break;

                case "per_person":
                default:
                    $query = $query = $calendar->select($wpdb->prefix . 'wpbooking_availability.id,
                                                ' . $wpdb->prefix . 'wpbooking_service.max_guests,calendar_maximum,SUM(adult_number + children_number + infant_number) AS total_people_booked,start,
                                                ' . $wpdb->prefix . 'wpbooking_availability.adult_price,
                                                ' . $wpdb->prefix . 'wpbooking_availability.child_price,
                                                ' . $wpdb->prefix . 'wpbooking_availability.infant_price,
                                                adult_minimum,
                                                child_minimum,
                                                infant_minimum
')
                        ->join('wpbooking_service', "wpbooking_service.post_id = wpbooking_availability.post_id")
                        ->join('wpbooking_order', "wpbooking_order.post_id = wpbooking_availability.post_id AND check_in_timestamp = `start` and wpbooking_order.STATUS NOT IN ('cancelled','refunded','trash','payment_failed')", 'left')
                        ->where(array(
                            $wpdb->prefix . 'wpbooking_availability.post_id' => $post_id,
                            $wpdb->prefix . 'wpbooking_availability.status'  => 'available',
                            'start'                                          => $start,
                        ))
                        ->where("({$wpdb->prefix}wpbooking_availability.adult_price > 0 or {$wpdb->prefix}wpbooking_availability.child_price>0 or {$wpdb->prefix}wpbooking_availability.infant_price>0)", false, true)
                        ->groupby($wpdb->prefix . 'wpbooking_availability.id')
                        ->having(' total_people_booked IS NULL OR total_people_booked < max_guests')
                        ->get()->row();
                    if (!$query) {
                        $is_validated = false;
                        wpbooking_set_message(esc_html__('Sorry! This tour is not available at your selected time', 'wpbooking'), 'error');
                    } else {
                        $total_people = $cart_params['adult_number'] + $cart_params['children_number'] + $cart_params['infant_number'];

                        // Check Slot(s) Remain
                        if ($total_people + $query['total_people_booked'] > $query['max_guests']) {
                            $is_validated = false;
                            wpbooking_set_message(sprintf(esc_html__('This tour only remain availability for %d people', 'wpbooking'), $query['max_guests'] - $query['total_people_booked']), 'error');
                        } else {

                            $error_message = array();

                            if ((!empty($query['adult_minimum']) and $cart_params['adult_number'] < $query['adult_minimum'])) {
                                $error_message[] = sprintf(esc_html__('%d adult(s)', 'wpbooking'), $query['adult_minimum']);
                            }
                            if ((!empty($query['child_minimum']) and $cart_params['children_number'] < $query['child_minimum'])) {
                                $error_message[] = sprintf(esc_html__('%d children', 'wpbooking'), $query['child_minimum']);
                            }
                            if ((!empty($query['infant_minimum']) and $cart_params['infant_number'] < $query['infant_minimum'])) {
                                $error_message[] = sprintf(esc_html__('%d infant(s)', 'wpbooking'), $query['infant_minimum']);
                            }

                            if (!empty($error_message)) {
                                $is_validated = false;
                                wpbooking_set_message(sprintf(esc_html__('This tour require at least %s', 'wpbooking'), implode(', ', $error_message)), 'error');
                            } elseif (!$total_people) {
                                $is_validated = false;
                                wpbooking_set_message(esc_html__('This tour require at least 1 person', 'wpbooking'), 'error');
                            }

                        }


                    }
                    break;
            }

            return $is_validated;
        }

        /**
         * Get Available Data for Specific
         *
         * @param $post_id
         * @param int $start (timestamp)
         * @return mixed
         */
        public function get_available_data($post_id, $start)
        {

            $service = wpbooking_get_service($post_id);
            $calendar = WPBooking_Calendar_Model::inst();
            global $wpdb;

            switch ($service->get_meta('pricing_type')) {
                case "per_unit":
                    $query = $calendar->select($wpdb->prefix . 'wpbooking_availability.id,
	' . $wpdb->prefix . 'wpbooking_service.max_guests,calendar_maximum,SUM(adult_number + children_number + infant_number) AS total_people_booked,start,calendar_price')
                        ->join('wpbooking_service', "wpbooking_service.post_id = wpbooking_availability.post_id")
                        ->join('wpbooking_order', "wpbooking_order.post_id = wpbooking_availability.post_id and check_in_timestamp=`start` and wpbooking_order.STATUS NOT IN ('cancelled','refunded','trash','payment_failed')", 'left')
                        ->where(array(
                            $wpdb->prefix . 'wpbooking_availability.post_id' => $post_id,
                            $wpdb->prefix . 'wpbooking_availability.status'  => 'available',
                            'start'                                          => $start,
                        ))
                        ->groupby($wpdb->prefix . 'wpbooking_availability.id')
                        ->having(' total_people_booked IS NULL OR total_people_booked < max_guests')
                        ->get()->row();

                    return $query;
                    break;

                case "per_person":
                default:
                    $query = $query = $calendar->select($wpdb->prefix . 'wpbooking_availability.id,
                                                ' . $wpdb->prefix . 'wpbooking_service.max_guests,calendar_maximum,SUM(adult_number + children_number + infant_number) AS total_people_booked,start,
                                                ' . $wpdb->prefix . 'wpbooking_availability.adult_price,
                                                ' . $wpdb->prefix . 'wpbooking_availability.child_price,
                                                ' . $wpdb->prefix . 'wpbooking_availability.infant_price,
                                                adult_minimum,
                                                child_minimum,
                                                infant_minimum
')
                        ->join('wpbooking_service', "wpbooking_service.post_id = wpbooking_availability.post_id")
                        ->join('wpbooking_order', "wpbooking_order.post_id = wpbooking_availability.post_id and check_in_timestamp=`start` and wpbooking_order.STATUS NOT IN ('cancelled','refunded','trash','payment_failed')", 'left')
                        ->where(array(
                            $wpdb->prefix . 'wpbooking_availability.post_id' => $post_id,
                            $wpdb->prefix . 'wpbooking_availability.status'  => 'available',
                            'start'                                          => $start,
                        ))
                        ->where("({$wpdb->prefix}wpbooking_availability.adult_price > 0 or {$wpdb->prefix}wpbooking_availability.child_price>0 or {$wpdb->prefix}wpbooking_availability.infant_price>0)", false, true)
                        ->groupby($wpdb->prefix . 'wpbooking_availability.id')
                        ->having(' total_people_booked IS NULL OR total_people_booked < max_guests')
                        ->get()->row();

                    return $query;
                    break;
            }
        }

        /**
         * Register Tour Type
         *
         * @since 1.0
         * @author dungdt
         */
        public function _register_tour_type()
        {
            // Register Taxonomy
            $labels = array(
                'name'              => _x('Tour Type', 'taxonomy general name', 'wpbooking'),
                'singular_name'     => _x('Tour Type', 'taxonomy singular name', 'wpbooking'),
                'search_items'      => __('Search Tour Type', 'wpbooking'),
                'all_items'         => __('All Tour Type', 'wpbooking'),
                'parent_item'       => __('Parent Tour Type', 'wpbooking'),
                'parent_item_colon' => __('Parent Tour Type:', 'wpbooking'),
                'edit_item'         => __('Edit Tour Type', 'wpbooking'),
                'update_item'       => __('Update Tour Type', 'wpbooking'),
                'add_new_item'      => __('Add New Tour Type', 'wpbooking'),
                'new_item_name'     => __('New Tour Type Name', 'wpbooking'),
                'menu_name'         => __('Tour Type', 'wpbooking'),
            );
            $args = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => false,
                'query_var'         => true,
                'meta_box_cb'       => false,
                'rewrite'           => array('slug' => 'tour-type'),
            );
            register_taxonomy('wb_tour_type', array('wpbooking_service'), $args);
        }


        /**
         * Query Minimum Price for Tour
         *
         * @since 1.0
         * @author dungdt
         *
         * @param $price
         * @param $post_id
         * @return string
         */
        public function _edit_price($price_html, $price, $post_id, $service_type)
        {
            global $wpdb;
            $calendar = WPBooking_Calendar_Model::inst();

            $pricing_type = get_post_meta($post_id, 'pricing_type', true);

            if ($pricing_type == 'per_person') {
                $query = $calendar->select('CASE WHEN MIN(child_price) < MIN(adult_price) THEN MIN(adult_price) ELSE MIN(child_price) END as min_price')->where(array(
                    'post_id'  => $post_id,
                    'status'   => 'available',
                    'start >=' => strtotime(date('d-m-Y'))

                ))->get(1)->row();
            } else {
                $query = $calendar->select('MIN(calendar_price) as min_price')->where(array(
                    'post_id'          => $post_id,
                    'status'           => 'available',
                    'calendar_price >' => 0,
                    'start >='         => strtotime(date('d-m-Y'))

                ))->get(1)->row();
            }

            if ($query) {
                $price = $query['min_price'];
            }
            $price_html = WPBooking_Currency::format_money($price);

            $price_html = sprintf(__('from %s', 'wpbooking'), '<br><span class="price">' . $price_html . '</span>');

            return $price_html;
        }

        /**
         * Register metabox fields
         *
         * @since 1.0
         * @author dungdt
         */
        public function _register_meta_fields()
        {
            // Metabox
            $this->set_metabox(array(
                'general_tab'  => array(
                    'label'  => esc_html__('1. Basic Information', 'wpbooking'),
                    'fields' => array(
                        array(
                            'type' => 'open_section',
                        ),
                        array(
                            'label' => __("About Your Tour", 'wpbooking'),
                            'type'  => 'title',
                            'desc'  => esc_html__('Basic information', 'wpbooking'),
                        ),
                        array(
                            'id'    => 'enable_property',
                            'label' => __("Enable Tour", 'wpbooking'),
                            'type'  => 'on-off',
                            'std'   => 'on',
                            'desc'  => esc_html__('Listing will appear in search results.', 'wpbooking'),
                        ),
                        array(
                            'id'       => 'tour_type',
                            'label'    => __("Tour Type", 'wpbooking'),
                            'type'     => 'dropdown',
                            'taxonomy' => 'wb_tour_type',
                            'class'    => 'small'
                        ),
                        array(
                            'id'    => 'star_rating',
                            'label' => __("Star Rating", 'wpbooking'),
                            'type'  => 'star-select',
                            'desc'  => esc_html__('Standard of tour from 1 to 5 star.', 'wpbooking'),
                            'class' => 'small'
                        ),
                        array(
                            'id'          => 'duration',
                            'label'       => __("Duration", 'wpbooking'),
                            'type'        => 'text',
                            'placeholder' => esc_html__('Example: 10 days', 'wpbooking'),
                            'class'       => 'small',
                            'rules'       => 'required'
                        ),
                        array(
                            'label' => __('Contact Number', 'wpbooking'),
                            'id'    => 'contact_number',
                            'desc'  => esc_html__('The contact phone', 'wpbooking'),
                            'type'  => 'text',
                            'class' => 'small',
                            'rules' => 'required'
                        ),
                        array(
                            'label'       => __('Contact Email', 'wpbooking'),
                            'id'          => 'contact_email',
                            'type'        => 'text',
                            'placeholder' => esc_html__('Example@domain.com', 'wpbooking'),
                            'class'       => 'small',
                            'rules'       => 'required|valid_email'
                        ),
                        array(
                            'label'       => __('Website', 'wpbooking'),
                            'id'          => 'website',
                            'type'        => 'text',
                            'desc'        => esc_html__('Property website (optional)', 'wpbooking'),
                            'placeholder' => esc_html__('http://exampledomain.com', 'wpbooking'),
                            'class'       => 'small',
                            'rules'       => 'valid_url'
                        ),
                        array('type' => 'close_section'),
                        array('type' => 'open_section'),
                        array(
                            'label' => __("Tour Destination", 'wpbooking'),
                            'type'  => 'title',
                        ),
                        array(
                            'label'           => __('Address', 'wpbooking'),
                            'id'              => 'address',
                            'type'            => 'address',
                            'container_class' => 'mb35',
                            'exclude'         => array('apt_unit'),
                            'rules'           => 'required'
                        ),
                        array(
                            'label' => __('Map Lat & Long', 'wpbooking'),
                            'id'    => 'gmap',
                            'type'  => 'gmap',
                            'desc'  => esc_html__('This is the location we will provide guests. Click to move the marker if you need to move it', 'wpbooking')
                        ),
                        array(
                            'type'    => 'desc_section',
                            'title'   => esc_html__('Your address matters! ', 'wpbooking'),
                            'content' => esc_html__('Please make sure to enter your full address ', 'wpbooking')
                        ),
                        array('type' => 'close_section'),
                        array(
                            'type' => 'section_navigation',
                            'prev' => false
                        ),

                    )
                ),
                'detail_tab'   => array(
                    'label'  => __('2. Booking Details', 'wpbooking'),
                    'fields' => array(
                        array('type' => 'open_section'),
                        array(
                            'label' => __("Pricing type", 'wpbooking'),
                            'type'  => 'title',
                        ),
                        array(
                            'label' => esc_html__('Pricing Type', 'wpbooking'),
                            'type'  => 'dropdown',
                            'id'    => 'pricing_type',
                            'value' => array(
                                'per_person' => esc_html__('Per person', 'wpbooking'),
                                'per_unit'   => esc_html__('Per unit', 'wpbooking'),
                            ),
                            'class' => 'small'
                        ),
                        array(
                            'label' => esc_html__('Maximum people', 'wpbooking'),
                            'id'    => 'max_guests',
                            'type'  => 'number',
                            'std'   => 1,
                            'class' => 'small',
                            'min'   => 1
                        ),
                        array(
                            'label'     => esc_html__('Age Options', 'wpbooking'),
                            'desc'      => esc_html__('Provide your requirements for what age defines a child vs. adult.', 'wpbooking'),
                            'id'        => 'age_options',
                            'type'      => 'age_options',
                            'condition' => 'pricing_type:is(per_person)',
                            'rules'     => 'required'
                        ),
//                        array(
//                            'label'=>esc_html__('This tour is available','wpbooking'),
//                            'id'=>'property_available_for',
//                            'type'=>'dropdown',
//                            'value'=>array(
//                                'forever'=>esc_html__('Forever','wpbooking'),
//                                'specific_periods'=>esc_html__('For specific periods','wpbooking'),
//                            ),
//                            'class' => 'small'
//                        ),
                        array(
                            'label' => __("Availability", 'wpbooking'),
                            'type'  => 'title',
                        ),
                        array(
                            'type'         => 'calendar',
                            'id'           => 'calendar',
                            'service_type' => 'tour'
                        ),
                        array('type' => 'close_section'),
                        array(
                            'type' => 'section_navigation',
                        ),
                    )
                ),
                'policies_tab' => array(
                    'label'  => __('3. Policies & Checkout', 'wpbooking'),
                    'fields' => array(

                        array('type' => 'open_section'),
                        array(
                            'label' => __("Pre-payment and cancellation policies", 'wpbooking'),
                            'type'  => 'title',
                            'desc'  => esc_html__("Pre-payment and cancellation policies", "wpbooking")
                        ),
                        array(
                            'label' => __('Select deposit optional', 'wpbooking'),
                            'id'    => 'deposit_payment_status',
                            'type'  => 'dropdown',
                            'value' => array(
                                ''        => __('Disallow Deposit', 'wpbooking'),
                                'percent' => __('Deposit by percent', 'wpbooking'),
                                'amount'  => __('Deposit by amount', 'wpbooking'),
                            ),
                            'desc'  => esc_html__("You can select Disallow Deposit, Deposit by percent, Deposit by amount", "wpbooking"),
                            'class' => 'small'
                        ),
                        array(
                            'label' => __('Deposit payment amount', 'wpbooking'),
                            'id'    => 'deposit_payment_amount',
                            'type'  => 'number',
                            'desc'  => esc_html__("Leave empty for disallow deposit payment", "wpbooking"),
                            'class' => 'small',
                            'min'   => 1
                        ),
                        array(
                            'label' => __('How many days in advance can guests cancel free of  charge?', 'wpbooking'),
                            'id'    => 'cancel_free_days_prior',
                            'type'  => 'dropdown',
                            'value' => array(
                                'day_of_arrival' => __('Day of arrival (6 pm)', 'wpbooking'),
                                '1'              => __('1 day', 'wpbooking'),
                                '2'              => __('2 days', 'wpbooking'),
                                '3'              => __('3 days', 'wpbooking'),
                                '7'              => __('7 days', 'wpbooking'),
                                '14'             => __('14 days', 'wpbooking'),
                            ),
                            'desc'  => esc_html__("Day of arrival ( 18: 00 ) , 1 day , 2 days, 3 days, 7 days, 14 days", "wpbooking"),
                            'class' => 'small'
                        ),
                        array(
                            'label' => __('Or guests will pay 100%', 'wpbooking'),
                            'id'    => 'cancel_guest_payment',
                            'type'  => 'dropdown',
                            'value' => array(
                                'first_night' => __('of the first night', 'wpbooking'),
                                'full_stay'   => __('of the full stay', 'wpbooking'),
                            ),
                            'desc'  => esc_html__("Of the first night, of the full stay", "wpbooking"),
                            'class' => 'small'
                        ),
                        array('type' => 'close_section'),
                        array('type' => 'open_section'),
                        array(
                            'label' => __("Tax", 'wpbooking'),
                            'type'  => 'title',
                            'desc'  => esc_html__("Set your local VAT, so guests know what is included in the price of their stay.", "wpbooking")
                        ),
                        array(
                            'label'  => __('VAT', 'wpbooking'),
                            'id'     => 'vat_different',
                            'type'   => 'vat_different',
                            'fields' => array(
                                'vat_excluded',
                                'vat_amount',
                                'vat_unit',
                            )
                        ),
                        array('type' => 'close_section'),

                        array('type' => 'open_section'),
                        array(
                            'label' => __("Term & condition", 'wpbooking'),
                            'type'  => 'title',
                            'desc'  => esc_html__("Setting terms and condition for your property", "wpbooking")
                        ),
                        array(
                            'label' => __('Terms & Conditions', 'wpbooking'),
                            'id'    => 'terms_conditions',
                            'type'  => 'textarea',
                            'rows'  => '5',
                            'rules' => 'required'
                        ),
                        array('type' => 'close_section'),
                        array(
                            'type' => 'section_navigation',
                        ),
                    ),
                ),
                'photo_tab'    => array(
                    'label'  => __('4. Photos', 'wpbooking'),
                    'fields' => array(
                        array(
                            'label' => __("Pictures", 'wpbooking'),
                            'type'  => 'title',
                        ),
                        array(
                            'label' => __("Gallery", 'wpbooking'),
                            'id'    => 'tour_gallery',
                            'type'  => 'gallery',
                            'rules' => 'required',
                            'desc'  => __('Great photos invite guests to get the full experience of your property. Be sure to include high-resolution photos of the building, facilities, and amenities. We will display these photos on your property\'s page', 'wpbooking')
                        ),

                        array(
                            'type'       => 'section_navigation',
                            'next_label' => esc_html__('Save', 'wpbooking'),
                            'step'       => 'finish'
                        ),
                    )
                ),

            ));
        }

        /**
         * Change Thumb Size of Gallery
         *
         * @since 1.0
         * @author dungdt
         *
         * @param $size
         * @param $service_type
         * @param $post_id
         * @return array
         */
        function _apply_thumb_size($size, $service_type, $post_id)
        {
            if ($service_type == $this->type_id) {
                $thumb = $this->thumb_size('150,150,off');
                $thumb = explode(',', $thumb);
                if (count($thumb) == 3) {
                    if ($thumb[2] == 'off') $thumb[2] = FALSE;

                    $size = array($thumb[0], $thumb[1]);
                }

            }

            return $size;
        }

        /**
         * @param bool $default
         * @return bool|mixed|void
         */
        function thumb_size($default = FALSE)
        {
            return $this->get_option('thumb_size_hotel', $default);
        }

        /**
         * Get Search Fields
         *
         * @since 1.0
         * @author dungdt
         *
         * @return mixed|void
         */
        public function get_search_fields()
        {
            $wpbooking_taxonomy = get_option('wpbooking_taxonomies');

            $list_taxonomy = array('wb_tour_type' => esc_html__('Tour type', 'wpbooking'));
            if (!empty($wpbooking_taxonomy) && is_array($wpbooking_taxonomy)) {
                foreach ($wpbooking_taxonomy as $key => $val) {
                    if (!empty($val['service_type']) && in_array('tour', $val['service_type'])) {
                        $list_taxonomy[$key] = $val['label'];
                    }
                }
            }


            $search_fields = apply_filters('wpbooking_search_field_' . $this->type_id, array(
                array(
                    'name'    => 'field_type',
                    'label'   => __('Field Type', "wpbooking"),
                    'type'    => "dropdown",
                    'options' => array(
                        ""            => __("-- Select --", "wpbooking"),
                        "location_id" => __("Destination", "wpbooking"),
                        "check_in"    => __("From date", "wpbooking"),
                        "check_out"   => __("To date", "wpbooking"),
                        "taxonomy"    => __("Taxonomy", "wpbooking"),
                        "star_rating" => __("Star Of Tour", "wpbooking"),
                        "price"       => __("Price", "wpbooking"),
                    )
                ),
                array(
                    'name'  => 'title',
                    'label' => __('Title', "wpbooking"),
                    'type'  => "text",
                    'value' => ""
                ),
                array(
                    'name'  => 'placeholder',
                    'label' => __('Placeholder', "wpbooking"),
                    'desc'  => __('Placeholder', "wpbooking"),
                    'type'  => 'text',
                ),
                array(
                    'name'    => 'taxonomy',
                    'label'   => __('- Taxonomy', "wpbooking"),
                    'type'    => "dropdown",
                    'class'   => "hide",
                    'options' => $list_taxonomy
                ),
                array(
                    'name'    => 'taxonomy_show',
                    'label'   => __('- Display Style', "wpbooking"),
                    'type'    => "dropdown",
                    'class'   => "hide",
                    'options' => array(
                        "dropdown"  => __("Dropdown", "wpbooking"),
                        "check_box" => __("Check Box", "wpbooking"),
                    )
                ),
                array(
                    'name'    => 'taxonomy_operator',
                    'label'   => __('- Operator', "wpbooking"),
                    'type'    => "dropdown",
                    'class'   => "hide",
                    'options' => array(
                        "AND" => __("And", "wpbooking"),
                        "OR"  => __("Or", "wpbooking"),
                    )
                ),
                array(
                    'name'    => 'required',
                    'label'   => __('Required', "wpbooking"),
                    'type'    => "dropdown",
                    'options' => array(
                        "no"  => __("No", "wpbooking"),
                        "yes" => __("Yes", "wpbooking"),
                    )
                ),
                array(
                    'name'  => 'in_more_filter',
                    'label' => __('In Advance Search?', "wpbooking"),
                    'type'  => "checkbox",
                ),

            ));

            return $search_fields;
            // TODO: Implement get_search_fields() method.
        }


        /**
         * Get Available Days by Month, Years
         *
         * @since 1.0
         * @author dungdt
         *
         *
         * @param $month
         * @param $year
         * @return array
         */
        public function get_available_days($post_id, $month, $year)
        {

            $calendar = WPBooking_Calendar_Model::inst();

            $start = strtotime(date('1-' . $month . '-' . $year));
            if ($start < strtotime(date('d-m-Y'))) $start = strtotime(date('d-m-Y'));
            $end = strtotime(date('t-' . $month . '-' . $year));
            global $wpdb;

            switch (get_post_meta($post_id, 'pricing_type', true)) {
                case "per_unit":
                    $query = $calendar->select($wpdb->prefix . 'wpbooking_availability.id,
	' . $wpdb->prefix . 'wpbooking_service.max_guests,calendar_maximum,SUM(adult_number + children_number + infant_number) AS total_people_booked,start,calendar_price')
                        ->join('wpbooking_service', "wpbooking_service.post_id = wpbooking_availability.post_id")
                        ->join('wpbooking_order', "wpbooking_order.post_id = wpbooking_availability.post_id and check_in_timestamp=`start` and wpbooking_order. STATUS NOT IN ('cancelled','refunded','trash','payment_failed')", 'left')
                        ->where(array(
                            $wpdb->prefix . 'wpbooking_availability.post_id' => $post_id,
                            $wpdb->prefix . 'wpbooking_availability.status'  => 'available',
                            'calendar_price >'                               => 0,
                            'start >='                                       => $start,
                            'end <='                                         => $end,
                        ))
                        ->groupby($wpdb->prefix . 'wpbooking_availability.id')
                        ->orderby($wpdb->prefix . 'wpbooking_availability.start')
                        ->having(' total_people_booked IS NULL OR total_people_booked < max_guests')
                        ->get()->result();
                    $calendar->_clear_query();

                    break;
                case "per_person":
                    $query = $calendar->select($wpdb->prefix . 'wpbooking_availability.id,
                                    ' . $wpdb->prefix . 'wpbooking_service.max_guests,calendar_maximum,SUM(adult_number + children_number + infant_number) AS total_people_booked,start,
                                    ' . $wpdb->prefix . 'wpbooking_availability.adult_price,
                                    ' . $wpdb->prefix . 'wpbooking_availability.child_price,
                                    ' . $wpdb->prefix . 'wpbooking_availability.infant_price')
                        ->join('wpbooking_service', "wpbooking_service.post_id = wpbooking_availability.post_id")
                        ->join('wpbooking_order', "wpbooking_order.post_id = wpbooking_availability.post_id and check_in_timestamp=`start` and wpbooking_order. STATUS NOT IN ('cancelled','refunded','trash','payment_failed')", 'left')
                        ->where(array(
                            $wpdb->prefix . 'wpbooking_availability.post_id' => $post_id,
                            $wpdb->prefix . 'wpbooking_availability.status'  => 'available',
                            'start >='                                       => $start,
                            'end <='                                         => $end,
                        ))
                        ->where("({$wpdb->prefix}wpbooking_availability.adult_price > 0 or {$wpdb->prefix}wpbooking_availability.child_price>0 or {$wpdb->prefix}wpbooking_availability.infant_price>0)", false, true)
                        ->groupby($wpdb->prefix . 'wpbooking_availability.id')
                        ->orderby($wpdb->prefix . 'wpbooking_availability.start')
                        ->having(' total_people_booked IS NULL OR total_people_booked < max_guests')
                        ->get()->result();
                default:
                    break;
            }

            return $query;
        }

        /**
         * Get Next Available 10 Month
         *
         * @since 1.0
         * @author dungdt
         *
         * @param bool $post_id
         * @return array
         */
        public function getNext10MonthAvailable($post_id = false)
        {
            if (!$post_id) $post_id = get_the_ID();

            $calendar = WPBooking_Calendar_Model::inst();

            global $wpdb;
            switch (get_post_meta($post_id, 'pricing_type', true)) {
                case "per_unit":
                    $from_query = $calendar->select($wpdb->prefix . 'wpbooking_availability.id,calendar_maximum,SUM(adult_number + children_number + infant_number) AS total_people_booked,start,calendar_price')
                        ->join('wpbooking_order', "wpbooking_order.post_id = wpbooking_availability.post_id and check_in_timestamp=`start` and wpbooking_order. STATUS NOT IN ('cancelled','refunded','trash','payment_failed')", 'left')
                        ->where(array(
                            $wpdb->prefix . 'wpbooking_availability.post_id' => $post_id,
                            $wpdb->prefix . 'wpbooking_availability.status'  => 'available',
                            'calendar_price >'                               => 0,
                            'start >='                                       => strtotime(date('d-m-Y')),
                        ))
                        ->groupby($wpdb->prefix . 'wpbooking_availability.id')
                        ->having(' total_people_booked IS NULL OR total_people_booked < calendar_maximum')
                        ->_get_query();
                    $calendar->_clear_query();

                    $query = $wpdb->get_results("
                            SELECT
                                calendar_maximum,
                                start,
                                CONCAT(
                                    MONTH (FROM_UNIXTIME(START)),
                                    '_',
                                    YEAR (FROM_UNIXTIME(START))
                                ) AS month_year
                                FROM ($from_query) as available_table
                                GROUP BY month_year
                                ORDER BY
                                    START ASC
                                LIMIT 0,
                                 10
                    
                    ", ARRAY_A);
                    break;
                case "per_person":
                    $from_query = $calendar->select($wpdb->prefix . 'wpbooking_availability.id,
	' . $wpdb->prefix . 'wpbooking_service.max_guests,calendar_maximum,SUM(adult_number + children_number + infant_number) AS total_people_booked,start,
' . $wpdb->prefix . 'wpbooking_availability.adult_price,
' . $wpdb->prefix . 'wpbooking_availability.child_price,
' . $wpdb->prefix . 'wpbooking_availability.infant_price')
                        ->join('wpbooking_service', "wpbooking_service.post_id = wpbooking_availability.post_id")
                        ->join('wpbooking_order', "wpbooking_order.post_id = wpbooking_availability.post_id and check_in_timestamp=`start` and wpbooking_order. STATUS NOT IN ('cancelled','refunded','trash','payment_failed')", 'left')
                        ->where(array(
                            $wpdb->prefix . 'wpbooking_availability.post_id' => $post_id,
                            $wpdb->prefix . 'wpbooking_availability.status'  => 'available',
                            'start >='                                       => strtotime(date('d-m-Y')),
                        ))
                        ->where("({$wpdb->prefix}wpbooking_availability.adult_price > 0 or {$wpdb->prefix}wpbooking_availability.child_price>0 or {$wpdb->prefix}wpbooking_availability.infant_price>0)", false, true)
                        ->groupby($wpdb->prefix . 'wpbooking_availability.id')
                        ->having(' total_people_booked IS NULL OR total_people_booked < max_guests')
                        ->_get_query();
                    $calendar->_clear_query();

                    $query = $wpdb->get_results("
                            SELECT
                                calendar_maximum,
                                start,
                                CONCAT(
                                    MONTH (FROM_UNIXTIME(START)),
                                    '_',
                                    YEAR (FROM_UNIXTIME(START))
                                ) AS month_year
                                FROM ($from_query) as available_table
                                GROUP BY month_year
                                ORDER BY
                                    START ASC
                                LIMIT 0,
                                 10
                    ", ARRAY_A);
                default:
                    break;
            }
            $res = array();

            if (!empty($query)) {
                foreach ($query as $item) {
                    $res[$item['month_year']] = array(
                        'days'  => $this->get_available_days($post_id, date('m', $item['start']), date('Y', $item['start'])),
                        'label' => date_i18n('M Y', $item['start'])
                    );
                }
            }

            return $res;
        }

        function _add_default_query_hook()
        {
            global $wpdb;
            $table_prefix = WPBooking_Service_Model::inst()->get_table_name();
            $injection = WPBooking_Query_Inject::inst();
            $tax_query = $injection->get_arg('tax_query');

            $posts_per_page = $this->get_option('posts_per_page', 10);

            $injection->add_arg('posts_per_page', $posts_per_page);

            // Taxonomy
            $tax = $this->request('taxonomy');
            if (!empty($tax) and is_array($tax)) {
                $taxonomy_operator = $this->request('taxonomy_operator');
                $tax_query_child = array();
                foreach ($tax as $key => $value) {
                    if ($value) {
                        if (!empty($taxonomy_operator[$key])) {
                            $operator = $taxonomy_operator[$key];
                        } else {
                            $operator = "OR";
                        }
                        if ($operator == 'OR') $operator = 'IN';
                        $value = explode(',', $value);
                        if (!empty($value) and is_array($value)) {
                            foreach ($value as $k => $v) {
                                if (!empty($v)) {
                                    $ids[] = $v;
                                }
                            }
                        }
                        if (!empty($ids)) {
                            $tax_query[] = array(
                                'taxonomy' => $key,
                                'terms'    => $ids,
                                'operator' => $operator,
                            );
                        }
                        $ids = array();
                    }
                }


                if (!empty($tax_query_child))
                    $tax_query[] = $tax_query_child;
            }

            // Star Rating
            if ($star_rating = $this->get('star_rating') and is_array(explode(',', $star_rating))) {

                $star_rating_arr = explode(',', $star_rating);
                $meta_query[] = array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'star_rating',
                        'type'    => 'CHAR',
                        'value'   => $star_rating_arr,
                        'compare' => 'IN'
                    )
                );
            }

            //Check in
            if ($this->request('checkin_d') && $this->request('checkin_m') && $this->request('checkin_y')) {
                $from_date = strtotime($this->request('checkin_d') . '-' . $this->request('checkin_m') . '-' . $this->request('checkin_y'));

                $injection->join('wpbooking_availability as avail', "avail.post_id={$wpdb->posts}.ID");
                $injection->where('avail.start', $from_date);
                $injection->where('avail.status', 'available');
                $injection->groupby('avail.post_id');
            }

            if (!empty($tax_query))
                $injection->add_arg('tax_query', $tax_query);

            if (!empty($meta_query))
                $injection->add_arg('meta_query', $meta_query);


            $injection->add_arg('post_status', 'publish');

            // Order By
            if ($sortby = $this->request('wb_sort_by')) {
                switch ($sortby) {
                    case "price_asc":
                        $injection->select("CASE WHEN meta.meta_value='per_person' AND MIN(CAST(avail.child_price as double)) < MIN(CAST(avail.adult_price as double)) THEN MIN(CAST(avail.adult_price as double))
			                                WHEN meta.meta_value='per_person' AND MIN(CAST(avail.child_price as double)) > MIN(CAST(avail.adult_price as double)) THEN MIN(CAST(avail.child_price as double))
			                                ELSE MIN(CAST(avail.calendar_price as double))
			                                END as min_price");
                        $injection->join('postmeta as meta', "meta.post_id={$wpdb->posts}.ID AND meta.meta_key='pricing_type'");
                        $injection->join('wpbooking_availability as avail', "avail.post_id = {$wpdb->posts}.ID");
                        $injection->orderby('min_price', 'asc');

                        break;
                    case "price_desc":
                        $injection->select("CASE WHEN meta.meta_value='per_person' AND MIN(CAST(avail.child_price as double)) < MIN(CAST(avail.adult_price as double)) THEN MIN(CAST(avail.adult_price as double))
			                                WHEN meta.meta_value='per_person' AND MIN(CAST(avail.child_price as double)) > MIN(CAST(avail.adult_price as double)) THEN MIN(CAST(avail.child_price as double))
			                                ELSE MIN(CAST(avail.calendar_price as double))
			                                END as min_price");
                        $injection->join('postmeta as meta', "meta.post_id={$wpdb->posts}.ID AND meta.meta_key='pricing_type'");
                        $injection->join('wpbooking_availability as avail', "avail.post_id = {$wpdb->posts}.ID");
                        $injection->orderby('min_price', 'desc');
                        break;
                    case "date_asc":
                        $injection->add_arg('orderby', 'date');
                        $injection->add_arg('order', 'asc');
                        break;
                    case "date_desc":
                        $injection->add_arg('orderby', 'date');
                        $injection->add_arg('order', 'desc');
                        break;
                }
            }


            parent::_add_default_query_hook();
        }

        static function inst()
        {
            if (!self::$_inst)
                self::$_inst = new self();

            return self::$_inst;
        }
    }

    WPBooking_Tour_Service_Type::inst();
}