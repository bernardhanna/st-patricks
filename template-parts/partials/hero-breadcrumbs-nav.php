<?php

$breadcrumb_items = is_array($args['items'] ?? null) ? $args['items'] : [];
$current_label = trim((string) ($args['current_label'] ?? ''));
$background_color = trim((string) ($args['background_color'] ?? '#F1F8F9'));

if ($breadcrumb_items === [] && $current_label === '') {
    return;
}

if ($background_color === '') {
    $background_color = '#F1F8F9';
}
?>
<div class="flex w-full items-center px-4 py-3 lg:h-[42px] lg:px-0 lg:py-0" style="background-color: <?php echo esc_attr($background_color); ?>;">
    <nav
        class="mx-auto w-full max-w-[1203px] lg:px-5"
        aria-label="Breadcrumb"
    >
        <ol class="flex flex-wrap items-center gap-3" role="list">
            <?php foreach ($breadcrumb_items as $breadcrumb_item) { ?>
                <?php
                if (! is_array($breadcrumb_item)) {
                    continue;
                }

                $title = trim((string) ($breadcrumb_item['title'] ?? ''));
                $url = trim((string) ($breadcrumb_item['url'] ?? ''));

                if ($title === '' || $url === '') {
                    continue;
                }
                ?>
                <li class="flex items-center gap-3">
                    <a
                        href="<?php echo esc_url($url); ?>"
                        target="<?php echo esc_attr(($breadcrumb_item['target'] ?? '') !== '' ? $breadcrumb_item['target'] : '_self'); ?>"
                        class="inline-flex w-fit whitespace-nowrap font-primary text-[14px] not-italic font-semibold leading-[20px] text-[#08284B] transition-colors duration-200 hover:text-[#024B79] focus-visible:text-[#024B79]"
                        aria-label="<?php echo esc_attr($title); ?>"
                    >
                        <?php echo esc_html($title); ?>
                    </a>
                    <svg class="shrink-0" width="10" height="12" viewBox="0 0 10 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 1L8 6L4 11" stroke="#08284B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </li>
            <?php } ?>

            <?php if ($current_label !== '') { ?>
                <li class="font-primary text-[14px] not-italic font-normal leading-[20px] text-[#08284B]" aria-current="page">
                    <?php echo esc_html($current_label); ?>
                </li>
            <?php } ?>
        </ol>
    </nav>
</div>
