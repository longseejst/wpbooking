<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 3/14/2016
 * Time: 9:32 AM
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WPBooking_Service')) {
	class WPBooking_Service
	{

		private static $_inst;

		function __construct()
		{
			// Load Abstract Service Type class and Default Service Types

			$loader = WPBooking_Loader::inst();
			$loader->load_library(array(
				'service-types/abstract-service-type',
				'service-types/room',
			));

			add_filter('comment_form_field_comment', array($this, 'add_review_field'));
			add_action('comment_post', array($this, '_save_review_stats'));
			add_filter('get_comment_text', array($this, '_show_review_stats'), 100);

			add_filter('template_include', array($this, '_show_single_service'));

			add_filter('template_include', array($this, 'template_loader'));
			add_filter('body_class', array($this, '_add_body_class'));

			/**
			 *
			 * Ajax Get Calendar Months
			 * @author dungdt
			 * @since 1.0
			 */
			add_action('wp_ajax_wpbooking_calendar_months',array($this,'_calendar_months'));
			add_action('wp_ajax_nopriv_wpbooking_calendar_months',array($this,'_calendar_months'));
		}

		/**
		 * Function Ajax Get Calendar Months
		 * @since 1.0
		 * @return string json result
		 */
		function _calendar_months()
		{
			$res=array();

			$post_id=WPBooking_Input::post('post_id');
			$currentMonth=WPBooking_Input::post('currentMonth');
			$currentYear=WPBooking_Input::post('currentYear');
			$start_date=new DateTime($currentYear.'-'.$currentMonth.'-1');
			$start=$start_date->getTimestamp();
			$end_date=$start_date->modify('+3 months');
			$end=$end_date->getTimestamp();

			$raw_data=WPBooking_Calendar_Model::inst()->calendar_months($post_id,$start,$end);
			$calendar_months=array();
			if(!empty($raw_data))
			{
				foreach($raw_data as $k=>$v){
					// Ignore Not Available Date
					if($v['status']=='not_available') continue;

					$key=date('m',$v['start']).'_'.date('Y',$v['start']);
					$calendar_months[$key][]=array(
						'date'=>date('Y-m-d',$v['start']),
						'price'=>$v['price']
					);
				}
			}

			$res['months']=$calendar_months;

			echo json_encode($res);

			die;
		}
		function _add_body_class($class)
		{

			return $class;
		}

		function query($args = array(), $service_type = FALSE)
		{
			$args = wp_parse_args($args, array(
				'post_type' => 'wpbooking_service'
			));

			$args = apply_filters('wpbooking_service_query_args', $args);
			$args = apply_filters('wpbooking_service_query_args_' . $service_type, $args);

			do_action('wpbooking_before_service_query', $args);
			do_action('wpbooking_before_service_query_' . $service_type, $args);

			$query = new WP_Query($args);

			do_action('wpbooking_after_service_query', $args);
			do_action('wpbooking_after_service_query_' . $service_type, $args);

			return $query;
		}

		/**
		 * @param $template
		 * @return string
		 */
		public function template_loader($template)
		{
			$is_page = get_the_ID();
			$list_page_search = apply_filters("wpbooking_add_page_archive_search", array());
			if (!empty($list_page_search[$is_page])) {
				$template = wpbooking_view_path('archive-service');
			}
			//var_dump($list_page_search);
			//var_dump($template);
			return $template;
		}

		/**
		 * @return array|mixed|void
		 */
		function _get_list_field_search()
		{
			$taxonomy = get_object_taxonomies('wpbooking_service', 'array');
			$list_taxonomy = array();
			if (!empty($taxonomy)) {
				foreach ($taxonomy as $k => $v) {
					if ($k == 'wpbooking_location') continue;
					$list_taxonomy[$k] = $v->label;
				}
			}
			$list_filed = array(
				'room' => array(
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
						'name'    => 'field_type',
						'label'   => __('Field Type', "wpbooking"),
						'type'    => "dropdown",
						'options' => array(
							"location_id"         => __("Location Dropdown", "wpbooking"),
							"location_suggestion" => __("Location Suggestion", "wpbooking"),
							"check_in"            => __("Check In", "wpbooking"),
							"check_out"           => __("Check Out", "wpbooking"),
							"taxonomy"            => __("Taxonomy", "wpbooking"),
							"review_rate"         => __("Review Rate", "wpbooking"),
							"price"               => __("Price", "wpbooking"),
							"bed"                 => __("Beds", "wpbooking"),
							"bedroom"             => __("Bedrooms", "wpbooking"),
							"bathroom"            => __("Bathrooms", "wpbooking"),
							"customer_confirm"    => __("Require Customer Confirm?", "wpbooking"),
							"partner_confirm"     => __("Require Partner Confirm?", "wpbooking"),
						)
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
						'label' => __('More Filter?', "wpbooking"),
						'type'  => "checkbox",
					),
				),
				'tour' => array(
					array(
						'name'  => 'title',
						'label' => __('Title', "wpbooking"),
						'type'  => "text",
						'value' => ""
					),

				)
			);
			$list_filed = apply_filters("wpbooking_list_fields_form_search", $list_filed);

			return $list_filed;
		}


		/**
		 *
		 */
		function _show_single_service($template)
		{

			if (get_post_type() == 'wpbooking_service' and is_single()) {
				$template = wpbooking_view_path('single-service');
			}

			return $template;
		}

		/**
		 * Add Review Stats in End of the Content Text
		 * @param $content
		 * @return string
		 */
		function _show_review_stats($content)
		{
			$comnent_id = get_comment_ID();
			$comemntObj = get_comment($comnent_id);
			$post_id = $comemntObj->comment_post_ID;
			if (get_post_type($post_id) != 'wpbooking_service') return $content;

			$content = wpbooking_load_view('review-item-stats') . $content;

			return $content;
		}

		/**
		 * Save Comment Stats Data
		 * @param $comment_id
		 * @return bool
		 */
		function _save_review_stats($comment_id)
		{
			$comemntObj = get_comment($comment_id);
			$post_id = $comemntObj->comment_post_ID;

			if (get_post_type($post_id) != 'wpbooking_service') return FALSE;

			update_comment_meta($comment_id, 'wpbooking_review', WPBooking_Input::post('wpbooking_review'));
			update_comment_meta($comment_id, 'wpbooking_review_detail', WPBooking_Input::post('wpbooking_review_detail'));

			do_action('after_wpbooking_update_review_stats');
		}

		function add_review_field($fields)
		{
			if (get_post_type() != 'wpbooking_service') return $fields;

			$field_review = apply_filters('wpbooking_review_field', wpbooking_load_view('review-field'));

			return $field_review . $fields;
		}

		function get_service_types()
		{
			$default = array();

			return apply_filters('wpbooking_service_types', $default);
		}

		function comments_template($template)
		{
			if (get_post_type() != 'wpbooking_service') return $template;

			$template = wpbooking_view_path('reviews');

			return $template;
		}

		static function inst()
		{
			if (!self::$_inst) {
				self::$_inst = new self();
			}

			return self::$_inst;
		}


	}

	WPBooking_Service::inst();
}