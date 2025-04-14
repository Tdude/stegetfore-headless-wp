<?php
/**
 * Chart module template fields
 * 
 * @package Steget
 */

/**
 * Render charts template fields
 */
function render_charts_template_fields($post) {
    $chart_type = get_post_meta($post->ID, 'module_chart_type', true) ?: 'bar';
    $chart_data = json_decode(get_post_meta($post->ID, 'module_chart_data', true), true) ?: [
        'labels' => ['', ''],
        'datasets' => [
            [
                'label' => '',
                'data' => [0, 0]
            ]
        ]
    ];
    ?>
<div id="charts_fields" class="template-fields">
    <p>
        <label for="chart_type"><strong><?php _e('Chart Type', 'steget'); ?>:</strong></label><br>
        <select name="chart_type" id="chart_type" class="widefat">
            <option value="bar" <?php selected($chart_type, 'bar'); ?>><?php _e('Bar Chart', 'steget'); ?></option>
            <option value="line" <?php selected($chart_type, 'line'); ?>><?php _e('Line Chart', 'steget'); ?></option>
            <option value="pie" <?php selected($chart_type, 'pie'); ?>><?php _e('Pie Chart', 'steget'); ?></option>
            <option value="doughnut" <?php selected($chart_type, 'doughnut'); ?>>
                <?php _e('Doughnut Chart', 'steget'); ?></option>
            <option value="radar" <?php selected($chart_type, 'radar'); ?>><?php _e('Radar Chart', 'steget'); ?>
            </option>
        </select>
    </p>

    <div class="chart-data-container">
        <h4><?php _e('Chart Data', 'steget'); ?></h4>

        <div class="chart-labels">
            <h5><?php _e('Labels', 'steget'); ?></h5>
            <div id="labels_container">
                <?php foreach ($chart_data['labels'] as $index => $label) : ?>
                <div class="label-row">
                    <input type="text" name="chart_label[]" value="<?php echo esc_attr($label); ?>"
                        placeholder="<?php _e('Label', 'steget'); ?>" class="widefat">
                    <button type="button" class="button remove-label"><?php _e('Remove', 'steget'); ?></button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button add-label"><?php _e('Add Label', 'steget'); ?></button>
        </div>

        <div class="chart-datasets">
            <h5><?php _e('Datasets', 'steget'); ?></h5>
            <div id="datasets_container">
                <?php foreach ($chart_data['datasets'] as $datasetIndex => $dataset) : ?>
                <div class="dataset-container">
                    <h6><?php _e('Dataset', 'steget'); ?> #<?php echo $datasetIndex + 1; ?></h6>
                    <p>
                        <label><strong><?php _e('Dataset Label', 'steget'); ?>:</strong></label>
                        <input type="text" name="dataset_label[]" value="<?php echo esc_attr($dataset['label']); ?>"
                            class="widefat">
                    </p>

                    <div class="dataset-values">
                        <h6><?php _e('Values', 'steget'); ?></h6>
                        <div class="values-container" data-dataset="<?php echo $datasetIndex; ?>">
                            <?php foreach ($dataset['data'] as $valueIndex => $value) : ?>
                            <div class="value-row">
                                <input type="number" name="dataset_value[<?php echo $datasetIndex; ?>][]"
                                    value="<?php echo esc_attr($value); ?>" class="widefat">
                                <button type="button"
                                    class="button remove-value"><?php _e('Remove', 'steget'); ?></button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button add-value"
                            data-dataset="<?php echo $datasetIndex; ?>"><?php _e('Add Value', 'steget'); ?></button>
                    </div>

                    <button type="button"
                        class="button remove-dataset"><?php _e('Remove Dataset', 'steget'); ?></button>
                    <hr>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button"
                class="button button-primary add-dataset"><?php _e('Add Dataset', 'steget'); ?></button>
        </div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new label
        $('.add-label').on('click', function() {
            var template = `
                        <div class="label-row">
                            <input type="text" name="chart_label[]" value="" placeholder="<?php _e('Label', 'steget'); ?>" class="widefat">
                            <button type="button" class="button remove-label"><?php _e('Remove', 'steget'); ?></button>
                        </div>
                    `;
            $('#labels_container').append(template);
        });

        // Remove label
        $(document).on('click', '.remove-label', function() {
            $(this).closest('.label-row').remove();
        });

        // Add new dataset
        $('.add-dataset').on('click', function() {
            var datasetCount = $('.dataset-container').length;
            var template = `
                        <div class="dataset-container">
                            <h6><?php _e('Dataset', 'steget'); ?> #${datasetCount + 1}</h6>
                            <p>
                                <label><strong><?php _e('Dataset Label', 'steget'); ?>:</strong></label>
                                <input type="text" name="dataset_label[]" value="" class="widefat">
                            </p>

                            <div class="dataset-values">
                                <h6><?php _e('Values', 'steget'); ?></h6>
                                <div class="values-container" data-dataset="${datasetCount}">
                                    <div class="value-row">
                                        <input type="number" name="dataset_value[${datasetCount}][]" value="0" class="widefat">
                                        <button type="button" class="button remove-value"><?php _e('Remove', 'steget'); ?></button>
                                    </div>
                                </div>
                                <button type="button" class="button add-value" data-dataset="${datasetCount}"><?php _e('Add Value', 'steget'); ?></button>
                            </div>

                            <button type="button" class="button remove-dataset"><?php _e('Remove Dataset', 'steget'); ?></button>
                            <hr>
                        </div>
                    `;
            $('#datasets_container').append(template);
        });

        // Remove dataset
        $(document).on('click', '.remove-dataset', function() {
            $(this).closest('.dataset-container').remove();

            // Renumber the datasets
            $('.dataset-container h6:first-child').each(function(index) {
                $(this).text('<?php _e('Dataset', 'steget'); ?> #' + (index + 1));
            });

            // Update dataset indices
            $('.values-container').each(function(index) {
                $(this).attr('data-dataset', index);
                $(this).find('input[type="number"]').each(function() {
                    $(this).attr('name', 'dataset_value[' + index + '][]');
                });
            });

            $('.add-value').each(function(index) {
                $(this).attr('data-dataset', index);
            });
        });

        // Add new value
        $(document).on('click', '.add-value', function() {
            var dataset = $(this).data('dataset');
            var template = `
                        <div class="value-row">
                            <input type="number" name="dataset_value[${dataset}][]" value="0" class="widefat">
                            <button type="button" class="button remove-value"><?php _e('Remove', 'steget'); ?></button>
                        </div>
                    `;
            $(this).prev('.values-container').append(template);
        });

        // Remove value
        $(document).on('click', '.remove-value', function() {
            $(this).closest('.value-row').remove();
        });
    });
    </script>

    <style type="text/css">
    .label-row,
    .value-row {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .label-row .button,
    .value-row .button {
        margin-left: 10px;
    }

    .chart-labels,
    .chart-datasets {
        margin-bottom: 20px;
    }

    .dataset-container {
        background: #f9f9f9;
        padding: 10px;
        border: 1px solid #e5e5e5;
        margin-bottom: 15px;
    }

    .dataset-values {
        margin-top: 15px;
    }
    </style>
</div>
<?php
}
