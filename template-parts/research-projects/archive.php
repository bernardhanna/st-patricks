<?php

$prepare_args = is_array($args['prepare_args'] ?? null) ? $args['prepare_args'] : [];
$hero_overrides = is_array($args['hero_overrides'] ?? null) ? $args['hero_overrides'] : [];
$breadcrumb_items = is_array($args['breadcrumb_items'] ?? null) ? $args['breadcrumb_items'] : null;
$current_breadcrumb_label = trim((string) ($args['current_breadcrumb_label'] ?? ''));
$show_filter_heading = array_key_exists('show_filter_heading', $args)
    ? (bool) $args['show_filter_heading']
    : false;

$hero_settings = matrix_get_research_project_archive_hero_settings($hero_overrides);
$hero_tag = (string) ($hero_settings['hero_heading_tag'] ?? 'h1');
$hero_text = (string) ($hero_settings['hero_heading_text'] ?? 'Research Projects');
$sub_text = (string) ($hero_settings['hero_subheading_text'] ?? '');
$filter_title = (string) ($hero_settings['filter_section_title'] ?? 'Filter by:');
$hero_image_id = (int) ($hero_settings['hero_image_id'] ?? 0);
$section_background_color = '#C6ECF4';
$breadcrumb_background_color = '#F1F8F9';
$heading_color = '#08284B';
$text_color = '#08284B';

$research_project_archive = matrix_prepare_research_project_archive(array_merge($prepare_args, [
    'filter_label' => $filter_title,
    'show_heading' => $show_filter_heading,
    'wrapper_classes' => 'flex w-full max-w-[1018px] flex-col items-center mx-auto py-12 lg:py-[100px] max-xl:px-5',
]));

if ($breadcrumb_items === null) {
    $archive_base_url = matrix_resolve_research_project_archive_base_url($prepare_args['base_url'] ?? '');
    $breadcrumb_items = [
        [
            'title' => 'Home',
            'url' => home_url('/'),
            'target' => '',
        ],
    ];

    if ($current_breadcrumb_label !== '' && $current_breadcrumb_label !== $hero_text) {
        $breadcrumb_items[] = [
            'title' => $hero_text,
            'url' => $archive_base_url,
            'target' => '',
        ];
    }
}

if ($current_breadcrumb_label === '') {
    $current_breadcrumb_label = $hero_text;
}

$breadcrumb_data = function_exists('matrix_resolve_hero_breadcrumbs')
    ? matrix_resolve_hero_breadcrumbs('manual', $breadcrumb_items, $current_breadcrumb_label, [])
    : [
        'items' => $breadcrumb_items,
        'current_label' => $current_breadcrumb_label,
    ];
$breadcrumb_items = is_array($breadcrumb_data['items'] ?? null) ? $breadcrumb_data['items'] : [];
$current_breadcrumb_label = (string) ($breadcrumb_data['current_label'] ?? '');

$hero_image_alt = (string) ($hero_settings['hero_image_alt'] ?? '');
$hero_image_title = (string) ($hero_settings['hero_image_title'] ?? '');

if ($hero_image_id > 0) {
    $hero_image_alt = (string) get_post_meta($hero_image_id, '_wp_attachment_image_alt', true) ?: $hero_image_alt;
    $hero_image_title = (string) get_the_title($hero_image_id) ?: $hero_image_title;
}

if ($hero_image_alt === '') {
    $hero_image_alt = $hero_image_title !== '' ? $hero_image_title : $hero_text;
}

$gradient_vars = matrix_get_hero_with_breadcrumbs_gradient_vars($section_background_color);
$gradient_solid = $gradient_vars['gradient_solid'];
$gradient_soft = $gradient_vars['gradient_soft'];
$gradient_clear = $gradient_vars['gradient_clear'];
?>
<div class="mt-[0rem] w-full">
    <section
        class="relative flex flex-col overflow-hidden"
        style="background-color: <?php echo esc_attr($section_background_color); ?>;"
    >
        <?php if (! empty($breadcrumb_items) || $current_breadcrumb_label !== '') { ?>
            <?php
            get_template_part('template-parts/partials/hero-breadcrumbs-nav', null, [
                'items' => $breadcrumb_items,
                'current_label' => $current_breadcrumb_label,
                'background_color' => $breadcrumb_background_color,
            ]);
            ?>
        <?php } ?>

        <div class="mx-auto flex w-full max-w-[1280px] flex-col items-center">
            <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_grid_class_names()); ?>">
                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names()); ?>" style="border-color: <?php echo esc_attr($section_background_color); ?>;">
                    <?php if ($hero_image_id > 0) { ?>
                        <?php
                        echo wp_get_attachment_image($hero_image_id, 'full', false, [
                            'alt' => esc_attr($hero_image_alt),
                            'title' => esc_attr($hero_image_title),
                            'class' => 'absolute inset-0 h-full w-full object-cover',
                            'loading' => 'eager',
                        ]);
                        ?>
                    <?php } ?>

                    <?php
                    get_template_part('template-parts/partials/hero-image-split-image-gradients', null, [
                        'gradient_solid' => $gradient_solid,
                        'gradient_soft' => $gradient_soft,
                        'gradient_clear' => $gradient_clear,
                        'background_color' => $section_background_color,
                    ]);
                    ?>
                </div>

                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_column_class_names()); ?>">
                    <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_text_group_class_names()); ?>">
                        <<?php echo esc_attr($hero_tag); ?>
                            class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_heading_class_names()); ?>"
                            style="color: <?php echo esc_attr($heading_color); ?>;"
                        >
                            <?php echo esc_html($hero_text); ?>
                        </<?php echo esc_attr($hero_tag); ?>>

                        <?php if ($sub_text !== '') { ?>
                            <p
                                class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_content_class_names()); ?>"
                                style="color: <?php echo esc_attr($text_color); ?>;"
                            >
                                <?php echo esc_html($sub_text); ?>
                            </p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    get_template_part('template-parts/research-projects/filter_archive', null, [
        'research_project_archive' => $research_project_archive,
    ]);
    ?>
</div>
