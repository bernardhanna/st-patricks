<?php

if (! function_exists('matrix_get_nav_mega_menu_shell_classes')) {
    function matrix_get_nav_mega_menu_shell_classes(): string
    {
        return 'pointer-events-none fixed left-0 top-[var(--site-header-height,120px)] z-[55] flex w-screen flex-col bg-transparent';
    }
}

if (! function_exists('matrix_get_nav_mega_menu_key')) {
    function matrix_get_nav_mega_menu_key(int $index): string
    {
        return 'nav-mega-' . $index;
    }
}

if (! function_exists('matrix_render_nav_mega_menu_trigger_attrs')) {
    function matrix_render_nav_mega_menu_trigger_attrs(int $index): void
    {
        $key = matrix_get_nav_mega_menu_key($index);

        printf(
            'data-nav-mega-trigger="%1$s" @mouseenter="$store.navMega.open(\'%1$s\')" @focusin="$store.navMega.open(\'%1$s\')" @focusout="$store.navMega.scheduleCloseFrom($event, \'%1$s\')"',
            esc_attr($key)
        );
    }
}

if (! function_exists('matrix_render_nav_mega_menu_shell_attrs')) {
    function matrix_render_nav_mega_menu_shell_attrs(int $index): void
    {
        $key = matrix_get_nav_mega_menu_key($index);

        printf(
            'data-nav-mega-menu x-show="$store.navMega.activeKey === \'%s\'" x-cloak @mouseenter="$store.navMega.open(\'%s\')"',
            esc_attr($key),
            esc_attr($key)
        );
    }
}

if (! function_exists('matrix_get_nav_mega_menu_cta_class_names')) {
    function matrix_get_nav_mega_menu_cta_class_names(): string
    {
        return implode(' ', [
            'btn',
            'inline-flex',
            'h-11',
            'w-fit',
            'items-center',
            'justify-center',
            'rounded-[6px]',
            'bg-[#7ED0E0]',
            'px-8',
            'font-primary',
            'text-sm',
            'font-medium',
            'leading-6',
            'text-[#1E244B]',
            'transition-colors',
            'hover:bg-[#66c4d8]',
            'focus-visible:outline-none',
            'focus-visible:ring-2',
            'focus-visible:ring-offset-2',
            'focus-visible:ring-[#7ED0E0]',
        ]);
    }
}

if (! function_exists('matrix_get_nav_mega_menu_link_class_names')) {
    function matrix_get_nav_mega_menu_link_class_names(bool $is_active = false): string
    {
        $classes = [
            'inline-flex',
            'whitespace-nowrap',
            'font-primary',
            'text-base',
            'font-medium',
            'leading-6',
            'transition-colors',
            'focus-visible:outline-none',
            'focus-visible:ring-2',
            'focus-visible:ring-offset-2',
            'focus-visible:ring-[#024B79]',
        ];

        if ($is_active) {
            $classes[] = 'text-[#024B79]';
            $classes[] = 'underline';
            $classes[] = 'decoration-1';
            $classes[] = 'underline-offset-[7px]';
        } else {
            $classes[] = 'text-[#001F33]';
            $classes[] = 'hover:text-[#024B79]';
            $classes[] = 'hover:underline';
            $classes[] = 'hover:decoration-1';
            $classes[] = 'hover:underline-offset-[7px]';
        }

        return implode(' ', $classes);
    }
}

if (! function_exists('matrix_render_nav_mega_menu_heading_underline')) {
    function matrix_render_nav_mega_menu_heading_underline(): void
    {
        ?>
        <div class="mt-8 h-px w-10 bg-[#5F604B]" aria-hidden="true"></div>
        <?php
    }
}

if (! function_exists('matrix_render_nav_mega_menu_decorative_symbol')) {
    function matrix_render_nav_mega_menu_decorative_symbol(): void
    {
        $theme_dir = function_exists('get_template_directory')
            ? get_template_directory()
            : dirname(__DIR__);
        $svg_path = $theme_dir . '/assets/svg/mega-menu-watermark.svg';

        if (! is_readable($svg_path)) {
            return;
        }

        $svg = file_get_contents($svg_path);

        if ($svg === false || $svg === '') {
            return;
        }

        $svg = str_replace('<svg ', '<svg class="w-full h-full" ', $svg);

        ?>
        <div
            class="absolute bottom-0 left-0 z-0 pointer-events-none"
            aria-hidden="true"
        >
            <?php echo $svg; ?>
        </div>
        <?php
    }
}

if (! function_exists('matrix_render_nav_mega_menu_pointer_graphic')) {
    function matrix_render_nav_mega_menu_pointer_graphic(): void
    {
        $filter_id = 'mega-menu-pointer-' . wp_unique_id();

        ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="76" height="48" viewBox="0 0 76 48" fill="none" class="block h-[25px] w-[76px] -translate-x-1/2" aria-hidden="true">
            <g filter="url(#<?php echo esc_attr($filter_id); ?>)">
                <path d="M12 5.74743e-07L36.5858 24.5858C37.3668 25.3668 38.6332 25.3668 39.4142 24.5858L64 0" fill="white"/>
            </g>
            <defs>
                <filter id="<?php echo esc_attr($filter_id); ?>" x="0" y="-2" width="76" height="49.1716" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feMorphology radius="4" operator="erode" in="SourceAlpha" result="effect1_dropShadow_489_1535"/>
                    <feOffset dy="4"/>
                    <feGaussianBlur stdDeviation="3"/>
                    <feComposite in2="hardAlpha" operator="out"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0"/>
                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_489_1535"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feMorphology radius="3" operator="erode" in="SourceAlpha" result="effect2_dropShadow_489_1535"/>
                    <feOffset dy="10"/>
                    <feGaussianBlur stdDeviation="7.5"/>
                    <feComposite in2="hardAlpha" operator="out"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0"/>
                    <feBlend mode="normal" in2="effect1_dropShadow_489_1535" result="effect2_dropShadow_489_1535"/>
                    <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_489_1535" result="shape"/>
                </filter>
            </defs>
        </svg>
        <?php
    }
}

/**
 * @deprecated Use the shared navbar pointer aligned to the active nav trigger.
 */
if (! function_exists('matrix_render_nav_mega_menu_pointer')) {
    function matrix_render_nav_mega_menu_pointer(string $pointer_left = ''): void
    {
        matrix_render_nav_mega_menu_pointer_graphic();
    }
}

if (! function_exists('matrix_render_nav_mega_menu')) {
    /**
     * @param array{
     *   layout: string,
     *   column_split?: int,
     *   show_portal_cta?: bool,
     *   min_height?: string,
     *   show_right_overlay?: bool,
     *   default_panel_label?: string,
     *   panel_cta_label?: string,
     *   panel_cta_url?: string,
     * } $config
     */
    function matrix_render_nav_mega_menu($item, int $index, array $config): void
    {
        if (! $item) {
            return;
        }

        $layout = (string) ($config['layout'] ?? 'simple');

        if ($layout === 'about_us_flyout') {
            get_template_part('template-parts/header/navbar/dropdown-about-us', null, [
                'item' => $item,
                'index' => $index,
            ]);

            return;
        }

        $item_children = ! empty($item->children) && is_iterable($item->children)
            ? array_values($item->children)
            : [];

        $nav_settings = get_field('navigation_settings_start', 'option') ?: [];
        $portal_cta = $nav_settings['dropdown_cta_button'] ?? null;
        $section_id = sanitize_title((string) $item->label) . '-mega-menu-' . $index;
        $min_height = (string) ($config['min_height'] ?? 'min-h-[360px]');
        $show_portal_cta = (bool) ($config['show_portal_cta'] ?? false);
        $column_split = (int) ($config['column_split'] ?? 0);
        ?>
        <div
            class="<?php echo esc_attr(matrix_get_nav_mega_menu_shell_classes()); ?>"
            <?php matrix_render_nav_mega_menu_shell_attrs($index); ?>
            role="region"
            aria-label="<?php echo esc_attr($item->label); ?> submenu"
        >
            <div class="relative w-full pointer-events-auto">
                <div
                    id="<?php echo esc_attr($section_id); ?>"
                    class="relative w-full overflow-visible bg-[#F1F8F9] shadow-lg"
                    role="navigation"
                    aria-label="<?php echo esc_attr($item->label); ?> menu"
                >
                    <?php matrix_render_nav_mega_menu_decorative_symbol(); ?>

                    <div class="relative mx-auto flex w-full max-w-container items-start justify-start gap-16 px-6 py-16 max-xl:px-10 max-lg:px-8 <?php echo esc_attr($min_height); ?>">
                        <?php
                        get_template_part('template-parts/header/navbar/mega-menu-header', null, [
                            'title' => $item->label,
                            'show_portal_cta' => $show_portal_cta,
                            'portal_cta' => $portal_cta,
                        ]);
                        ?>

                        <?php if ($layout === 'two_column' && $column_split > 0) : ?>
                            <?php
                            $column_one = array_slice($item_children, 0, $column_split);
                            $column_two = array_slice($item_children, $column_split);
                            ?>
                            <div class="relative z-[1] flex flex-1 items-start gap-16">
                                <ul class="flex flex-col gap-4" role="list">
                                    <?php foreach ($column_one as $child) : ?>
                                        <li>
                                            <a
                                                href="<?php echo esc_url($child->url); ?>"
                                                class="<?php echo esc_attr(matrix_get_nav_mega_menu_link_class_names(! empty($child->active))); ?>"
                                                <?php if (! empty($child->target)) : ?>target="<?php echo esc_attr($child->target); ?>"<?php endif; ?>
                                            >
                                                <?php echo esc_html($child->label); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <div class="w-px shrink-0 self-stretch bg-[#E2E8F0]" aria-hidden="true"></div>

                                <ul class="flex flex-col gap-4" role="list">
                                    <?php foreach ($column_two as $child) : ?>
                                        <li>
                                            <a
                                                href="<?php echo esc_url($child->url); ?>"
                                                class="<?php echo esc_attr(matrix_get_nav_mega_menu_link_class_names(! empty($child->active))); ?>"
                                                <?php if (! empty($child->target)) : ?>target="<?php echo esc_attr($child->target); ?>"<?php endif; ?>
                                            >
                                                <?php echo esc_html($child->label); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else : ?>
                            <ul class="relative z-[1] flex flex-col gap-4" role="list">
                                <?php foreach ($item_children as $child) : ?>
                                    <li>
                                        <a
                                            href="<?php echo esc_url($child->url); ?>"
                                            class="<?php echo esc_attr(matrix_get_nav_mega_menu_link_class_names(! empty($child->active))); ?>"
                                            <?php if (! empty($child->target)) : ?>target="<?php echo esc_attr($child->target); ?>"<?php endif; ?>
                                        >
                                            <?php echo esc_html($child->label); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

if (! function_exists('matrix_get_nav_mega_menu_config')) {
    function matrix_get_nav_mega_menu_config(string $label): ?array
    {
        $label_key = strtolower(trim($label));

        $configs = [
            'about us' => [
                'layout' => 'about_us_flyout',
            ],
            'what we offer' => [
                'layout' => 'simple',
                'min_height' => 'min-h-[280px]',
            ],
            'healthcare professionals' => [
                'layout' => 'two_column',
                'column_split' => 6,
                'min_height' => 'min-h-[360px]',
            ],
            'service users and visitors' => [
                'layout' => 'two_column',
                'column_split' => 8,
                'show_portal_cta' => true,
                'min_height' => 'min-h-[476px]',
            ],
        ];

        return $configs[$label_key] ?? null;
    }
}
