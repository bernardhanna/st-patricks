<?php

$item = $args['item'] ?? null;
$index = (int) ($args['index'] ?? 0);

if (! $item) {
    return;
}

$item_children = ! empty($item->children) && is_iterable($item->children)
    ? array_values($item->children)
    : [];

$nav_settings = get_field('navigation_settings_start', 'option') ?: [];
$portal_cta = $nav_settings['dropdown_cta_button'] ?? null;

$default_panel_index = 0;

foreach ($item_children as $child_index => $child) {
    $child_children = ! empty($child->children) && is_iterable($child->children)
        ? array_values($child->children)
        : [];

    if ($child_children === []) {
        continue;
    }

    if (stripos((string) $child->label, 'Careers') !== false) {
        $default_panel_index = $child_index;
        break;
    }

    $default_panel_index = $child_index;
}

$application_form_url = home_url('/application-form/');
$section_id = 'about-us-mega-menu-' . $index;
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
            x-data="{ activePanelIndex: <?php echo (int) $default_panel_index; ?> }"
            @mouseleave="activePanelIndex = <?php echo (int) $default_panel_index; ?>"
        >
            <?php matrix_render_nav_mega_menu_decorative_symbol(); ?>

            <div class="relative mx-auto flex min-h-[553px] w-full max-w-container items-stretch gap-16 px-6 py-16 max-xl:px-10 max-lg:px-8">
                <?php
                get_template_part('template-parts/header/navbar/mega-menu-header', null, [
                    'title' => $item->label,
                    'show_portal_cta' => true,
                    'portal_cta' => $portal_cta,
                ]);
                ?>

                <ul class="relative z-[1] flex w-[307px] shrink-0 flex-col gap-4" role="list">
                    <?php foreach ($item_children as $child_index => $child) : ?>
                        <?php
                        $child_children = ! empty($child->children) && is_iterable($child->children)
                            ? array_values($child->children)
                            : [];
                        $has_panel = $child_children !== [];
                        ?>
                        <li>
                            <?php if ($has_panel) : ?>
                                <a
                                    href="<?php echo esc_url($child->url); ?>"
                                    class="flex gap-2.5 items-center w-full text-left font-primary text-base font-medium leading-6 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                                    :class="activePanelIndex === <?php echo $child_index; ?> ? 'text-[#024B79] underline decoration-1 underline-offset-[7px]' : 'text-[#001F33] hover:text-[#024B79] hover:underline hover:decoration-1 hover:underline-offset-[7px]'"
                                    aria-controls="<?php echo esc_attr($section_id); ?>-panel-<?php echo $child_index; ?>"
                                    :aria-expanded="activePanelIndex === <?php echo $child_index; ?> ? 'true' : 'false'"
                                    @mouseenter="activePanelIndex = <?php echo $child_index; ?>"
                                    @focus="activePanelIndex = <?php echo $child_index; ?>"
                                >
                                    <span><?php echo esc_html($child->label); ?></span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0">
                                        <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            <?php else : ?>
                                <a
                                    href="<?php echo esc_url($child->url); ?>"
                                    class="<?php echo esc_attr(matrix_get_nav_mega_menu_link_class_names(! empty($child->active))); ?>"
                                    <?php if (! empty($child->target)) : ?>target="<?php echo esc_attr($child->target); ?>"<?php endif; ?>
                                    @mouseenter="activePanelIndex = -1"
                                >
                                    <?php echo esc_html($child->label); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="flex-1 min-h-full pointer-events-auto" aria-hidden="true"></div>

                <div class="absolute right-0 top-0 z-[1] h-full w-[461px] max-w-[40%] border-l border-[#E2E8F0] bg-white/50 max-xl:w-[400px]">
                    <div class="relative w-full h-full">
                    <?php foreach ($item_children as $child_index => $child) : ?>
                        <?php
                        $child_children = ! empty($child->children) && is_iterable($child->children)
                            ? array_values($child->children)
                            : [];

                        if ($child_children === []) {
                            continue;
                        }

                        $show_application_form = stripos((string) $child->label, 'Careers') !== false;
                        ?>
                        <aside
                            id="<?php echo esc_attr($section_id); ?>-panel-<?php echo $child_index; ?>"
                            x-show="activePanelIndex === <?php echo $child_index; ?>"
                            x-transition.opacity.duration.150ms
                            class="flex absolute inset-0 flex-col gap-4 px-16 py-16 max-xl:px-10"
                            aria-label="<?php echo esc_attr($child->label); ?> submenu"
                        >
                            <div class="flex gap-2.5 items-end pb-8">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0 rotate-90 text-[#001F33]">
                                    <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <h3 class="font-primary text-[20px] font-semibold leading-7 tracking-[-0.1px] text-[#1E244B]">
                                    <a
                                        href="<?php echo esc_url($child->url); ?>"
                                        class="inline-flex text-[#1E244B] transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                                        <?php if (! empty($child->target)) : ?>target="<?php echo esc_attr($child->target); ?>"<?php endif; ?>
                                    >
                                        <?php echo esc_html($child->label); ?>
                                    </a>
                                </h3>
                            </div>

                            <ul class="flex flex-col gap-4" role="list">
                                <?php foreach ($child_children as $grandchild) : ?>
                                    <li>
                                        <a
                                            href="<?php echo esc_url($grandchild->url); ?>"
                                            class="<?php echo esc_attr(matrix_get_nav_mega_menu_link_class_names(false)); ?>"
                                            <?php if (! empty($grandchild->target)) : ?>target="<?php echo esc_attr($grandchild->target); ?>"<?php endif; ?>
                                        >
                                            <?php echo esc_html($grandchild->label); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if ($show_application_form) : ?>
                                <div class="pt-8">
                                    <a
                                        href="<?php echo esc_url($application_form_url); ?>"
                                        class="<?php echo esc_attr(matrix_get_nav_mega_menu_cta_class_names()); ?>"
                                    >
                                        Application form
                                    </a>
                                </div>
                            <?php endif; ?>
                        </aside>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
