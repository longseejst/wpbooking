<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 4/5/2016
 * Time: 12:01 PM
 */
if (!class_exists('WPBooking_Email')) {
	class WPBooking_Email
	{
		static $_inst;

		function __construct()
		{
			// Init Shortcodes

			add_action('init', array($this, '_load_email_shortcodes'));

			add_action('wpbooking_after_checkout_success', array($this, '_send_order_email_success'));

			/**
			 * Send Emails when new Order Item has been updated/changed, example: payment complete or cancelled
			 * @since 1.0
			 */
			add_action('wpbooking_order_item_changed', array($this, '_send_order_email_success'));


			//add_action('wpbooking_after_checkout_success',array($this,'_send_order_email_confirm'));

			add_action('admin_init', array($this, '_test_email'));

			/**
			 * Preview Booking Email
			 *
			 * @since 1.0
			 * @author dungdt
			 */
			add_action('wp_ajax_wpbooking_booking_email_preview', array($this, '_preview_email'));

		}


		/**
		 * Get All Available Booking Email Shortcodes
		 *
		 * @since 1.0
		 * @author dungdt
		 *
		 * @return array|mixed|void
		 */
		function get_email_shortcodes()
		{
			$all_shortcodes = array(
				'checkout_info'       => esc_html__('Your Username', 'wpbooking'),// Default Value for Preview
				'order_table'         => esc_html__('email@domain.com', 'wpbooking'),
				'order_form_field'    => esc_html__('Form Value', 'wpbooking'),
				'checkout_form_field' => esc_html__('Form Value', 'wpbooking'),
			);

			$all_shortcodes = apply_filters('wpbooking_booking_email_shortcodes', $all_shortcodes);

			return $all_shortcodes;
		}

		function _test_email()
		{
			if (WPBooking_Input::get('test_email') and $order_id = WPBooking_Input::get('post_id')) {
				$order_model = WPBooking_Order_Model::inst();

				$items = $order_model->get_order_items($order_id);
				WPBooking()->set('order_id', $order_id);
				WPBooking()->set('is_email_to_author', 1);
				$message = do_shortcode(wpbooking_get_option('email_to_partner'));
				if (class_exists('Emogrifier')) {

					$e = new Emogrifier();
					$e->setHtml($message);
					$e->setCss(wpbooking_get_option('email_stylesheet'));
					$message = $e->emogrify();
				}
				WPBooking()->set('is_email_to_author', 0);
				echo($message);
				die;
			}
		}

		function _send_order_email_success($order_id)
		{

			if(wpbooking_get_option('on_booking_email_customer')){

				$this->send_customer_email($order_id);
			}

			if(wpbooking_get_option('on_booking_email_author')){
				$this->send_partner_email($order_id);
			}

			if(wpbooking_get_option('on_booking_email_admin')){
				$this->send_admin_email($order_id);
			}

		}

		/**
		 * Send Booking Email to Admin
		 *
		 * @author dungdt
		 * @since 1.0
		 * @param $order_id
		 */
		private function send_admin_email($order_id)
		{

			$to = wpbooking_get_option('system_email');

			WPBooking()->set('is_email_to_admin', 1);
			WPBooking()->set('order_id',$order_id);
			$subject = sprintf(__("New Order from %s", 'wpbooking'), get_bloginfo('title'));
			$message = wpbooking_get_option('email_to_admin');
			$message = $this->replaceShortcode($message);
			$message = do_shortcode($message);
			$this->send($to, $subject, $message);

			WPBooking()->set('is_email_to_admin', 0);
		}

		/**
		 * @since 1.0
		 * @author dungdt
		 *
		 * @param bool|FALSE $order_id
		 * @return array
		 */
		private function get_order_author_emails($order_id = FALSE)
		{
			$order_model = WPBooking_Order_Model::inst();
			$items = $order_model->get_order_items($order_id);
			$authors_email = array();

			if (!empty($items)) {
				foreach ($items as $key => $value) {
					if (!empty($value['partner_id'])) {
						$authors[$value['partner_id']][] = $value;
					}
				}
			}

			if (!empty($authors)) {
				foreach ($authors as $key => $value) {
					$to = $user_email = get_the_author_meta('user_email', $key);
					$authors_email[] = $to;
				}
			}

			return $authors_email;
		}

		/**
		 * @since 1.0
		 * @author dungdt
		 *
		 * @param $order_id
		 */
		private function send_partner_email($order_id)
		{
			do_action('wpbooking_before_send_partner_email', $order_id);

			WPBooking()->set('order_id', $order_id);
			$order_model = WPBooking_Order_Model::inst();
			$items = $order_model->get_order_items($order_id);

			$admin_email = wpbooking_get_option('system_email');

			// Send Booking Information
			// To all Partners
			$authors = array();
			$authors_email = array();
			if (!empty($items)) {
				foreach ($items as $key => $value) {
					if (!empty($value['partner_id'])) {
						$authors[$value['partner_id']][] = $value;
					}
				}
			}

			if (!empty($authors)) {
				foreach ($authors as $key => $value) {
					$to = $user_email = get_the_author_meta('user_email', $key);

					// check Author is Admin, then Ignore
					if($to==$admin_email) continue;

					// Check if author is sent, then ignore current loop
					if (in_array($to, $authors_email)) continue;

					$authors_email[] = $to;

					$subject = sprintf(__("New Order from %s", 'wpbooking'), get_bloginfo('title'));
					WPBooking()->set('items', $value);
					WPBooking()->set('is_email_to_author', 1);

					$message = $this->replaceShortcode(wpbooking_get_option('email_to_partner'));
					$message = do_shortcode($message);
					$this->send($to, $subject, $message);

					WPBooking()->set('is_email_to_author', 0);
				}
			}


			do_action('wpbooking_after_send_partner_email', $order_id);
		}

		/**
		 * Send Booking Email to Customer
		 *
		 * @author dungdt
		 * @since 1.0
		 *
		 * @param $order_id
		 *
		 */
		private function send_customer_email($order_id)
		{

			do_action('wpbooking_before_send_customer_email', $order_id);

			WPBooking()->set('order_id', $order_id);

			$order_model = WPBooking_Order_Model::inst();

			$items = $order_model->get_order_items($order_id);

			// Send Booking Information to Customer
			$customer = FALSE;
			if (!empty($items)) {
				foreach ($items as $key => $value) {
					if (!empty($value['customer_id'])) {
						$customer = $value;
					}
				}
			}

			$to = $user_email = get_the_author_meta('user_email', $customer['customer_id']);

			$subject = sprintf(__("New Order from %s", 'wpbooking'), get_bloginfo('title'));

			WPBooking()->set('is_email_to_customer', 1);

			$message = $this->replaceShortcode(wpbooking_get_option('email_to_customer'));
			$message = do_shortcode($message);

			$this->send($to, $subject, $message);

			WPBooking()->set('is_email_to_customer', 0);

			do_action('wpbooking_after_send_customer_email', $order_id);
		}

		function _send_order_email_confirm()
		{

		}

		/**
		 * Replace shortcode key to real registered shortcode. Example checkout_info -> wpbooking_email_checkout_info
		 *
		 * @since 1.0
		 * @author dungdt
		 *
		 * @param bool|string $message
		 * @return string
		 */
		function replaceShortcode($message = FALSE)
		{
			$all_shortcodes = $this->get_email_shortcodes();

			if (!empty($all_shortcodes)) {
				foreach ($all_shortcodes as $k => $v) {
					$message = str_replace($k, 'wpbooking_email_' . $k, $message);
				}
			}

			return $message;
		}

		/**
		 * Do Send Email to Specific Address, Subject and Message
		 *
		 * @since 1.0
		 * @author dungdt
		 *
		 * @param $to
		 * @param $subject
		 * @param $message
		 * @param bool|FALSE $attachment
		 * @return array
		 */
		function send($to, $subject, $message, $attachment = FALSE)
		{

			if (!$message) return array(
				'status'  => 0,
				'message' => __("Email content is empty", 'wpbooking')
			);
			if (!$to) return array(
				'status'  => 0,
				'message' => __("Email To Address is empty", 'wpbooking')
			);
			$from = wpbooking_get_option('email_from');
			$from_address = wpbooking_get_option('email_from_address');
			$headers = array();

			if ($from and $from_address) {
				$headers[] = 'From:' . $from . ' <' . $from_address . '>';
			}

			add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));

			// Apply CSS to Inline CSS
			if (class_exists('Emogrifier') and $email_css = wpbooking_get_option('email_stylesheet')) {
				try {
					$Emogrifier = new Emogrifier();
					$Emogrifier->setHtml($message);
					$Emogrifier->setCss($email_css);
					$message = $Emogrifier->emogrify();
				} catch (Exception $e) {

				}

			}

			$check = wp_mail($to, $subject, $message, $headers, $attachment);

			remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));

			return array(
				'status' => $check,
				'data'   => array(
					'to'      => $to,
					'subject' => $subject,
					'message' => $message,
					'headers' => $headers
				)
			);
		}

		function set_html_content_type()
		{
			return 'text/html';
		}

		/**
		 * Apply CSS From WPBooking -> Email Option ->CSS to Email Content
		 *
		 * @since 1.0
		 * @author dungdt
		 *
		 * @param $message
		 * @return mixed|string|void
		 */
		function apply_css($message)
		{
			// Apply CSS to Inline CSS
			if (class_exists('Emogrifier') and $email_css = wpbooking_get_option('email_stylesheet')) {
				try {
					$Emogrifier = new Emogrifier();
					$Emogrifier->setHtml($message);
					$Emogrifier->setCss($email_css);
					$message = $Emogrifier->emogrify();
				} catch (Exception $e) {

				}

			}

			$message = apply_filters('wpbooking_email_content_apply_css', $message);

			return $message;
		}

		/**
		 * Load Default Email Shortcodes in folders libraries/shortcodes/email
		 *
		 * @since 1.0
		 */
		function _load_email_shortcodes()
		{
			WPBooking_Loader::inst()->load_library('shortcodes/emails/order-table');
			WPBooking_Loader::inst()->load_library('shortcodes/emails/checkout-info');
			WPBooking_Loader::inst()->load_library('shortcodes/emails/checkout_form_field');
		}


		/**
		 * Ajax Preview Booking Email
		 *
		 * @since 1.0
		 * @author dungdt
		 */
		function _preview_email()
		{
			$allowed = array(
				'email_to_customer',
				'email_to_partner',
				'email_to_admin',
			);
			if (in_array(WPBooking_Input::get('email'), $allowed)) {

				$content = wpbooking_get_option(WPBooking_Input::get('email'));
				$content=$this->replaceShortcode($content);
				$content = do_shortcode($content);

				$content = WPBooking_Email::inst()->apply_css($content);
				echo($content);
				die;
			}
		}

		static function inst()
		{
			if (!self::$_inst) {
				self::$_inst = new self();
			}

			return self::$_inst;
		}
	}

	WPBooking_Email::inst();
}