<?php
/**
 * About Links Grid card — Figma 3279:18939 (careers useful links).
 * Flush full-width image with title-only footer (no image padding).
 */

$card = is_array($args['card'] ?? null) ? $args['card'] : [];

if ($card === []) {
    return;
}

$title = trim((string) ($card['title'] ?? ''));
$image_url = trim((string) ($card['image_url'] ?? ''));
$icon_url = trim((string) ($card['icon_url'] ?? ''));
$icon_alt = trim((string) ($card['icon_alt'] ?? $title));
$has_link = ! empty($card['has_link']);
$link_url = (string) ($card['link_url'] ?? '');
$link_target = (string) ($card['link_target'] ?? '_self');
$link_title = (string) ($card['link_title'] ?? $title);
$footer_background = (string) ($card['footer_background'] ?? '#F1F8F9');
$card_title_color = (string) ($card['card_title_color'] ?? '#1E244B');

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
        class="group flex h-full flex-col overflow-hidden rounded-lg shadow-[0_1px_1px_rgba(0,0,0,0.05)] transition-[filter] duration-200 hover:brightness-[0.98] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]<?php echo $has_link ? '' : ' pointer-events-none'; ?>"
        <?php if ($has_link) { ?>
            aria-label="<?php echo esc_attr($link_title); ?>"
        <?php } ?>
    >
        <?php if ($image_url !== '' || $icon_url !== '') { ?>
            <div class="relative aspect-[318/273] w-full shrink-0 overflow-hidden">
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
                            class="h-12 w-12 object-contain"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($title !== '') { ?>
            <div
                class="flex min-h-[96px] flex-1 items-start px-6 py-6"
                style="background-color: <?php echo esc_attr($footer_background); ?>;"
            >
                <h3
                    class="font-primary text-[20px] font-semibold leading-8 tracking-[-0.12px] transition-colors group-hover:text-[#024B79]"
                    style="color: <?php echo esc_attr($card_title_color); ?>;"
                >
                    <?php echo esc_html($title); ?><?php if ($has_link) { ?><span aria-hidden="true"> →</span><?php } ?>
                </h3>
            </div>
        <?php } ?>
    </<?php echo $card_tag; ?>>
</article>
