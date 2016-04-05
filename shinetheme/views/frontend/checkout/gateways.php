<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 4/5/2016
 * Time: 10:04 AM
 */
$booking=Traveler_Booking::inst();
$gateway=Traveler_Payment_Gateways::inst();
$all=$gateway->get_available_gateways();
$pay_amount=$booking->get_cart_pay_amount();
?>
<ul class="traveler-all-gateways">
	<?php if(!empty($all))
	{
		foreach($all as $key=>$value)
		{
			?>
			<li class="traveler-gateway-item">

				<h4 class="gateway-title">
					<label>
						<input type="radio" name="payment_gateway" value="<?php echo esc_attr($key)?>" >
						<?php echo $value->get_option('title') ?>
					</label>
				</h4>
				<div class="gateway-desc">
					<?php echo do_shortcode($value->get_option('desc'));
					do_action('traveler_gateway_desc',$key,$value);
					do_action('traveler_gateway_desc_'.$key,$value);
					?>
				</div>
			</li>
			<?php
		}
	}
	?>
</ul>