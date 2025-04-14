<?php
/**
 * Calendar module template fields
 * 
 * @package Steget
 */

/**
 * Render calendar template fields
 */
function render_calendar_template_fields($post) {
    $calendar_settings = json_decode(get_post_meta($post->ID, 'module_calendar_settings', true), true) ?: [
        'calendar_type' => 'date_picker',
        'min_date' => '',
        'max_date' => '',
        'disabled_dates' => [],
        'available_times' => []
    ];
    ?>
<div id="calendar_fields" class="template-fields">
    <p>
        <label for="calendar_type"><strong><?php _e('Calendar Type', 'steget'); ?>:</strong></label><br>
        <select name="calendar_type" id="calendar_type" class="widefat">
            <option value="date_picker" <?php selected($calendar_settings['calendar_type'], 'date_picker'); ?>>
                <?php _e('Date Picker', 'steget'); ?></option>
            <option value="date_range" <?php selected($calendar_settings['calendar_type'], 'date_range'); ?>>
                <?php _e('Date Range Picker', 'steget'); ?></option>
            <option value="booking" <?php selected($calendar_settings['calendar_type'], 'booking'); ?>>
                <?php _e('Booking Calendar', 'steget'); ?></option>
            <option value="event" <?php selected($calendar_settings['calendar_type'], 'event'); ?>>
                <?php _e('Event Calendar', 'steget'); ?></option>
        </select>
    </p>

    <p>
        <label for="calendar_min_date"><strong><?php _e('Minimum Date', 'steget'); ?>:</strong></label><br>
        <input type="date" name="calendar_min_date" id="calendar_min_date"
            value="<?php echo esc_attr($calendar_settings['min_date']); ?>" class="widefat">
        <span
            class="description"><?php _e('Earliest selectable date (leave empty for no restriction)', 'steget'); ?></span>
    </p>

    <p>
        <label for="calendar_max_date"><strong><?php _e('Maximum Date', 'steget'); ?>:</strong></label><br>
        <input type="date" name="calendar_max_date" id="calendar_max_date"
            value="<?php echo esc_attr($calendar_settings['max_date']); ?>" class="widefat">
        <span
            class="description"><?php _e('Latest selectable date (leave empty for no restriction)', 'steget'); ?></span>
    </p>

    <div id="calendar_disabled_dates">
        <h4><?php _e('Disabled Dates', 'steget'); ?></h4>
        <div id="disabled_dates_container">
            <?php foreach ($calendar_settings['disabled_dates'] as $index => $date) : ?>
            <div class="disabled-date-row">
                <input type="date" name="calendar_disabled_date[]" value="<?php echo esc_attr($date); ?>"
                    class="widefat">
                <button type="button" class="button remove-disabled-date"><?php _e('Remove', 'steget'); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button add-disabled-date"><?php _e('Add Disabled Date', 'steget'); ?></button>
    </div>

    <div id="calendar_booking_times"
        class="<?php echo $calendar_settings['calendar_type'] === 'booking' ? '' : 'hidden'; ?>">
        <h4><?php _e('Available Times', 'steget'); ?></h4>
        <div id="available_times_container">
            <?php foreach ($calendar_settings['available_times'] as $index => $time) : ?>
            <div class="available-time-row">
                <input type="time" name="calendar_available_time[]" value="<?php echo esc_attr($time); ?>"
                    class="widefat">
                <button type="button" class="button remove-available-time"><?php _e('Remove', 'steget'); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button add-available-time"><?php _e('Add Available Time', 'steget'); ?></button>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Show/hide booking times based on calendar type
        $('#calendar_type').on('change', function() {
            if ($(this).val() === 'booking') {
                $('#calendar_booking_times').removeClass('hidden');
            } else {
                $('#calendar_booking_times').addClass('hidden');
            }
        });

        // Add disabled date
        $('.add-disabled-date').on('click', function() {
            var template = `
                        <div class="disabled-date-row">
                            <input type="date" name="calendar_disabled_date[]" value="" class="widefat">
                            <button type="button" class="button remove-disabled-date"><?php _e('Remove', 'steget'); ?></button>
                        </div>
                    `;
            $('#disabled_dates_container').append(template);
        });

        // Remove disabled date
        $(document).on('click', '.remove-disabled-date', function() {
            $(this).closest('.disabled-date-row').remove();
        });

        // Add available time
        $('.add-available-time').on('click', function() {
            var template = `
                        <div class="available-time-row">
                            <input type="time" name="calendar_available_time[]" value="" class="widefat">
                            <button type="button" class="button remove-available-time"><?php _e('Remove', 'steget'); ?></button>
                        </div>
                    `;
            $('#available_times_container').append(template);
        });

        // Remove available time
        $(document).on('click', '.remove-available-time', function() {
            $(this).closest('.available-time-row').remove();
        });
    });
    </script>

    <style type="text/css">
    .disabled-date-row,
    .available-time-row {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .disabled-date-row .button,
    .available-time-row .button {
        margin-left: 10px;
    }

    .hidden {
        display: none;
    }

    #calendar_disabled_dates,
    #calendar_booking_times {
        margin-top: 20px;
    }
    </style>
</div>
<?php
}
