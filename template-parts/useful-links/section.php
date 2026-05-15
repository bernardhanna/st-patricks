<?php

$section = is_array($args['useful_links'] ?? null) ? $args['useful_links'] : [];
$section_id = trim((string) ($section['section_id'] ?? ''));
$data_block = trim((string) ($section['data_block'] ?? 'useful-links'));
$heading = trim((string) ($section['heading'] ?? ''));
$heading_tag = (string) ($section['heading_tag'] ?? 'h2');
$background_color = (string) ($section['background_color'] ?? '#E9E2F7');
$heading_color = (string) ($section['heading_color'] ?? '#1E244B');
$link_color = (string) ($section['link_color'] ?? '#1E244B');
$variant = (string) ($section['variant'] ?? 'flexi');
$wrapper_classes = trim((string) ($section['wrapper_classes'] ?? ''));
$links = matrix_normalize_useful_links($section['links'] ?? []);

if ($heading === '') {
    $heading = 'Useful links';
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($section_id === '') {
    $section_id = 'useful-links-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
}

if ($links === []) {
    return;
}

if ($wrapper_classes === '') {
    if ($variant === 'search') {
        $wrapper_classes = 'mx-auto flex w-full max-w-[1018px] flex-col px-5 py-12 xl:px-0 xl:py-[100px]';
    } else {
        $wrapper_classes = 'mx-auto flex w-full max-w-[1018px] flex-col max-xl:px-5 pt-5 pb-5';
    }
}

$link_classes = $variant === 'search'
    ? 'group inline-flex min-h-[32px] items-center text-[20px] font-semibold leading-[32px] tracking-[-0.12px] transition-colors hover:text-[#024B79] focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-[#024B79]'
    : 'group inline-flex items-center justify-between gap-4 border-b border-[rgba(30,36,75,0.15)] pb-4 text-[22px] font-semibold leading-[30px] tracking-[-0.14px] transition-colors hover:text-[#024B79] focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-[#024B79] lg:text-[20px] lg:leading-[28px]';

$grid_classes = $variant === 'search'
    ? 'mt-8 grid grid-cols-1 gap-y-4 lg:mt-8 lg:grid-cols-3 lg:gap-x-10 lg:gap-y-4'
    : 'mt-8 grid grid-cols-1 gap-x-10 gap-y-6 lg:mt-12 lg:grid-cols-3 lg:gap-y-8';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    <?php if ($data_block !== '') { ?>
        data-matrix-block="<?php echo esc_attr($data_block); ?>"
    <?php } ?>
    class="relative flex overflow-hidden"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <<?php echo esc_attr($heading_tag); ?>
            class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
            style="color: <?php echo esc_attr($heading_color); ?>;"
        >
            <?php echo esc_html($heading); ?>
        </<?php echo esc_attr($heading_tag); ?>>

        <div class="mt-4 h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>

        <div class="<?php echo esc_attr($grid_classes); ?>">
            <?php foreach ($links as $link) { ?>
                <a
                    href="<?php echo esc_url($link['url']); ?>"
                    target="<?php echo esc_attr($link['target']); ?>"
                    class="<?php echo esc_attr($link_classes); ?>"
                    style="color: <?php echo esc_attr($link_color); ?>;"
                    <?php if ($link['target'] === '_blank') { ?>
                        rel="noopener noreferrer"
                    <?php } ?>
                >
                    <span><?php echo esc_html($link['title']); ?></span>
                    <?php if ($variant === 'search') { ?>
                        <span class="ml-1" aria-hidden="true">&rarr;</span>
                    <?php } else { ?>
                        <span class="shrink-0" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M6 3L12 9L6 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    <?php } ?>
                </a>
            <?php } ?>
        </div>
    </div>
</section>

