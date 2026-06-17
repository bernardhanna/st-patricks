<?php

$item = is_array($args['item'] ?? null) ? $args['item'] : [];
$panel_class_names = is_string($args['panel_class_names'] ?? null) ? $args['panel_class_names'] : matrix_get_key_contact_info_panel_class_names();
$contact_row_class_names = matrix_get_key_contact_info_contact_row_class_names();
$contact_text_class_names = matrix_get_key_contact_info_contact_text_class_names();
$opening_hours = is_array($item['opening_hours'] ?? null) ? $item['opening_hours'] : [];
$location_url = trim((string) ($item['location_url'] ?? ''));

$has_bullets = ($item['bullet_items'] ?? []) !== [];
$wrapper_classes = trim($panel_class_names . ($has_bullets ? '' : ' pt-0'));
?>

<div class="<?php echo esc_attr($wrapper_classes); ?>">
    <div class="flex flex-col gap-2">
        <?php foreach ($item['bullet_items'] as $bullet_label) { ?>
            <div class="<?php echo esc_attr($contact_row_class_names); ?>">
                <span class="flex size-6 shrink-0 items-center justify-center font-primary text-[16px] font-medium leading-[28px] text-[#08284B]" aria-hidden="true">-</span>
                <span class="font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                    <?php echo esc_html($bullet_label); ?>
                </span>
            </div>
        <?php } ?>

        <?php if (($item['phone'] ?? '') !== '') { ?>
            <div class="<?php echo esc_attr($contact_row_class_names); ?>">
                <span class="flex size-6 shrink-0 items-center justify-center text-[#08284B]">
                    <?php echo matrix_get_key_contact_info_phone_icon_svg(); ?>
                </span>
                <a
                    href="<?php echo esc_url('tel:' . matrix_format_locations_map_phone_link((string) $item['phone'])); ?>"
                    class="<?php echo esc_attr($contact_text_class_names); ?>"
                >
                    <?php echo esc_html((string) $item['phone']); ?>
                </a>
            </div>
        <?php } ?>

        <?php if (($item['email'] ?? '') !== '') { ?>
            <div class="<?php echo esc_attr($contact_row_class_names); ?>">
                <span class="flex size-6 shrink-0 items-center justify-center text-[#08284B]">
                    <?php echo matrix_get_key_contact_info_email_icon_svg(); ?>
                </span>
                <a
                    href="<?php echo esc_url('mailto:' . sanitize_email((string) $item['email'])); ?>"
                    class="<?php echo esc_attr($contact_text_class_names); ?>"
                >
                    <?php echo esc_html((string) $item['email']); ?>
                </a>
            </div>
        <?php } ?>

        <?php if ($opening_hours !== []) { ?>
            <div>
                <p class="<?php echo esc_attr(matrix_get_contact_directory_opening_hours_heading_class_names()); ?>">
                    <?php echo esc_html__('Opening hours', 'matrix-starter'); ?>
                </p>
                <dl class="<?php echo esc_attr(matrix_get_contact_directory_opening_hours_grid_class_names()); ?>">
                    <?php foreach ($opening_hours as $hours_row) { ?>
                        <dt class="<?php echo esc_attr(matrix_get_contact_directory_opening_hours_label_class_names()); ?>">
                            <?php echo esc_html((string) ($hours_row['day_label'] ?? '')); ?>
                        </dt>
                        <dd class="<?php echo esc_attr(matrix_get_contact_directory_opening_hours_label_class_names()); ?>">
                            <?php echo esc_html((string) ($hours_row['hours'] ?? '')); ?>
                        </dd>
                    <?php } ?>
                </dl>
            </div>
        <?php } ?>

        <?php if ($location_url !== '') { ?>
            <div class="pt-1">
                <a
                    href="<?php echo esc_url($location_url); ?>"
                    class="font-primary text-[16px] font-medium leading-[28px] text-[#024B79] underline underline-offset-2 transition-colors hover:text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                >
                    <?php echo esc_html__('View location details', 'matrix-starter'); ?>
                </a>
            </div>
        <?php } ?>
    </div>
</div>
