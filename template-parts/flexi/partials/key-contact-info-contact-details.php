<?php

$item = is_array($args['item'] ?? null) ? $args['item'] : [];
$panel_class_names = is_string($args['panel_class_names'] ?? null) ? $args['panel_class_names'] : matrix_get_key_contact_info_panel_class_names();
$contact_row_class_names = matrix_get_key_contact_info_contact_row_class_names();
$contact_text_class_names = matrix_get_key_contact_info_contact_text_class_names();

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
                    href="<?php echo esc_url('tel:' . preg_replace('/\s+/', '', (string) $item['phone'])); ?>"
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
    </div>
</div>
