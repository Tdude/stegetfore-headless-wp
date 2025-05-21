/**
 * Admin JavaScript for the Theme Options page
 */
jQuery(document).ready(function($) {
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
      console.log('[DEBUG] #add_module clicked:', this);
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
            console.log('[DEBUG] Module added:', moduleId);
            $('.module-color-picker').wpColorPicker();
          }
        }
      });
    });

    // Remove module (defensive: unbind first to prevent double binding)
    $(document).off('click', '.module-remove');
    $(document).on('click', '.module-remove', function(e) {
      e.preventDefault();
      console.log('[DEBUG] .module-remove clicked:', this, $(this).closest('.module-item').data('id'));
      if (confirm('Are you sure you want to remove this module?')) {
        $(this).closest('.module-item').remove();
        console.log('[DEBUG] Module removed:', $(this).closest('.module-item').data('id'));
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

  // --- Improved auto-refresh page after save to show new module fields ---
  // Listen for post/page save via form submission (classic editor)
  $('#post').on('submit', function() {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        $(mutation.addedNodes).each(function() {
          // Accept ANY .notice, .updated, .is-success, .notice-success, #message.updated
          if ($(this).is('.notice, .updated, .is-success, .notice-success, #message.updated')) {
            if (!$(this).data('handled')) {
              $(this).data('handled', true);
              if (!window._stegetModuleReloaded) {
                window._stegetModuleReloaded = true;
                location.reload();
              }
            }
          }
        });
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
    setTimeout(function() {
      if (!window._stegetModuleReloaded) {
        window._stegetModuleReloaded = true;
        location.reload();
      }
    }, 1500);
  });
  // Also reload after AJAX save if the request is a post/page/module save (not just modules)
  $(document).ajaxSuccess(function(event, xhr, settings) {
    // Accept any AJAX save for post, page, or module
    if (settings && settings.data && /action=save_module_meta|action=editpost|action=save-post|action=save_page_modules/i.test(settings.data)) {
      if (xhr && xhr.responseText && /updated|success|saved/i.test(xhr.responseText)) {
        if (!window._stegetModuleReloaded) {
          window._stegetModuleReloaded = true;
          location.reload();
        }
      }
    }
  });

  // --- Defensive logging for AJAX POSTs (icon preview, repeaters) ---
  // Patch $.post to log and validate data
  var originalPost = $.post;
  $.post = function(url, data, success, dataType) {
    // Defensive: Always ensure 'action' is present
    if (typeof data === 'object' && data && !data.action) {
      console.warn('[steget-admin] AJAX POST missing action:', data, url);
      // Optionally, add a default action or abort
    }
    // Log all AJAX POSTs for debugging
    if (window.console && window.console.log) {
      window.console.log('[steget-admin] $.post', url, data);
    }
    return originalPost.apply(this, arguments);
  };

  // --- Hide "No buttons added yet" message when not relevant ---
  // Hide the message if there are any .sharing-network-item present
  function stegetHideNoButtonsMsgIfSharingPresent() {
    if ($('.sharing-network-item').length > 0) {
      $('.no-buttons-message').hide();
    }
  }
  // Call on page load and after adding/removing sharing networks
  stegetHideNoButtonsMsgIfSharingPresent();
  $(document).on('click', '.add-sharing-network, .steget-remove-sharing-network', stegetHideNoButtonsMsgIfSharingPresent);
  // Also call after AJAX reload (in case fields are dynamically refreshed)
  $(document).ajaxSuccess(function() { setTimeout(stegetHideNoButtonsMsgIfSharingPresent, 200); });

  // --- WordPress Media Uploader for all .select-media and .steget-media-field inputs ---
  function stegetUnifiedMediaButtonHandler() {
    $(document).on('click', '.select-media', function(e) {
      e.preventDefault();
      var button = $(this);
      // Try to find the closest input[type=text] with .steget-media-field class
      var input = button.siblings('input.steget-media-field');
      if (!input.length) {
        // fallback: find input in parent if not a sibling
        input = button.closest('.tab-item, .testimonial-item, .media-field').find('input.steget-media-field').first();
      }
      var custom_uploader = wp.media({
        title: (typeof stegetAdminI18n !== 'undefined' && stegetAdminI18n.select_image) ? stegetAdminI18n.select_image : 'Select Image',
        button: {
          text: (typeof stegetAdminI18n !== 'undefined' && stegetAdminI18n.use_image) ? stegetAdminI18n.use_image : 'Use this image'
        },
        multiple: false
      });
      custom_uploader.on('select', function() {
        var attachment = custom_uploader.state().get('selection').first().toJSON();
        input.val(attachment.url);
      });
      custom_uploader.open();
    });
  }

  // Call the unified handler once on page load
  stegetUnifiedMediaButtonHandler();

  // Image upload for all .steget-media-field instances
  $(document).on('click', '.steget-upload-image', function(e) {
    e.preventDefault();
    var button = $(this);
    var field = button.closest('.steget-media-field');
    var imageInput = field.find('.steget-media-input');
    var imagePreview = field.find('.steget-image-preview');
    var removeButton = field.find('.steget-remove-image');

    // Use WordPress media uploader
    var custom_uploader = wp.media({
      title: (typeof stegetAdminI18n !== 'undefined' && stegetAdminI18n.select_image) ? stegetAdminI18n.select_image : 'Select Image',
      button: {
        text: (typeof stegetAdminI18n !== 'undefined' && stegetAdminI18n.use_image) ? stegetAdminI18n.use_image : 'Use this image'
      },
      multiple: false
    }).on('select', function() {
      var attachment = custom_uploader.state().get('selection').first().toJSON();
      imageInput.val(attachment.url);
      imagePreview.html('<img src="' + attachment.url + '" style="max-width:100%;height:auto;" />');
      removeButton.show();
    }).open();
  });

  // Remove image
  $(document).on('click', '.steget-remove-image', function() {
    var field = $(this).closest('.steget-media-field');
    field.find('.steget-media-input').val('');
    field.find('.steget-image-preview').empty();
    $(this).hide();
  });

  // --- Sharing module dynamic repeaters and icon preview ---
  // Add new sharing network
  $(document).on('click', '.add-sharing-network', function() {
    var iconTemplate = $('#sharing-icon-select-template').html();
    var template = `
      <div class="sharing-network-item">
        <p>
          <label><strong>Network:</strong></label><br>
          <input type="text" name="sharing_network_name[]" value="" class="widefat">
        </p>
        <p>
          <label><strong>URL:</strong></label><br>
          <input type="text" name="sharing_network_url[]" value="" class="widefat">
        </p>
        <p>
          <label><strong>Icon:</strong></label><br>
          ${iconTemplate}
          <span class="sharing-network-icon-preview"></span>
        </p>
        <button type="button" class="button is-destructive steget-remove-sharing-network">Remove</button>
        <hr>
      </div>
    `;
    $('#sharing_networks_container').append(template);
  });
  // Remove sharing network
  $(document).on('click', '.steget-remove-sharing-network', function() {
    $(this).closest('.sharing-network-item').remove();
  });
  // Icon preview AJAX
  $(document).on('change', '.sharing-network-icon-select', function() {
    var selected = $(this).val();
    var $preview = $(this).siblings('.sharing-network-icon-preview');
    $preview.empty();
    $.post(ajaxurl, {
      action: 'get_sharing_icon_svg',
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
  // On page load, set previews
  $('.sharing-network-icon-select').each(function() {
    var selected = $(this).val();
    var $preview = $(this).siblings('.sharing-network-icon-preview');
    $.post(ajaxurl, {
      action: 'get_sharing_icon_svg',
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

  // --- Selling Points module dynamic repeaters and icon preview ---
  // Add new selling point
  $(document).on('click', '.add-selling-point', function() {
    var count = $('.selling-point-item').length + 1;
    var iconTemplate = $('#selling-point-icon-select-template').html();
    var template = `
      <div class="selling-point-item">
        <h4>Selling Point #${count}</h4>
        <p>
          <label><strong>Title:</strong></label><br>
          <input type="text" name="selling_point_title[]" value="" class="widefat">
        </p>
        <p>
          <label><strong>Description:</strong></label><br>
          <textarea name="selling_point_description[]" rows="3" class="widefat"></textarea>
        </p>
        <p>
          <label><strong>Icon:</strong></label><br>
          ${iconTemplate}
          <span class="selling-point-icon-preview"></span>
        </p>
        <p>
          <label><strong>Color:</strong></label><br>
          <input type="text" name="selling_point_color[]" value="" class="widefat selling-point-color-picker">
        </p>
        <button type="button" class="button steget-remove-selling-point">Remove</button>
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
  // Icon preview AJAX
  $(document).on('change', '.selling-point-icon-select', function() {
    var selected = $(this).val();
    var $preview = $(this).siblings('.selling-point-icon-preview');
    $preview.empty();
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
  
  // --- Logging: Investigate post-publish toast and DOM state ---
  function logModuleAdminState(context) {
    console.log('[Steget Module Debug][' + context + ']');
    console.log('Current template:', $('#module_template').val());
    console.log('template-fields present:', $('.template-fields').length);
    console.log('sharing_fields present:', $('#sharing_fields').length);
    console.log('sharing_networks_container:', $('#sharing_networks_container').length, $('#sharing_networks_container').html());
    console.log('sharing-network-item count:', $('.sharing-network-item').length);
    console.log('Toast (notices):', $('.notice, .updated, .is-success, .notice-success, #message.updated').length);
  }
  // Log on page load
  logModuleAdminState('page load');
  // Log when notices (toasts) appear (e.g. after Publish)
  var adminNoticeObserver = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      $(mutation.addedNodes).each(function() {
        if ($(this).is('.notice, .updated, .is-success, .notice-success, #message.updated')) {
          logModuleAdminState('notice appeared');
        }
      });
    });
  });
  adminNoticeObserver.observe(document.body, { childList: true, subtree: true });

  // --- Robust: Ensure template fields show after save/reload (wait for DOM) ---
  function stegetToggleTemplateFields() {
    var template = $('#module_template').val();
    $('.template-fields').show();
    if (template) {
      $('#' + template + '_fields').show();
    }
  }

  function stegetWaitAndToggleTemplateFields(attempts) {
    if ($('#module_template').length && $('.template-fields').length) {
      stegetToggleTemplateFields();
    } else if (attempts > 0) {
      setTimeout(function() { stegetWaitAndToggleTemplateFields(attempts - 1); }, 100);
    }
  }

  if ($('#module_template').length) {
    $('#module_template').on('change', function() {
      var $select = $(this);
      var template = $select.val();
      var postId = $('#post_ID').val();
      if (!template || !postId) return;
      // Save template via AJAX
      $.post(ajaxurl, {
        action: 'steget_save_module_template',
        post_id: postId,
        template: template,
        _wpnonce: $('#_wpnonce').val() // Use the default WP nonce field
      }, function(response) {
        // After save, reload to show fields
        location.reload();
      });
    });
    stegetWaitAndToggleTemplateFields(30); // Try for up to 3 seconds
  }

  // --- Fix for Sharing module fields visibility ---
  function stegetShowAllSharingFields() {
    var $sharingFields = $('#sharing_fields');
    if ($sharingFields.length) {
      $sharingFields.find('p, #sharing_networks_container, .add-sharing-network, #sharing-icon-select-template').show();
      $sharingFields.find('.sharing-network-item').show();
    }
  }
  stegetShowAllSharingFields();
  $(document).ajaxSuccess(function() { setTimeout(stegetShowAllSharingFields, 200); });

  // --- Fix: Always reload after module save in module view ---
  // This ensures the module view updates after save (not just on AJAX success or notices)
  if ($('body.post-type-module').length && $('#post').length) {
    $('#post').on('submit', function(e) {
      console.log('[Steget Module Debug][form submit] Module form is being submitted.');
      console.log('[Steget Module Debug][form submit] Current template:', $('#module_template').val());
      var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          $(mutation.addedNodes).each(function() {
            if ($(this).is('.notice, .updated, .is-success, .notice-success, #message.updated')) {
              if (!$(this).data('handled')) {
                $(this).data('handled', true);
                if (!window._stegetModuleReloaded) {
                  window._stegetModuleReloaded = true;
                  location.reload();
                }
              }
            }
          });
        });
      });
      observer.observe(document.body, { childList: true, subtree: true });
      setTimeout(function() {
        if (!window._stegetModuleReloaded) {
          window._stegetModuleReloaded = true;
          location.reload();
        }
      }, 1500);
    });
  }
});
