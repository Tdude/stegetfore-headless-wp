jQuery(document).ready(function($) {
    if ($.fn.wpColorPicker) {
        $('.color-picker, .selling-point-color-picker').wpColorPicker();
    } else {
        console.error('wpColorPicker is not available!');
    }
});
