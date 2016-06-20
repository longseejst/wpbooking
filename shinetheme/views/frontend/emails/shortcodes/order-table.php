<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 4/21/2016
 * Time: 4:33 PM
 */
$booking=WPBooking_Order::inst();
$order_id=WPBooking()->get('order_id');
if(!$order_id) return;
$items=$booking->get_order_items($order_id);
?>
<table width="100%" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th><?php _e('No','wpbooking') ?></th>
		<th class="review-order-item-info"><?php _e('Service','wpbooking')?></th>
		<th class="review-order-item-total"><?php _e('Price','wpbooking')?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i=1;
	foreach($items as $key=>$value)
	{
		$service_type=$value['service_type'];
		?>
		<tr>
			<td class="small-td" width="5"><?php echo esc_html($i) ?></td>
			<td class="review-order-item-info">
				<h4 class="service-name"><a href="<?php echo get_permalink($value['post_id'])?>" target="_blank"><?php echo get_the_title($value['post_id'])?></a></h4>
				<?php do_action('wpbooking_order_item_information',$value) ?>
				<?php do_action('wpbooking_order_item_information_'.$service_type,$value) ?>
				<?php do_action('wpbooking_email_order_item_information_'.$service_type,$value) ?>
			</td>
			<td class="review-order-item-total">
				<p class="cart-item-price"><?php echo WPBooking_Currency::format_money($booking->get_order_item_total($value)); ?></p>
			</td>
		</tr>
		<?php
		$i++;
	}?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><?php _e('Total','wpbooking')?></td>
			<td><?php echo WPBooking_Currency::format_money($booking->get_order_total($order_id));?></td>
		</tr>
		<tr>
			<td colspan="2"><?php _e('Pay Amount','wpbooking')?></td>
			<td><?php echo WPBooking_Currency::format_money($booking->get_order_pay_amount($order_id));?></td>
		</tr>
		<?php do_action('wpbooking_review_order_footer') ?>
	</tfoot>
</table>