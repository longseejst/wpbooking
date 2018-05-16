<?php
    if ( !class_exists( 'WPBooking_Accommodation_Service_Type' ) and class_exists( 'WPBooking_Abstract_Service_Type' ) ) {
        class WPBooking_Accommodation_Service_Type extends WPBooking_Abstract_Service_Type
        {
            static $_inst = false;

            protected $type_id = 'accommodation';

            function __construct()
            {
                $this->type_info = [
                    'label'  => esc_html__( "Accommodation", 'wpbooking' ),
                    'labels' => esc_html__( "Accommodations", 'wpbooking' ),
                    'desc'   => esc_html__( 'You can post any kind of property like hotels, hostels, room like airbnb... anything  is called accommodation', 'wpbooking' )
                ];

                $this->settings = [
                    [
                        'id'    => 'title',
                        'label' => esc_html__( 'Layout', 'wpbooking' ),
                        'type'  => 'title',
                    ],
                    [
                        'id'    => 'posts_per_page',
                        'label' => esc_html__( "Item per page", 'wpbooking' ),
                        'type'  => 'number',
                        'std'   => 10
                    ],
                    [
                        'id'    => "thumb_size",
                        'label' => esc_html__( "Thumb Size", 'wpbooking' ),
                        'type'  => 'image-size'
                    ],
                    [
                        'id'    => "gallery_size",
                        'label' => esc_html__( "Gallery Size", 'wpbooking' ),
                        'type'  => 'image-size'
                    ],
                ];

                parent::__construct();

                add_action( 'init', [ $this, '_add_init_action' ] );


                /**
                 * Ajax Show Room Form
                 *
                 * @since  1.0
                 * @author dungdt
                 */
                add_action( 'wp_ajax_wpbooking_show_room_form', [ $this, '_ajax_room_edit_template' ] );

                /**
                 * Ajax duplicate room
                 * @since   1.7
                 * @updated 1.7
                 * @author  haint
                 */
                add_action( 'wp_ajax_wpbooking_duplicate_post', [ $this, '_duplicate_post' ] );

                /**
                 * Ajax Save Room Data
                 *
                 * @since  1.0
                 * @author dungdt
                 */
                add_action( 'wp_ajax_wpbooking_save_hotel_room', [ $this, '_ajax_save_room' ] );

                /**
                 * Ajax delete room item
                 *
                 * @since  1.0
                 * @author Tien37
                 */
                add_action( 'wp_ajax_wpbooking_del_room_item', [ $this, '_ajax_del_room_item' ] );

                /**
                 * Ajax search room
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_action( 'wp_ajax_ajax_search_room', [ $this, 'ajax_search_room' ] );
                add_action( 'wp_ajax_nopriv_ajax_search_room', [ $this, 'ajax_search_room' ] );

                /**
                 * Ajax search room
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_action( 'wp_ajax_wpbooking_reload_image_list_room', [ $this, 'wpbooking_reload_image_list_room' ] );
                add_action( 'wp_ajax_nopriv_wpbooking_reload_image_list_room', [ $this, 'wpbooking_reload_image_list_room' ] );

                /**
                 * Filter List Room Size
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_filter( 'wpbooking_hotel_room_form_updated_content', [ $this, '_get_list_room_size' ], 10, 3 );


                //wpbooking_archive_loop_image_size
                add_filter( 'wpbooking_archive_loop_image_size', [ $this, '_apply_thumb_size' ], 10, 3 );


                /**
                 * Change Base Price
                 *
                 * @author quandq
                 * @since  1.0
                 */
                add_filter( 'wpbooking_service_base_price_' . $this->type_id, [ $this, '_change_base_price' ], 10, 3 );

                /**
                 * Move name and email field to top in comment
                 */

                add_filter( 'comment_form_fields', [ $this, '_move_fields_comment_top' ] );

                /**
                 * Add more params to cart items
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_filter( 'wpbooking_cart_item_params_' . $this->type_id, [ $this, '_change_cart_item_params' ], 10, 2 );

                /**
                 * Validate add to cart
                 *
                 * @since  1.0
                 * @author quandq
                 *
                 */
                add_filter( 'wpbooking_add_to_cart_validate_' . $this->type_id, [ $this, '_add_to_cart_validate' ], 10, 4 );

                /**
                 * Validate Do Checkout
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_filter( 'wpbooking_do_checkout_validate_' . $this->type_id, [ $this, '_validate_checkout' ], 10, 2 );

                /**
                 * Validate add to cart
                 *
                 * @since  1.0
                 * @author quandq
                 *
                 */
                add_filter( 'wpbooking_get_cart_total_' . $this->type_id, [ $this, '_get_cart_total_price_hotel_room' ], 10, 4 );


                /**
                 * Add info room checkout
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_action( 'wpbooking_review_checkout_item_information_' . $this->type_id, [ $this, '_add_info_checkout_item_room' ], 10, 2 );
                add_action( 'wpbooking_check_out_total_item_information_' . $this->type_id, [ $this, '_add_info_total_item_room' ], 10, 2 );
                add_action( 'wpbooking_save_order_' . $this->type_id, [ $this, '_save_order_hotel_room' ], 10, 2 );
                /**
                 * Change Tax Room CheckOut
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_action( 'wpbooking_get_cart_tax_price_' . $this->type_id, [ $this, '_change_tax_room_checkout' ], 10, 2 );

                /**
                 * Add info Room Order Detail
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_action( 'wpbooking_order_detail_item_information_' . $this->type_id, [ $this, '_add_info_order_detail_item_room' ], 10, 2 );
                add_action( 'wpbooking_order_detail_total_item_information_' . $this->type_id, [ $this, '_add_info_order_total_item_room' ], 10, 2 );

                add_action( 'wpbooking_email_detail_item_information_' . $this->type_id, [ $this, '_add_information_email_detail_item' ], 10, 2 );


                /**
                 * Show More Order Info for Booking Admin
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_action( 'wpbooking_admin_after_order_detail_other_info_' . $this->type_id, [ $this, '_show_order_info_after_order_detail_in_booking_admin' ], 10, 2 );


                /**
                 * Delete Item Room
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_action( 'template_redirect', [ $this, '_delete_cart_item_hotel_room' ] );

                /**
                 * Show List Room in single Hotel
                 *
                 * @since  1.0
                 * @author dungdt
                 */
                add_action( 'wpbooking_after_service_amenity', [ $this, '_show_list_room' ] );

                /**
                 * Show Start,End Information
                 *
                 * @since  1.0
                 * @author dungdt
                 */
                add_action( 'wpbooking_review_after_address_' . $this->type_id, [ $this, '_show_start_end_information' ] );


                /**
                 * Show Order Info after Address
                 *
                 * @since  1.0
                 * @author dungdt
                 */
                add_action( 'wpbooking_order_detail_after_address_' . $this->type_id, [ $this, '_show_order_info_after_address' ] );

                /**
                 * Show More Order Info for Email
                 *
                 * @since  1.0
                 * @author dungdt
                 */
                add_action( 'wpbooking_email_order_after_address_' . $this->type_id, [ $this, '_show_email_order_info_after_address' ] );

                add_action( 'wpbooking_order_history_after_service_name_' . $this->type_id, [ $this, '_show_order_info_listing' ] );

                /**
                 * Get Min and Max Price
                 *
                 * @since  1.0
                 * @author quandq
                 */
                add_filter( 'wpbooking_min_max_price_' . $this->type_id, [ $this, '_change_min_max_price' ], 10, 1 );

                /**
                 * Update Metabox min_price Hotel by Room
                 *
                 * @since  1.3
                 * @author quandq
                 */
                add_action( 'wpbooking_after_save_room_hotel', [ $this, '_update_min_price_hotel' ] );
                add_action( 'save_post', [ $this, '_update_min_price_hotel' ] );
                add_action( 'wpbooking_save_metabox_section', [ $this, '_update_min_price_hotel' ] );

                /**
                 * Get inventory data
                 *
                 * @since   1.4
                 * @updated 1.4
                 * @author  haint
                 */
                add_action( 'wp_ajax_fetch_inventory_accommodation', [ $this, 'fetch_inventory_accommodation' ] );


                /**
                 * Update price of the room in inventory
                 *
                 * @since   1.4
                 * @updated 1.4
                 * @author  haint
                 */
                add_action( 'wp_ajax_add_price_inventory', [ $this, 'add_price_inventory_accommodation' ] );

                /**
                 * @since   1.5
                 * @updated 1.5
                 * @author  haint
                 */
                add_action( 'wpbooking_review_before_address', [ $this, 'before_address_checkout' ] );

                add_filter( 'wpbooking_table_availability', [ $this, '__set_availability_table' ] );
            }

            public function __set_availability_table( $table )
            {
                $table = $this->table_availability;

                return $table;
            }

            public function before_address_checkout( $cart )
            {
                if ( $cart[ 'service_type' ] == 'accommodation' ) {
                    ?>
                    <div class="wb-hotel-star">
                        <?php
                            $service = wpbooking_get_service( $cart[ 'post_id' ] );
                            $service->get_star_rating_html();
                        ?>
                    </div>
                    <?php
                }
            }

            public function fetch_inventory_accommodation()
            {
                $post_id = WPBooking_Input::post( 'id_post', '' );
                if ( get_post_type( $post_id ) == 'wpbooking_service' ) {
                    $start = strtotime( WPBooking_Input::post( 'start', '' ) );
                    $end   = strtotime( WPBooking_Input::post( 'end', '' ) );
                    if ( $start > 0 && $end > 0 ) {
                        $args    = [
                            'post_type'      => 'wpbooking_hotel_room',
                            'posts_per_page' => -1,
                            'post_parent'    => $post_id
                        ];
                        $current = wpbooking_current_lang();
                        if ( wpbooking_is_wpml() ) {
                            global $sitepress;
                            $sitepress->switch_lang( wpbooking_default_lang(), true );
                        }
                        $rooms = [];
                        $query = new WP_Query( $args );
                        while ( $query->have_posts() ): $query->the_post();
                            $rooms[] = [
                                'id'   => get_the_ID(),
                                'name' => get_the_title()
                            ];
                        endwhile;
                        wp_reset_postdata();
                        if ( wpbooking_is_wpml() ) {
                            global $sitepress;
                            $current = wpbooking_current_lang();
                            $sitepress->switch_lang( $current, true );
                        }
                        $datarooms = [];
                        if ( !empty( $rooms ) ) {
                            foreach ( $rooms as $key => $value ) {
                                $datarooms[] = $this->featch_dataroom( $post_id, $value[ 'id' ], $value[ 'name' ], $start, $end );
                            }
                        }
                        echo json_encode( [
                            'status' => 1,
                            'rooms'  => $datarooms
                        ] );
                        die;
                    }
                }
                echo json_encode( [
                    'status'  => 0,
                    'message' => esc_html__( 'Can not fetch data', 'wpbooking' ),
                    'rooms'   => ''
                ] );
                die;
            }

            public function featch_dataroom( $hotel_id, $post_id, $post_name, $start, $end )
            {
                $number_room = (int)get_post_meta( $post_id, 'room_number', true );
                $base_price  = (float)get_post_meta( $post_id, 'base_price', true );

                global $wpdb;
                $sql     = "SELECT
                    *
                FROM
                    {$wpdb->prefix}wpbooking_availability AS avai
                WHERE
                    (
                        (
                            avai.`start` <= {$start}
                            AND avai.`end` >= {$start}
                        )
                        OR (
                            avai.`start` <= {$end}
                            AND avai.`end` >= {$end}
                        )
                        OR (
                            avai.`start` <= {$start}
                            AND avai.`end` >= {$end}
                        )
                        OR (
                            avai.`start` >= {$start}
                            AND avai.`end` <= {$end}
                        )
                    )
                and avai.post_id = {$post_id}";
                $avai_rs = $wpdb->get_results( $sql );

                $sql = "SELECT
                    _order.*, _order_room.number
                FROM
                    {$wpdb->prefix}wpbooking_order AS _order
                    INNER JOIN {$wpdb->prefix}wpbooking_order_hotel_room _order_room on _order_room.order_id = _order.order_id
                WHERE
                    (
                        (
                            _order.check_in_timestamp <= {$start}
                            AND _order.check_out_timestamp >= {$start}
                        )
                        OR (
                            _order.check_in_timestamp <= {$end}
                            AND _order.check_out_timestamp >= {$end}
                        )
                        OR (
                            _order.check_in_timestamp <= {$start}
                            AND _order.check_out_timestamp >= {$end}
                        )
                        OR (
                            _order.check_in_timestamp >= {$start}
                            AND _order.check_out_timestamp <= {$end}
                        )
                    )
                AND _order_room.room_id = {$post_id} AND _order.`status` NOT IN ('cancelled')
                GROUP BY _order.id";

                $order_rs = $wpdb->get_results( $sql );

                $return = [
                    'name'   => esc_html( $post_name ),
                    'values' => [],
                    'id'     => $post_id
                ];
                for ( $i = $start; $i <= $end; $i = strtotime( '+1 day', $i ) ) {
                    $i         = strtotime( date( 'Y-m-d', $i ) );
                    $date      = $i * 1000;
                    $available = true;
                    $price     = $base_price;
                    if ( !empty( $avai_rs ) ) {
                        foreach ( $avai_rs as $key => $value ) {
                            if ( $i >= (int)$value->start && $i <= (int)$value->end ) {
                                if ( $value->status == 'available' ) {
                                    $price = (float)$value->price;
                                } else {
                                    $available = false;
                                }
                                break;
                            }
                        }
                    }
                    if ( $available ) {
                        $ordered = 0;
                        if ( !empty( $order_rs ) ) {
                            foreach ( $order_rs as $key => $value ) {
                                if ( $i >= $value->check_in_timestamp && $i <= $value->check_out_timestamp ) {
                                    $ordered += (int)$value->number;
                                }
                            }
                        }
                        if ( $number_room - $ordered > 0 ) {
                            $return[ 'values' ][] = [
                                'from'        => "/Date({$date})/",
                                'to'          => "/Date({$date})/",
                                'label'       => $number_room - $ordered,
                                'desc'        => sprintf( esc_html__( '%s left', 'wpbooking' ), $number_room - $ordered ),
                                'customClass' => 'ganttBlue',
                                'price'       => WPBooking_Currency::format_money( $price, [ 'simple_html' => true ] )
                            ];
                        } else {
                            $return[ 'values' ][] = [
                                'from'        => "/Date({$date})/",
                                'to'          => "/Date({$date})/",
                                'label'       => $number_room - $ordered . '',
                                'desc'        => esc_html__( 'Out of stock', 'wpbooking' ),
                                'customClass' => 'ganttOrange',
                                'price'       => WPBooking_Currency::format_money( $price, [ 'simple_html' => true ] )
                            ];
                        }
                    } else {
                        $return[ 'values' ][] = [
                            'from'        => "/Date({$date})/",
                            'to'          => "/Date({$date})/",
                            'label'       => $number_room,
                            'desc'        => esc_html__( 'Not Available', 'wpbooking' ),
                            'customClass' => 'ganttRed',
                            'price'       => WPBooking_Currency::format_money( $price, [ 'simple_html' => true ] )
                        ];
                    }
                }

                return $return;

            }

            public function add_price_inventory_accommodation()
            {
                $post_id = (int)WPBooking_Input::post( 'post_id' );
                $price   = WPBooking_Input::post( 'price' );
                $status  = WPBooking_Input::post( 'status', 'available' );
                $start   = (float)WPBooking_Input::post( 'start' );
                $end     = (float)WPBooking_Input::post( 'end' );
                $start   /= 1000;
                $end     /= 1000;

                $start = strtotime( date( 'Y-m-d', $start ) );
                $end   = strtotime( date( 'Y-m-d', $end ) );

                if ( get_post_type( $post_id ) != 'wpbooking_hotel_room' ) {
                    echo json_encode( [
                        'status'  => 0,
                        'message' => esc_html__( 'Can not set price for this room', 'wpbooking' )
                    ] );
                    die;
                }
                if ( ( $status == 'available' ) && ( $price == '' || !is_numeric( $price ) || (float)$price < 0 ) ) {
                    echo json_encode( [
                        'status'  => 0,
                        'message' => esc_html__( 'Price is incorrect', 'wpbooking' )
                    ] );
                    die;
                }
                $price = (float)$price;

                $base_id = (int)wpbooking_origin_id( $post_id, 'wpbooking_hotel_room' );

                $new_item = WPBooking_Calendar_Metabox::inst()->_calendar_save_data( $post_id, $base_id, $start, $end, $price, $status, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

                if ( $new_item > 0 ) {
                    echo json_encode( [
                        'status'  => 1,
                        'message' => esc_html__( 'Successffully added', 'wpbooking' )
                    ] );
                    die;
                } else {
                    echo json_encode( [
                        'status'  => 0,
                        'message' => esc_html__( 'Getting an error when adding new item.', 'wpbooking' )
                    ] );
                    die;
                }
            }

            /**
             * Get Min and Max Price
             *
             * @since  1.0
             * @author quandq
             *
             * @param array $args
             *
             * @return array
             */
            function _change_min_max_price( $args = [] )
            {

                $service = WPBooking_Service_Model::inst();

                global $wpdb;

                $service->select( '
               MIN(
                    CAST(
                        ' . $wpdb->prefix . 'postmeta.meta_value AS DECIMAL
                    )
                ) AS min_price,
                MAX(
                    CAST(
                        ' . $wpdb->prefix . 'postmeta.meta_value AS DECIMAL
                    )
                ) AS max_price
                ' )
                    ->join( 'posts as wpb_hotel', 'wpb_hotel.ID=' . $service->get_table_name( false ) . '.post_id' )
                    ->join( 'posts as wpb_room', 'wpb_room.post_parent=' . $service->get_table_name( false ) . '.post_id' )
                    ->join( 'postmeta', "postmeta.post_id= wpb_room.ID and meta_key = 'base_price'" );

                $service->where( 'service_type', $this->type_id );
                $service->where( 'enable_property', 'on' );
                $service->where( 'wpb_hotel.post_status', 'publish' );
                $service->where( 'wpb_hotel.post_type', 'wpbooking_service' );
                $service->groupby( 'wpb_hotel.ID' );

                $sql = $service->_get_query();
                $service->_clear_query();
                $res = $wpdb->get_row( "
                    SELECT 	MIN(min_price) as min, MAX(max_price) as max FROM ($sql) as wpb_table
             ", 'ARRAY_A' );

                if ( !is_wp_error( $res ) ) {
                    $args = $res;

                }

                return $args;
            }

            public function _show_order_info_listing( $order_data )
            {
                ?>

                <div class="item-form-to">
                    <span><?php esc_html_e( "From:", "wpbooking" ) ?> </span> <?php echo date( get_option( 'date_format' ), $order_data[ 'check_in_timestamp' ] ) ?>
                    &nbsp
                    <span><?php esc_html_e( "To:", "wpbooking" ) ?> </span><?php echo date( get_option( 'date_format' ), $order_data[ 'check_out_timestamp' ] ) ?>
                    &nbsp
                    <br>
                    <?php
                        $diff = $order_data[ 'check_out_timestamp' ] - $order_data[ 'check_in_timestamp' ];
                        $diff = $diff / ( 60 * 60 * 24 );
                        if ( $diff > 1 ) {
                            echo sprintf( esc_html__( '(%s nights)', 'wpbooking' ), $diff );
                        } else {
                            echo sprintf( esc_html__( '(%s night)', 'wpbooking' ), $diff );
                        }
                    ?>

                </div>
                <?php
            }

            /**
             * Show Start,End Information
             *
             * @since  1.0
             * @author dungdt
             *
             * @param array $cart
             */
            public function _show_start_end_information( $cart )
            {
                $post_id = $cart[ 'post_id' ];
                ?>

                <div class="review-order-item-form-to">
                    <span><?php esc_html_e( "From:", "wpbooking" ) ?> </span> <?php echo date_i18n( get_option( 'date_format' ), $cart[ 'check_in_timestamp' ] ) ?>
                    &nbsp
                    <span><?php esc_html_e( "To:", "wpbooking" ) ?> </span><?php echo date_i18n( get_option( 'date_format' ), $cart[ 'check_out_timestamp' ] ) ?>
                    &nbsp
                    <?php
                        $diff = $cart[ 'check_out_timestamp' ] - $cart[ 'check_in_timestamp' ];
                        $diff = $diff / ( 60 * 60 * 24 );
                        if ( $diff > 1 ) {
                            echo sprintf( esc_html__( '(%s nights)', 'wpbooking' ), $diff );
                        } else {
                            echo sprintf( esc_html__( '(%s night)', 'wpbooking' ), $diff );
                        }

                        $url_change_date = add_query_arg( [
                            'checkin_d' => date( "d", $cart[ 'check_in_timestamp' ] ),
                            'checkin_m' => date( "m", $cart[ 'check_in_timestamp' ] ),
                            'checkin_y' => date( "Y", $cart[ 'check_in_timestamp' ] ),

                            'checkout_d'   => date( "d", $cart[ 'check_out_timestamp' ] ),
                            'checkout_m'   => date( "m", $cart[ 'check_out_timestamp' ] ),
                            'checkout_y'   => date( "Y", $cart[ 'check_out_timestamp' ] ),
                            'check_in_out' => $cart[ 'check_in_out' ],
                        ], get_permalink( $post_id ) );
                        if ( !isset( $cart[ 'is_cart_page' ] ) or $cart[ 'is_cart_page' ] ) {
                            ?>
                            <small><a
                                        href="<?php echo esc_url( $url_change_date ) ?>"><?php esc_html_e( "Change Date", "wpbooking" ) ?></a>
                            </small>
                        <?php } ?>
                </div>
                <?php
            }

            /**
             * Show Order Info after Address
             *
             * @since  1.0
             * @author dungdt
             *
             * @param $order_data
             */
            public function _show_order_info_after_address( $order_data )
            {
                if ( !empty( $order_data[ 'raw_data' ] ) ) {
                    $raw_data = json_decode( $order_data[ 'raw_data' ], true );
                    if ( $raw_data ) {
                        $raw_data[ 'is_cart_page' ] = false;
                        $this->_show_start_end_information( $raw_data );
                    }
                }
            }

            /**
             * Show More Order Info for Email
             *
             * @since  1.0
             * @author dungdt
             *
             * @param $order_data
             */
            public function _show_email_order_info_after_address( $order_data )
            {

                ?>
                <h4 class=color_black>
                    <span
                            class=bold><?php esc_html_e( "From:", "wpbooking" ) ?> </span> <?php echo date_i18n( get_option( 'date_format' ), $order_data[ 'check_in_timestamp' ] ) ?>
                    <span
                            class=bold><?php esc_html_e( "To:", "wpbooking" ) ?> </span><?php echo date_i18n( get_option( 'date_format' ), $order_data[ 'check_out_timestamp' ] ) ?>
                    <?php
                        $diff = $order_data[ 'check_out_timestamp' ] - $order_data[ 'check_in_timestamp' ];
                        $diff = $diff / ( 60 * 60 * 24 );
                        if ( $diff > 1 ) {
                            echo sprintf( esc_html__( '(%s nights)', 'wpbooking' ), $diff );
                        } else {
                            echo sprintf( esc_html__( '(%s night)', 'wpbooking' ), $diff );
                        }
                    ?>

                </h4>
                <?php
            }

            /**
             * Show List Room in single Hotel
             *
             * @since  1.0
             * @author dungdt
             */
            public function _show_list_room()
            {
                $service = wpbooking_get_service();
                if ( $service->get_type() == $this->type_id ) {
                    echo wpbooking_load_view( 'single/hotel/room' );
                }
            }


            /**
             * Init Action
             *
             * @since  1.0
             * @author dungdt
             */
            public function _add_init_action()
            {
                $labels = [
                    'name'               => esc_html__( 'Accommodation Room', 'wpbooking' ),
                    'singular_name'      => esc_html__( 'Accommodation Room', 'wpbooking' ),
                    'menu_name'          => esc_html__( 'Accommodation Room', 'wpbooking' ),
                    'name_admin_bar'     => esc_html__( 'Accommodation Room', 'wpbooking' ),
                    'add_new'            => esc_html__( 'Add New', 'wpbooking' ),
                    'add_new_item'       => esc_html__( 'Add New Accommodation Room', 'wpbooking' ),
                    'new_item'           => esc_html__( 'New Accommodation Room', 'wpbooking' ),
                    'edit_item'          => esc_html__( 'Edit Accommodation Room', 'wpbooking' ),
                    'view_item'          => esc_html__( 'View Accommodation Room', 'wpbooking' ),
                    'all_items'          => esc_html__( 'All Accommodation Room', 'wpbooking' ),
                    'search_items'       => esc_html__( 'Search Accommodation Room', 'wpbooking' ),
                    'parent_item_colon'  => esc_html__( 'Parent Accommodation Room:', 'wpbooking' ),
                    'not_found'          => esc_html__( 'No Accommodation Room found.', 'wpbooking' ),
                    'not_found_in_trash' => esc_html__( 'No Accommodation Room found in Trash.', 'wpbooking' )
                ];

                $args = [
                    'labels'             => $labels,
                    'description'        => esc_html__( 'Description.', 'wpbooking' ),
                    'public'             => true,
                    'publicly_queryable' => true,
                    'show_ui'            => true,
                    'show_in_menu'       => false,
                    'query_var'          => true,
                    'capability_type'    => 'post',
                    'hierarchical'       => false,
                    //'menu_position'      => '59.9',
                    'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ]
                ];

                register_post_type( 'wpbooking_hotel_room', $args );

                // Register Taxonomy
                $labels = [
                    'name'              => esc_html__( 'Room Type', 'wpbooking' ),
                    'singular_name'     => esc_html__( 'Room Type', 'wpbooking' ),
                    'search_items'      => esc_html__( 'Search Room Type', 'wpbooking' ),
                    'all_items'         => esc_html__( 'All Room Type', 'wpbooking' ),
                    'parent_item'       => esc_html__( 'Parent Room Type', 'wpbooking' ),
                    'parent_item_colon' => esc_html__( 'Parent Room Type:', 'wpbooking' ),
                    'edit_item'         => esc_html__( 'Edit Room Type', 'wpbooking' ),
                    'update_item'       => esc_html__( 'Update Room Type', 'wpbooking' ),
                    'add_new_item'      => esc_html__( 'Add New Room Type', 'wpbooking' ),
                    'new_item_name'     => esc_html__( 'New Room Type Name', 'wpbooking' ),
                    'menu_name'         => esc_html__( 'Room Type', 'wpbooking' ),
                ];
                $args   = [
                    'hierarchical'      => true,
                    'labels'            => $labels,
                    'show_ui'           => true,
                    'show_admin_column' => false,
                    'query_var'         => true,
                    'meta_box_cb'       => false,
                    'rewrite'           => [ 'slug' => 'hotel-room-type' ],
                ];
                register_taxonomy( 'wb_hotel_room_type', [ 'wpbooking_hotel_room', 'wpbooking_service' ], $args );

                // Register Taxonomy
                $labels = [
                    'name'              => esc_html__( 'Room Facilities', 'wpbooking' ),
                    'singular_name'     => esc_html__( 'Room Facilities', 'wpbooking' ),
                    'search_items'      => esc_html__( 'Search Room Facilities', 'wpbooking' ),
                    'all_items'         => esc_html__( 'All Room Facilities', 'wpbooking' ),
                    'parent_item'       => esc_html__( 'Parent Room Facilities', 'wpbooking' ),
                    'parent_item_colon' => esc_html__( 'Parent Room Facilities:', 'wpbooking' ),
                    'edit_item'         => esc_html__( 'Edit Room Facilities', 'wpbooking' ),
                    'update_item'       => esc_html__( 'Update Room Facilities', 'wpbooking' ),
                    'add_new_item'      => esc_html__( 'Add New Room Facilities', 'wpbooking' ),
                    'new_item_name'     => esc_html__( 'New Room Facilities Name', 'wpbooking' ),
                    'menu_name'         => esc_html__( 'Room Facilities', 'wpbooking' ),
                ];
                $args   = [
                    'hierarchical'      => true,
                    'labels'            => $labels,
                    'show_ui'           => true,
                    'show_admin_column' => false,
                    'query_var'         => true,
                    'meta_box_cb'       => false,
                    'rewrite'           => [ 'slug' => 'hotel-room-facilities' ],
                ];
                register_taxonomy( 'wb_hotel_room_facilities', [ 'wpbooking_service' ], $args );


                // Metabox
                $this->set_metabox( [
                    'general_tab'     => [
                        'label'  => esc_html__( '1. Property Information', 'wpbooking' ),
                        'fields' => [
                            [
                                'type' => 'open_section',
                            ],
                            [
                                'label' => esc_html__( "About Your Property", 'wpbooking' ),
                                'type'  => 'title',
                                'desc'  => esc_html__( 'Basic information', 'wpbooking' ),
                            ],
                            [
                                'id'    => 'enable_property',
                                'label' => esc_html__( "Enable Property", 'wpbooking' ),
                                'type'  => 'on-off',
                                'std'   => 'on',
                                'desc'  => esc_html__( 'Listing will appear in search results.', 'wpbooking' ),
                            ],
                            [
                                'id'    => 'star_rating',
                                'label' => esc_html__( "Star Rating", 'wpbooking' ),
                                'type'  => 'star-select',
                                'desc'  => esc_html__( 'Standard of property from 1 to 5 stars', 'wpbooking' ),
                                'class' => 'small'
                            ],
                            [
                                'label'       => esc_html__( 'Contact Phone Number', 'wpbooking' ),
                                'id'          => 'contact_number',
                                'desc'        => esc_html__( 'The contact phone', 'wpbooking' ),
                                'type'        => 'text',
                                'class'       => 'small',
                                'rules'       => 'required',
                                'min'         => 0,
                                'placeholder' => esc_html__( 'Phone number', 'wpbooking' ),
                            ],
                            [
                                'label'       => esc_html__( 'Contact Email', 'wpbooking' ),
                                'id'          => 'contact_email',
                                'type'        => 'text',
                                'placeholder' => esc_html__( 'Example@domain.com', 'wpbooking' ),
                                'class'       => 'small',
                                'rules'       => 'required|valid_email'
                            ],
                            [
                                'label'       => esc_html__( 'Website', 'wpbooking' ),
                                'id'          => 'website',
                                'type'        => 'text',
                                'desc'        => esc_html__( 'Property website (optional)', 'wpbooking' ),
                                'placeholder' => esc_html__( 'http://exampledomain.com', 'wpbooking' ),
                                'class'       => 'small'
                            ],
                            [ 'type' => 'close_section' ],
                            [ 'type' => 'open_section' ],
                            [
                                'label' => esc_html__( "Property Location", 'wpbooking' ),
                                'type'  => 'title',
                                'desc'  => esc_html__( "Property's address and your contact number", 'wpbooking' ),
                            ],
                            [
                                'label'           => esc_html__( 'Address', 'wpbooking' ),
                                'id'              => 'address',
                                'type'            => 'address',
                                'container_class' => 'mb35',
                                'extra_rules'     => [
                                    'location_id' => [ 'label' => esc_html__( 'Location', 'wpbooking' ), 'rule' => 'required_integer' ],
                                    'address'     => [ 'label' => esc_html__( 'Address', 'wpbooking' ), 'rule' => 'required' ],
                                ]
                            ],
                            [
                                'label' => esc_html__( 'Map\'s Latitude & Longitude', 'wpbooking' ),
                                'id'    => 'gmap',
                                'type'  => 'gmap',
                                'desc'  => esc_html__( 'This is the location we will provide for guests. Click to move the marker if you need to move it', 'wpbooking' )
                            ],
                            [
                                'type'    => 'desc_section',
                                'title'   => esc_html__( 'Your address matters! ', 'wpbooking' ),
                                'content' => esc_html__( 'Please make sure to enter your full address including building name, apartment number, etc.', 'wpbooking' )
                            ],
                            [ 'type' => 'close_section' ],
                            [
                                'type' => 'section_navigation',
                                'prev' => false,
                                'step' => 'first'
                            ]

                        ]
                    ],
                    'detail_tab'      => [
                        'label'  => esc_html__( '2. Property Details', 'wpbooking' ),
                        'fields' => [
                            [ 'type' => 'open_section' ],
                            [
                                'label' => esc_html__( "Check In & Check Out", 'wpbooking' ),
                                'type'  => 'title',
                                'desc'  => esc_html__( 'Time to check in, out in your property', 'wpbooking' )
                            ],
                            [
                                'label'  => esc_html__( 'Time for Check In ', 'wpbooking' ),
                                'desc'   => esc_html__( 'Time for Check In ', 'wpbooking' ),
                                'type'   => 'check_in',
                                'id'     => 'check_in',
                                'fields' => [ 'checkin_from', 'checkin_to' ],// Fields to save
                            ],
                            [
                                'label'  => esc_html__( 'Time for Check Out', 'wpbooking' ),
                                'desc'   => esc_html__( 'Time for Check Out', 'wpbooking' ),
                                'type'   => 'check_out',
                                'id'     => 'check_out',
                                'fields' => [ 'checkout_from', 'checkout_to' ],// Fields to save
                            ],
                            [ 'type' => 'close_section' ],

                            // Miscellaneous
                            [ 'type' => 'open_section' ],
                            [
                                'label' => esc_html__( "Amenity", 'wpbooking' ),
                                'type'  => 'title',
                            ],
                            [
                                'label'    => esc_html__( "Amenity", 'wpbooking' ),
                                'id'       => 'wpbooking_select_amenity',
                                'taxonomy' => 'wpbooking_amenity',
                                'type'     => 'taxonomy_select',
                                'rules'    => 'required'
                            ],
                            [
                                'id'           => 'taxonomy_custom',
                                'type'         => 'taxonomy_custom',
                                'service_type' => $this->type_id
                            ],
                            [ 'type' => 'close_section' ],
                            // End Miscellaneous

                            [
                                'type' => 'section_navigation',
                            ],
                        ]
                    ],
                    'room_detail_tab' => [
                        'label'  => esc_html__( '3. Room Details', 'wpbooking' ),
                        'fields' => [
                            [
                                'label' => esc_html__( 'Your Rooms', 'wpbooking' ),
                                'type'  => 'hotel_room_list',
                                'desc'  => esc_html__( 'Here is an overview of your rooms', 'wpbooking' )
                            ],
                            [
                                'type'        => 'section_navigation',
                                'next_label'  => esc_html__( 'Next Step', 'wpbooking' ),
                                'ajax_saving' => 0
                            ],

                        ]
                    ],
                    'inventory_tab'   => [
                        'label'  => esc_html__( '4. Inventory', 'wpbooking' ),
                        'fields' => [
                            [
                                'label' => esc_html__( 'Inventory', 'wpbooking' ),
                                'type'  => 'accommodation_inventory',
                                'desc'  => ''
                            ],
                            [
                                'type' => 'section_navigation',
                            ],
                        ]
                    ],
                    'facilities_tab'  => [
                        'label'  => esc_html__( '5. Facilities', 'wpbooking' ),
                        'fields' => [
                            [
                                'type' => 'open_section',
                            ],
                            [
                                'label' => esc_html__( "Space", 'wpbooking' ),
                                'type'  => 'title',
                                'desc'  => esc_html__( "We display the size of guest room", "wpbooking" )
                            ],
                            [
                                'label' => esc_html__( 'What is your preferred  unit of measurement?', 'wpbooking' ),
                                'id'    => 'room_measunit',
                                'type'  => 'radio',
                                'value' => [
                                    "metres" => esc_html__( "Square metres", 'wpbooking' ),
                                    "feet"   => esc_html__( "Square feet", 'wpbooking' ),
                                ],
                                'std'   => 'metres',
                                'class' => 'radio_pro',
                                'desc'  => esc_html__( "Select the preferred unit of your measurement", "wpbooking" )
                            ],
                            [
                                'label' => esc_html__( 'Room size', 'wpbooking' ),
                                'id'    => 'room_size',
                                'type'  => 'text',
                            ],
                            [ 'type' => 'close_section' ],
                            [
                                'type' => 'open_section',
                            ],
                            [
                                'label' => esc_html__( "Room facilities", 'wpbooking' ),
                                'type'  => 'title',
                            ],
                            [
                                'id'       => 'hotel_room_facilities',
                                'label'    => esc_html__( "Facilities", 'wpbooking' ),
                                'type'     => 'taxonomy_room_select',
                                'taxonomy' => 'wb_hotel_room_facilities',
                                'rules'    => 'required'
                            ],

                            [ 'type' => 'close_section' ],


                            //Room amenities
                            [
                                'type' => 'section_navigation',
                            ],
                        ]
                    ],
                    'policies_tab'    => [
                        'label'  => esc_html__( '6. Policies & Checkout', 'wpbooking' ),
                        'fields' => [
                            [ 'type' => 'open_section' ],
                            [
                                'label' => esc_html__( 'External Link', 'wpbooking' ),
                                'id'    => 'external_link',
                                'type'  => 'text',
                                'desc'  => esc_html__( 'Enter an external link to use this feature.', 'wpbooking' )
                            ],
                            [
                                'label' => esc_html__( "Payment information", 'wpbooking' ),
                                'type'  => 'title',
                                'desc'  => esc_html__( "Specify the methods of payment you accept at your accommodation as payment for staying", "wpbooking" )
                            ],
                            [
                                'label' => esc_html__( 'We are accepted:', 'wpbooking' ),
                                'id'    => 'creditcard_accepted',
                                'type'  => 'creditcard',
                            ],
                            [ 'type' => 'close_section' ],
                            [ 'type' => 'open_section' ],
                            [
                                'label' => esc_html__( "Pre-payment and cancellation policies", 'wpbooking' ),
                                'type'  => 'title',
                                'desc'  => esc_html__( "Pre-payment and cancellation policies", "wpbooking" )
                            ],
                            [
                                'label' => esc_html__( 'Select optional deposit ', 'wpbooking' ),
                                'id'    => 'deposit_payment_status',
                                'type'  => 'dropdown',
                                'value' => [
                                    ''        => esc_html__( 'Disallow Deposit', 'wpbooking' ),
                                    'percent' => esc_html__( 'Deposit by percent', 'wpbooking' ),
                                    'amount'  => esc_html__( 'Deposit by amount', 'wpbooking' ),
                                ],
                                'desc'  => esc_html__( "You can select Disallow Deposit, Deposit by percent, Deposit by amount", "wpbooking" ),
                                'class' => 'small'
                            ],
                            [
                                'label'     => esc_html__( 'Deposit payment amount', 'wpbooking' ),
                                'id'        => 'deposit_payment_amount',
                                'type'      => 'number',
                                'desc'      => esc_html__( "Leave empty for disallow deposit payment", "wpbooking" ),
                                'class'     => 'small',
                                'min'       => 1,
                                'rules'     => 'required|integer|greater_than[0]',
                                'condition' => 'deposit_payment_status:not()'
                            ],
                            [
                                'label' => esc_html__( 'How many days in advance can guests cancel free of  charge?', 'wpbooking' ),
                                'id'    => 'cancel_free_days_prior',
                                'type'  => 'dropdown',
                                'value' => [
                                    'day_of_arrival' => esc_html__( 'Day of arrival (6 pm)', 'wpbooking' ),
                                    '1'              => esc_html__( '1 day', 'wpbooking' ),
                                    '2'              => esc_html__( '2 days', 'wpbooking' ),
                                    '3'              => esc_html__( '3 days', 'wpbooking' ),
                                    '7'              => esc_html__( '7 days', 'wpbooking' ),
                                    '14'             => esc_html__( '14 days', 'wpbooking' ),
                                ],
                                'desc'  => esc_html__( "Day of arrival ( 18: 00 ) , 1 day , 2 days, 3 days, 7 days, 14 days", "wpbooking" ),
                                'class' => 'small'
                            ],
                            [
                                'label' => esc_html__( 'Or guests will pay 100%', 'wpbooking' ),
                                'id'    => 'cancel_guest_payment',
                                'type'  => 'dropdown',
                                'value' => [
                                    'first_night' => esc_html__( 'of the first night', 'wpbooking' ),
                                    'full_stay'   => esc_html__( 'of the full stay', 'wpbooking' ),
                                ],
                                'desc'  => esc_html__( "Of the first night, of the full stay", "wpbooking" ),
                                'class' => 'small'
                            ],
                            [ 'type' => 'close_section' ],
                            [ 'type' => 'open_section' ],
                            [
                                'label' => esc_html__( "Tax", 'wpbooking' ),
                                'type'  => 'title',
                                'desc'  => esc_html__( "Set your local VAT or city tax, so guests know what is included in the price of their stay.", "wpbooking" )
                            ],
                            [
                                'label'       => esc_html__( 'VAT', 'wpbooking' ),
                                'id'          => 'vat_different',
                                'type'        => 'vat_different',
                                'fields'      => [
                                    'vat_excluded',
                                    'vat_amount',
                                    'vat_unit',
                                ],
                                'extra_rules' => [
                                    'vat_amount' => [ 'label' => esc_html__( 'VAT amount', 'wpbooking' ), 'rules' => 'required|greater_than[0]', 'rule_condition' => 'vat_excluded:not_empty' ]
                                ],

                            ],
                            [
                                'label'       => esc_html__( 'City Tax', 'wpbooking' ),
                                'id'          => 'citytax_different',
                                'type'        => 'citytax_different',
                                'fields'      => [
                                    'citytax_excluded',
                                    'citytax_amount',
                                    'citytax_unit',
                                ],
                                'extra_rules' => [
                                    'citytax_amount' => [ 'label' => esc_html__( 'City Tax amount', 'wpbooking' ), 'rules' => 'required|greater_than[0]', 'rule_condition' => 'citytax_excluded:not_empty' ]
                                ],
                            ],

                            [ 'type' => 'close_section' ],

                            [ 'type' => 'open_section' ],
                            [
                                'label' => esc_html__( "Term & condition", 'wpbooking' ),
                                'type'  => 'title',
                                'desc'  => esc_html__( "Set terms and conditions for your property", "wpbooking" )
                            ],
                            [
                                'label' => esc_html__( 'Minimum Stay (night)', 'wpbooking' ),
                                'id'    => 'minimum_stay',
                                'type'  => 'dropdown',
                                'value' => [
                                    1,
                                    2,
                                    3,
                                    4,
                                    5,
                                    6,
                                    7,
                                    8,
                                    9,
                                    10,
                                    11,
                                    12,
                                    13,
                                    14,
                                    15,
                                    16,
                                    17,
                                    18,
                                    19,
                                    20,
                                    21,
                                    22,
                                    23,
                                    24,
                                    25,
                                    26,
                                    27,
                                    28,
                                    29,
                                    30
                                ],
                                'class' => 'small'
                            ],
                            [
                                'label' => esc_html__( 'Maximum Stay (night)', 'wpbooking' ),
                                'id'    => 'maximum_stay',
                                'type'  => 'number',
                                'value' => '',
                                'class' => 'small'
                            ],
                            [
                                'label' => esc_html__( 'Terms & Conditions', 'wpbooking' ),
                                'id'    => 'terms_conditions',
                                'type'  => 'textarea',
                                'rows'  => '5',
                                'rules' => 'required'
                            ],
                            [ 'type' => 'close_section' ],
                            [
                                'type' => 'section_navigation',
                            ],
                        ],
                    ],
                    'photo_tab'       => [
                        'label'  => esc_html__( '7. Photos', 'wpbooking' ),
                        'fields' => [
                            [ 'type' => 'open_section' ],
                            [
                                'label' => esc_html__( "Pictures", 'wpbooking' ),
                                'type'  => 'title',
                            ],
                            [
                                'label'         => esc_html__( "Gallery", 'wpbooking' ),
                                'id'            => 'gallery',
                                'type'          => 'gallery_hotel',
                                'rules'         => 'array_key_required[gallery]',
                                'error_message' => esc_html__( 'You must upload one minimum photo for your accommodation', 'wpbooking' ),
                                'desc'          => esc_html__( 'Great photos invite guests to get the full experience of your property. Be sure to include high-resolution photos of the building, facilities, and amenities. We will display these photos on your property\'s page', 'wpbooking' )
                            ],
                            [ 'type' => 'close_section' ],
                            [
                                'type'       => 'section_navigation',
                                'next_label' => esc_html__( 'Save', 'wpbooking' ),
                                'step'       => 'finish'
                            ],
                        ]
                    ],
                ] );

            }


            /**
             * Get Room by Hotel Metabox Fields
             *
             * @since  1.0
             * @author quandq
             *
             * @param $post_id
             *
             * @return array|void|bool
             */
            function _get_room_by_hotel( $post_id )
            {
                if ( empty( $post_id ) )
                    return false;
                $list     = [];
                $args     = [
                    'post_type'      => 'wpbooking_hotel_room',
                    'post_parent'    => $post_id,
                    'posts_per_page' => 200,
                    'post_status'    => [ 'pending', 'future', 'publish' ],
                ];
                $my_query = new WP_Query( $args );
                if ( $my_query->have_posts() ) {
                    while ( $my_query->have_posts() ) {
                        $my_query->the_post();
                        $list[] = [ 'ID' => get_the_ID(), 'post_title' => get_the_title() ];
                    }
                }
                wp_reset_postdata();

                return $list;
            }

            /**
             * Get Room Metabox Fields
             *
             * @since  1.0
             * @author dungdt
             *
             * @return mixed|void
             */
            function get_room_meta_fields()
            {
                $fields = [
                    [
                        'type'     => 'breadcrumb',
                        'new_text' => esc_html__( 'Add new room', 'wpbooking' ),
                    ],
                    [ 'type' => 'open_section', 'conner_button' => '<a href="#" onclick="return false" class="wb-button wb-back-all-rooms"><i class="fa fa-chevron-circle-left fa-force-show" aria-hidden="true"></i> ' . esc_html__( 'Back to All Rooms', 'wpbooking' ) . '</a>' ],
                    [
                        'label' => esc_html__( "Room Name", 'wpbooking' ),
                        'type'  => 'title',
                    ],
                    [
                        'label' => esc_html__( 'Room name', 'wpbooking' ),
                        'type'  => 'text',
                        'id'    => 'room_name',
                        'desc'  => esc_html__( "Create an optional, custom name for your reference.", 'wpbooking' ),
                        'rules' => 'required'
                    ],
                    [
                        'label'    => esc_html__( 'Room Type', 'wpbooking' ),
                        'type'     => 'dropdown',
                        'id'       => 'room_type',
                        'taxonomy' => 'wb_hotel_room_type',
                        'parent'   => 0,
                        'class'    => 'small',
                        'desc'     => esc_html__( "Based on the amenities of room, select one most accurate type", 'wpbooking' ),
                    ],
                    [
                        'label' => esc_html__( 'Room Number', 'wpbooking' ),
                        'type'  => 'number',
                        'id'    => 'room_number',
                        'class' => 'small',
                        'rules' => 'required|integer|greater_than[0]',
                        'min'   => 1
                    ],
                    [
                        'label' => esc_html__( 'Bed Rooms', 'wpbooking' ),
                        'type'  => 'dropdown',
                        'id'    => 'bed_rooms',
                        'value' => [
                            1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
                        ],
                        'class' => 'small'
                    ],
                    [
                        'label' => esc_html__( 'Bath Rooms', 'wpbooking' ),
                        'type'  => 'dropdown',
                        'id'    => 'bath_rooms',
                        'value' => [
                            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
                        ],
                        'std'   => 0,
                        'class' => 'small'
                    ],
                    [
                        'label' => esc_html__( 'Living Rooms', 'wpbooking' ),
                        'type'  => 'dropdown',
                        'id'    => 'living_rooms',
                        'value' => [
                            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
                        ],
                        'class' => 'small'
                    ],
                    [
                        'label' => esc_html__( 'Max guests', 'wpbooking' ),
                        'type'  => 'dropdown',
                        'id'    => 'max_guests',
                        'value' => [
                            1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
                        ],
                        'class' => 'small'
                    ],
                    [ 'type' => 'close_section' ],

                    // Extra Service
                    [ 'type' => 'open_section' ],
                    [
                        'type'  => 'title',
                        'label' => esc_html__( 'Extra Services', 'wpbooking' ),
                        'desc'  => esc_html__( 'Set the extended services for your property', 'wpbooking' )
                    ],
                    [
                        'type'           => 'extra_services',
                        'label'          => esc_html__( 'Choose extra services', 'wpbooking' ),
                        'id'             => 'extra_services',
                        'extra_services' => $this->get_extra_services(),
                        'service_type'   => $this->type_id
                    ],
                    [
                        'type'  => 'dropdown',
                        'label' => esc_html__( 'Type of Extra price', 'wpbooking' ),
                        'id'    => 'type_extra',
                        'value' => [
                            ''         => esc_html__( 'Fixed', 'wpbooking' ),
                            'by_night' => esc_html__( 'per Night', 'wpbooking' )
                        ],
                        'std'   => ''
                    ],
                    [
                        'type' => 'close_section'
                    ],

                    // Calendar
                    [ 'type' => 'open_section' ],
                    [
                        'label' => esc_html__( "Price Settings", 'wpbooking' ),
                        'type'  => 'title',
                        'desc'  => esc_html__( 'You can set price for room', 'wpbooking' )
                    ],
                    [
                        'id'          => 'calendar',
                        'type'        => 'calendar',
                        'extra_rules' => [
                            'base_price' => [
                                'label' => esc_html__( 'Base Price', 'wpbooking' ),
                                'rule'  => 'required|greater_than[0]',
                            ]
                        ],
                    ],
                    [ 'type' => 'close_section' ],
                ];

                return apply_filters( 'wpbooking_hotel_room_meta_fields', $fields );
            }


            /**
             * Ajax Show Room Form
             *
             * @since  1.0
             * @author dungdt
             */
            function _ajax_room_edit_template()
            {
                $res      = [
                    'status' => 0
                ];
                $room_id  = $this->post( 'room_id' );
                $hotel_id = trim( $this->post( 'hotel_id' ) );


                if ( !$room_id ) {

                    // Validate Permission
                    if ( !$hotel_id ) {
                        $res[ 'message' ] = esc_html__( 'Please specify Property ID', 'wpbooking' );
                        echo json_encode( $res );
                        die;
                    } else {
                        $hotel = get_post( $hotel_id );
                        if ( !$hotel ) {
                            $res[ 'message' ] = esc_html__( 'Property does not exist', 'wpbooking' );
                            echo json_encode( $res );
                            die;
                        }
                        // Check Role
                        if ( !current_user_can( 'edit_posts' ) and $hotel->post_parent != get_current_user_id() ) {
                            $res[ 'message' ] = esc_html__( 'You do not have permission to do it', 'wpbooking' );
                            echo json_encode( $res );
                            die;
                        }

                    }


                    // Create Draft Room
                    $room_id = wp_insert_post( [
                        'post_author' => get_current_user_id(),
                        'post_title'  => esc_html__( 'Room Draft', 'wpbooking' ),
                        'post_type'   => 'wpbooking_hotel_room',
                        'post_status' => 'draft',
                        'post_parent' => $hotel_id
                    ] );

                    if ( is_wp_error( $room_id ) ) {
                        $res[ 'message' ] = esc_html__( 'Room cannot created, please check again', 'wpbooking' );
                        echo json_encode( $res );
                        die;
                    }
                }

                $res[ 'status' ] = 1;
                $res[ 'html' ]   = "
            
                <input name='wb_room_id' type='hidden' value='" . esc_attr( $room_id ) . "'>
            ";
                $res[ 'html' ]   .= sprintf( '<input type="hidden" name="wb_hotel_room_security" value="%s">', wp_create_nonce( "wpbooking_hotel_room_" . $room_id ) );

                $res[ 'html' ] .= '<div class="wb-back-all-rooms-wrap"><a href="#" onclick="return false" class="wb-button wb-back-all-rooms"><i class="fa fa-chevron-circle-left fa-force-show" aria-hidden="true"></i> ' . esc_html__( 'Back to All Rooms', 'wpbooking' ) . '</a></div>';
                $fields        = $this->get_room_meta_fields();
                foreach ( (array)$fields as $field_id => $field ):

                    if ( empty( $field[ 'type' ] ) )
                        continue;

                    $default = [
                        'id'          => '',
                        'label'       => '',
                        'type'        => '',
                        'desc'        => '',
                        'std'         => '',
                        'class'       => '',
                        'location'    => false,
                        'map_lat'     => '',
                        'map_long'    => '',
                        'map_zoom'    => 13,
                        'server_type' => '',
                        'width'       => ''
                    ];

                    $field = wp_parse_args( $field, $default );

                    $class_extra = false;
                    if ( $field[ 'location' ] == 'hndle-tag' ) {
                        $class_extra = 'wpbooking-hndle-tag-input';
                    }
                    $file       = 'metabox-fields/' . $field[ 'type' ];
                    $field_html = apply_filters( 'wpbooking_metabox_field_html_' . $field[ 'type' ], false, $field );

                    if ( $field_html )
                        $res[ 'html' ] .= $field_html;
                    else
                        $res[ 'html' ] .= wpbooking_admin_load_view( $file, [
                            'data'        => $field,
                            'class_extra' => $class_extra,
                            'post_id'     => $room_id
                        ] );


                endforeach;

                $res[ 'html' ] .= wpbooking_admin_load_view( 'metabox-fields/room-form-button' );

                echo json_encode( $res );
                die;
            }

            public function _duplicate_post()
            {
                check_ajax_referer( 'wpbooking-nonce-field', 'security' );
                $post_id         = WPBooking_Input::post( 'id_post' );
                $post_id_origin  = wpbooking_origin_id( $post_id, get_post_type( $post_id ) );
                $post_translated = wpbooking_post_translated( $post_id, get_post_type( $post_id ) );

                global $sitepress;
                $current_lang = wpbooking_current_lang();
                $sitepress->switch_lang( wpbooking_default_lang(), true );

                if ( wpbooking_is_wpml() ) {
                    $query = new WP_Query( [
                        'post_parent'    => $post_id_origin,
                        'posts_per_page' => 200,
                        'post_type'      => 'wpbooking_hotel_room'
                    ] );
                    if ( $query->have_posts() ) {
                        while ( $query->have_posts() ) {
                            $query->the_post();
                            $this->duplicate_service( get_the_ID() );
                        }
                    }
                    wp_reset_postdata();

                    $sitepress->switch_lang( $current_lang, true );

                    update_post_meta( $post_id, 'wpbooking_duplicated', 'duplicated' );

                    $query = new WP_Query( [
                        'post_parent'      => $post_translated,
                        'posts_per_page'   => 200,
                        'post_type'        => 'wpbooking_hotel_room',
                        'suppress_filters' => 0
                    ] );
                    $html  = '';
                    if ( $query->have_posts() ) {
                        @ob_start();
                        while ( $query->have_posts() ): $query->the_post(); ?>
                            <div class="room-item item-hotel-room-<?php echo (int)get_the_ID(); ?>">
                                <div class="room-item-wrap">
                                    <div class="room-remain">
                                        <span class="room-remain-left"><?php printf( esc_html__( '%d room(s)', 'wpbooking' ), get_post_meta( get_the_ID(), 'room_number', true ) ) ?></span>
                                    </div>
                                    <div class="room-image">
                                        <?php
                                            $thumbnail = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), [ 220, 120 ] );
                                            echo do_shortcode( $thumbnail );
                                        ?>
                                    </div>
                                    <h3 class="room-type"><?php the_title(); ?></h3>
                                    <div class="room-actions">
                                        <a href="#" data-room_id="<?php the_ID() ?>" class="room-edit tooltip_desc"><i
                                                    class="fa fa-pencil-square-o"></i> <span
                                                    class="tooltip_content"><?php esc_html_e( 'Edit', 'wpbooking' ) ?></span></a>
                                        <?php $del_security_post = wp_create_nonce( 'del_security_post_' . get_the_ID() ); ?>
                                        <a href="javascript:void(0)" data-room_id="<?php the_ID(); ?>"
                                           data-del-security="<?php echo esc_attr( $del_security_post ); ?>"
                                           data-confirm="<?php echo esc_html__( 'Do you want delete this room?', 'wpbooking' ); ?>"
                                           class="room-delete tooltip_desc"><i class="fa fa-trash"></i><span
                                                    class="tooltip_content"><?php esc_html_e( 'Delete', 'wpbooking' ) ?></span></a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile;
                        $html = @ob_get_clean();
                    }
                    wp_reset_postdata();
                    echo json_encode( [
                        'status'  => 1,
                        'html'    => $html,
                        'message' => 'duplicated'
                    ] );
                    die;
                }
                echo json_encode( [
                    'status'  => 0,
                    'message' => 'Error'
                ] );
                die;
            }

            private function duplicate_service( $post_id )
            {
                global $sitepress;
                $translation_management = new TranslationManagement();
                $sitepress->switch_lang( wpbooking_default_lang(), true );

                $languages   = wpbooking_all_langs( true );
                $master_post = get_post( $post_id );
                foreach ( $languages as $code ) {
                    $title          = $master_post->post_title;
                    $content        = $master_post->post_content;
                    $excerpt        = $master_post->post_excerpt;
                    $post_type      = $master_post->post_type;
                    $author         = get_current_user_id();
                    $status         = 'publish';
                    $featured_image = get_post_thumbnail_id( $post_id );
                    $args           = [
                        'post_type'    => $post_type,
                        'post_author'  => $author,
                        'post_status'  => $status,
                        'post_title'   => $title,
                        'post_name'    => sanitize_title( $title ),
                        'post_content' => $content,
                        'post_excerpt' => $excerpt
                    ];
                    if ( $master_post->post_parent ) {
                        $parent                = $sitepress->get_object_id( $master_post->post_parent, $master_post->post_type, false, trim( $code ) );
                        $args[ 'post_parent' ] = $parent;
                    }

                    $post_translated = wpbooking_post_translated( $post_id, $post_type, trim( $code ) );
                    if ( $post_translated == $post_id ) {
                        $create_post_helper = wpml_get_create_post_helper();

                        $post_translated = $create_post_helper->insert_post( $args, $code, true );

                        $trid = $sitepress->get_element_trid( $post_id, 'post_' . $post_type );
                        $sitepress->set_element_language_details( $post_translated, 'post_' . $post_type, $trid, trim( $code ) );

                        require_once WPML_PLUGIN_PATH . '/inc/cache.php';

                        icl_cache_clear();

                        if ( $sitepress->get_option( 'sync_post_taxonomies' ) ) {
                            $this->duplicate_taxonomies( $post_id, trim( $code ) );
                        }
                        update_post_meta( $post_translated, '_icl_lang_duplicate_of', $post_id );

                        $status_helper = wpml_get_post_status_helper();
                        $status_helper->set_status( $post_translated, ICL_TM_DUPLICATE );
                        $status_helper->set_update_status( $post_translated, false );

                        global $wpdb;
                        $sql = "INSERT INTO {$wpdb->prefix}postmeta (
                            post_id,
                            meta_key,
                            meta_value
                        ) SELECT
                            {$post_translated},
                            meta_key,
                            meta_value
                        FROM
                            {$wpdb->prefix}postmeta
                        WHERE
                            post_id = {$post_id}";
                        $wpdb->query( $sql );
                        $translation_management->reset_duplicate_flag( $post_translated );
                        do_action( 'icl_make_duplicate', $post_id, trim( $code ), $args, $post_translated );

                        if ( $featured_image ) {
                            update_post_meta( $post_translated, '_thumbnail_id', $featured_image );
                        }


                    }

                }
                wp_reset_query();

            }

            private function duplicate_taxonomies( $master_post_id, $lang )
            {
                global $sitepress;
                $post_type  = get_post_field( 'post_type', $master_post_id );
                $taxonomies = get_object_taxonomies( $post_type );
                $trid       = $sitepress->get_element_trid( $master_post_id, 'post_' . $post_type );
                if ( $trid ) {
                    $translations = $sitepress->get_element_translations( $trid, 'post_' . $post_type, false, false, true );
                    if ( isset( $translations[ $lang ] ) ) {
                        $duplicate_post_id = $translations[ $lang ]->element_id;
                        /* If we have an existing post, we first of all remove all terms currently attached to it.
                         * The main reason behind is the removal of the potentially present default category on the post.
                         */
                        wp_delete_object_term_relationships( $duplicate_post_id, $taxonomies );
                    } else {
                        return false; // translation not found!
                    }
                }
                $term_helper = wpml_get_term_translation_util();
                $term_helper->duplicate_terms( $master_post_id, $lang );

                return true;
            }

            /**
             * Ajax Save Room Data
             *
             * @since  1.0
             * @author dungdt
             */
            public function _ajax_save_room()
            {
                $res     = [ 'status' => 0 ];
                $room_id = $this->post( 'wb_room_id' );
                if ( $room_id ) {
                    // Validate
                    check_ajax_referer( "wpbooking_hotel_room_" . $room_id, 'wb_hotel_room_security' );
                    if ( $name = $this->request( 'room_name' ) ) {
                        $my_post = [
                            'ID'          => $room_id,
                            'post_title'  => $name,
                            'post_status' => 'publish',
                        ];
                        wp_update_post( $my_post );
                    }
                    $fields        = $this->get_room_meta_fields();
                    $form_validate = new WPBooking_Form_Validator();
                    $need_validate = false;
                    $is_validated  = true;
                    foreach ( $fields as $field ) {

                        if ( !empty( $field[ 'rules' ] ) ) {
                            $need_validate = true;
                            $form_validate->set_rules( $field[ 'id' ], $field[ 'label' ], $field[ 'rules' ] );
                        }
                        if ( !empty( $field[ 'extra_rules' ] ) and is_array( $field[ 'extra_rules' ] ) ) {
                            $need_validate = true;
                            foreach ( $field[ 'extra_rules' ] as $name => $rule ) {
                                $form_validate->set_rules( $name, $rule[ 'label' ], $rule[ 'rule' ] );
                            }

                        }

                    }
                    if ( $need_validate ) {
                        $is_validated = $form_validate->run();

                        if ( !$is_validated ) $res[ 'error_fields' ] = $form_validate->get_error_fields();
                    }
                    if ( $is_validated ) {
                        WPBooking_Metabox::inst()->do_save_metabox( $room_id, $fields, 'wpbooking_hotel_room_form' );

                        // Save Extra Fields
                        //property_available_for
                        if ( isset( $_POST[ 'property_available_for' ] ) ) update_post_meta( $room_id, 'property_available_for', $_POST[ 'property_available_for' ] );
                        $hotel_id      = wp_get_post_parent_id( $room_id );
                        $list_room_new = $this->_get_room_by_hotel( $hotel_id );


                        $list_room_new                = json_encode( $list_room_new );
                        $res[ 'data' ][ 'list_room' ] = $list_room_new;

                        $res[ 'data' ][ 'number' ]    = get_post_meta( $room_id, 'room_number', true );
                        $res[ 'data' ][ 'thumbnail' ] = '';
                        $res[ 'data' ][ 'title' ]     = get_the_title( $room_id );
                        $res[ 'data' ][ 'room_id' ]   = $room_id;
                        $res[ 'data' ][ 'security' ]  = wp_create_nonce( 'del_security_post_' . $room_id );
                        $updated_content              = [
                            '.wp-room-actions .room-count' => $this->_get_room_count_text( $hotel_id )
                        ];
                        $res[ 'updated_content' ]     = apply_filters( 'wpbooking_hotel_room_form_updated_content', $updated_content, $room_id, $hotel_id );

                        $res[ 'status' ] = 1;

                        do_action( 'wpbooking_after_save_room_hotel', $hotel_id );
                    }
                }
                echo json_encode( $res );
                die;
            }

            /**
             * Ajax delete room
             *
             * @since : 1.0
             * @author: Tien37
             */

            public function _ajax_del_room_item()
            {
                $res      = [ 'status' => 0 ];
                $room_id  = $this->post( 'wb_room_id' );
                $hotel_id = wp_get_post_parent_id( $room_id );
                if ( $room_id ) {
                    check_ajax_referer( 'del_security_post_' . $room_id, 'wb_del_security' );
                    $parent_id = wp_get_post_parent_id( $room_id );
                    if ( wp_delete_post( $room_id ) !== false ) {
                        $res[ 'status' ]              = 1;
                        $list_room_new                = $this->_get_room_by_hotel( $parent_id );
                        $list_room_new                = json_encode( $list_room_new );
                        $res[ 'data' ][ 'list_room' ] = $list_room_new;
                    }
                }
                $updated_content          = [
                    '.wp-room-actions .room-count' => $this->_get_room_count_text( $hotel_id )
                ];
                $res[ 'updated_content' ] = apply_filters( 'wpbooking_hotel_room_form_updated_content', $updated_content, $room_id, $hotel_id );
                echo json_encode( $res );
                wp_die();
            }

            /**
             * Get Hotel Room Count HTML for List Room in Dashboard
             *
             * @since  1.0
             * @author dungft
             *
             * @param $hotel_id
             * @param bool @query
             *
             * @return string
             *
             */
            public function _get_room_count_text( $hotel_id, $query = false )
            {
                if ( !$query ) {
                    $query = new WP_Query( [
                        'post_parent'    => $hotel_id,
                        'posts_per_page' => 200,
                        'post_type'      => 'wpbooking_hotel_room',
                        'post_status'    => [ 'pending', 'future', 'publish' ],
                    ] );
                }

                $total_room = 0;
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $total_room += get_post_meta( get_the_ID(), 'room_number', true );
                }

                if ( $query->found_posts ) {
                    $text_count = sprintf( '<span class="n text-color">%d </span><b>%s</b> ', $query->found_posts, esc_html__( 'room type(s)', 'wpbooking' ) );
                    if ( $total_room ) {
                        $text_count .= sprintf( esc_html__( 'with %s ', 'wpbooking' ), sprintf( '<span class="n text-color">%d </span><b>%s</b>', $total_room, esc_html__( 'room(s)', 'wpbooking' ) ) );
                    }
                    $html = '<div class="room-count">' . sprintf( esc_html__( 'There are %s in your listing', 'wpbooking' ), $text_count ) . '</div>';
                } else {
                    $html = '<div class="room-count">' . esc_html__( 'There is no room in your listing', 'wpbooking' ) . '</div>';
                }

                wp_reset_postdata();

                return $html;
            }

            /**
             * Ajax search room
             *
             * @since : 1.0
             * @author: quandq
             */
            function ajax_search_room()
            {
                if ( $this->post( 'room_search' ) ) {
                    if ( !wp_verify_nonce( $this->post( 'room_search' ), 'room_search' ) ) {
                        $result = [
                            'status' => 0,
                            'data'   => "",
                        ];
                        echo json_encode( $result );
                        die;
                    }
                    $result = [
                        'status' => 1,
                        'data'   => "",
                    ];
                    if ( empty( $hotel_id ) ) $hotel_id = WPBooking_Input::request( 'hotel_id', 0 );
                    $query = $this->search_room( $hotel_id );
                    if ( $query->have_posts() ) {
                        while ( $query->have_posts() ) {
                            $query->the_post();
                            $result[ 'data' ] .= wpbooking_load_view( 'single/loop-room', [ 'hotel_id' => $hotel_id ] );
                        }
                        $result[ 'pagination' ] = wpbooking_pagination_room( $query );
                    } else {
                        $result = [
                            'status'         => 0,
                            'data'           => '',
                            'message'        => esc_html__( 'Our system does not find any rooms from your searching. You can change search feature now.', 'wpbooking' ),
                            'status_message' => 'default',
                        ];
                        echo json_encode( $result );
                        die;
                    }
                    $check_in  = $this->request( 'checkin_y' ) . "-" . $this->request( 'checkin_m' ) . "-" . $this->request( 'checkin_d' );
                    $check_out = $this->request( 'checkout_y' ) . "-" . $this->request( 'checkout_m' ) . "-" . $this->request( 'checkout_d' );
                    if ( $check_in == '--' ) $check_in = '';
                    if ( $check_out == '--' ) $check_out = '';
                    // Validate Minimum Stay
                    if ( $check_in and $check_out ) {
                        $service             = new WB_Service( WPBooking_Input::request( 'hotel_id' ) );
                        $check_in_timestamp  = strtotime( $check_in );
                        $check_out_timestamp = strtotime( $check_out );
                        $minimum_stay        = $service->get_minimum_stay();
                        $dDiff               = wpbooking_timestamp_diff_day( $check_in_timestamp, $check_out_timestamp );
                        if ( $dDiff < $minimum_stay ) {
                            $result[ 'message' ] = sprintf( esc_html__( 'This %s required minimum stay is %s night(s).', 'wpbooking' ), $service->get_type(), $minimum_stay );
                            $result[ 'status' ]  = 2;
                        }
                        $maximum_stay = $service->get_maximum_stay();
                        if ( $maximum_stay > 0 && $dDiff > $maximum_stay ) {
                            $result[ 'message' ] = sprintf( esc_html__( 'This %s required maximum stay is %s night(s).', 'wpbooking' ), $service->get_type(), $maximum_stay );
                            $result[ 'status' ]  = 2;
                        }
                    }

                    wp_reset_query();
                    echo json_encode( $result );
                    wp_die();
                }
            }

            function _add_default_query_hook()
            {
                global $wpdb;
                $injection = WPBooking_Query_Inject::inst();
                $tax_query = $injection->get_arg( 'tax_query' );

                //posts per page
                $posts_per_page = $this->get_option( 'posts_per_page', 10 );
                $injection->add_arg( 'posts_per_page', $posts_per_page );

                // Taxonomy
                $tax = $this->request( 'taxonomy' );
                if ( !empty( $tax ) and is_array( $tax ) ) {
                    $taxonomy_operator = $this->request( 'taxonomy_operator' );
                    $tax_query_child   = [];
                    foreach ( $tax as $key => $value ) {
                        if ( $value ) {
                            if ( !empty( $taxonomy_operator[ $key ] ) ) {
                                $operator = $taxonomy_operator[ $key ];
                            } else {
                                $operator = "OR";
                            }
                            if ( $operator == 'OR' ) $operator = 'IN';
                            $value = explode( ',', $value );
                            if ( !empty( $value ) and is_array( $value ) ) {
                                foreach ( $value as $k => $v ) {
                                    if ( !empty( $v ) ) {
                                        $ids[] = $v;
                                    }
                                }
                            }
                            if ( !empty( $ids ) ) {
                                $tax_query[] = [
                                    'taxonomy' => $key,
                                    'terms'    => $ids,
                                    'operator' => $operator,
                                ];
                            }
                            $ids = [];
                        }
                    }


                    if ( !empty( $tax_query_child ) )
                        $tax_query[] = $tax_query_child;
                }

                $check_in  = $this->request( 'checkin_y' ) . "-" . $this->request( 'checkin_m' ) . "-" . $this->request( 'checkin_d' );
                $check_out = $this->request( 'checkout_y' ) . "-" . $this->request( 'checkout_m' ) . "-" . $this->request( 'checkout_d' );
                if ( $check_in == '--' ) $check_in = '';
                if ( $check_out == '--' ) $check_out = '';
                // Validate Minimum Stay
                if ( $check_in and $check_out ) {
                    $check_in_timestamp  = strtotime( $check_in );
                    $check_out_timestamp = strtotime( $check_out );
                    $dDiff               = wpbooking_timestamp_diff_day( $check_in_timestamp, $check_out_timestamp );
                    $meta_query[]        = [
                        'relation' => 'AND',
                        [
                            'key'     => 'minimum_stay',
                            'type'    => 'NUMERIC',
                            'value'   => $dDiff,
                            'compare' => '<='
                        ]
                    ];
                }

                // Star Rating
                if ( $star_rating = $this->get( 'star_rating' ) and is_array( explode( ',', $star_rating ) ) ) {

                    $star_rating_arr = explode( ',', $star_rating );
                    $meta_query[]    = [
                        'relation' => 'AND',
                        [
                            'key'     => 'star_rating',
                            'type'    => 'CHAR',
                            'value'   => $star_rating_arr,
                            'compare' => 'IN'
                        ]
                    ];
                }
                // Review

                if ( !empty( $tax_query ) )
                    $injection->add_arg( 'tax_query', $tax_query );

                if ( !empty( $meta_query ) )
                    $injection->add_arg( 'meta_query', $meta_query );


                $injection->add_arg( 'post_status', 'publish' );

                // Price
                if ( $price = WPBooking_Input::get( 'price' ) ) {
                    $array = explode( ';', $price );
                    $injection->select( '
                           MIN(
                                    CAST(
                                        wpb_room_meta.meta_value AS DECIMAL
                                    )
                                ) AS wpb_base_price' )
                        ->join( 'posts as wpb_room', $wpdb->prefix . 'posts.ID = wpb_room.post_parent' )
                        ->join( 'postmeta as wpb_room_meta', 'wpb_room_meta.post_id= wpb_room.ID and wpb_room_meta.meta_key = \'base_price\'' );
                    if ( !empty( $array[ 0 ] ) ) {
                        $injection->having( 'wpb_base_price >= ' . $array[ 0 ] );
                    }
                    if ( !empty( $array[ 1 ] ) ) {
                        $injection->having( 'wpb_base_price <= ' . $array[ 1 ] );
                    }
                }

                // Order By
                if ( $sortby = $this->request( 'wb_sort_by' ) ) {
                    switch ( $sortby ) {
                        case "price_asc":
                            $injection->select( 'MIN(CAST(order_table.meta_value as DECIMAL)) as min_price' );
                            $injection->join( 'posts as post_table', "post_table.post_parent={$wpdb->posts}.ID", 'left' );
                            $injection->join( 'postmeta as order_table', "order_table.post_ID=post_table.ID and order_table.meta_key='base_price' and order_table.meta_value>0", 'left' );
                            $injection->orderby( 'min_price', 'asc' );

                            break;
                        case "price_desc":
                            $injection->select( 'MIN(CAST(order_table.meta_value as DECIMAL)) as min_price' );
                            $injection->join( 'posts as post_table', "post_table.post_parent={$wpdb->posts}.ID", 'left' );
                            $injection->join( 'postmeta as order_table', "order_table.post_ID=post_table.ID and order_table.meta_key='base_price' and order_table.meta_value>0", 'left' );
                            $injection->orderby( 'min_price', 'desc' );
                            break;
                        case "date_asc":
                            $injection->add_arg( 'orderby', 'date' );
                            $injection->add_arg( 'order', 'asc' );
                            break;
                        case "date_desc":
                            $injection->add_arg( 'orderby', 'date' );
                            $injection->add_arg( 'order', 'desc' );
                            break;
                        case "rate_asc":
                        case "rate_desc":
                            $injection->select( 'avg(' . $wpdb->commentmeta . '.meta_value) as avg_rate' )
                                ->join( 'comments', $wpdb->prefix . 'comments.comment_post_ID=' . $wpdb->posts . '.ID and  ' . $wpdb->comments . '.comment_approved=1', 'LEFT' )
                                ->join( 'commentmeta', $wpdb->prefix . 'commentmeta.comment_id=' . $wpdb->prefix . 'comments.comment_ID and ' . $wpdb->commentmeta . ".meta_key='wpbooking_review'", 'LEFT' );
                            if ( $sortby == 'rate_asc' ) {
                                $injection->orderby( 'avg_rate', 'asc' );
                            } else {
                                $injection->orderby( 'avg_rate', 'desc' );
                            }

                            break;
                        case "name_a_z":
                            $injection->add_arg( 'orderby', 'post_title' );
                            $injection->add_arg( 'order', 'asc' );
                            break;
                        case "name_z_a":
                            $injection->add_arg( 'orderby', 'post_title' );
                            $injection->add_arg( 'order', 'desc' );
                            break;
                    }
                }
                $sql = "
            {$wpdb->posts}.ID IN (
                    (
                        SELECT
                            hotel_id
                        FROM
                            (
                                SELECT
                                    {$wpdb->posts}.ID AS room_id,
                                    {$wpdb->posts}.post_parent AS hotel_id
                                FROM
                                    {$wpdb->posts}
                                JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
                                AND {$wpdb->postmeta}.meta_key = 'room_number'
                                WHERE
                                    {$wpdb->posts}.post_type = 'wpbooking_hotel_room'
                                AND {$wpdb->postmeta}.meta_value > 0
                                AND {$wpdb->posts}.post_status = 'publish'
                                GROUP BY
                                    hotel_id
                            ) AS ID
                    )
                )
            ";


                $injection->where( $sql, false, true );
                parent::_add_default_query_hook();

            }

            /**
             *  Query Room
             *
             * @since : 1.0
             * @author: quandq
             *
             * @param bool $hotel_id
             *
             * @return WP_Query
             */
            function search_room( $hotel_id = false )
            {
                if ( empty( $hotel_id ) ) $hotel_id = get_the_ID();
                $inject = WPBooking_Query_Inject::inst();
                $inject->inject();

                $check_in  = $this->request( 'checkin_y' ) . "-" . $this->request( 'checkin_m' ) . "-" . $this->request( 'checkin_d' );
                $check_out = $this->request( 'checkout_y' ) . "-" . $this->request( 'checkout_m' ) . "-" . $this->request( 'checkout_d' );
                if ( $check_in == '--' ) $check_in = '';
                if ( $check_out == '--' ) $check_out = '';

                $number_room     = $this->request( 'room_number', 1 );
                $is_minimum_stay = true;
                if ( $check_in and $check_out ) {
                    $service             = new WB_Service( WPBooking_Input::request( 'hotel_id' ) );
                    $check_in_timestamp  = strtotime( $check_in );
                    $check_out_timestamp = strtotime( $check_out );
                    $minimum_stay        = $service->get_minimum_stay();
                    $dDiff               = wpbooking_timestamp_diff_day( $check_in_timestamp, $check_out_timestamp );
                    if ( $dDiff < $minimum_stay ) {
                        $is_minimum_stay = false;
                    }
                }
                if ( $is_minimum_stay ) {
                    $ids_not_in = $this->get_unavailability_hotel_room( $hotel_id, $check_in, $check_out, $number_room );
                    $inject->where_not_in( 'ID', $ids_not_in );
                }

                $post_per_page = $this->request( 'wpbooking_post_per_page', 10 );
                $page          = $this->request( 'wpbooking_paged' );
                $arg           = [
                    'post_type'      => 'wpbooking_hotel_room',
                    'posts_per_page' => $post_per_page,
                    'post_status'    => 'publish',
                    'post_parent'    => $hotel_id,
                    'paged'          => $page
                ];
                $adults        = $this->request( 'adults' );
                $children      = $this->request( 'children' );
                $max_guests    = $adults + $children;
                if ( !empty( $max_guests ) ) {
                    $arg[ 'meta_query' ][] = [
                        'key'     => 'max_guests',
                        'value'   => $max_guests,
                        'compare' => '>=',
                        'type'    => 'NUMERIC',
                    ];
                }
                $arg[ 'meta_query' ][] = [
                    'key'     => 'room_number',
                    'value'   => $number_room,
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                ];
                $query                 = new WP_Query( $arg );
                $inject->clear();

                return $query;
            }

            /**
             * Get List Unavailability Room
             *
             * @since : 1.0
             * @author: quandq
             *
             * @param     $hotel_id
             * @param     $check_in
             * @param     $check_out
             * @param int $number_room
             *
             * @return array
             */
            function get_unavailability_hotel_room( $hotel_id, $check_in, $check_out, $number_room = 1 )
            {

                if ( empty( $hotel_id ) or empty( $check_in ) or empty( $check_out ) or empty( $number_room ) ) {
                    return [];
                }
                $check_in  = strtotime( $check_in );
                $check_out = strtotime( $check_out );
                if ( empty( $hotel_id ) or empty( $check_in ) ) {
                    return [];
                }
                global $wpdb;
                $hotel_id_origin = wpbooking_origin_id( $hotel_id, 'wpbooking_service' );
                $extra_clause    = "OR {$wpdb->posts}.ID IN (
                        SELECT
                            post_id
                        FROM
                            (
                                SELECT
                                    post_id
                                FROM
                                    {$wpdb->prefix}wpbooking_availability
                                WHERE
                                    1 = 1
                                AND (
                                    `start` >= {$check_in}
                                    AND
                                    `end` <= {$check_out}
                                    AND `status` = 'not_available'
                                )
                                GROUP BY
                                    post_id
                            )as table_availability
                    )";
                if ( wpbooking_is_wpml() ) {
                    $lang         = wpbooking_current_lang();
                    $extra_clause = "OR {$wpdb->posts}.ID IN (
                        SELECT
                            table_availability.post_id
                        FROM(
                        SELECT
                            translation.element_id as post_id
                        FROM
                            {$wpdb->prefix}wpbooking_availability AS avai
                        INNER JOIN {$wpdb->prefix}icl_translations AS translation ON (
                            translation.trid = avai.post_id
                            AND translation.language_code = '{$lang}'
                        )
                        WHERE
                            1 = 1
                        AND (
                            `start` >= {$check_in}
                            AND `end` <= {$check_out}
                            AND `status` = 'not_available'
                        )
                        GROUP BY
                            translation.element_id
                            )as table_availability
                    )";
                }
                $sql = "
                SELECT
                    {$wpdb->posts}.ID
                FROM
                    {$wpdb->posts}
                WHERE
                    1 = 1
                AND {$wpdb->posts}.post_type = 'wpbooking_hotel_room'
                AND {$wpdb->posts}.post_parent = {$hotel_id}
                AND (
                     {$wpdb->posts}.ID IN (
                        SELECT
                            room_id
                        FROM
                            (
                                SELECT
                                    {$wpdb->prefix}wpbooking_order_hotel_room.room_id,
                                    count(id) AS total_booked,
                                    SUM({$wpdb->prefix}wpbooking_order_hotel_room.number) as total_number,
                                    {$wpdb->postmeta}.meta_value AS room_number
                                FROM
                                    {$wpdb->prefix}wpbooking_order_hotel_room
                                JOIN {$wpdb->postmeta} ON {$wpdb->postmeta}.post_id = {$wpdb->prefix}wpbooking_order_hotel_room.room_id_origin
                                AND {$wpdb->postmeta}.meta_key = 'room_number'
                                WHERE
                                    1 = 1
                                AND (
                                    (
                                        check_in_timestamp <= {$check_in}
                                        AND check_out_timestamp >= {$check_in}
                                    )
                                    OR (
                                        check_in_timestamp >= {$check_in}
                                        AND check_in_timestamp <= {$check_out}
                                    )
                                )
                                GROUP BY
                                    {$wpdb->prefix}wpbooking_order_hotel_room.room_id_origin
                                HAVING
                                    room_number - total_number < {$number_room}
                            ) AS table_booked
                    )
                    {$extra_clause}
                )";
                if ( $check_out <= $check_in ) {
                    $sql = "
                        SELECT
                            {$wpdb->posts}.ID
                        FROM
                            {$wpdb->posts}
                        WHERE
                            1 = 1
                        AND {$wpdb->posts}.post_type = 'wpbooking_hotel_room'
                        AND {$wpdb->posts}.post_parent = {$hotel_id}";
                }
                $r   = [];
                $res = $wpdb->get_results( $sql, ARRAY_A );
                if ( !is_wp_error( $res ) ) {
                    foreach ( $res as $key => $value ) {
                        $r[] = $value[ 'ID' ];
                    }
                }

                return $r;
            }

            /**
             * Filter List Room Size
             *
             * @since : 1.0
             * @author: quandq
             *
             * @param $data
             *
             * @return mixed
             */
            function _get_list_room_size( $data, $room_id, $hotel_id )
            {
                $html = '<div class="wpbooking-row room_size_content">';
                $arg  = [
                    'post_type'      => 'wpbooking_hotel_room',
                    'posts_per_page' => '200',
                    'post_status'    => [ 'pending', 'future', 'publish' ],
                    'post_parent'    => $hotel_id
                ];
                query_posts( $arg );
                while ( have_posts() ) {
                    the_post();
                    $html .= '<div class="wpbooking-col-sm-6">
                            <div class="form-group">
                                <p>' . get_the_title() . '</p>
                                <div class="input-group">
                                    <input class="form-control" id="room_size[' . get_the_ID() . ']" name="room_size[' . get_the_ID() . ']" type="number" value="' . get_post_meta( get_the_ID(), 'room_size', true ) . '">
                                    <span data-condition="room_measunit:is(metres)" class="input-group-addon wpbooking-condition">m<sup>2</sup></span>
                                    <span data-condition="room_measunit:is(feet)" class="input-group-addon wpbooking-condition">ft<sup>2</sup></span>
                                </div>
                            </div>
                        </div>';
                }
                $html .= '</div>';
                wp_reset_query();
                $data[ '.room_size_content' ] = $html;

                return $data;

            }

            /**
             * @param $size
             * @param $service_type
             * @param $post_id
             *
             * @return array
             */
            function _apply_thumb_size( $size, $service_type, $post_id )
            {
                if ( $service_type == $this->type_id ) {
                    $thumb = $this->thumb_size( '300,300,off' );
                    $thumb = explode( ',', $thumb );
                    if ( count( $thumb ) == 3 ) {
                        if ( $thumb[ 2 ] == 'off' ) $thumb[ 2 ] = false;

                        $size = [ $thumb[ 0 ], $thumb[ 1 ] ];
                    }

                }

                return $size;
            }

            /**
             * @param bool $defaultwpbooking_min_max_price_
             *
             * @return bool|mixed|void
             */
            function thumb_size( $default = false )
            {
                return $this->get_option( 'thumb_size', $default );
            }

            /**
             * Get Fields Search Form
             *
             * @since   : 1.0
             * @author  : quandq
             * @updated 1.5
             *
             * @return array
             */
            public function get_search_fields()
            {
                $taxonomy           = get_object_taxonomies( 'wpbooking_service', 'array' );
                $wpbooking_taxonomy = get_option( 'wpbooking_taxonomies' );
                $list_taxonomy      = [];
                if ( !empty( $taxonomy ) && !empty( $wpbooking_taxonomy ) ) {
                    foreach ( $taxonomy as $k => $v ) {
                        if ( $k == 'wpbooking_location' ) continue;
                        if ( $k == 'wpbooking_extra_service' ) continue;
                        if ( $k == 'wb_review_stats' ) continue;
                        if ( $k == 'wb_tour_type' ) continue;
                        if ( key_exists( $k, $wpbooking_taxonomy ) ) {
                            if ( !empty( $wpbooking_taxonomy[ $k ][ 'service_type' ] ) && in_array( 'accommodation', $wpbooking_taxonomy[ $k ][ 'service_type' ] ) ) {
                                $list_taxonomy[ $k ] = $v->label;
                            }
                        } else {
                            $list_taxonomy[ $k ] = $v->label;
                        }
                    }
                }

                /*
             * remove check_out field
             * */
                $search_fields = apply_filters( 'wpbooking_search_field_' . $this->type_id, [
                    [
                        'name'    => 'field_type',
                        'label'   => esc_html__( 'Field Type', "wpbooking" ),
                        'type'    => "dropdown",
                        'options' => [
                            ""            => esc_html__( "-- Select --", "wpbooking" ),
                            "location_id" => esc_html__( "Location Dropdown", "wpbooking" ),
                            "check_in"    => esc_html__( "Date", "wpbooking" ),
                            /*"check_out"   => esc_html__("Check Out", "wpbooking"),*/
                            "adult_child" => esc_html__( "Adult & Children", "wpbooking" ),
                            "taxonomy"    => esc_html__( "Taxonomy", "wpbooking" ),
                            "star_rating" => esc_html__( "Star Of Property", "wpbooking" ),
                            "price"       => esc_html__( "Price", "wpbooking" ),
                        ]
                    ],
                    [
                        'name'  => 'title',
                        'label' => esc_html__( 'Title', "wpbooking" ),
                        'type'  => "text",
                        'value' => ""
                    ],
                    [
                        'name'  => 'placeholder',
                        'label' => esc_html__( 'Placeholder', "wpbooking" ),
                        'desc'  => esc_html__( 'Placeholder', "wpbooking" ),
                        'type'  => 'text',
                    ],
                    [
                        'name'    => 'taxonomy',
                        'label'   => esc_html__( '- Taxonomy', "wpbooking" ),
                        'type'    => "dropdown",
                        'class'   => "hide",
                        'options' => $list_taxonomy
                    ],
                    [
                        'name'    => 'taxonomy_show',
                        'label'   => esc_html__( '- Display Style', "wpbooking" ),
                        'type'    => "dropdown",
                        'class'   => "hide",
                        'options' => [
                            "dropdown"  => esc_html__( "Dropdown", "wpbooking" ),
                            "check_box" => esc_html__( "Check Box", "wpbooking" ),
                        ]
                    ],
                    [
                        'name'    => 'taxonomy_operator',
                        'label'   => esc_html__( '- Operator', "wpbooking" ),
                        'type'    => "dropdown",
                        'class'   => "hide",
                        'options' => [
                            "AND" => esc_html__( "And", "wpbooking" ),
                            "OR"  => esc_html__( "Or", "wpbooking" ),
                        ]
                    ],
                    [
                        'name'    => 'required',
                        'label'   => esc_html__( 'Required', "wpbooking" ),
                        'type'    => "dropdown",
                        'options' => [
                            "no"  => esc_html__( "No", "wpbooking" ),
                            "yes" => esc_html__( "Yes", "wpbooking" ),
                        ]
                    ],
                    [
                        'name'  => 'in_more_filter',
                        'label' => esc_html__( 'In Advance Search?', "wpbooking" ),
                        'type'  => "checkbox",
                    ],

                ] );

                // TODO: Implement get_search_fields() method.
                return $search_fields;
            }

            /**
             * Hook Callback Change Base Price
             *
             * @since 1.0
             * @author
             *
             * @param $base_price
             * @param $hotel_id
             * @param $service_type
             *
             * @return mixed
             */
            public function _change_base_price( $base_price, $hotel_id, $service_type )
            {
                $base_price = WPBooking_Meta_Model::inst()->get_price_accommodation( $hotel_id );

                return $base_price;
            }

            /**
             * Hook Callback Change Base Price
             *
             * @since  1.0
             * @author quandq
             *
             * @param $price_html
             * @param $price
             * @param $post_id
             * @param $service_type
             *
             * @return string
             */
            public function _change_base_price_html( $price_html, $price, $post_id, $service_type )
            {
                if ( !$post_id ) return;
                $check_in   = WPBooking_Input::request( 'checkin_y' ) . "-" . WPBooking_Input::request( 'checkin_m' ) . "-" . WPBooking_Input::request( 'checkin_d' );
                $check_out  = WPBooking_Input::request( 'checkout_y' ) . "-" . WPBooking_Input::request( 'checkout_m' ) . "-" . WPBooking_Input::request( 'checkout_d' );
                $price_html = WPBooking_Currency::format_money( $price );
                $diff       = strtotime( $check_out ) - strtotime( $check_in );
                $diff       = $diff / ( 60 * 60 * 24 );
                if ( $diff > 1 ) {
                    $price_html = sprintf( esc_html__( 'from %s /%s nights', 'wpbooking' ), '<br><span class="price">' . $price_html . '</span>', $diff );
                } else {
                    $price_html = sprintf( esc_html__( 'from %s /night', 'wpbooking' ), '<br><span class="price">' . $price_html . '</span>' );
                }

                return $price_html;
            }


            /**
             * Move fields in comment to top
             */
            public function _move_fields_comment_top( $fields )
            {
                $comment_field = $fields[ 'comment' ];
                unset( $fields[ 'comment' ] );
                $fields[ 'comment' ] = $comment_field;

                return $fields;
            }

            /**
             * Add Specific params to cart item before adding to cart
             *
             * @since  1.0
             * @author quandq
             *
             * @param            $cart_item
             * @param bool|FALSE $post_id
             *
             * @return array
             */
            function _change_cart_item_params( $cart_item, $post_id = false )
            {
                $calendar   = WPBooking_Calendar_Model::inst();
                $cart_item  = wp_parse_args( $cart_item, [
                    'check_in_timestamp'  => false,
                    'check_out_timestamp' => false,
                ] );
                $data_rooms = $this->post( 'wpbooking_room' );
                if ( !empty( $data_rooms ) ) {
                    foreach ( $data_rooms as $room_id => $data_room ) {
                        if ( !empty( $data_room[ 'number_room' ] ) ) {
                            $extra_service            = [];
                            $extra_service[ 'title' ] = esc_html__( 'Extra Service', 'wpbooking' );
                            $my_extra_services        = get_post_meta( $room_id, 'extra_services', true );

                            $type_extra   = get_post_meta( $room_id, 'type_extra', true );
                            $number_night = 1;
                            if ( $type_extra == 'by_night' ) {
                                $number_night = wpbooking_date_diff( $cart_item[ 'check_in_timestamp' ], $cart_item[ 'check_out_timestamp' ] );
                            }

                            if ( !empty( $data_room[ 'extra_service' ] ) ) {
                                $post_extras = $data_room[ 'extra_service' ];

                                foreach ( $post_extras as $key => $value ) {
                                    if ( !empty( $value[ 'is_check' ] ) ) {
                                        $price = 0;
                                        if ( !empty( $my_extra_services[ $key ][ 'money' ] ) ) {
                                            $price = $my_extra_services[ $key ][ 'money' ];
                                        }
                                        $extra_service[ 'data' ][ $key ] = [
                                            'title'    => $value[ 'is_check' ],
                                            'quantity' => $value[ 'quantity' ],
                                            'price'    => (float)$price * (int)$number_night
                                        ];
                                    }
                                }
                            }
                            // Check require
                            if ( !empty( $my_extra_services ) ) {
                                foreach ( $my_extra_services as $key => $value ) {
                                    if ( $value[ 'require' ] == 'yes' and empty( $extra_service[ $key ] ) ) {
                                        $extra_service[ 'data' ][ $key ] = [
                                            'title'    => $value[ 'is_selected' ],
                                            'quantity' => 1,
                                            'price'    => (float)$value[ 'money' ] * (int)$number_night,
                                        ];
                                    }
                                }
                            }
                            $cart_item[ 'rooms' ][ $room_id ] = [
                                'room_id'    => $room_id,
                                'number'     => $data_room[ 'number_room' ],
                                'extra_fees' => [
                                    'extra_service' => $extra_service
                                ]
                            ];
                            if ( $cart_item[ 'check_in_timestamp' ] and $cart_item[ 'check_out_timestamp' ] ) {
                                $cart_item[ 'rooms' ][ $room_id ][ 'calendar_prices' ] = $calendar->get_prices( $room_id, $cart_item[ 'check_in_timestamp' ], $cart_item[ 'check_out_timestamp' ] );
                            }

                            // add list date price
                            $price_base = get_post_meta( $room_id, 'base_price', true );
                            $check_in   = $cart_item[ 'check_in_timestamp' ];
                            $check_out  = $cart_item[ 'check_out_timestamp' ];
                            if ( !empty( $cart_item[ 'rooms' ][ $room_id ][ 'calendar_prices' ] ) ) {
                                $custom_calendar = $cart_item[ 'rooms' ][ $room_id ][ 'calendar_prices' ];
                            }
                            $groupday = $this->getGroupDay( $check_in, $check_out );
                            if ( is_array( $groupday ) && count( $groupday ) ) {
                                foreach ( $groupday as $date ) {
                                    $price_tmp = $price_base;
                                    if ( !empty( $custom_calendar ) ) {
                                        foreach ( $custom_calendar as $date_calendar ) {
                                            if ( $date[ 0 ] >= $date_calendar[ 'start' ] && $date[ 0 ] <= $date_calendar[ 'end' ] ) {
                                                $price_tmp = $date_calendar[ 'price' ];
                                            }
                                        }
                                    }
                                    $cart_item[ 'rooms' ][ $room_id ][ 'list_date_price' ][ $date[ 0 ] ] = $price_tmp;
                                }
                            }

                        }

                    }
                }

                $wpbooking_adults      = WPBooking_Input::post( 'wpbooking_adults', 1 );
                $cart_item[ 'person' ] = $wpbooking_adults;
                $wpbooking_children    = WPBooking_Input::post( 'wpbooking_children' );
                if ( !empty( $wpbooking_children ) ) {
                    $cart_item[ 'person' ] += $wpbooking_children;
                }
                $cart_item[ 'adult_number' ]    = WPBooking_Input::post( 'wpbooking_adults' );
                $cart_item[ 'children_number' ] = WPBooking_Input::post( 'wpbooking_children' );

                return $cart_item;
            }

            /**
             * Validate checkout
             *
             * @author quandq
             * @since  1.0
             *
             * @param       $is_validated
             * @param array $cart
             *
             * @return bool
             */
            function _validate_checkout( $is_validated, $cart = [] )
            {
                if ( $is_validated ) {
                    if ( !empty( $cart ) ) {
                        // Validate Availability last time
                        $cart_item = wp_parse_args( $cart, [
                            'check_out_timestamp' => '',
                            'check_in_timestamp'  => ''
                        ] );
                        if ( $cart_item[ 'check_out_timestamp' ] ) {
                            $cart_item[ 'check_out_timestamp' ] = $cart_item[ 'check_in_timestamp' ];
                        }
                        $check_in_timestamp  = $cart_item[ 'check_in_timestamp' ];
                        $check_out_timestamp = $cart_item[ 'check_out_timestamp' ];

                        if ( !empty( $cart_item[ 'rooms' ] ) ) {
                            $list_room = $cart_item[ 'rooms' ];
                            //check availability Calendar
                            foreach ( $list_room as $room_id => $data ) {
                                $res = $this->check_availability_room( $room_id, $check_in_timestamp, $check_out_timestamp );
                                if ( !$res[ 'status' ] ) {
                                    $is_validated = false;
                                    // If there are some day not available, return the message
                                    if ( !empty( $res[ 'can_not_check_in' ] ) ) {
                                        wpbooking_set_message( sprintf( "You cannot check-in at: %s", 'wpbooking' ), date( get_option( 'date_format' ), $check_in_timestamp ) );
                                    }
                                    if ( !empty( $res[ 'can_not_check_out' ] ) ) {
                                        wpbooking_set_message( sprintf( "You cannot check-out at: %s", 'wpbooking' ), date( get_option( 'date_format' ), $check_out_timestamp ) );
                                    }
                                    if ( !empty( $res[ 'unavailable_dates' ] ) ) {
                                        $message         = esc_html__( 'You cannot book "%s" on: %s', 'wpbooking' );
                                        $not_avai_string = false;
                                        $not_avai_string .= date( get_option( 'date_format' ), $res[ 'unavailable_dates' ] );
                                        wpbooking_set_message( sprintf( $message, get_the_title( $room_id ), $not_avai_string ), 'error' );
                                    }

                                }
                            }
                            //check availability Order
                            $ids_room_not_availability = [];
                            foreach ( $list_room as $room_id => $data ) {
                                $data_rs = $this->check_availability_order_hotel_room( $room_id, $check_in_timestamp, $check_out_timestamp, $data[ 'number' ] );
                                if ( !empty( $data_rs[ 'total' ] ) ) {
                                    $number_room                 = get_post_meta( $room_id, 'room_number', true );
                                    $availability_number         = $number_room - $data_rs[ 'total' ];
                                    $ids_room_not_availability[] = [ 'title' => get_the_title( $data_rs[ 'id' ] ), 'number' => $availability_number ];
                                }
                            }
                            if ( !empty( $ids_room_not_availability ) ) {
                                $is_validated = false;
                                $message      = '';
                                foreach ( $ids_room_not_availability as $k_not_availability => $value_not_availability ) {
                                    $message = esc_html__( "Number of rooms you booked is not enough, please change your search.", "wpbooking" );
                                }
                                wpbooking_set_message( $message, 'error' );

                                return $is_validated;
                            }
                        }
                    }
                }

                return $is_validated;
            }

            /**
             * Calendar Validate Before Add To Cart
             *
             * @author dungdt
             * @since  1.0
             *
             * @param $is_validated
             * @param $service_type
             * @param $post_id
             *
             * @return mixed
             */
            function _add_to_cart_validate( $is_validated, $service_type, $post_id, $cart_params )
            {
                $service = new WB_Service( $post_id );

                $check_in  = $this->request( 'wpbooking_checkin_y' ) . "-" . $this->request( 'wpbooking_checkin_m' ) . "-" . $this->request( 'wpbooking_checkin_d' );
                $check_out = $this->request( 'wpbooking_checkout_y' ) . "-" . $this->request( 'wpbooking_checkout_m' ) . "-" . $this->request( 'wpbooking_checkout_d' );
                if ( $check_in == '--' ) $check_in = '';
                if ( $check_out == '--' ) $check_out = '';

                $wpbooking_room    = $this->post( 'wpbooking_room' );
                $check_number_room = false;
                $total_number_room = 0;
                if ( !empty( $wpbooking_room ) ) {
                    foreach ( $wpbooking_room as $k => $v ) {
                        if ( !empty( $v[ 'number_room' ] ) ) {
                            $check_number_room = true;
                            $total_number_room += $v[ 'number_room' ];
                        }
                    }
                }

                // check max room
                $check_max_number_room = false;
                if ( !empty( $wpbooking_room ) ) {
                    foreach ( $wpbooking_room as $k => $v ) {
                        $number_room = get_post_meta( $k, 'room_number', true );
                        if ( $number_room < $v[ 'number_room' ] ) {
                            $check_max_number_room = true;
                        }
                    }
                }
                if ( $check_max_number_room ) {
                    $is_validated = false;
                    $message      = esc_html__( "Number of rooms you booked is not enough, please change your search.", "wpbooking" );
                    wpbooking_set_message( $message, 'error' );

                    return $is_validated;
                }

                if ( empty( $check_in ) and empty( $check_out ) ) {
                    wpbooking_set_message( esc_html__( "To see price details, please select check-in and check-out date.", "wpbooking" ), 'error' );
                    $is_validated = false;

                    return $is_validated;
                }
                if ( empty( $check_in ) ) {
                    wpbooking_set_message( esc_html__( "Please select check-in date.", "wpbooking" ), 'error' );
                    $is_validated = false;

                    return $is_validated;
                }
                if ( empty( $check_out ) ) {
                    wpbooking_set_message( esc_html__( "Please select check-out date.", "wpbooking" ), 'error' );
                    $is_validated = false;

                    return $is_validated;
                }
                if ( empty( $check_number_room ) or $total_number_room <= 0 ) {
                    wpbooking_set_message( esc_html__( "Please select number of room.", "wpbooking" ), 'error' );
                    $is_validated = false;

                    return $is_validated;
                }

                $adult = $this->post( 'wpbooking_adults' );

                /*if ( $total_number_room > $adult ) {
                    $is_validated = false;
                    $message      = esc_html__( 'Number of bookable rooms cannot be more than number of adults.', 'wpbooking' );
                    wpbooking_set_message( $message, 'error' );

                    return $is_validated;
                }*/


                if ( $check_in ) {

                    $check_in_timestamp = strtotime( $check_in );

                    if ( $check_out ) {
                        $check_out_timestamp = strtotime( $check_out );
                    } else {
                        $check_out_timestamp = $check_in_timestamp;
                    }

                    if ( $check_out_timestamp < $check_in_timestamp ) {
                        wpbooking_set_message( esc_html__( "The day after check out day to check in", "wpbooking" ), 'error' );
                        $is_validated = false;

                        return $is_validated;
                    }

                    // Validate Minimum Stay
                    if ( $check_in_timestamp and $check_out_timestamp ) {
                        $minimum_stay = $service->get_minimum_stay();
                        $dDiff        = wpbooking_timestamp_diff_day( $check_in_timestamp, $check_out_timestamp );
                        if ( $dDiff < $minimum_stay ) {
                            $is_validated = false;
                            wpbooking_set_message( sprintf( esc_html__( 'This %s required minimum stay is %s night(s)', 'wpbooking' ), $service->get_type(), $minimum_stay ), 'error' );

                            return $is_validated;
                        }
                        $maximum_stay = $service->get_maximum_stay();
                        if ( (int)$maximum_stay > 0 && $dDiff > (int)$maximum_stay ) {
                            $is_validated = false;
                            wpbooking_set_message( sprintf( esc_html__( 'This %s required maximum stay is %s night(s)', 'wpbooking' ), $service->get_type(), $maximum_stay ), 'error' );

                            return $is_validated;
                        }
                    }


                    if ( !empty( $cart_params[ 'rooms' ] ) ) {
                        $list_room = $cart_params[ 'rooms' ];
                        //check availability Calendar
                        foreach ( $list_room as $room_id => $data ) {
                            $res = $this->check_availability_room( $room_id, $check_in_timestamp, $check_out_timestamp );
                            if ( !$res[ 'status' ] ) {
                                $is_validated = false;
                                // If there are some day not available, return the message
                                if ( !empty( $res[ 'can_not_check_in' ] ) ) {
                                    wpbooking_set_message( sprintf( esc_html__( "You cannot check-in at: %s", 'wpbooking' ), date_i18n( get_option( 'date_format' ), $check_in_timestamp ) ), 'error' );
                                }
                                if ( !empty( $res[ 'can_not_check_out' ] ) ) {
                                    wpbooking_set_message( sprintf( esc_html__( "You cannot check-out at: %s", 'wpbooking' ), date_i18n( get_option( 'date_format' ), $check_out_timestamp ) ), 'error' );
                                }
                                if ( !empty( $res[ 'unavailable_dates' ] ) ) {
                                    $message         = esc_html__( 'You cannot book "%s" on: %s', 'wpbooking' );
                                    $not_avai_string = false;
                                    $not_avai_string .= date_i18n( get_option( 'date_format' ), $res[ 'unavailable_dates' ] );
                                    wpbooking_set_message( sprintf( $message, get_the_title( $room_id ), $not_avai_string ), 'error' );
                                }

                            }
                        }
                        //check availability Order
                        $ids_room_not_availability = [];
                        foreach ( $list_room as $room_id => $data ) {
                            $data_rs = $this->check_availability_order_hotel_room( $room_id, $check_in_timestamp, $check_out_timestamp, $data[ 'number' ] );
                            if ( !empty( $data_rs[ 'total' ] ) ) {
                                $number_room                 = get_post_meta( $room_id, 'room_number', true );
                                $availability_number         = $number_room - $data_rs[ 'total' ];
                                $ids_room_not_availability[] = [ 'title' => get_the_title( $data_rs[ 'id' ] ), 'number' => $availability_number ];
                            }
                        }
                        if ( !empty( $ids_room_not_availability ) ) {
                            $is_validated = false;
                            $message      = '';
                            foreach ( $ids_room_not_availability as $k_not_availability => $value_not_availability ) {
                                $message = esc_html__( "Number of rooms you booked is not enough, please change your search.", "wpbooking" );
                            }
                            wpbooking_set_message( $message, 'error' );

                            return $is_validated;
                        }

                    }


                }

                return $is_validated;
            }

            /**
             * Check Room availability Calendar
             *
             * @author quandq
             * @since  1.0
             *
             * @param $room_id
             * @param $start
             * @param $end
             *
             * @return mixed|void
             */
            function check_availability_room( $room_id, $start, $end )
            {

                $return = [
                    'status'            => 0,
                    'unavailable_dates' => []
                ];

                if ( $room_id ) {
                    $calendar        = WPBooking_Calendar_Model::inst();
                    $calendar_prices = $calendar->calendar_months( $room_id, $start, $end );
                    if ( !empty( $calendar_prices ) ) {
                        foreach ( $calendar_prices as $key => $value ) {
                            $calendar_prices[ date( 'd-m-Y', $value[ 'start' ] ) ] = $value;
                        }
                    }
                    $is_available_for = get_post_meta( $room_id, 'property_available_for', true );
                    switch ( $is_available_for ) {
                        case "specific_periods":
                            if ( !empty( $calendar_prices ) ) {
                                $return[ 'status' ] = 1;
                                $check_in_temp      = $start;
                                while ( $check_in_temp <= $end ) {
                                    if ( !array_key_exists( date( 'd-m-Y', $check_in_temp ), $calendar_prices ) or $calendar_prices[ date( 'd-m-Y', $check_in_temp ) ][ 'status' ] == 'not_available' ) {
                                        $return[ 'unavailable_dates' ] = $check_in_temp;
                                        $return[ 'status' ]            = 0;
                                    }
                                    $check_in_temp = strtotime( '+1 day', $check_in_temp );
                                }
                            } else {
                                $return[ 'unavailable_dates' ] = $start;
                                $return[ 'status' ]            = 0;
                            }
                            break;
                        case "forever":
                        default:
                            $return[ 'status' ] = 1;
                            if ( !empty( $calendar_prices ) ) {
                                $check_in_temp = $start;
                                while ( $check_in_temp <= $end ) {
                                    if ( array_key_exists( date( 'd-m-Y', $check_in_temp ), $calendar_prices ) and $calendar_prices[ date( 'd-m-Y', $check_in_temp ) ][ 'status' ] == 'not_available' ) {
                                        $return[ 'unavailable_dates' ] = $check_in_temp;
                                        $return[ 'status' ]            = 0;
                                    }
                                    $check_in_temp = strtotime( '+1 day', $check_in_temp );
                                }
                            }
                            break;
                    }
                }

                return apply_filters( 'wpbooking_service_check_availability_room', $return, $this, $start, $end );

            }

            /**
             * Check Room availability Order
             *
             * @author quandq
             * @since  1.0
             *
             * @param     $room_id
             * @param     $check_in
             * @param     $check_out
             * @param int $number_room
             *
             * @return array|mixed|string
             */
            function check_availability_order_hotel_room( $room_id, $check_in, $check_out, $number_room = 1 )
            {

                if ( empty( $room_id ) or empty( $check_in ) or empty( $check_out ) or empty( $number_room ) ) {
                    return [];
                }
                $hotel_id = wp_get_post_parent_id( $room_id );
                global $wpdb;
                $sql = "
            SELECT
                {$wpdb->posts}.ID
            FROM
                {$wpdb->posts}
            WHERE
                1 = 1
            AND {$wpdb->posts}.post_type = 'wpbooking_hotel_room'
            AND {$wpdb->posts}.post_parent = {$hotel_id}
            AND {$wpdb->posts}.ID = {$room_id}
            AND (
                 {$wpdb->posts}.ID IN (
                    SELECT
                        room_id
                    FROM
                        (
                            SELECT
                                {$wpdb->prefix}wpbooking_oRoom sizerder_hotel_room.room_id,
                                count(id) AS total_booked,
                                SUM({$wpdb->prefix}wpbooking_order_hotel_room.number) as total_number,
                                {$wpdb->postmeta}.meta_value AS room_number
                            FROM
                                {$wpdb->prefix}wpbooking_order_hotel_room
                            JOIN {$wpdb->postmeta} ON {$wpdb->postmeta}.post_id = {$wpdb->prefix}wpbooking_order_hotel_room.room_id
                            AND {$wpdb->postmeta}.meta_key = 'room_number'
                            WHERE
                                1 = 1
                            AND (
                                (
                                    check_in_timestamp <= {$check_in}
                                    AND check_out_timestamp >= {$check_in}
                                )
                                OR (
                                    check_in_timestamp >= {$check_in}
                                    AND check_in_timestamp <= {$check_out}
                                )
                            )
                            GROUP BY
                                {$wpdb->prefix}wpbooking_order_hotel_room.room_id
                            HAVING
                                room_number - total_number < {$number_room}
                        ) AS table_booked
                )
            )";
                $r   = [];
                $res = $wpdb->get_row( $sql, ARRAY_A );
                if ( !is_wp_error( $res ) ) {
                    $res          = (array)$res;
                    $room_id      = array_shift( $res );
                    $r[ 'total' ] = 0;
                    $r[ 'id' ]    = $room_id;
                    if ( !empty( $room_id ) ) {
                        $sql2 = "
                           SELECT
                                SUM({$wpdb->prefix}wpbooking_order_hotel_room.number) as total_number
                            FROM
                                {$wpdb->prefix}wpbooking_order_hotel_room
                            WHERE
                                1 = 1
                            	AND  {$wpdb->prefix}wpbooking_order_hotel_room.room_id = {$room_id}
                            AND (
                                (
                                    check_in_timestamp <= {$check_in}
                                    AND check_out_timestamp >= {$check_in}
                                )
                                OR (
                                    check_in_timestamp >= {$check_in}
                                    AND check_in_timestamp <= {$check_out}
                                )
                            )
                            GROUP BY
                                {$wpdb->prefix}wpbooking_order_hotel_room.room_id";
                        $res2 = $wpdb->get_row( $sql2, ARRAY_A );
                        if ( !is_wp_error( $res ) ) {
                            $total        = array_shift( $res2 );
                            $r[ 'total' ] = $total;
                        }
                    }
                }

                return $r;
            }

            /**
             * Reload image list Room after save gallery
             *
             * @author quandq
             * @since  1.0
             *
             */
            function wpbooking_reload_image_list_room()
            {
                $post_id   = $this->request( 'wb_post_id' );
                $tab       = $this->request( 'wb_meta_section' );
                $service   = new WB_Service( $post_id );
                $list_room = [];
                if ( $service->get_type() == $this->type_id and $tab == 'photo_tab' ) {
                    $arg = [
                        'post_type'      => 'wpbooking_hotel_room',
                        'posts_per_page' => '200',
                        'post_status'    => [ 'pending', 'future', 'publish' ],
                        'post_parent'    => $post_id
                    ];
                    query_posts( $arg );
                    while ( have_posts() ) {
                        the_post();
                        $image_id = '';
                        $gallery  = get_post_meta( get_the_ID(), 'gallery_room', true );
                        if ( !empty( $gallery ) ) {
                            foreach ( $gallery as $k => $v ) {
                                if ( empty( $image_id ) ) {
                                    $image_id = $v;
                                }
                            }
                        }
                        $list_room[ get_the_ID() ] = wp_get_attachment_image( $image_id, [ 220, 120 ] );
                    }
                    wp_reset_query();
                }
                echo json_encode( $list_room );
                wp_die();
            }

            /**
             * Add Item Info Room for Page CheckOut
             * @author quandq
             * @since  1.0
             *
             * @param $cart
             */
            function _add_info_checkout_item_room( $cart )
            {
                echo wpbooking_load_view( 'checkout/other/checkout-item-room', [ 'cart' => $cart ] );
            }

            /**
             * Add Item Info Room for Page CheckOut
             *
             * @author quandq
             * @since  1.0
             *
             * @param $cart
             */
            function _add_info_total_item_room( $cart )
            {
                if ( !empty( $cart[ 'rooms' ] ) ) {
                    $number = 0;
                    foreach ( $cart[ 'rooms' ] as $room ) {
                        $number += $room[ 'number' ];
                    }
                    if ( $number > 1 ) {
                        $html = sprintf( esc_html__( "%s rooms", 'wpbooking' ), $number );
                    } else {
                        $html = sprintf( esc_html__( "%s room", 'wpbooking' ), $number );
                    }
                    $price = $this->_get_total_price_all_room_in_cart( $cart, false );
                    echo '<span class="total-title">' . esc_html( $html ) . '</span>
                      <span class="total-amount">' . WPBooking_Currency::format_money( $price ) . '</span>';

                    foreach ( $cart[ 'rooms' ] as $room_id => $room ) {
                        $number_room = $room[ 'number' ];
                        if ( !empty( $room[ 'extra_fees' ] ) ) {
                            $extra_fees = $cart[ 'rooms' ][ $room_id ][ 'extra_fees' ];
                            foreach ( $extra_fees as $extra_items ) {
                                $price = 0;
                                if ( !empty( $extra_items[ 'data' ] ) ) {
                                    echo '<span class="total-title">' . esc_html( $extra_items[ 'title' ] ) . '</span>';
                                    foreach ( $extra_items[ 'data' ] as $data ) {
                                        $price += ( $data[ 'price' ] * $data[ 'quantity' ] ) * $number_room;
                                    }
                                    echo '<span class="total-amount">' . WPBooking_Currency::format_money( $price ) . '</span>';
                                }
                            }
                        }
                    }
                }
            }

            /**
             * Add Item Info Room for Page Order Detail
             *
             * @author quandq
             * @since  1.0
             *
             * @param $order_data
             */
            function _add_info_order_detail_item_room( $order_data )
            {
                $order                 = WPBooking_Order_Hotel_Order_Model::inst();
                $order_data[ 'rooms' ] = $order->get_order( $order_data[ 'order_id' ] );
                echo wpbooking_load_view( 'order/other/order-item-room', [ 'order_data' => $order_data ] );
            }

            /**
             * Add Item Info Room for Email Detail
             *
             * @author quandq
             * @since  1.0
             *
             * @param $order_data
             */
            function _add_information_email_detail_item( $order_data )
            {
                $order                 = WPBooking_Order_Hotel_Order_Model::inst();
                $order_data[ 'rooms' ] = $order->get_order( $order_data[ 'order_id' ] );
                echo wpbooking_load_view( 'emails/shortcodes/detail-item-room', [ 'order_data' => $order_data ] );
            }

            /**
             * Add Item Info Room for Page Order Detail
             *
             * @author quandq
             * @since  1.0
             *
             * @param $order_data
             */
            function _add_info_order_total_item_room( $order_data )
            {
                $order = WPBooking_Order_Hotel_Order_Model::inst();
                $rooms = $order->get_order( $order_data[ 'order_id' ] );
                if ( !empty( $rooms ) ) {
                    $number = 0;
                    foreach ( $rooms as $room ) {
                        $number += $room[ 'number' ];
                    }
                    if ( $number > 1 ) {
                        $html = sprintf( esc_html__( "%s rooms", 'wpbooking' ), $number );
                    } else {
                        $html = sprintf( esc_html__( "%s room", 'wpbooking' ), $number );
                    }
                    $price = 0;
                    foreach ( $rooms as $room ) {
                        $price += $room[ 'price' ] * $room[ 'number' ];
                    }
                    echo '<span class="total-title">' . esc_html( $html ) . '</span>
                      <span class="total-amount">' . WPBooking_Currency::format_money( $price ) . '</span>';
                    foreach ( $rooms as $room ) {
                        $number_room = $room[ 'number' ];
                        if ( !empty( $room[ 'extra_fees' ] ) ) {
                            $extra_fees = unserialize( $room[ 'extra_fees' ] );
                            if ( !empty( $extra_fees ) ) {
                                foreach ( $extra_fees as $extra_items ) {
                                    $price = 0;
                                    if ( !empty( $extra_items[ 'data' ] ) ) {
                                        echo '<span class="total-title">' . esc_html( $extra_items[ 'title' ] ) . '</span>';
                                        foreach ( $extra_items[ 'data' ] as $data ) {
                                            $price += ( $data[ 'price' ] * $data[ 'quantity' ] ) * $number_room;
                                        }
                                        echo '<span class="total-amount">' . WPBooking_Currency::format_money( $price ) . '</span>';
                                    }
                                }
                            }

                        }
                    }
                }
            }

            /**
             * Get Price Room In Cart
             *
             * @author quandq
             * @since  1.0
             *
             * @param $cart
             * @param $room_id
             *
             * @return int
             */
            function _get_price_room_in_cart( $cart, $room_id )
            {
                if ( empty( $room_id ) ) return 0;
                $total_price = 0;
                if ( !empty( $cart[ 'rooms' ][ $room_id ] ) ) {
                    $data_room  = $cart[ 'rooms' ][ $room_id ];
                    $service    = new WB_Service( $data_room[ 'room_id' ] );
                    $price_base = $service->get_meta( 'base_price' );
                    $check_in   = $cart[ 'check_in_timestamp' ];
                    $check_out  = $cart[ 'check_out_timestamp' ];

                    if ( !empty( $cart[ 'rooms' ][ $room_id ][ 'calendar_prices' ] ) ) {
                        $custom_calendar = $cart[ 'rooms' ][ $room_id ][ 'calendar_prices' ];
                    }
                    $groupday = self::getGroupDay( $check_in, $check_out );
                    if ( is_array( $groupday ) && count( $groupday ) ) {
                        foreach ( $groupday as $date ) {
                            $price_tmp = $price_base;
                            if ( !empty( $custom_calendar ) ) {
                                foreach ( $custom_calendar as $date_calendar ) {
                                    if ( $date[ 0 ] >= $date_calendar[ 'start' ] && $date[ 0 ] <= $date_calendar[ 'end' ] ) {
                                        $price_tmp = (float)$date_calendar[ 'price' ];
                                    }
                                }
                            }
                            $total_price += $price_tmp;
                        }
                    }
                }

                return $total_price;
            }

            /**
             * Get Total Price Room In Cart
             *
             * @author quandq
             * @since  1.0
             *
             * @param      $cart
             * @param      $room_id
             * @param bool $include_price_extra
             *
             * @return int|mixed
             */
            function _get_total_price_room_in_cart( $cart, $room_id, $include_price_extra = true )
            {
                if ( empty( $room_id ) ) return 0;
                $extra_price = 0;
                $price_room  = 0;
                $number_room = 0;
                if ( !empty( $cart[ 'rooms' ][ $room_id ] ) ) {
                    $data_room   = $cart[ 'rooms' ][ $room_id ];
                    $service     = new WB_Service( $data_room[ 'room_id' ] );
                    $price_base  = $service->get_meta( 'base_price' );
                    $check_in    = $cart[ 'check_in_timestamp' ];
                    $check_out   = $cart[ 'check_out_timestamp' ];
                    $number_room = $cart[ 'rooms' ][ $room_id ][ 'number' ];
                    // Base Price
                    if ( !empty( $cart[ 'rooms' ][ $room_id ][ 'calendar_prices' ] ) ) {
                        $custom_calendar = $cart[ 'rooms' ][ $room_id ][ 'calendar_prices' ];
                    }
                    $groupday = self::getGroupDay( $check_in, $check_out );
                    if ( is_array( $groupday ) && count( $groupday ) ) {
                        foreach ( $groupday as $date ) {
                            $price_tmp = $price_base;
                            if ( !empty( $custom_calendar ) ) {
                                foreach ( $custom_calendar as $date_calendar ) {
                                    if ( $date[ 0 ] >= $date_calendar[ 'start' ] && $date[ 0 ] <= $date_calendar[ 'end' ] ) {
                                        $price_tmp = $date_calendar[ 'price' ];
                                    }
                                }
                            }
                            $price_room += $price_tmp;
                        }
                    }
                    // Extra Price
                    if ( !empty( $cart[ 'rooms' ][ $room_id ][ 'extra_fees' ] ) ) {
                        $extra_fees = $cart[ 'rooms' ][ $room_id ][ 'extra_fees' ];
                        foreach ( $extra_fees as $extra_items ) {
                            if ( !empty( $extra_items[ 'data' ] ) ) {
                                foreach ( $extra_items[ 'data' ] as $data ) {
                                    $extra_price += $data[ 'price' ] * $data[ 'quantity' ];
                                }
                            }
                        }
                    }
                }
                if ( $include_price_extra ) {
                    $total_price = ( $price_room + $extra_price ) * $number_room;
                } else {
                    $total_price = ( $price_room ) * $number_room;
                }

                return $total_price;
            }

            /**
             * Get  Price Room with date
             *
             * @author quandq
             * @since  1.0
             *
             * @param $room_id
             * @param $check_in
             * @param $check_out
             *
             * @return int|mixed
             */
            function _get_price_room_with_date( $room_id, $check_in, $check_out )
            {
                if ( empty( $room_id ) ) $room_id = get_the_ID();
                $calendar            = WPBooking_Calendar_Model::inst();
                $check_in_timestamp  = strtotime( $check_in );
                $check_out_timestamp = strtotime( $check_out );
                $price_room          = 0;

                $service         = new WB_Service( $room_id );
                $price_base      = $service->get_meta( 'base_price' );
                $custom_calendar = $calendar->get_prices( $room_id, $check_in_timestamp, $check_out_timestamp );

                $groupday = self::getGroupDay( $check_in_timestamp, $check_out_timestamp );
                if ( is_array( $groupday ) && count( $groupday ) ) {
                    foreach ( $groupday as $date ) {
                        $price_tmp = $price_base;
                        if ( !empty( $custom_calendar ) ) {
                            foreach ( $custom_calendar as $date_calendar ) {
                                if ( $date[ 0 ] >= $date_calendar[ 'start' ] && $date[ 0 ] <= $date_calendar[ 'end' ] ) {
                                    $price_tmp = $date_calendar[ 'price' ];
                                }
                            }
                        }
                        $price_room += $price_tmp;
                    }
                }

                return $price_room;
            }

            /**
             * Get Total Price all Room in cart
             *
             * @author quandq
             * @since  1.0
             *
             * @param      $cart
             * @param bool $include_price_extra
             *
             * @return int|mixed
             */
            function _get_total_price_all_room_in_cart( $cart, $include_price_extra = true )
            {
                $price = 0;
                if ( !empty( $cart[ 'rooms' ] ) ) {
                    foreach ( $cart[ 'rooms' ] as $room_id => $room ) {
                        $price += $this->_get_total_price_room_in_cart( $cart, $room_id, $include_price_extra );
                    }
                }

                return $price;
            }

            /**
             *  Get GroupDay
             * @author quandq
             * @since  1.0
             *
             * @param string $start
             * @param string $end
             *
             * @return array
             */
            static function getGroupDay( $start = '', $end = '' )
            {
                $list = [];
                for ( $i = $start; $i <= $end; $i = strtotime( '+1 day', $i ) ) {
                    $next = strtotime( '+1 day', $i );
                    if ( $next <= $end ) {
                        $list[] = [ $i, $next ];
                    }
                }

                return $list;
            }

            /**
             * Get Total Price Room In Cart
             * @author quandq
             * @since  1.0
             *
             * @param $price
             * @param $cart
             *
             * @return int
             */
            function _get_cart_total_price_hotel_room( $price, $cart )
            {
                if ( !empty( $cart[ 'rooms' ] ) ) {
                    foreach ( $cart[ 'rooms' ] as $room_id => $room ) {
                        $price += $this->_get_total_price_room_in_cart( $cart, $room_id );
                    }
                }

                return $price;
            }

            /**
             * Save Order Hotel Room
             * @author quandq
             * @since  1.0
             *
             * @param $cart
             * @param $order_id
             */
            function _save_order_hotel_room( $cart, $order_id )
            {
                if ( !empty( $cart[ 'rooms' ] ) ) {
                    $hotel_id = $cart[ 'post_id' ];
                    foreach ( $cart[ 'rooms' ] as $room_id => $room ) {
                        $order            = WPBooking_Order_Hotel_Order_Model::inst();
                        $price_room       = WPBooking_Accommodation_Service_Type::inst()->_get_price_room_in_cart( $cart, $room_id );
                        $price_total_room = WPBooking_Accommodation_Service_Type::inst()->_get_total_price_room_in_cart( $cart, $room_id );
                        $data             = [
                            'order_id'            => $order_id,
                            'hotel_id'            => $hotel_id,
                            'room_id'             => $room_id,
                            'price'               => $price_room,
                            'price_total'         => $price_total_room,
                            'number'              => $room[ 'number' ],
                            'extra_fees'          => serialize( $room[ 'extra_fees' ] ),
                            'check_in_timestamp'  => $cart[ 'check_in_timestamp' ],
                            'check_out_timestamp' => $cart[ 'check_out_timestamp' ],
                            'raw_data'            => serialize( $room[ 'list_date_price' ] ),
                        ];
                        $order->save_order_hotel_room( $data, $room_id, $order_id );
                    }
                }
            }

            /**
             * Handler Action Delete Cart Item Hotel Room
             *
             * @since  1.0
             * @author quandq
             */
            function _delete_cart_item_hotel_room()
            {
                if ( isset( $_GET[ 'delete_item_hotel_room' ] ) ) {
                    $index   = WPBooking_Input::get( 'delete_item_hotel_room' );
                    $booking = WPBooking_Checkout_Controller::inst();
                    $all     = $booking->get_cart();
                    if ( !empty( $all[ 'service_type' ] ) and $all[ 'service_type' ] = 'accommodation' ) {
                        unset( $all[ 'rooms' ][ $index ] );
                        if ( empty( $all[ 'rooms' ] ) ) {
                            $booking->set_cart( [] );
                        } else {
                            $booking->set_cart( $all );
                        }
                        wpbooking_set_message( esc_html__( "Delete item successfully", 'wpbooking' ), 'success' );
                    }
                }
            }

            /**
             * Change Tax Room CheckOut
             *
             * @since  1.0
             * @author quandq
             *
             * @param $tax
             * @param $cart
             *
             * @return mixed
             */
            function _change_tax_room_checkout( $tax, $cart )
            {

                $tax       = [];
                $diff      = $cart[ 'check_out_timestamp' ] - $cart[ 'check_in_timestamp' ];
                $date_diff = $diff / ( 60 * 60 * 24 );

                $total_price = WPBooking_Checkout_Controller::inst()->get_cart_total( [ 'without_tax' => false ] );
                $total_tax   = 0;
                $tax_total   = 0;

                if ( !empty( $cart[ 'tax' ] ) and !empty( $cart[ 'rooms' ] ) ) {
                    $number_room = 0;
                    foreach ( $cart[ 'rooms' ] as $room ) {
                        $number_room += $room[ 'number' ];
                    }
                    foreach ( $cart[ 'tax' ] as $key => $value ) {
                        if ( $value[ 'excluded' ] != '' and !empty( $value[ 'amount' ] ) ) {
                            $unit        = $value[ 'unit' ];
                            $tax[ $key ] = $value;
                            $price       = 0;
                            switch ( $unit ) {
                                case "fixed":
                                case "stay":
                                    $price = $value[ 'amount' ] * $number_room;
                                    break;
                                case "percent":
                                    $price = $total_price * ( $value[ 'amount' ] / 100 );
                                    break;
                                case "night":
                                    $price = $value[ 'amount' ] * $date_diff * $number_room;
                                    break;
                                case "person_per_stay":
                                    if ( !empty( $cart[ 'person' ] ) ) {
                                        $person = $cart[ 'person' ];
                                        $price  = $person * $value[ 'amount' ] * $number_room;
                                    }
                                    break;
                                case "person_per_night":
                                    if ( !empty( $cart[ 'person' ] ) ) {
                                        $person = $cart[ 'person' ];
                                        $price  = ( $value[ 'amount' ] * $person ) * $date_diff * $number_room;
                                    }
                                    break;
                                default:
                            }
                            if ( $value[ 'excluded' ] == 'yes_not_included' ) {
                                $total_tax += $price;
                            }
                            $tax_total              += $price;
                            $tax[ $key ][ 'price' ] = floatval( $price );
                        }
                    }
                }
                $tax[ 'total_price' ] = $total_tax;
                $tax[ 'tax_total' ]   = $tax_total;

                return $tax;
            }

            /**
             * /**
             * Update min_price hotel
             *
             * @since  1.2
             * @author quandq
             *
             * @param $hotel_id
             *
             * @return bool
             */
            function _update_min_price_hotel( $hotel_id )
            {
                if ( get_post_type( $hotel_id ) != 'wpbooking_service' ) return false;
                $service_type = get_post_meta( $hotel_id, 'service_type', true );
                if ( $service_type != $this->type_id ) return false;
                $min_price = WPBooking_Meta_Model::inst()->get_price_accommodation( $hotel_id );
                update_post_meta( $hotel_id, 'price', $min_price );
                WPBooking_Service_Model::inst()->save_extra( $hotel_id );
            }

            /**
             * Show Other Info Order In Booking Admin
             *
             * @since  1.0
             * @author quandq
             *
             * @param $order_id
             * @param $order_data
             */
            function _show_order_info_after_order_detail_in_booking_admin( $order_id, $order_data )
            {
                $order     = new WB_Order( $order_id );
                $room_data = $order->get_order_room_data();
                echo '<li>' . esc_html__( 'Rooms: ', 'wpbooking' ) . '</li>';
                foreach ( $room_data as $key => $value ) {
                    $extra_fees = unserialize( $value[ 'extra_fees' ] );
                    $price      = WPBooking_Currency::format_money( $value[ 'price' ] );
                    echo '<li class="wb-room-item"><span class="wb-room-name"><strong>' . get_the_title( $value[ 'room_id' ] ) . ' x' . esc_html( $value[ 'number' ] ) . '</strong></span>';
                    echo '<span class="wb-room-price">' . do_shortcode( $price ) . '</span>';
                    echo '</li>';
                    if ( !empty( $extra_fees[ 'extra_service' ][ 'data' ] ) && is_array( $extra_fees[ 'extra_service' ][ 'data' ] ) ) {
                        foreach ( $extra_fees[ 'extra_service' ][ 'data' ] as $k => $v ) {
                            echo '<li class="wb-room-item"><span class="wb-extra-title">' . esc_html( $v[ 'title' ] ) . ' x' . esc_html( $v[ 'quantity' ] ) . '</span>';
                            echo '<span class="wb-extra-price">' . WPBooking_Currency::format_money( $v[ 'price' ] ) . '</span>';
                            echo '</li>';
                        }
                    }
                }
            }

            static function inst()
            {
                if ( !self::$_inst )
                    self::$_inst = new self();

                return self::$_inst;
            }
        }

        WPBooking_Accommodation_Service_Type::inst();
    }