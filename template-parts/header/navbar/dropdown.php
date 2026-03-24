<?php
$item      = $args['item']   ?? null;
$index     = $args['index']  ?? 0;
$imagesMap = $args['images'] ?? [];

// ------------- guard -------------
if (!$item) {
    return;
}

// Ensure children are 0-based arrays so third-tier index matches (Navi may key by id)
$item_children = !empty($item->children) && is_iterable($item->children) ? array_values($item->children) : [];

$nav_settings = get_field('navigation_settings_start', 'option') ?: [];
$dropdown_cta = $nav_settings['dropdown_cta_button'] ?? null;

/**
 * Get the WP menu-item ID that was used in the repeater.
 * Navi exposes both ->id and ->ID depending on version,
 * so ask for whichever exists first.
 */
$item_id = $item->ID ?? $item->id ?? null;

/** @var array|null $img  Full image array or null */
$img = $imagesMap[$item_id] ?? null;

/** Use safe helpers so null never throws notices */
$img_url   = is_array($img) && !empty($img['url'])   ? $img['url']   : '';
$img_alt   = is_array($img) && !empty($img['alt'])   ? $img['alt']   : ($item->label . ' image');
$img_title = is_array($img) && !empty($img['title']) ? $img['title'] : $img_alt;
?>

<div
    class="hidden fixed left-0 z-50 w-screen bg-transparent group-hover:flex group-focus-within:flex"
    role="region"
    aria-label="<?php echo esc_attr($item->label); ?> submenu"
>
    <div class="w-full pt-2">
        <div class="mx-auto w-full max-w-[1440px] px-6">
            <div
                class="relative overflow-hidden w-full border border-slate-200 bg-slate-100 rounded-lg shadow-lg"
                role="navigation"
                aria-label="<?php echo esc_attr($item->label); ?> menu"
                x-data="{ activeTier3Index: <?php echo !empty($item_children) ? '0' : 'null'; ?> }"
                @mouseleave="activeTier3Index = <?php echo !empty($item_children) ? '0' : 'null'; ?>"
            >
                <div class="flex min-h-[420px]">
                    <div class="flex flex-col justify-between px-8 py-10 w-[260px] border-r border-slate-200 bg-slate-100">
                        <div>
                            <h2 class="text-[40px] font-semibold leading-[44px] tracking-[-0.6px] text-[#1E244B]">
                                <?php echo esc_html($item->label); ?>
                            </h2>
                            <div class="mt-8 w-10 h-px bg-[#FF9E66]" aria-hidden="true"></div>
                        </div>

                        <?php if (is_array($dropdown_cta) && !empty($dropdown_cta['url']) && !empty($dropdown_cta['title'])) : ?>
                            <a
                                href="<?php echo esc_url($dropdown_cta['url']); ?>"
                                target="<?php echo esc_attr($dropdown_cta['target'] ?? '_self'); ?>"
                                class="inline-flex justify-center items-center px-6 mt-8 h-11 text-sm font-medium leading-6 text-[#1E244B] bg-[#7ED0E0] rounded-[6px] transition-colors hover:bg-[#66c4d8] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] w-fit"
                            >
                                <?php echo esc_html($dropdown_cta['title']); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-1">
                        <section class="px-8 py-10 w-[360px] bg-white border-r border-slate-200" aria-label="Secondary navigation">
                            <?php if ($item_children) : ?>
                                <ul class="space-y-3" role="list">
                                    <?php foreach ($item_children as $child_index => $child) : ?>
                                        <?php
                                        $child_children = !empty($child->children) && is_iterable($child->children) ? array_values($child->children) : [];
                                        $is_active_child = !empty($child->active);
                                        ?>
                                        <li>
                                            <?php if ($child_children) : ?>
                                                <button
                                                    type="button"
                                                    class="flex justify-between items-center w-full text-left text-[16px] font-medium leading-6 transition-colors"
                                                    :class="activeTier3Index === <?php echo $child_index; ?> ? 'text-[#024B79] underline underline-offset-2' : 'text-[#0F2419] hover:text-[#024B79]'"
                                                    aria-label="<?php echo esc_attr($child->label); ?>"
                                                    :aria-expanded="activeTier3Index === <?php echo $child_index; ?>"
                                                    aria-controls="tier3-<?php echo $index; ?>-<?php echo $child_index; ?>"
                                                    @mouseenter="activeTier3Index = <?php echo $child_index; ?>"
                                                    @focus="activeTier3Index = <?php echo $child_index; ?>"
                                                >
                                                    <span><?php echo esc_html($child->label); ?></span>
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            <?php else : ?>
                                                <a
                                                    href="<?php echo esc_url($child->url); ?>"
                                                    class="inline-flex items-center text-[16px] font-medium leading-6 transition-colors <?php echo $is_active_child ? 'text-[#024B79] underline underline-offset-2' : 'text-[#0F2419] hover:text-[#024B79]'; ?>"
                                                    <?php if (!empty($child->target)) : ?>target="<?php echo esc_attr($child->target); ?>"<?php endif; ?>
                                                    @mouseenter="activeTier3Index = null"
                                                >
                                                    <?php echo esc_html($child->label); ?>
                                                </a>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </section>

                        <div class="flex-1 bg-slate-50 min-h-[420px]">
                            <?php if ($item_children) : ?>
                                <?php foreach ($item_children as $child_index => $child) : ?>
                                    <?php
                                    $child_children_tier3 = !empty($child->children) && is_iterable($child->children) ? array_values($child->children) : [];
                                    if (!$child_children_tier3) { continue; }
                                    ?>
                                    <aside
                                        id="tier3-<?php echo $index; ?>-<?php echo $child_index; ?>"
                                        x-show="activeTier3Index === <?php echo $child_index; ?>"
                                        x-transition.opacity.duration.150ms
                                        class="px-8 py-10"
                                        aria-label="<?php echo esc_attr($child->label); ?> submenu"
                                    >
                                        <h3 class="pb-6 text-[20px] font-semibold leading-[28px] tracking-[-0.1px] text-[#1E244B]">
                                            <?php echo esc_html($child->label); ?>
                                        </h3>
                                        <ul class="space-y-3" role="list">
                                            <?php foreach ($child_children_tier3 as $grandchild) : ?>
                                                <li>
                                                    <a
                                                        href="<?php echo esc_url($grandchild->url); ?>"
                                                        class="inline-flex items-center text-[16px] font-medium leading-6 text-[#0F2419] transition-colors hover:text-[#024B79]"
                                                        <?php if (!empty($grandchild->target)) : ?>target="<?php echo esc_attr($grandchild->target); ?>"<?php endif; ?>
                                                    >
                                                        <?php echo esc_html($grandchild->label); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </aside>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($img_url) : ?>
                                <div
                                    x-show="activeTier3Index === null"
                                    x-transition.opacity.duration.150ms
                                    class="p-8 h-full"
                                >
                                    <img
                                        src="<?php echo esc_url($img_url); ?>"
                                        alt="<?php echo esc_attr($img_alt); ?>"
                                        title="<?php echo esc_attr($img_title); ?>"
                                        class="object-cover w-full h-full rounded-lg min-h-[356px]"
                                        loading="lazy"
                                    />
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
