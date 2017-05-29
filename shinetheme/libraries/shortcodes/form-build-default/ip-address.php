<?php
if (!class_exists('WPBooking_Ip_Address_Field')) {
	class WPBooking_Ip_Address_Field extends WPBooking_Abstract_Formbuilder_Field
	{
		static $_inst;

		function __construct()
		{
			$this->field_id = 'ip_address';
			$this->field_data = array(
				"title"    => esc_html__("IP Address (Client)", 'wpbooking'),
				"category" => esc_html__("Hidden Fields", 'wpbooking'),
				"options"  => array(
					array(
						"type"             => "text",
						"title"            => esc_html__("Title", 'wpbooking'),
						"name"             => "title",
						"desc"             => esc_html__("Title", 'wpbooking'),
						'edit_field_class' => 'wpbooking-col-md-6',
						'value'            => ""
					),

					array(
						"type"             => "text",
						"title"            => esc_html__("ID (optional)", 'wpbooking'),
						"name"             => "id",
						"desc"             => esc_html__("ID", 'wpbooking'),
						'edit_field_class' => 'wpbooking-col-md-6',
						'value'            => ""
					),
					array(
						"type"             => "text",
						"title"            => esc_html__("Class (optional)", 'wpbooking'),
						"name"             => "class",
						"desc"             => esc_html__("Class", 'wpbooking'),
						'edit_field_class' => 'wpbooking-col-md-6',
						'value'            => ""
					),
//					array(
//						"type"             => "text",
//						"title"            => esc_html__("Value (optional)", 'wpbooking'),
//						"name"             => "value",
//						"desc"             => esc_html__("Value", 'wpbooking'),
//						'edit_field_class' => 'wpbooking-col-md-6',
//						'value'            => ""
//					),
				)
			);
			parent::__construct();
		}

		function shortcode($attr = array(), $content = FALSE)
		{
			$data = wp_parse_args($attr,
				array(
					'is_required' => 'off',
					'title'       => '',
					'name'        => 'ip_address',
					'id'          => '',
					'class'       => '',
					'value'       => '',
					'placeholder' => '',
					'size'        => '',
					'maxlength'   => '',
				));
			extract($data);



			$array = array(
				'id'          => $id,
				'class'       => $class.' ',
				'value'       => $value,
				'placeholder' => $placeholder,
				'size'        => $size,
				'maxlength'   => $maxlength,
				'name'        => $name
			);

			$required = "";
			$rule = array();
			if ($is_required == "on") {
				$required = "required";
				$rule [] = "required";
				$array['class'].=' required';
			}
			if (!empty($maxlength)) {
				$rule [] = "max_length[".$maxlength."]";
			}
			parent::add_field($name, array('data' => $data, 'rule' => implode('|', $rule)));

			$a = FALSE;

			foreach ($array as $key => $val) {
				if ($val) {
					$a .= ' ' . $key . '="' . $val . '"';
				}
			}

			return '<input type="hidden" '.$a.' />';
		}

		function get_value($form_item_data,$post_id)
		{
			return isset($form_item_data['value']) ? $form_item_data['value'] : FALSE;
		}

		static function inst()
		{
			if (!self::$_inst) {
				self::$_inst = new self();
			}

			return self::$_inst;
		}
	}

	WPBooking_Ip_Address_Field::inst();

}

