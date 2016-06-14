<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 4/1/2016
 * Time: 2:41 PM
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WPBooking_Order_Model')) {
	class WPBooking_Order_Model extends WPBooking_Model
	{
		static $_inst = FALSE;

		function __construct()
		{
			$this->table_name = 'wpbooking_order_item';
			$this->table_version = '1.1.3';
			$this->columns = array(
				'id'                    => array(
					'type'           => "int",
					'AUTO_INCREMENT' => TRUE
				),
				'order_id'              => array('type' => "INT"),
				'post_id'               => array('type' => "INT"),
				'base_price'            => array('type' => "FLOAT"),
				'sub_total'             => array('type' => "FLOAT"),
				'currency'              => array('type' => "VARCHAR", 'length' => 50),
				'is_main_currency'      => array('type' => "INT"),
				'raw_data'              => array('type' => "text"),
				'order_form'            => array('type' => "text"),
				'service_type'          => array('type' => "VARCHAR", 'length' => 50),
				'check_out_timestamp'   => array('type' => "INT"),
				'check_in_timestamp'    => array('type' => "INT"),
				'adult_number'          => array('type' => "INT"),
				'child_number'          => array('type' => "INT"),
				'infant_number'         => array('type' => "INT"),
				'customer_id'           => array('type' => "INT"),
				'partner_id'            => array('type' => "INT"),
				'deposit'               => array('type' => "varchar", 'length' => 50),
				'deposit_amount'        => array('type' => "FLOAT"),
				'need_customer_confirm' => array('type' => 'INT'),
				'customer_confirm_code' => array('type' => "varchar", 'length' => 255),
				'partner_confirm_code'  => array('type' => "varchar", 'length' => 255),
				'need_partner_confirm'  => array('type' => 'INT'),
				'payment_status'        => array('type' => "varchar", 'length' => 50),
				'payment_id'            => array('type' => "varchar", 'length' => 50),
				'status'                => array('type' => "varchar", 'length' => 50),
			);
			parent::__construct();
		}

		function create($cart, $checkout_form_data = array(),$selected_gateway=FALSE,$customer_id=FALSE)
		{
			$order_data = array(
				'post_title'  => sprintf(__('New Order In %s', 'wpbooking'), date(get_option('date_format') . ' @' . get_option('time_format'))),
				'post_type'   => 'wpbooking_order',
				'post_status' => 'publish'
			);
			$order_id = wp_insert_post($order_data);

			if ($order_id) {
				update_post_meta($order_id, 'checkout_form_data', $checkout_form_data);
				update_post_meta($order_id, 'wpbooking_selected_gateway', $selected_gateway);
				update_post_meta($order_id, 'customer_id', $customer_id);

				//User Fields in case of customer dont want to create new account
				$f=array('user_email','user_first_name','user_last_name');
				foreach($f as $v){
					if(array_key_exists($v,$checkout_form_data))
					update_post_meta($order_id,$v,$checkout_form_data[$v]);
				}


				if (!empty($checkout_form_data)) {
					foreach ($checkout_form_data as $key => $value) {
						update_post_meta($order_id, 'wpbooking_form_' . $key, $value['value']);
					}
				}
			}

			if (!empty($cart) and is_array($cart)) {
				foreach ($cart as $key => $value) {
					$this->save_order_item($value, $order_id,$customer_id);
				}
			}

			return $order_id;
		}

		function save_order_item($cart_item, $order_id,$customer_id=FALSE)
		{
			if(!$customer_id) $customer_id=is_user_logged_in() ? get_current_user_id() : FALSE;

			$cart_item = wp_parse_args($cart_item, array(
				'post_id'               => '',
				'base_price'            => 0,
				'sub_total'             => 0,
				'service_type'          => '',
				'currency'              => '',
				'order_form'            => array(),
				'check_in_timestamp'    => '',
				'check_out_timestamp'   => '',
				'adult_number'          => 0,
				'child_number'          => 0,
				'infant_number'         => 0,
				'customer_id'           => 0,
				'need_customer_confirm' => 0,
				'need_partner_confirm'  => 0,
				'deposit'               => '',
				'deposit_amount'        => '',
			));
			$insert = array(
				'order_id'              => $order_id,
				'post_id'               => $cart_item['post_id'],
				'base_price'            => $cart_item['base_price'],
				'sub_total'             => $cart_item['sub_total'],
				'service_type'          => $cart_item['service_type'],
				'raw_data'              => serialize($cart_item),
				'currency'              => $cart_item['currency'],
				'order_form'            => serialize($cart_item['order_form']),
				'check_in_timestamp'    => $cart_item['check_in_timestamp'],
				'check_out_timestamp'   => $cart_item['check_out_timestamp'],
				'adult_number'          => $cart_item['adult_number'],
				'child_number'          => $cart_item['child_number'],
				'infant_number'         => $cart_item['infant_number'],
				'customer_id'           => $customer_id,
				'deposit'               => $cart_item['deposit'],
				'deposit_amount'        => $cart_item['deposit_amount'],
				'partner_id'            => get_post_field('post_author', $cart_item['post_id']),
				'need_customer_confirm' => $cart_item['need_customer_confirm'] ? 1 : 0,
				'need_partner_confirm'  => $cart_item['need_partner_confirm'] ? 1 : 0,
				'payment_status'        => 0,
				'status'                => 'on-hold'
			);

			if($insert['need_customer_confirm']) $insert['customer_confirm_code']=$this->generate_random_code();
			if($insert['need_partner_confirm']) $insert['partner_confirm_code']=$this->generate_random_code();

			return $this->insert($insert);
		}

		/**
		 * Generate Confirmation Code
		 *
		 * @since 1.0
		 * @author dungdt
		 *
		 * @param $order_item_id int
		 */
		function generate_order_item_confirm_code($order_item_id){
			if($order_item_id){
				$item=$this->find($order_item_id);
				if(!empty($item)){
					$update=array();

					// Customer Confirm
					if($item['need_customer_confirm'] and !$item['customer_confirm_code']){
						$update['customer_confirm_code']=$this->generate_random_code();
					}
					// Partner Confirm
					if($item['need_partner_confirm'] and !$item['partner_confirm_code']){
						$update['partner_confirm_code']=$this->generate_random_code();
					}

					if(!empty($update)){
						$this->where('id',$order_item_id)->update($update);
					}
				}
			}
		}

		/**
		 * Generate Random MD5 string
		 * @param $string
		 * @return string
		 */
		function generate_random_code($string=FALSE)
		{
			if(!$string) $string=rand(0,99999);

			return md5($string.time());
		}

		/**
		 * Get all items of an Order
		 * @param $order_id
		 * @return $this
		 */
		function get_order_items($order_id)
		{
			$a = $this->where('order_id', $order_id)->get();

			return $a->result();
		}


		/**
		 * Get Payable order items at current time
		 * @param $order_id
		 * @return bool|array
		 */
		function prepare_paying($order_id, $payment_id)
		{
			$items = $this->get_order_items($order_id);
			if (!empty($items)) {
				$on_paying = array();
				foreach ($items as $key => $value) {
					// Payment Completed -> Ignore
					if ($value['payment_status'] == 'completed') continue;

					// Payment processing -> Ignore
					if ($value['payment_status'] == 'processing') continue;

					// Customer does not confirm the booking -> Ignore
					if ($value['need_customer_confirm'] === 1) continue;

					// Partner does not confirm the booking -> Ignore
					if ($value['need_partner_confirm'] === 1) continue;

					$on_paying[] = $value['id'];
					$this->where('id', $value['id'])->update(array('payment_status' => 'processing', 'payment_id' => $payment_id));
				}

				return $on_paying;
			}
		}

		/**
		 * Update Payment Status of Items by Payment ID
		 *
		 * @param $payment_id
		 * @since 1.0
		 */
		function complete_purchase($payment_id)
		{

			$this->where('payment_id', $payment_id)->update(array('payment_status' => 'completed', 'status' => 'completed'));
		}

		/**
		 * Update Order Status to On-Hold for Offline Payment
		 *
		 * @since 1.0
		 * @author dungdt
		 *
		 * @param $payment_id
		 */
		function onhold_purchase($payment_id)
		{
			$this->where('payment_id', $payment_id)->update(array('status' => 'completed'));
		}

		static function inst()
		{
			if (!self::$_inst) {
				self::$_inst = new self();
			}

			return self::$_inst;
		}
	}


	WPBooking_Order_Model::inst();
}
