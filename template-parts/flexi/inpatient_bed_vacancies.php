<?php

$defaults = matrix_get_inpatient_bed_vacancies_defaults();
$section_id = 'inpatient-bed-vacancies-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$updated_text = matrix_resolve_inpatient_bed_vacancies_updated_label(get_sub_field('updated_text'));
$vacancy_items = matrix_normalize_inpatient_bed_vacancy_items(
    get_sub_field('vacancy_items'),
    [
        'status_background_color' => (string) $defaults['status_background_color'],
    ]
);
$section_background_color = (string) get_sub_field('section_background_color');
$card_background_color = (string) get_sub_field('card_background_color');
$heading_color = (string) get_sub_field('heading_color');
$updated_color = (string) get_sub_field('updated_color');
$location_color = (string) get_sub_field('location_color');
$disclaimer_color = (string) get_sub_field('disclaimer_color');
$count_color = (string) get_sub_field('count_color');
$beds_label_color = (string) get_sub_field('beds_label_color');
$underline_color = (string) get_sub_field('underline_color');

if ($heading === '') {
    $heading = (string) $defaults['heading'];
}

if ($section_background_color === '') {
    $section_background_color = (string) $defaults['section_background_color'];
}

if ($card_background_color === '') {
    $card_background_color = (string) $defaults['card_background_color'];
}

if ($heading_color === '') {
    $heading_color = (string) $defaults['heading_color'];
}

if ($updated_color === '') {
    $updated_color = (string) $defaults['updated_color'];
}

if ($location_color === '') {
    $location_color = (string) $defaults['location_color'];
}

if ($disclaimer_color === '') {
    $disclaimer_color = (string) $defaults['disclaimer_color'];
}

if ($count_color === '') {
    $count_color = (string) $defaults['count_color'];
}

if ($beds_label_color === '') {
    $beds_label_color = (string) $defaults['beds_label_color'];
}

if ($underline_color === '') {
    $underline_color = (string) $defaults['underline_color'];
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($vacancy_items === []) {
    return;
}

$heading_id = $section_id . '-heading';

$wrapper_classes = ['mx-auto', 'flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'gap-8', 'pt-5', 'pb-5', 'max-xl:px-5', 'lg:gap-8', 'lg:py-[100px]'];

?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden"
    style="background-color: <?php echo esc_attr($section_background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($heading_id); ?>"
>
    <div class="<?php echo esc_attr(implode(' ', array_unique($wrapper_classes))); ?>">
        <header class="flex w-full flex-col gap-4">
            <div class="flex flex-wrap items-baseline gap-x-3 gap-y-2">
                <<?php echo esc_attr($heading_tag); ?>
                    id="<?php echo esc_attr($heading_id); ?>"
                    class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                    style="color: <?php echo esc_attr($heading_color); ?>;"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>

                <?php if ($updated_text !== '') { ?>
                    <p
                        class="font-primary text-[16px] font-normal leading-[24px] lg:text-[18px] lg:leading-[28px]"
                        style="color: <?php echo esc_attr($updated_color); ?>;"
                    >
                        <?php echo esc_html($updated_text); ?>
                    </p>
                <?php } ?>
            </div>

            <div class="h-[4px] w-10" style="background-color: <?php echo esc_attr($underline_color); ?>;" aria-hidden="true"></div>
        </header>

        <div
            class="flex w-full flex-col overflow-hidden rounded-[8px]"
            style="background-color: <?php echo esc_attr($card_background_color); ?>;"
        >
            <?php foreach ($vacancy_items as $index => $item) { ?>
                <?php
                $location_label = matrix_format_inpatient_bed_vacancy_location_label($item);
                $is_last_item = $index === count($vacancy_items) - 1;
                ?>

                <article class="flex w-full flex-col gap-6 px-6 py-6 lg:flex-row lg:items-center lg:gap-8 lg:px-8 lg:py-8<?php echo $is_last_item ? '' : ' border-b border-[rgba(30,36,75,0.12)]'; ?>">
                    <div
                        class="flex h-[88px] w-[88px] shrink-0 flex-col items-center justify-center rounded-[4px] px-3 py-3 text-center"
                        style="background-color: <?php echo esc_attr($item['status_background_color']); ?>;"
                        aria-label="<?php echo esc_attr(sprintf('%d beds available', $item['bed_count'])); ?>"
                    >
                        <p
                            class="font-primary text-[36px] font-semibold leading-[40px] tracking-[-0.36px]"
                            style="color: <?php echo esc_attr($count_color); ?>;"
                        >
                            <?php echo esc_html((string) $item['bed_count']); ?>
                        </p>
                        <p
                            class="font-primary text-[14px] font-normal leading-[20px] lowercase"
                            style="color: <?php echo esc_attr($beds_label_color); ?>;"
                        >
                            beds
                        </p>
                    </div>

                    <?php if ($location_label !== '') { ?>
                        <p
                            class="min-w-0 flex-1 font-primary text-[20px] font-semibold leading-[28px] tracking-[-0.12px] lg:text-[24px] lg:leading-[32px] lg:tracking-[-0.144px]"
                            style="color: <?php echo esc_attr($location_color); ?>;"
                        >
                            <?php echo esc_html($location_label); ?>
                        </p>
                    <?php } ?>

                    <?php if ($item['disclaimer'] !== '') { ?>
                        <p
                            class="min-w-0 font-primary text-[14px] font-normal leading-[24px] lg:max-w-[360px] lg:text-right lg:text-[16px] lg:leading-[28px]"
                            style="color: <?php echo esc_attr($disclaimer_color); ?>;"
                        >
                            <?php echo esc_html($item['disclaimer']); ?>
                        </p>
                    <?php } ?>
                </article>
            <?php } ?>
        </div>
    </div>
</section>
