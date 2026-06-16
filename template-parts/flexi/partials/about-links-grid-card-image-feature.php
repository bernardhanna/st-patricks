<?php
/**
 * About Links Grid card — Figma 2780:3457 (desktop) / 2780:6686 (mobile).
 * Padded tone card with inset image, title row, and chevron.
 */

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

$card_tag = $has_link ? 'a' : 'div';
$card_attrs = $has_link
    ? sprintf(
        ' href="%s" target="%s"%s',
        esc_url($link_url),
        esc_attr($link_target),
        $link_target === '_blank' ? ' rel="noopener noreferrer"' : ''
    )
    : '';

$title_classes = 'min-w-0 font-primary text-[20px] font-semibold leading-8 tracking-[-0.12px] transition-colors group-hover:text-[#024B79]';

if ($description === '') {
    $title_classes .= ' lg:whitespace-nowrap';
}
?>

<article class="h-full">
    <<?php echo $card_tag; ?>
        <?php echo $card_attrs; ?>
        class="group flex h-full flex-col rounded-lg p-6 shadow-[0_1px_1px_rgba(0,0,0,0.05)] transition-[filter] duration-200 hover:brightness-[0.98] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]<?php echo $has_link ? '' : ' pointer-events-none'; ?>"
        style="background-color: <?php echo esc_attr($footer_background); ?>;"
        <?php if ($has_link) { ?>
            aria-label="<?php echo esc_attr($link_title); ?>"
        <?php } ?>
    >
        <?php if ($image_url !== '' || $icon_url !== '') { ?>
            <div class="relative h-[180px] w-full shrink-0 overflow-hidden rounded-lg lg:h-[129px]">
                <?php if ($image_url !== '') { ?>
                    <img
                        src="<?php echo esc_url($image_url); ?>"
                        alt="<?php echo esc_attr($title); ?>"
                        class="absolute inset-0 h-full w-full rounded-lg object-cover"
                        loading="lazy"
                        decoding="async"
                    >
                <?php } else { ?>
                    <div class="flex h-full w-full items-center justify-center rounded-lg bg-white/40">
                        <img
                            src="<?php echo esc_url($icon_url); ?>"
                            alt="<?php echo esc_attr($icon_alt); ?>"
                            class="h-12 w-12 object-contain"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="mt-4 flex min-w-0 flex-1 flex-col gap-2">
            <?php if ($title !== '') { ?>
                <div class="flex w-full items-center justify-between gap-4">
                    <h3
                        class="<?php echo esc_attr($title_classes); ?>"
                        style="color: <?php echo esc_attr($card_title_color); ?>;"
                    >
                        <?php echo esc_html($title); ?>
                    </h3>
                    <?php if ($has_link) { ?>
                        <span class="shrink-0 text-[#1E244B] transition-colors group-hover:text-[#024B79]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M6 3L12 9L6 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($description !== '') { ?>
                <p
                    class="font-primary text-base font-medium leading-7"
                    style="color: <?php echo esc_attr($card_desc_color); ?>;"
                >
                    <?php echo esc_html($description); ?>
                </p>
            <?php } ?>
        </div>
    </<?php echo $card_tag; ?>>
</article>
