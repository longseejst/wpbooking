<?php
if(!class_exists('WPBooking_Form_Text_Field')){
	class WPBooking_Form_Text_Field extends WPBooking_Abstract_Formbuilder_Field
	{
		static $_inst;

		function __construct()
		{
			$this->field_id='text';
			$this->field_data=array(
				"title"    => esc_html__( "Text" , 'wpbooking' ) ,
				"category" => 'Standard Fields' ,
				"options"  => array(
					array(
						"type"             => "required" ,
						"title"            => esc_html__( "Set as <strong>required</strong>" , 'wpbooking' ) ,
						"desc"             => "" ,
						'edit_field_class' => 'wpbooking-col-md-6' ,
					) ,
					array(
						"type"             => "checkbox" ,
						'name'=>'hide_when_logged_in',
						'options'=>array(
							esc_html__( "Hide with <strong>Logged in user</strong>" , 'wpbooking' )=>1
						),
						'single_checkbox'=>1,
						'edit_field_class' => 'wpbooking-col-md-6' ,
					) ,
					array(
						"type"             => "text" ,
						"title"            => esc_html__( "Title" , 'wpbooking' ) ,
						"name"             => "title" ,
						"desc"             => esc_html__( "Title" , 'wpbooking' ) ,
						'edit_field_class' => 'wpbooking-col-md-6' ,
						'value'            => ""
					) ,
					array(
						"type"             => "text" ,
						"title"            => esc_html__( "Name" , 'wpbooking' ) ,
						"name"             => "name" ,
						"desc"             => esc_html__( "Name" , 'wpbooking' ) ,
						'edit_field_class' => 'wpbooking-col-md-6' ,
						'value'            => ""
					) ,
					array(
						"type"             => "text" ,
						"title"            => esc_html__( "ID" , 'wpbooking' ) ,
						"name"             => "id" ,
						"desc"             => esc_html__( "ID" , 'wpbooking' ) ,
						'edit_field_class' => 'wpbooking-col-md-6' ,
						'value'            => ""
					) ,
					array(
						"type"             => "text" ,
						"title"            => esc_html__( "Class" , 'wpbooking' ) ,
						"name"             => "class" ,
						"desc"             => esc_html__( "Class" , 'wpbooking' ) ,
						'edit_field_class' => 'wpbooking-col-md-6' ,
						'value'            => ""
					) ,
					array(
						"type"             => "text" ,
						"title"            => esc_html__( "Value" , 'wpbooking' ) ,
						"name"             => "value" ,
						"desc"             => esc_html__( "Value" , 'wpbooking' ) ,
						'edit_field_class' => 'wpbooking-col-md-6' ,
						'value'            => ""
					) ,
					array(
						"type"             => "text" ,
						"title"            => esc_html__( "Placeholder" , 'wpbooking' ) ,
						"name"             => "placeholder" ,
						"desc"             => esc_html__( "Placeholder" , 'wpbooking' ) ,
						'edit_field_class' => 'wpbooking-col-md-6' ,
						'value'            => ""
					) ,
					array(
						"type"             => "text" ,
						"title"            => esc_html__( "Maxlength" , 'wpbooking' ) ,
						"name"             => "maxlength" ,
						"desc"             => esc_html__( "Maxlength" , 'wpbooking' ) ,
						'edit_field_class' => 'wpbooking-col-md-6' ,
						'value'            => ""
					)
				)
			);
			parent::__construct();
		}

		function shortcode($attr=array(),$content=FALSE)
		{
			$data = wp_parse_args($attr,
				array(
					'is_required' => 'off' ,
					'title'        => '' ,
					'name'        => '' ,
					'id'          => '' ,
					'class'       => '' ,
					'value'       => '' ,
					'placeholder' => '' ,
					'size'        => '' ,
					'maxlength'   => '' ,
				)  );
			extract( $data );
			$required = "";
			$rule = array();
			if($this->is_required($attr)) {
				$required = "required";
				$rule []= "required";
			}
			if(!empty($maxlength)){
				$rule []= "max_length[".$maxlength."]";
			}

			parent::add_field($name,array('data'=>$data,'rule'=>implode('|',$rule)));
			if($this->is_hidden($attr)) return FALSE;

			$html=array('<div class="wb-field">');
			if(!empty($data['title'])){

                $title=wpbooking_get_translated_string($data['title']);
                if($required) $title.=' <span class=required >*</span>';
				$html[]=sprintf('<p><label>%s</label></p>',$title);
			}
			$html[]= '<input type="text" name="' . $name . '" id="' . $id . '" class="' . $class . '" value="' . $value . '" placeholder="' . $placeholder . '"  maxlength="' . $maxlength . '" size="' . $size . '"  ' . $required . ' /></div>';

			return implode("\r\n",$html);
		}

		function get_value($form_item_data,$post_id){
			return isset($form_item_data['value'])?$form_item_data['value']:FALSE;
		}

		static  function inst()
		{
			if(!self::$_inst){
				self::$_inst=new self();
			}

			return self::$_inst;
		}
	}
	WPBooking_Form_Text_Field::inst();

}

