<?php
/**
 * Login module template fields
 * 
 * @package Steget
 */

/**
 * Render login template fields
 */
function render_login_template_fields($post) {
    $login_settings = json_decode(get_post_meta($post->ID, 'module_login_settings', true), true) ?: [
        'redirect_url' => '',
        'show_register' => true,
        'show_lost_password' => true
    ];
    ?>
<div id="login_fields" class="template-fields">
    <p>
        <label for="login_redirect_url"><strong><?php _e('Redirect URL after login', 'steget'); ?>:</strong></label><br>
        <input type="url" name="login_redirect_url" id="login_redirect_url"
            value="<?php echo esc_url($login_settings['redirect_url']); ?>" class="widefat">
        <span class="description"><?php _e('Leave empty to redirect to the homepage', 'steget'); ?></span>
    </p>

    <p>
        <label>
            <input type="checkbox" name="login_show_register"
                <?php checked(isset($login_settings['show_register']) && $login_settings['show_register']); ?>>
            <?php _e('Show registration link', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="login_show_lost_password"
                <?php checked(isset($login_settings['show_lost_password']) && $login_settings['show_lost_password']); ?>>
            <?php _e('Show lost password link', 'steget'); ?>
        </label>
    </p>
</div>
<?php
}
