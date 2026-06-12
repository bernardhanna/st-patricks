<?php

$section = is_array($args['section'] ?? null) ? $args['section'] : [];

$section_id = trim((string) ($section['section_id'] ?? ''));
$data_block = trim((string) ($section['data_block'] ?? 'related-cards'));
$heading = trim((string) ($section['heading'] ?? 'Related'));
$heading_tag = (string) ($section['heading_tag'] ?? 'h2');
$intro_text = (string) ($section['intro_text'] ?? '');
$background_color = (string) ($section['background_color'] ?? '#FFFFFF');
$columns = (string) ($section['columns'] ?? '3');
$wrapper_classes = trim((string) ($section['wrapper_classes'] ?? ''));
$cards = is_array($section['cards'] ?? null) ? $section['cards'] : [];

if ($section_id === '') {
    $section_id = 'related-cards-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
}

if ($cards === []) {
    return;
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
    $heading_tag = 'h2';
}

$grid_columns = $columns === '2' ? 'lg:grid-cols-2' : 'lg:grid-cols-3';

if ($wrapper_classes === '') {
    $wrapper_classes = 'mx-auto flex w-full max-w-[1018px] flex-col gap-8 px-4 py-12 lg:gap-8 lg:py-[100px] xl:px-0';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    <?php if ($data_block !== '') { ?>
        data-matrix-block="<?php echo esc_attr($data_block); ?>"
    <?php } ?>
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <div class="flex w-full flex-col gap-8">
            <?php if ($heading !== '') { ?>
                <<?php echo tag_escape($heading_tag); ?>
                    class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo tag_escape($heading_tag); ?>>
                <div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>
            <?php } ?>

            <?php if ($intro_text !== '') { ?>
                <div class="max-w-2xl font-primary text-base font-medium leading-7 text-[#08284B] wp_editor">
                    <?php echo matrix_kses_rich_text($intro_text); ?>
                </div>
            <?php } ?>
        </div>

        <div class="grid grid-cols-1 gap-4 <?php echo esc_attr($grid_columns); ?> lg:gap-x-8 lg:gap-y-4">
            <?php foreach ($cards as $card) { ?>
                <?php
                $title = (string) ($card['title'] ?? '');
                $description = (string) ($card['description'] ?? '');
                $image_id = (int) ($card['image_id'] ?? 0);
                $link = is_array($card['link'] ?? null) ? $card['link'] : null;

                if ($title === '' || $link === null || ($link['url'] ?? '') === '') {
                    continue;
                }

                $link_target = ($link['target'] ?? '_self') === '_blank' ? '_blank' : '_self';
                $link_url = function_exists('matrix_normalize_asset_url')
                    ? matrix_normalize_asset_url((string) $link['url'])
                    : (string) $link['url'];
                ?>
                <article class="h-full overflow-hidden rounded-lg bg-white shadow-[0_1px_1px_rgba(0,0,0,0.05)]">
                    <a
                        href="<?php echo esc_url($link_url); ?>"
                        target="<?php echo esc_attr($link_target); ?>"
                        class="group flex h-full flex-col focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                        aria-label="<?php echo esc_attr($title); ?>"
                        <?php if ($link_target === '_blank') { ?>
                            rel="noopener noreferrer"
                        <?php } ?>
                    >
                        <?php if ($image_id > 0) { ?>
                            <div class="relative h-[220px] w-full overflow-hidden rounded-t-lg lg:h-[273px]">
                                <?php
                                echo wp_get_attachment_image($image_id, 'large', false, [
                                    'class' => 'absolute inset-0 h-full w-full object-cover',
                                    'alt' => $title,
                                    'loading' => 'lazy',
                                    'decoding' => 'async',
                                ]);
                                ?>
                            </div>
                        <?php } else { ?>
                            <div class="flex h-[220px] w-full items-center justify-center rounded-t-lg bg-[#F1F8F9] lg:h-[273px]">
                                <span class="font-primary text-[48px] font-bold leading-none text-[#6FC9C0]" aria-hidden="true">&rarr;</span>
                            </div>
                        <?php } ?>

                        <div class="flex flex-1 items-center bg-[#F1F8F9] p-6">
                            <div class="flex min-w-0 flex-col gap-2">
                                <p class="font-primary text-[20px] font-semibold leading-6 tracking-[-0.12px] text-[#1E244B] transition-colors group-hover:text-[#024B79]">
                                    <span><?php echo esc_html($title); ?></span>
                                    <span aria-hidden="true"> &rarr;</span>
                                </p>

                                <?php if ($description !== '') { ?>
                                    <p class="font-primary text-base font-medium leading-7 text-[#4A4B37]">
                                        <?php echo esc_html($description); ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </div>
                    </a>
                </article>
            <?php } ?>
        </div>
    </div>
</section>
