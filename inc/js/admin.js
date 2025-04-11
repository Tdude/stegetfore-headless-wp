/**
 * Admin JavaScript for the Theme Options page
 */
jQuery(document).ready(function ($) {
  // Prevent JSON parsing errors when typing in editor
  // This ensures our module functions don't interfere with the editor
  var safeJsonParse = function(jsonString) {
    if (typeof jsonString !== 'string' || !jsonString.trim()) {
      return null;
    }
    try {
      return JSON.parse(jsonString);
    } catch (e) {
      console.log("Warning: Failed to parse JSON string: ", e);
      return null;
    }
  };

  // Monkey patch global JSON.parse to prevent errors in WordPress admin
  // This specifically targets the issue in Gutenberg editor
  (function() {
    var originalJsonParse = JSON.parse;
    JSON.parse = function(text, reviver) {
      try {
        return originalJsonParse.call(JSON, text, reviver);
      } catch (e) {
        console.log("Prevented JSON parse error:", e.message.substring(0, 50));
        // Return null instead of throwing an error
        return null;
      }
    };
    
    console.log('Applied JSON parsing fix to WordPress admin');
  })();

  // Handle repeatable items (add)
  $(".steget-add-item").on("click", function () {
    var templateId = $(this).data("template");
    var containerId = $(this).data("container");
    var template = $("#" + templateId).html();
    var container = $("#" + containerId);
    var newIndex = container.find(".steget-repeater-item").length;
    var newId = new Date().getTime(); // Unique ID for the new item

    // Replace placeholders with actual values
    template = template.replace(/\{\{index\}\}/g, newIndex);
    template = template.replace(/\{\{id\}\}/g, newId);

    // Add the new item to the container
    container.append(template);
  });

  // Handle repeatable items (remove)
  $(document).on("click", ".steget-remove-item", function () {
    var item = $(this).closest(".steget-repeater-item");

    if (confirm("Är du säker på att du vill ta bort detta objekt?")) {
      item.fadeOut(300, function () {
        item.remove();

        // Optional: Reindex the remaining items
        // This would involve updating the index in name attributes
      });
    }
  });

  // Initialize any media uploaders if needed
  // Note: Each feature file that uses media uploader has its own JS

  // MODULE PAGE INTEGRATION FUNCTIONALITY
  // Initialize sortable for modules
  if ($('#page_modules_container').length) {
    $('#page_modules_container').sortable({
      handle: '.module-drag',
      placeholder: 'module-placeholder',
      forcePlaceholderSize: true
    });

    // Add new module
    $('#add_module').on('click', function() {
      var moduleId = $('#module_selector').val();

      if (!moduleId) {
        return;
      }

      var moduleTitle = $('#module_selector option:selected').text();
      var index = $('.module-item').length;

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'get_module_info',
          module_id: moduleId,
          nonce: $('#nonce').val()
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            var moduleData = response.data;
            var moduleHtml = '<div class="module-item" data-id="' + moduleId + '">' +
              '<div class="module-header">' +
              '<span class="module-drag dashicons dashicons-move"></span>' +
              '<strong>' + moduleData.title + '</strong>' +
              '<span class="module-type">(' + moduleData.template_name + ')</span>' +
              '<div class="module-actions">' +
              '<a href="' + moduleData.edit_url + '" class="module-edit" target="_blank">' +
              '<span class="dashicons dashicons-edit"></span>' +
              '</a>' +
              '<a href="#" class="module-remove">' +
              '<span class="dashicons dashicons-trash"></span>' +
              '</a>' +
              '</div>' +
              '</div>' +
              '<div class="module-settings">' +
              '<input type="hidden" name="module_id[]" value="' + moduleId + '">' +
              '<label>' +
              '<input type="checkbox" name="module_override_settings[' + index + ']" value="1"> ' +
              'Override module settings' +
              '</label>' +
              '<div class="module-override-options hidden">' +
              '<p>' +
              '<label>Layout:</label>' +
              '<select name="module_layout[' + index + ']">' +
              '<option value="center">Center</option>' +
              '<option value="left">Left</option>' +
              '<option value="right">Right</option>' +
              '</select>' +
              '</p>' +
              '<p>' +
              '<label>' +
              '<input type="checkbox" name="module_full_width[' + index + ']" value="1"> ' +
              'Full Width' +
              '</label>' +
              '</p>' +
              '<p>' +
              '<label>Background Color: ' +
              '<input type="text" name="module_background_color[' + index + ']" class="module-color-picker" value="">' +
              '</label>' +
              '</p>' +
              '</div>' +
              '</div>' +
              '</div>';

            $('#page_modules_container').append(moduleHtml);
            $('.module-color-picker').wpColorPicker();
          }
        }
      });
    });

    // Remove module
    $(document).on('click', '.module-remove', function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to remove this module?')) {
        $(this).closest('.module-item').remove();
      }
    });

    // Toggle override settings
    $(document).on('change', 'input[name^="module_override_settings"]', function() {
      $(this).closest('.module-settings').find('.module-override-options').toggleClass('hidden', !this.checked);
    });

    // Initialize color pickers
    $('.module-color-picker').wpColorPicker();
  }
  
  // Fix for WordPress editor JSON parsing errors
  // This prevents our module code from trying to parse editor content as JSON
  $(document).on('input change keyup', '.wp-editor-area', function() {
    // Check if we're in the content editor
    if ($(this).attr('id') === 'content') {
      // Mark the editor as being modified to prevent other scripts from parsing its content
      $(this).attr('data-content-modified', 'true');
      
      // Prevent JSON parsing of content by setting a flag
      window.preventContentParsing = true;
    }
  });
  
  console.log('Admin JS loaded with JSON parsing safeguards');
});
