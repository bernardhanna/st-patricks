<?php

if (! defined('ABSPATH')) {
    exit;
}

$marker = is_array($args['marker'] ?? null) ? $args['marker'] : [];
$phone = trim((string) ($marker['phone'] ?? ''));
$email = trim((string) ($marker['email'] ?? ''));
$address = trim((string) ($marker['address'] ?? ''));
$opening_hours = is_array($marker['opening_hours'] ?? null) ? $marker['opening_hours'] : [];

$contact_row_class_names = matrix_get_locations_map_panel_contact_row_class_names();
$contact_text_class_names = matrix_get_locations_map_panel_contact_text_class_names();
?>

<div class="<?php echo esc_attr(matrix_get_locations_map_panel_contact_block_class_names()); ?>">
    <?php if ($phone !== '') { ?>
        <div class="<?php echo esc_attr($contact_row_class_names); ?>">
            <span class="<?php echo esc_attr(matrix_get_locations_map_panel_icon_wrapper_class_names()); ?>" aria-hidden="true">
                <?php echo matrix_get_locations_map_phone_icon_svg(); ?>
            </span>
            <a
                href="<?php echo esc_url('tel:' . matrix_format_locations_map_phone_link($phone)); ?>"
                class="<?php echo esc_attr($contact_text_class_names); ?>"
            >
                <?php echo esc_html($phone); ?>
            </a>
        </div>
    <?php } ?>

    <?php if ($email !== '') { ?>
        <div class="<?php echo esc_attr($contact_row_class_names); ?>">
            <span class="<?php echo esc_attr(matrix_get_locations_map_panel_icon_wrapper_class_names()); ?>" aria-hidden="true">
                <?php echo matrix_get_locations_map_email_icon_svg(); ?>
            </span>
            <a
                href="<?php echo esc_url('mailto:' . sanitize_email($email)); ?>"
                class="<?php echo esc_attr($contact_text_class_names); ?> break-all"
            >
                <?php echo esc_html($email); ?>
            </a>
        </div>
    <?php } ?>

    <?php if ($address !== '') { ?>
        <div class="<?php echo esc_attr($contact_row_class_names); ?>">
            <span class="<?php echo esc_attr(matrix_get_locations_map_panel_icon_wrapper_class_names()); ?>" aria-hidden="true">
                <?php echo matrix_get_locations_map_address_icon_svg(); ?>
            </span>
            <p class="<?php echo esc_attr($contact_text_class_names); ?>">
                <?php echo esc_html($address); ?>
            </p>
        </div>
    <?php } ?>

    <?php if ($opening_hours !== []) { ?>
        <div>
            <p class="<?php echo esc_attr(matrix_get_locations_map_panel_opening_hours_heading_class_names()); ?>">
                <?php echo esc_html__('Opening hours', 'matrix-starter'); ?>
            </p>
            <dl class="<?php echo esc_attr(matrix_get_locations_map_panel_opening_hours_grid_class_names()); ?>">
                <?php foreach ($opening_hours as $hours_row) { ?>
                    <dt class="<?php echo esc_attr(matrix_get_locations_map_panel_opening_hours_label_class_names()); ?>">
                        <?php echo esc_html((string) ($hours_row['day_label'] ?? '')); ?>
                    </dt>
                    <dd class="<?php echo esc_attr(matrix_get_locations_map_panel_opening_hours_label_class_names()); ?>">
                        <?php echo esc_html((string) ($hours_row['hours'] ?? '')); ?>
                    </dd>
                <?php } ?>
            </dl>
        </div>
    <?php } ?>
</div>
