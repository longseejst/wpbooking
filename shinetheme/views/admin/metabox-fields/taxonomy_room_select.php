<?php

$data=wp_parse_args($data,array(
    'taxonomy'=>false
));
$class = ' wpbooking-form-group ';
$data_class = '';
if(!empty($data['condition'])){
    $class .= ' wpbooking-condition ';
    $data_class .= ' data-condition='.$data['condition'].' ' ;
}
$class.=' width-'.$data['width'];
if(!empty($data['container_class'])) $class.=' '.$data['container_class'];

$list_room = WPBooking_Hotel_Service_Type::_get_room_by_hotel(get_the_ID());

$my_term =wp_get_post_terms(get_the_ID(),$data['taxonomy']);
?>
<div class="wpbooking-settings <?php echo esc_html( $class ); ?>" <?php echo esc_html( $data_class ); ?>>
    <div class="st-metabox-left">
        <label for="<?php echo esc_html( $data['id'] ); ?>"><?php echo esc_html( $data['label'] ); ?></label>
    </div>
    <div class="st-metabox-right">
        <div class="st-metabox-content-wrapper">
            <div class="form-group taxonomy_room_select">
                     <input name="<?php echo esc_attr($data['id']) ?>_base[<?php echo esc_attr($data['taxonomy']) ?>]" type="hidden" value="false">
                    <?php
                    if(!empty($data['taxonomy'])){
                        $terms=get_terms($data['taxonomy'],array('taxonomy'=>$data['taxonomy'],'hide_empty'=>false));
                        if(!empty($terms) and !is_wp_error($terms)){
                            $i=1;
                            foreach ($terms as $term) {
                                $checked = "";
                                $checked_all = "";
                                $checked_custom = "";
                                $html_room = '';
                                if(!empty($my_term)){
                                    foreach($my_term as$k=>$v){
                                        if($v->term_id == $term->term_id){
                                            $checked = 'checked="checked"';
                                            if(empty($checked_custom)){
                                                foreach($list_room as $k=>$v){
                                                    $data_taxonomy = get_post_meta($v->ID,'taxonomy_room',true);
                                                    if(!empty($data_taxonomy[$data['taxonomy']])){
                                                        if(in_array($term->term_id,$data_taxonomy[$data['taxonomy']])){
                                                            $checked_custom = 'checked="checked"';
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($checked_custom)){
                                                $checked_all = 'checked="checked"';
                                            }
                                        }
                                    }
                                }
                                ?>
                                    <div class="wpbooking-row">
                                        <div class="wpbooking-col-sm-4">
                                            <label><input class="item_base" <?php echo esc_html($checked) ?> onclick="return false" name="<?php echo esc_attr($data['id']) ?>[<?php echo esc_attr($i) ?>][<?php echo esc_attr($data['taxonomy']) ?>]" value="<?php echo esc_attr($term->term_id) ?>" type="checkbox"><?php echo esc_html($term->name) ?></label>
                                        </div>
                                        <div class="wpbooking-col-sm-4">
                                            <label><input class="item_all" name="type_data"  value="all" <?php echo esc_html($checked_all) ?>  type="checkbox"><?php echo esc_html_e("All","wpbooking") ?></label>
                                        </div>
                                        <div class="wpbooking-col-sm-4">
                                            <label><input class="item_custom"  name="type_data" value="custom" <?php echo esc_html($checked_custom) ?> type="checkbox"><?php echo esc_html_e("Some","wpbooking") ?></label>
                                            <div class="list_post <?php if(!empty($checked_custom)) echo 'active' ?>">
                                                <?php
                                                if(!empty($list_room)){
                                                    foreach($list_room as $k=>$v){
                                                        $data_taxonomy = get_post_meta($v->ID,'taxonomy_room',true);
                                                        $checked_post = '';
                                                        if(!empty($data_taxonomy[$data['taxonomy']])){
                                                            if(in_array($term->term_id,$data_taxonomy[$data['taxonomy']])){
                                                                $checked_post = 'checked="checked"';
                                                            }
                                                        }
                                                        ?>
                                                        <p>
                                                            <label><input class="item_post" <?php echo esc_html($checked_post) ?> name="<?php echo esc_attr($data['id']) ?>[<?php echo esc_attr($i) ?>][post_id][]" value="<?php echo esc_html($v->ID) ?>" type="checkbox"><?php echo esc_html($v->post_title) ?></label>
                                                        </p>
                                                        <?php
                                                    }
                                                }else{
                                                    esc_html_e("<p>No Data</p>",'wpbooking');
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                $i++;
                            }
                        }
                    }
                    ?>

                <?php
                if(!empty($data['help_inline'])){
                    printf('<span class="help_inline">%s</span>',$data['help_inline']);
                }
                ?>

            </div>
        </div>
    </div>
    <div class="metabox-help"><?php echo balanceTags( $data['desc'] ) ?></div>
</div>
