<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 8/5/2016
 * Time: 11:22 AM
 */
$service=new WB_Service();
$service_type=$service->get_type();
$related=$service->get_related_query();
if(!$related or !$related->have_posts()) return FALSE;
?>
<div class="service-content-section">
	<h5 class="service-info-title"><?php esc_html_e('Related Room','wpbooing')?></h5>
	<div class="wpbooking-loop-wrap">
	<?php
	echo wpbooking_load_view('archive/loop',array('my_query'=>$related,'service_type'=>$service_type));
	 ?>
	</div>
</div>
<?php wp_reset_postdata();