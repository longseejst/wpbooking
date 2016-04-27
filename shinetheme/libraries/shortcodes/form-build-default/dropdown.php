<?php
if(function_exists( 'traveler_add_field_form_builder' )) {
    traveler_add_field_form_builder( array(
            "title"    => __( "Drop Down" , 'traveler-booking' ) ,
            "name"     => 'traveler_booking_drop_down' ,
            "category" => 'Standard Fields' ,
            "options"  => array(
                array(
                    "type"             => "required" ,
                    "title"            => __( "Set as <strong>required</strong>" , 'traveler-booking' ) ,
                    "desc"             => "" ,
                    'edit_field_class' => 'traveler-col-md-12' ,
                ) ,
                array(
                    "type"             => "text" ,
                    "title"            => __( "Title" , 'traveler-booking' ) ,
                    "name"             => "title" ,
                    "desc"             => __( "Title" , 'traveler-booking' ) ,
                    'edit_field_class' => 'traveler-col-md-6' ,
                    'value'            => ""
                ) ,
                array(
                    "type"             => "text" ,
                    "title"            => __( "Name" , 'traveler-booking' ) ,
                    "name"             => "name" ,
                    "desc"             => __( "Name" , 'traveler-booking' ) ,
                    'edit_field_class' => 'traveler-col-md-6' ,
                    'value'            => ""
                ) ,
                array(
                    "type"             => "text" ,
                    "title"            => __( "ID" , 'traveler-booking' ) ,
                    "name"             => "id" ,
                    "desc"             => __( "ID" , 'traveler-booking' ) ,
                    'edit_field_class' => 'traveler-col-md-6' ,
                    'value'            => ""
                ) ,
                array(
                    "type"             => "text" ,
                    "title"            => __( "Class" , 'traveler-booking' ) ,
                    "name"             => "class" ,
                    "desc"             => __( "Class" , 'traveler-booking' ) ,
                    'edit_field_class' => 'traveler-col-md-6' ,
                    'value'            => ""
                ) ,
                array(
                    "type"             => "textarea" ,
                    "title"            => __( "Options" , 'traveler-booking' ) ,
                    "name"             => "options" ,
                    "desc"             => __( "Ex: Title 1:value_1|Title 2:value_2" , 'traveler-booking' ) ,
                    'edit_field_class' => 'traveler-col-md-12' ,
                    'value'            => ''
                ) ,
            )
        )
    );
}
if(!function_exists( 'traveler_sc_booking_drop_down' )) {
    function traveler_sc_booking_drop_down( $attr , $content = false )
    {
        $data = shortcode_atts(
            array(
                'is_required' => 'off' ,
                'title'        => '' ,
                'name'        => '' ,
                'id'          => '' ,
                'class'       => '' ,
                'options'     => '' ,
            ) , $attr , 'traveler_booking_drop_down' );
        extract( $data );
        $required = "";
        $rule = "";
        if($is_required == "on") {
            $required = "required";
            $rule .= "required";
        }
        Traveler_Admin_Form_Build::inst()->add_form_field($name,array('data'=>$data,'rule'=>$rule));
        $list_item = "<option value=''>" . __( "-- Select --" , 'traveler-booking' ) . '</option>';
        if(!empty( $options )) {
            $tmp_list_item = explode( '|' , $options );
            if(!empty( $tmp_list_item )) {
                foreach( $tmp_list_item as $k => $v ) {
                    $tmp = explode( ':' , $v );
                    if(!empty( $tmp[ 0 ] ) and !empty( $tmp[ 1 ] )) {
                        $list_item .= "<option value='" . $tmp[ 1 ] . "'>" . $tmp[ 0 ] . "</option>";
                    }
                }
            }

        }
        return '<select name="' . $name . '" id="' . $id . '" class="' . $class . ' '.$required.'">
                    ' . $list_item . '
                </select>';
    }
}
add_shortcode( 'traveler_booking_drop_down' , 'traveler_sc_booking_drop_down' );