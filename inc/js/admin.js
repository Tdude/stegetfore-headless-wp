/**
 * Admin JavaScript for the Theme Options page
 */
jQuery(document).ready(function ($) {
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
});
