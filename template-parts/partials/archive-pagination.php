<?php

$pagination = is_array($args['archive_pagination'] ?? null) ? $args['archive_pagination'] : [];

if ($pagination === []) {
    return;
}

$current_page = max(1, (int) ($pagination['current_page'] ?? 1));
$total_pages = max(1, (int) ($pagination['total_pages'] ?? 1));
$build_page_url = $pagination['build_page_url'] ?? null;
$aria_label = trim((string) ($pagination['aria_label'] ?? 'Archive pagination'));
$variant = (string) ($pagination['variant'] ?? 'chip');
$colors = is_array($pagination['colors'] ?? null) ? $pagination['colors'] : [];
$link_attributes_callback = $pagination['link_attributes_callback'] ?? null;
$show_prev_next = array_key_exists('show_prev_next', $pagination)
    ? (bool) $pagination['show_prev_next']
    : true;

if ($total_pages <= 1 || ! is_callable($build_page_url)) {
    return;
}

$item_sets = matrix_build_filter_archive_pagination_item_sets($current_page, $total_pages);
$viewports = [
    'mobile' => $item_sets['mobile'],
    'desktop' => $item_sets['desktop'],
];

$render_link_attributes = static function (int $page) use ($link_attributes_callback): string {
    if (! is_callable($link_attributes_callback)) {
        return '';
    }

    $attributes = (array) $link_attributes_callback($page);
    $markup = '';

    foreach ($attributes as $attribute => $value) {
        if (! is_string($attribute) || $attribute === '' || $value === null || $value === false) {
            continue;
        }

        $markup .= sprintf(' %s="%s"', esc_attr($attribute), esc_attr((string) $value));
    }

    return $markup;
};

$render_nav_button = static function (string $url, string $label, bool $is_previous, int $page) use ($variant, $colors, $render_link_attributes): void {
    ?>
    <a
        href="<?php echo esc_url($url); ?>"
        class="<?php echo esc_attr($variant === 'pill' ? 'btn inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[#08284B]' : 'btn inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border'); ?>"
        <?php if ($variant !== 'pill') { ?>
            style="border-color: <?php echo esc_attr($colors['chip_border'] ?? '#08284B'); ?>; color: <?php echo esc_attr($colors['chip_text'] ?? '#08284B'); ?>;"
        <?php } ?>
        aria-label="<?php echo esc_attr($label); ?>"
        <?php echo $render_link_attributes($page); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <?php if ($is_previous) { ?>
                <path d="M8.75 3.5L5.25 7L8.75 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <?php } else { ?>
                <path d="M5.25 3.5L8.75 7L5.25 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <?php } ?>
        </svg>
    </a>
    <?php
};

foreach ($viewports as $viewport => $pagination_items) {
    ?>
    <nav class="<?php echo esc_attr(matrix_get_filter_archive_pagination_viewport_nav_class_names($viewport)); ?>" aria-label="<?php echo esc_attr($aria_label); ?>">
        <?php if ($show_prev_next && $current_page > 1) { ?>
            <?php $render_nav_button((string) $build_page_url($current_page - 1), 'Go to previous page', true, $current_page - 1); ?>
        <?php } ?>

        <?php foreach ($pagination_items as $pagination_item) { ?>
            <?php if (($pagination_item['type'] ?? '') === 'ellipsis') { ?>
                <span
                    class="<?php echo esc_attr($variant === 'pill' ? 'inline-flex h-8 w-8 shrink-0 items-center justify-center font-primary text-[14px] font-semibold text-[#08284B]' : 'inline-flex h-11 w-11 shrink-0 items-center justify-center font-primary text-[14px] font-semibold'); ?>"
                    <?php if ($variant !== 'pill') { ?>
                        style="color: <?php echo esc_attr($colors['chip_text'] ?? '#08284B'); ?>;"
                    <?php } ?>
                    aria-hidden="true"
                >
                    …
                </span>
            <?php } elseif (($pagination_item['type'] ?? '') === 'page') { ?>
                <?php
                $page = max(1, (int) ($pagination_item['page'] ?? 1));
                $is_current_page = $page === $current_page;
                ?>
                <?php if ($is_current_page) { ?>
                    <span
                        class="<?php echo esc_attr($variant === 'pill' ? 'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#024B79] text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-white' : 'inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border font-primary text-[14px] font-semibold'); ?>"
                        <?php if ($variant !== 'pill') { ?>
                            style="<?php echo esc_attr(matrix_build_filter_archive_pagination_active_inline_style($colors)); ?>"
                        <?php } ?>
                        aria-current="page"
                    >
                        <?php echo esc_html((string) $page); ?>
                    </span>
                <?php } else { ?>
                    <a
                        href="<?php echo esc_url((string) $build_page_url($page)); ?>"
                        class="<?php echo esc_attr($variant === 'pill' ? 'btn inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-[#08284B]' : 'btn inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border font-primary text-[14px] font-semibold'); ?>"
                        <?php if ($variant !== 'pill') { ?>
                            style="border-color: <?php echo esc_attr($colors['chip_border'] ?? '#08284B'); ?>; color: <?php echo esc_attr($colors['chip_text'] ?? '#08284B'); ?>;"
                        <?php } ?>
                        aria-label="<?php echo esc_attr(sprintf('Go to page %d', $page)); ?>"
                        <?php echo $render_link_attributes($page); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    >
                        <?php echo esc_html((string) $page); ?>
                    </a>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <?php if ($show_prev_next && $current_page < $total_pages) { ?>
            <?php $render_nav_button((string) $build_page_url($current_page + 1), 'Go to next page', false, $current_page + 1); ?>
        <?php } ?>
    </nav>
    <?php
}
