<?php $is_tab = WPBooking_Input::request('wp_step','wp_general'); ?>
<form method="post">
    <?php wp_nonce_field('wpbooking_action','wpbooking_save_setup_demo') ?>
    <input type="hidden" name="is_tab" value="<?php echo esc_attr($is_tab) ?>">
    <div class="setup-content">
        <h1 class="text-center"><?php esc_html_e("General Setup","wp-booking-management-system") ?></h1>
        <div class="item_setup <?php echo esc_attr($is_tab) ?>">
            <h3><?php esc_html_e("Currency","wp-booking-management-system") ?></h3>
            <table class="form-table wpbooking-settings">
                <tbody>
                <tr class="setup_demo[currency][currency]">
                    <th scope="row">
                        <label for="currency"><?php esc_html_e("Currency:","wp-booking-management-system") ?></label>
                    </th>
                    <td>
                        <?php
                        $data = apply_filters('wpbooking_get_all_currency', array());
                        ?>
                        <?php if(!empty($data)){ ?>
                            <select name="setup_demo[currency][currency]" class="form-control min-width-500">
                                <?php foreach($data as $k=>$v){ ?>
                                    <option <?php if($k=='USD') echo 'selected'; ?>  value="<?php echo esc_attr($k) ?>"><?php echo esc_html($v) ?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                        <i class="wpbooking-desc"></i>
                    </td>
                </tr>
                <tr class="setup_demo[currency][symbol]">
                    <th scope="row">
                        <label for="symbol"><?php esc_html_e("Symbol:","wp-booking-management-system") ?></label>
                    </th>
                    <td>
                        <input type="text" value="$" name="setup_demo[currency][symbol]" class="form-control  min-width-500" id="setup_demo[currency][symbol]">
                        <i class="wpbooking-desc"><?php esc_html_e("Symbol of currency. For example: $","wp-booking-management-system") ?></i>
                    </td>
                </tr>
                <tr class="setup_demo[currency][position]">
                    <th scope="row">
                        <label for="position"><?php esc_html_e("Position:","wp-booking-management-system") ?></label>
                    </th>
                    <td>
                        <select name="setup_demo[currency][position]" class="form-control  min-width-500" id="setup_demo[currency][position]">
                            <option value="left" selected=""><?php esc_html_e("$99","wp-booking-management-system") ?></option>
                            <option value="right"><?php esc_html_e("99$","wp-booking-management-system") ?></option>
                            <option value="left_with_space"><?php esc_html_e("$ 99","wp-booking-management-system") ?></option>
                            <option value="right_with_space"><?php esc_html_e("99 $","wp-booking-management-system") ?></option>
                        </select>
                        <i class="wpbooking-desc"><?php esc_html_e("Position of Symbol","wp-booking-management-system") ?></i>
                    </td>
                </tr>
                <tr class="setup_demo[currency][thousand_sep]">
                    <th scope="row">
                        <label for="thousand_sep"><?php esc_html_e("Thousand Separator:","wp-booking-management-system") ?></label>
                    </th>
                    <td>
                        <input type="text" name="setup_demo[currency][thousand_sep]" value="," class="form-control  min-width-500" >
                        <i class="wpbooking-desc"><?php esc_html_e("Thousand Separator","wp-booking-management-system") ?></i>
                    </td>
                </tr>
                <tr class="setup_demo[currency][decimal_sep]">
                    <th scope="row">
                        <label for="decimal_sep"><?php esc_html_e("Decimal Separator:","wp-booking-management-system") ?></label>
                    </th>
                    <td>
                        <input type="text" name="setup_demo[currency][decimal_sep]" value="." class="form-control  min-width-500">
                        <i class="wpbooking-desc"><?php esc_html_e("Decimal Separator","wp-booking-management-system") ?></i>
                    </td>
                </tr>
                <tr class="setup_demo[currency][decimal]">
                    <th scope="row">
                        <label for="decimal"><?php esc_html_e("Decimal:","wp-booking-management-system") ?></label>
                    </th>
                    <td>
                        <input type="number" name="setup_demo[currency][decimal]" value="2" class="form-control">
                        <i class="wpbooking-desc"><?php esc_html_e("Decimal","wp-booking-management-system") ?></i>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="control">
            <button name="wpbooking_save_setup" value="true" class="button-primary button button-large"><?php esc_html_e("Save & Continue","wp-booking-management-system") ?></button>
        </div>
    </div>
</form>