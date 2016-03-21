<?php 
/**
*@since 1.0.0
**/

$old_data = esc_html( $data['std'] );

$value = get_post_meta( get_the_ID(), esc_html( $data['id'] ) );
if( !empty( $value ) ){
	$old_data = $value;
}

$field = '<div class="form-group traveler-settings">';

if( !empty( $data['label'] ) )
	echo '<div class="form-label"><label for="'.esc_html( $data['id'] ).'">'. esc_html( $data['label'] ) .'</label></div>';

$field .= '<input type="text" id="fg_metadata" class="fg_metadata none" value="'. esc_html( $old_data ) .'" name="'. esc_html( $data['id'] ) .'">
			<br>
        <div class="featuredgallerydiv max-width-500">';

$tmp = explode( ',', $old_data );

if( count( $tmp ) > 0 and !empty( $tmp[ 0 ] ) ){
 	foreach( $tmp as $k => $v ){
        $url = wp_get_attachment_image_url( $v );
        if( !empty( $url ) ){
            $field .= '<img src="<?php echo esc_url($url) ?>" class="demo-image-gallery settings-demo-gallery" >';
        } 
    }
}

$field .= '</div>';        

$field .= '<button style="margin-right: 10px;" id="btn_upload_gallery" class="btn button button-primary btn_upload_gallery" type="button" name="">'. __("Add Gallery","traveler-booking").'</button>';
if( count( $tmp = explode(',', $old_data ) ) > 0 ){
    $field .= '<button class="btn button btn_remove_demo_gallery button-secondary" type="button" name="">'.__("Remove Gallery","traveler-booking").'</button>';
}

$field .= '</div>';
if( !empty( $data['desc'] ) ): ?>
<div class="st-metabox-content-wrapper">
	<div class="st-metabox-content-left">
		<?php echo $field;  ?>
	</div>
	<div class="st-metabox-content-right">
		<div class="description"><?php echo esc_html( $data['desc'] ); ?></div>
	</div>
</div>	
<?php else: 
echo '<div class="st-metabox-content-wrapper">';
	echo $field; 
echo '</div>';	
endif; ?>