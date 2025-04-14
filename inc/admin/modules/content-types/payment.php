<?php
/**
 * Payment module template fields
 * 
 * @package Steget
 */

/**
 * Render payment template fields
 */
function render_payment_template_fields($post) {
    $payment_settings = json_decode(get_post_meta($post->ID, 'module_payment_settings', true), true) ?: [
        'payment_type' => 'stripe',
        'product_id' => '',
        'amount' => '',
        'currency' => 'SEK',
        'success_url' => '',
        'cancel_url' => ''
    ];
    ?>
<div id="payment_fields" class="template-fields">
    <p>
        <label for="payment_type"><strong><?php _e('Payment Gateway', 'steget'); ?>:</strong></label><br>
        <select name="payment_type" id="payment_type" class="widefat">
            <option value="stripe" <?php selected($payment_settings['payment_type'], 'stripe'); ?>>
                <?php _e('Stripe', 'steget'); ?></option>
            <option value="swish" <?php selected($payment_settings['payment_type'], 'swish'); ?>>
                <?php _e('Swish', 'steget'); ?></option>
            <option value="paypal" <?php selected($payment_settings['payment_type'], 'paypal'); ?>>
                <?php _e('PayPal', 'steget'); ?></option>
            <option value="klarna" <?php selected($payment_settings['payment_type'], 'klarna'); ?>>
                <?php _e('Klarna', 'steget'); ?></option>
        </select>
    </p>

    <p>
        <label for="payment_product_id"><strong><?php _e('Product ID', 'steget'); ?>:</strong></label><br>
        <input type="text" name="payment_product_id" id="payment_product_id"
            value="<?php echo esc_attr($payment_settings['product_id']); ?>" class="widefat">
        <span class="description"><?php _e('If using a specific product/service', 'steget'); ?></span>
    </p>

    <p>
        <label for="payment_amount"><strong><?php _e('Amount', 'steget'); ?>:</strong></label><br>
        <input type="number" name="payment_amount" id="payment_amount"
            value="<?php echo esc_attr($payment_settings['amount']); ?>" class="widefat">
        <span class="description"><?php _e('Leave empty if product-based pricing', 'steget'); ?></span>
    </p>

    <p>
        <label for="payment_currency"><strong><?php _e('Currency', 'steget'); ?>:</strong></label><br>
        <select name="payment_currency" id="payment_currency" class="widefat">
            <option value="SEK" <?php selected($payment_settings['currency'], 'SEK'); ?>>
                <?php _e('Swedish Krona (SEK)', 'steget'); ?></option>
            <option value="EUR" <?php selected($payment_settings['currency'], 'EUR'); ?>>
                <?php _e('Euro (EUR)', 'steget'); ?></option>
            <option value="USD" <?php selected($payment_settings['currency'], 'USD'); ?>>
                <?php _e('US Dollar (USD)', 'steget'); ?></option>
            <option value="GBP" <?php selected($payment_settings['currency'], 'GBP'); ?>>
                <?php _e('British Pound (GBP)', 'steget'); ?></option>
            <option value="DKK" <?php selected($payment_settings['currency'], 'DKK'); ?>>
                <?php _e('Danish Krone (DKK)', 'steget'); ?></option>
            <option value="NOK" <?php selected($payment_settings['currency'], 'NOK'); ?>>
                <?php _e('Norwegian Krone (NOK)', 'steget'); ?></option>
        </select>
    </p>

    <p>
        <label for="payment_success_url"><strong><?php _e('Success URL', 'steget'); ?>:</strong></label><br>
        <input type="url" name="payment_success_url" id="payment_success_url"
            value="<?php echo esc_url($payment_settings['success_url']); ?>" class="widefat">
        <span class="description"><?php _e('Redirect URL after successful payment', 'steget'); ?></span>
    </p>

    <p>
        <label for="payment_cancel_url"><strong><?php _e('Cancel URL', 'steget'); ?>:</strong></label><br>
        <input type="url" name="payment_cancel_url" id="payment_cancel_url"
            value="<?php echo esc_url($payment_settings['cancel_url']); ?>" class="widefat">
        <span class="description"><?php _e('Redirect URL if payment is canceled', 'steget'); ?></span>
    </p>
</div>
<?php
}
