jQuery(document).ready(function($) {
    // Add new selling point
    $('.add-selling-point').on('click', function() {
        var count = $('.selling-point-item').length + 1;
        // Clone the icon select template from the hidden div
        var iconTemplate = $('#selling-point-icon-select-template').html();
        var template = `
            <div class="selling-point-item">
                <h4>${stegetSellingPointsAdmin.labels.selling_point} #${count}</h4>
                <p>
                    <label><strong>${stegetSellingPointsAdmin.labels.title}:</strong></label><br>
                    <input type="text" name="selling_point_title[]" value="" class="widefat">
                </p>
                <p>
                    <label><strong>${stegetSellingPointsAdmin.labels.description}:</strong></label><br>
                    <textarea name="selling_point_description[]" rows="3" class="widefat"></textarea>
                </p>
                <p>
                    <label><strong>${stegetSellingPointsAdmin.labels.icon}:</strong></label><br>
                    ${iconTemplate}
                </p>
                <p>
                    <label><strong>${stegetSellingPointsAdmin.labels.color}:</strong></label><br>
                    <input type="text" name="selling_point_color[]" value="" class="widefat selling-point-color-picker">
                </p>
                <button type="button" class="button steget-remove-selling-point">${stegetSellingPointsAdmin.labels.remove}</button>
                <hr>
            </div>
        `;
        $('#selling_points_container').append(template);
        // Defensive: Only initialize color picker if available
        if ($.fn.wpColorPicker) {
            $('#selling_points_container .selling-point-color-picker').last().wpColorPicker();
        } else {
            console.error('wpColorPicker is not available!');
        }
    });
    // Remove selling point
    $(document).on('click', '.steget-remove-selling-point', function() {
        $(this).closest('.selling-point-item').remove();
    });
    $(document).on('change', '.selling-point-icon-select', function() {
        var selected = $(this).val();
        var $preview = $(this).siblings('.selling-point-icon-preview');
        $preview.empty();
        // Fetch SVG preview from WP via AJAX (PHP will render inline SVG)
        $.post(ajaxurl, {
            action: 'get_selling_point_icon_svg',
            icon: selected
        }, function(response) {
            if (response && response.success && response.data) {
                $preview.html(response.data);
            } else {
                $preview.text(selected);
            }
        }).fail(function() {
            $preview.text(selected);
        });
    });
    // On page load, make sure all previews are correct
    $('.selling-point-icon-select').each(function() {
        var selected = $(this).val();
        var $preview = $(this).siblings('.selling-point-icon-preview');
        $.post(ajaxurl, {
            action: 'get_selling_point_icon_svg',
            icon: selected
        }, function(response) {
            if (response && response.success && response.data) {
                $preview.html(response.data);
            } else {
                $preview.text(selected);
            }
        }).fail(function() {
            $preview.text(selected);
        });
    });
    // Defensive: Only initialize color picker if available
    if ($.fn.wpColorPicker) {
        $('.selling-point-color-picker').wpColorPicker();
    } else {
        console.error('wpColorPicker is not available!');
    }
});
