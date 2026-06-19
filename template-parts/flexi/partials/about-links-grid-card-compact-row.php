<?php

$card = is_array($args['card'] ?? null) ? $args['card'] : [];

if ($card === []) {
    return;
}

$title = trim((string) ($card['title'] ?? ''));
$description = trim((string) ($card['description'] ?? ''));
$image_url = trim((string) ($card['image_url'] ?? ''));
$icon_url = trim((string) ($card['icon_url'] ?? ''));
$icon_alt = trim((string) ($card['icon_alt'] ?? $title));
$has_link = ! empty($card['has_link']);
$link_url = (string) ($card['link_url'] ?? '');
$link_target = (string) ($card['link_target'] ?? '_self');
$link_title = (string) ($card['link_title'] ?? $title);
$footer_background = (string) ($card['footer_background'] ?? '#F1F8F9');
$card_title_color = (string) ($card['card_title_color'] ?? '#1E244B');
$card_desc_color = (string) ($card['card_desc_color'] ?? '#08284B');
$allow_title_wrap = ! empty($card['allow_title_wrap']);

$title_classes = 'font-primary text-[18px] font-semibold leading-7 tracking-[-0.12px] text-[#1E244B] transition-colors group-hover:text-[#024B79] lg:text-[20px] lg:leading-8';

if (! $allow_title_wrap) {
    $title_classes .= ' lg:whitespace-nowrap';
}

$card_tag = $has_link ? 'a' : 'div';
$card_attrs = $has_link
    ? sprintf(
        ' href="%s" target="%s"%s',
        esc_url($link_url),
        esc_attr($link_target),
        $link_target === '_blank' ? ' rel="noopener noreferrer"' : ''
    )
    : '';
?>

<article class="h-full">
    <<?php echo $card_tag; ?>
        <?php echo $card_attrs; ?>
        class="group flex h-full min-h-[88px] items-center gap-4 rounded-lg p-4 shadow-[0_1px_1px_rgba(0,0,0,0.05)] transition-[filter] duration-200 hover:brightness-[0.98] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]<?php echo $has_link ? '' : ' pointer-events-none'; ?>"
        style="background-color: <?php echo esc_attr($footer_background); ?>;"
        <?php if ($has_link) { ?>
            aria-label="<?php echo esc_attr($link_title); ?>"
        <?php } ?>
    >
        <?php if ($image_url !== '' || $icon_url !== '') { ?>
            <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-[6px] bg-white/50 lg:h-20 lg:w-20">
                <?php if ($image_url !== '') { ?>
                    <img
                        src="<?php echo esc_url($image_url); ?>"
                        alt="<?php echo esc_attr($title); ?>"
                        class="h-full w-full object-cover"
                        loading="lazy"
                        decoding="async"
                    >
                <?php } else { ?>
                    <div class="flex h-full w-full items-center justify-center bg-[#F1F8F9]">
                        <img
                            src="<?php echo esc_url($icon_url); ?>"
                            alt="<?php echo esc_attr($icon_alt); ?>"
                            class="h-10 w-10 object-contain"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="flex min-w-0 flex-1 items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <?php if ($title !== '') { ?>
                    <h3
                        class="<?php echo esc_attr($title_classes); ?>"
                        style="color: <?php echo esc_attr($card_title_color); ?>;"
                    >
                        <?php echo esc_html($title); ?>
                    </h3>
                <?php } ?>

                <?php if ($description !== '') { ?>
                    <p
                        class="mt-1 font-primary text-[14px] font-medium leading-6 lg:hidden"
                        style="color: <?php echo esc_attr($card_desc_color); ?>;"
                    >
                        <?php echo esc_html($description); ?>
                    </p>
                <?php } ?>
            </div>

            <?php if ($has_link) { ?>
                <span class="shrink-0 text-[#1E244B] transition-colors group-hover:text-[#024B79]" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M6 3L12 9L6 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            <?php } ?>
        </div>
    </<?php echo $card_tag; ?>>
</article>
