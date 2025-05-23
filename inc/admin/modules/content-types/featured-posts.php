<?php
/**
 * Featured Posts module template fields
 * 
 * @package Steget
 */

/**
 * Render featured posts template fields
 */
function render_featured_posts_template_fields($post) {
    $settings = json_decode(get_post_meta($post->ID, 'module_featured_posts_settings', true), true) ?: [
        'categories' => [],
        'post_count' => 6,
        'display_style' => 'grid',
        'show_date' => true,
        'show_excerpt' => true,
        'show_author' => false,
        'layout_style' => 'traditional'
    ];

    // Get all categories
    $categories = get_categories([
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
    ?>
<div id="featured-posts_fields" class="template-fields">
    <div class="category-selection-panel">
        <p>
            <label><strong><?php _e('Select Categories', 'steget'); ?>:</strong></label>
        </p>
        <div class="category-checklist">
            <?php foreach ($categories as $category) : ?>
            <label class="category-checklist-label">
                <input type="checkbox" name="featured_posts_categories[]"
                    value="<?php echo esc_attr($category->term_id); ?>"
                    <?php echo in_array($category->term_id, $settings['categories']) ? 'checked' : ''; ?>>
                <?php echo esc_html($category->name); ?>
                <span class="post-count">(<?php echo $category->count; ?> posts)</span>
            </label>
            <?php endforeach; ?>
        </div>
        <p class="description">
            <?php _e('Select categories to include in this module. Leave all unchecked to include posts from all categories.', 'steget'); ?>
        </p>
    </div>

    <p>
        <label
            for="featured_posts_count"><strong><?php _e('Number of Posts to Display', 'steget'); ?>:</strong></label><br>
        <select name="featured_posts_count" id="featured_posts_count" class="widefat">
            <?php for ($i = 1; $i <= 20; $i++) : ?>
            <option value="<?php echo $i; ?>" <?php selected($settings['post_count'], $i); ?>>
                <?php echo $i; ?>
            </option>
            <?php endfor; ?>
        </select>
    </p>

    <p>
        <label for="featured_posts_display"><strong><?php _e('Display Style', 'steget'); ?>:</strong></label><br>
        <select name="featured_posts_display" id="featured_posts_display" class="widefat">
            <option value="grid" <?php selected($settings['display_style'], 'grid'); ?>><?php _e('Grid', 'steget'); ?>
            </option>
            <option value="list" <?php selected($settings['display_style'], 'list'); ?>><?php _e('List', 'steget'); ?>
            </option>
            <option value="carousel" <?php selected($settings['display_style'], 'carousel'); ?>>
                <?php _e('Carousel', 'steget'); ?></option>
        </select>
    </p>

    <p>
        <label for="featured_posts_layout_style"><strong><?php _e('Layout Style', 'steget'); ?>:</strong></label><br>
        <select name="featured_posts_layout_style" id="featured_posts_layout_style" class="widefat">
            <option value="traditional" <?php selected($settings['layout_style'] ?? 'traditional', 'traditional'); ?>><?php _e('Traditional Grid', 'steget'); ?>
            </option>
            <option value="magazine" <?php selected($settings['layout_style'] ?? 'traditional', 'magazine'); ?>><?php _e('Magazine Layout', 'steget'); ?>
            </option>
        </select>
        <p class="description">
            <?php _e('Traditional Grid: All cards have equal size. Magazine Layout: First card takes 2/3 width with two smaller cards stacked in the remaining space.', 'steget'); ?>
        </p>
    </p>

    <div class="display-options-panel">
        <h4 class="display-options-title"><?php _e('Display Options', 'steget'); ?></h4>
        <label class="display-options-label">
            <input type="checkbox" name="featured_posts_show_date" <?php checked($settings['show_date'], true); ?>>
            <?php _e('Show Date', 'steget'); ?>
        </label>
        <label class="display-options-label">
            <input type="checkbox" name="featured_posts_show_excerpt"
                <?php checked($settings['show_excerpt'], true); ?>>
            <?php _e('Show Excerpt', 'steget'); ?>
        </label>
        <label class="display-options-label">
            <input type="checkbox" name="featured_posts_show_author" <?php checked($settings['show_author'], true); ?>>
            <?php _e('Show Author', 'steget'); ?>
        </label>
    </div>
</div>
<?php
}
