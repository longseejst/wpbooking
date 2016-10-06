<?php 
/**
*@since 1.0.0
**/

$old_data = (isset( $data['custom_data'] ) ) ? esc_html( $data['custom_data'] ) : get_post_meta( $post_id, esc_html( $data['id'] ), true);

$class = ' wpbooking-form-group ';
$data_class = '';
if(!empty($data['condition'])){
    $class .= ' wpbooking-condition ';
    $data_class .= ' data-condition='.$data['condition'].' ' ;
}

$class.=' width-'.$data['width'];
$name = isset( $data['custom_name'] ) ? esc_html( $data['custom_name'] ) : esc_html( $data['id'] );

$field = '<div class="st-metabox-content-wrapper"><div class="form-group">';

$terms=get_terms(array('taxonomy'=>'wb_hotel_room_type','hide_empty'=>false,'parent'=>0));

$selected_term=wp_get_object_terms($post_id,'wb_hotel_room_type',array('fields'=>'ids'));
$checked=false;
if(in_array($checked,$selected_term)) $checked='selected';
if(!is_wp_error($terms) and   !empty( $terms ) ){

	$field .= '<div style="margin-bottom: 7px;"><select name="'. $name .'" id="'. esc_html( $data['id'] ) .'" class="widefat form-control '. esc_html( $data['class'] ).'">';
	foreach( $terms as $parent_key => $parent_term ){

	    $child=get_terms(array('taxonomy'=>'wb_hotel_room_type','hide_empty'=>false,'parent'=>$parent_term->term_id));
        if(!empty($child)){
            foreach($child as $term_id => $term){
                $field .= '<option parent="'.$parent_term->term_id.'" value="'. esc_html( $term_id ).'" '. $checked .'>'. esc_html( $term->name ).'</option>';
            }
        }

	}
	$field .= '</select></div>';
}

$field .= '</div></div>';

?>
<div class="form-table wpbooking-settings <?php echo esc_html( $class ); ?>" <?php echo esc_html( $data_class ); ?>>
<div class="st-metabox-left">
	<label for="<?php echo esc_html( $data['id'] ); ?>"><?php echo esc_html( $data['label'] ); ?></label>
</div>
<div class="st-metabox-right">
	<?php echo do_shortcode($field); ?>
	<div class="metabox-help"><?php echo balanceTags( $data['desc'] ) ?></div>
</div>
</div>