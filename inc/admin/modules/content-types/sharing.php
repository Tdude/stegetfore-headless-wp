<?php
/**
 * Sharing module template fields
 * 
 * @package Steget
 */

/**
 * Render sharing template fields
 */
function render_sharing_template_fields($post) {
    $networks = json_decode(get_post_meta($post->ID, 'module_sharing_networks', true), true) ?: [
        'facebook' => true,
        'twitter' => true,
        'linkedin' => true,
        'email' => true
    ];
    ?>
<div id="sharing_fields" class="template-fields">
    <p><?php _e('Select social networks to display:', 'steget'); ?></p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_facebook"
                <?php checked(isset($networks['facebook']) && $networks['facebook']); ?>>
            <?php _e('Facebook', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_twitter"
                <?php checked(isset($networks['twitter']) && $networks['twitter']); ?>>
            <?php _e('Twitter', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_linkedin"
                <?php checked(isset($networks['linkedin']) && $networks['linkedin']); ?>>
            <?php _e('LinkedIn', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_pinterest"
                <?php checked(isset($networks['pinterest']) && $networks['pinterest']); ?>>
            <?php _e('Pinterest', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_email"
                <?php checked(isset($networks['email']) && $networks['email']); ?>>
            <?php _e('Email', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_whatsapp"
                <?php checked(isset($networks['whatsapp']) && $networks['whatsapp']); ?>>
            <?php _e('WhatsApp', 'steget'); ?>
        </label>
    </p>
</div>
<?php
}
