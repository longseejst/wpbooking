<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 6/14/2016
 * Time: 8:46 AM
 */
$order_model=WPBooking_Order_Model::inst();
$order_model->limit(20)->orderby('id','desc');

if(WPBooking_Input::get('service_type')){
	$order_model->where('service_type',WPBooking_Input::get('service_type'));
}
if(WPBooking_Input::get('status')){
	$order_model->where('status',WPBooking_Input::get('status'));
}

if(WPBooking_Input::get('payment_status')){
	$order_model->where('payment_status',WPBooking_Input::get('payment_status'));
}

$rows=$order_model->get()->result();
?>
<table class="wp-list-table widefat fixed striped posts">
	<thead>
	<tr>
		<td id="cb" class="manage-column column-cb check-column">
			<input id="cb-select-all-1" type="checkbox">
		</td>
		<td class="manage-column column-title column-primary sortable">
			<?php esc_html_e('ID - Customer','wpbooking') ?>
		</td>
		<td class="manage-column column-title column-primary sortable">
			<?php esc_html_e('Booking Data','wpbooking') ?>
		</td>
		<td class="manage-column column-date asc"> <?php esc_html_e('Status','wpbooking') ?></td>
		<td class="manage-column column-date asc"> <?php esc_html_e('Payment','wpbooking') ?></td>
		<td class="manage-column column-date asc"> <?php esc_html_e('Service Type','wpbooking') ?></td>
		<td class="manage-column column-date asc"> <?php esc_html_e('Total','wpbooking') ?></td>
	</tr>
	</thead>

	<tbody>
	<?php if(!empty($rows)){
		foreach($rows as $row){
			$url=add_query_arg(array('id'=>$row['id'],'page'=>'wpbooking_page_orders'),admin_url());
			$service_type=$row['service_type'];
			?>
			<tr>
				<th class="manage-column column-cb check-column">
					<input  type="checkbox" name="wpbooking_post[]" value="<?php echo esc_attr($row['id']) ?>">
				</th>
				<td>
					<a href="<?php echo esc_url($url)  ?>">#<?php echo esc_attr($row['id']) ?></a>
					-
					<?php if($row['customer_id']){
						$user=get_userdata($row['customer_id']);
						if(!$user){
							printf('<label class="label label-warning">%s</label>',esc_html__('Unknown','wpbooking'));
						}else{
							printf('<label class="label label-info"><a href="%s" target="_blank"> %s</a></label>',get_edit_user_link($row['customer_id']),$user->display_name);
						}
					}else{
						printf('<label class="label label-default">%s</label>',esc_html__('Guest','wpbooking'));
					} ?>
					<div class="row-actions">
						<span class="edit"><a href="<?php echo esc_url($url)  ?>" title="<?php esc_html_e('Edit this item','wpbooking')?>"><?php esc_html_e('Edit','wpbooking')?></a> | </span>
						<span class="edit"><a href="<?php echo esc_url($url)  ?>" title="<?php esc_html_e('Edit this item','wpbooking')?>"><?php esc_html_e('Edit','wpbooking')?></a> | </span>
					</div>
				</td>
				<td class="booking-data">
					<a class="service-name" href="<?php echo get_permalink($row['post_id'])?>" target="_blank"><?php echo get_the_title($row['post_id'])?></a>
					<?php do_action('wpbooking_order_item_information',$row) ?>
					<?php do_action('wpbooking_order_item_information_'.$service_type,$row) ?>
				</td>
				<td>
					<?php
					echo wpbooking_order_item_status_html($row['status']);
					?>
				</td>
				<td>
					<?php
					echo wpbooking_payment_status_html($row['payment_status']);
					?>

				</td>
				<td>
					<?php
					$service_type_obj=WPBooking_Service::inst()->get_service_type($service_type);
					if($service_type_obj){
						echo ($service_type_obj['label']);
					}

					?>
				</td>
				<td class="manage-column column-date asc">
					<?php
					echo WPBooking_Order::inst()->get_order_item_total_html($row);
					?>
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr>
			<td colspan="10"><?php esc_html_e('No Booking Found','wpbooking') ?></td>
		</tr>
		<?php
	} ?>
	</tbody>
</table>
