<?php

use Log1x\Navi\Navi;
use Illuminate\Support\Collection;

$primary_navigation = $primary_navigation ?? Navi::make()->build('primary');

$enable_hamburger = get_field('enable_hamburger', 'option');
$hamburger_style  = get_field('hamburger_style', 'option');

$bg_colour = get_field('mobile_menu_background', 'option') ?: '#FFFFFF';

$valid_styles = ['hamburger--spin', 'hamburger--squeeze', 'hamburger--elastic', 'hamburger--collapse', 'hamburger--vortex', 'hamburger--arrow', 'hamburger--emphatic', 'hamburger--slider'];

if (! in_array($hamburger_style, $valid_styles, true)) {
    $hamburger_style = 'hamburger--spin';
}

function matrix_mobile_nav_val($src, array $keys, $default = null)
{
    foreach ($keys as $key) {
        if (is_array($src) && array_key_exists($key, $src)) {
            return $src[$key];
        }

        if (is_object($src) && isset($src->{$key})) {
            return $src->{$key};
        }
    }

    return $default;
}

function matrix_mobile_nav_to_seq_array($maybe): array
{
    if ($maybe instanceof Collection) {
        return $maybe->values()->all();
    }

    if (is_array($maybe)) {
        return array_values($maybe);
    }

    if (is_object($maybe) && $maybe instanceof Traversable) {
        return array_values(iterator_to_array($maybe));
    }

    return [];
}

function matrix_mobile_nav_normalize_items($items): array
{
    $items = matrix_mobile_nav_to_seq_array($items);

    usort($items, static function ($left, $right) {
        $left_order = (int) matrix_mobile_nav_val($left, ['menu_order', 'order', 'position'], 0);
        $right_order = (int) matrix_mobile_nav_val($right, ['menu_order', 'order', 'position'], 0);

        return $left_order <=> $right_order;
    });

    $normalized = [];

    foreach ($items as $item) {
        $label = matrix_mobile_nav_val($item, ['label', 'title', 'name', 'post_title'], '');
        $url = matrix_mobile_nav_val($item, ['url', 'link', 'permalink', 'guid'], '');
        $active = (bool) matrix_mobile_nav_val($item, ['active', 'current', 'is_current'], false);
        $children = matrix_mobile_nav_val($item, ['children', 'items', 'submenu', 'child_items'], []);

        $normalized[] = [
            'label' => (string) $label,
            'url' => (string) $url,
            'active' => $active,
            'children' => matrix_mobile_nav_normalize_items($children),
        ];
    }

    return $normalized;
}

if ($primary_navigation instanceof Collection) {
    $nav_array = $primary_navigation->toArray();
} elseif (method_exists($primary_navigation, 'toArray')) {
    $nav_array = $primary_navigation->toArray();
} else {
    $nav_array = $primary_navigation;
}

$top_items = is_array($nav_array) && array_key_exists('items', $nav_array) ? $nav_array['items'] : $nav_array;
$menu_data = matrix_mobile_nav_normalize_items($top_items);

$nav_settings = get_field('navigation_settings_start', 'option') ?: [];
$help_btn = $nav_settings['looking_help_button'] ?? null;
$referral_btn = $nav_settings['referral_button'] ?? null;
$topbar_phone = get_field('topbar_phone_link', 'option');
$topbar_links = get_field('topbar_links', 'option');
$enable_search = ! empty($nav_settings['enable_search']);

$logo_id = get_theme_mod('custom_logo');
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
$logo_alt = $logo_id ? get_post_meta($logo_id, '_wp_attachment_image_alt', true) : get_bloginfo('name');
?>

<?php if ($enable_hamburger && $menu_data !== []) : ?>
<div
    x-data="navSlide(<?php echo esc_attr(wp_json_encode($menu_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>)"
    x-cloak
    class="flex items-center gap-3 lg:hidden"
    @keydown.escape.window="close()"
>
    <?php get_template_part('template-parts/header/navbar/cart'); ?>

    <button
        type="button"
        class="relative z-[130] hamburger <?php echo esc_attr($hamburger_style); ?>"
        :class="{ 'is-active': open }"
        @click="toggle"
        :aria-expanded="open ? 'true' : 'false'"
        aria-controls="mobile-nav-panel"
        aria-label="Menu"
    >
        <span class="hamburger-box"><span class="hamburger-inner"></span></span>
    </button>

    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[120] bg-[#08284B]/55"
        aria-hidden="true"
        @click="close()"
    ></div>

    <div
        id="mobile-nav-panel"
        x-show="open"
        x-transition:enter="transition transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform ease-in duration-200"
        x-transition:leave-end="translate-x-full"
        @click.outside="close"
        class="fixed inset-y-0 right-0 z-[125] flex w-full max-w-[min(100%,24rem)] flex-col shadow-2xl font-primary"
        style="background-color: <?php echo esc_attr($bg_colour); ?>;"
        role="dialog"
        aria-modal="true"
        aria-label="Mobile navigation"
        x-cloak
    >
        <div class="flex items-center justify-between gap-3 border-b border-[#E2E8F0] bg-[#F1F8F9] px-5 py-4">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="shrink-0" @click="close()">
                <?php if ($logo_url !== '') : ?>
                    <img
                        src="<?php echo esc_url($logo_url); ?>"
                        alt="<?php echo esc_attr($logo_alt); ?>"
                        class="h-9 w-[140px] object-contain"
                    />
                <?php else : ?>
                    <span class="text-base font-semibold text-[#08284B]"><?php echo esc_html(get_bloginfo('name')); ?></span>
                <?php endif; ?>
            </a>

            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-[6px] text-[#08284B] transition-colors hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                @click="close()"
                aria-label="Close menu"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <?php if (is_array($topbar_links) && $topbar_links !== []) : ?>
            <div class="border-b border-[#E2E8F0] bg-[#08284B] px-5 py-3">
                <nav class="flex flex-wrap gap-x-4 gap-y-2" aria-label="Quick links">
                    <?php foreach ($topbar_links as $row) :
                        $link = $row['link'] ?? null;

                        if (! is_array($link) || empty($link['url']) || empty($link['title'])) {
                            continue;
                        }
                        ?>
                        <a
                            href="<?php echo esc_url($link['url']); ?>"
                            target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                            class="text-sm font-medium leading-5 text-white transition-opacity hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-white"
                            @click="close()"
                        >
                            <?php echo esc_html($link['title']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        <?php endif; ?>

        <div
            class="flex items-center gap-2 border-b border-[#E2E8F0] bg-white px-5 py-3"
            x-show="depth > 0"
            x-transition.opacity
            x-cloak
        >
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-[6px] text-[#08284B] hover:bg-[#F1F8F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                @click="back()"
                aria-label="Back to previous menu"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <p class="truncate text-sm font-semibold text-[#1E244B]" x-text="parentTitle"></p>
        </div>

        <div class="relative min-h-0 flex-1 overflow-hidden">
            <template x-for="(level, idx) in stack" :key="idx">
                <ul
                    class="absolute inset-0 m-0 list-none overflow-y-auto px-0 py-2 transition-transform duration-300 ease-out"
                    :style="slideStyle(idx)"
                    role="list"
                >
                    <template x-for="(item, i) in level" :key="i">
                        <li class="border-b border-[#E2E8F0] last:border-b-0">
                            <template x-if="item.children.length">
                                <div class="flex items-center justify-between gap-3 px-5 py-4">
                                    <a
                                        :href="item.url"
                                        class="min-w-0 flex-1 text-base font-medium leading-6 text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                                        :class="item.active ? 'font-semibold text-[#024B79]' : ''"
                                        x-text="item.label"
                                        @click="close()"
                                    ></a>
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[6px] text-[#08284B] hover:bg-[#F1F8F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                                        @click.prevent="forward(item.children, item.label)"
                                        :aria-label="'Open ' + item.label + ' submenu'"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!item.children.length">
                                <a
                                    :href="item.url"
                                    class="flex items-center justify-between gap-3 px-5 py-4 text-base font-medium leading-6 text-[#08284B] transition-colors hover:bg-[#F1F8F9] hover:text-[#024B79] focus-visible:bg-[#F1F8F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#024B79]"
                                    :class="item.active ? 'bg-[#F1F8F9] font-semibold text-[#024B79]' : ''"
                                    @click="close()"
                                >
                                    <span x-text="item.label"></span>
                                </a>
                            </template>
                        </li>
                    </template>
                </ul>
            </template>
        </div>

        <div class="shrink-0 space-y-3 border-t border-[#E2E8F0] bg-white p-5">
            <?php if ($enable_search) : ?>
                <button
                    type="button"
                    class="flex w-full items-center justify-center gap-2 rounded-[6px] border border-[#E2E8F0] bg-[#F1F8F9] px-4 py-3 text-sm font-medium text-[#08284B] transition-colors hover:bg-[#E7EEF0] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                    @click="close(); $dispatch('open-navbar-search')"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20.9999 21L16.6499 16.65" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Search</span>
                </button>
            <?php endif; ?>

            <?php if (is_array($topbar_phone) && ! empty($topbar_phone['url']) && ! empty($topbar_phone['title'])) : ?>
                <a
                    href="<?php echo esc_url($topbar_phone['url']); ?>"
                    class="btn flex w-full items-center justify-center gap-2 rounded-[6px] border border-[#024B79] bg-white px-4 py-3 text-sm font-medium text-[#08284B] transition-colors hover:bg-[#F1F8F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                    @click="close()"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M21.9999 16.92V19.92C22.0011 20.1985 21.944 20.4742 21.8324 20.7294C21.7209 20.9845 21.5572 21.2136 21.352 21.4019C21.1468 21.5901 20.9045 21.7335 20.6407 21.8227C20.3769 21.9119 20.0973 21.9451 19.8199 21.92C16.7428 21.5856 13.7869 20.5342 11.1899 18.85C8.77376 17.3147 6.72527 15.2662 5.18993 12.85C3.49991 10.2412 2.44818 7.271 2.11993 4.18001C2.09494 3.90347 2.12781 3.62477 2.21643 3.36163C2.30506 3.09849 2.4475 2.85669 2.6347 2.65163C2.82189 2.44656 3.04974 2.28271 3.30372 2.17053C3.55771 2.05834 3.83227 2.00027 4.10993 2.00001H7.10993C7.59524 1.99523 8.06572 2.16708 8.43369 2.48354C8.80166 2.79999 9.04201 3.23945 9.10993 3.72001C9.23656 4.68007 9.47138 5.62273 9.80993 6.53001C9.94448 6.88793 9.9736 7.27692 9.89384 7.65089C9.81408 8.02485 9.6288 8.36812 9.35993 8.64001L8.08993 9.91001C9.51349 12.4136 11.5864 14.4865 14.0899 15.91L15.3599 14.64C15.6318 14.3711 15.9751 14.1859 16.3491 14.1061C16.723 14.0263 17.112 14.0555 17.4699 14.19C18.3772 14.5286 19.3199 14.7634 20.2799 14.89C20.7657 14.9585 21.2093 15.2032 21.5265 15.5775C21.8436 15.9518 22.0121 16.4296 21.9999 16.92Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><?php echo esc_html($topbar_phone['title']); ?></span>
                </a>
            <?php endif; ?>

            <?php if (is_array($help_btn) && ! empty($help_btn['url']) && ! empty($help_btn['title'])) : ?>
                <a
                    href="<?php echo esc_url($help_btn['url']); ?>"
                    target="<?php echo esc_attr($help_btn['target'] ?: '_self'); ?>"
                    class="btn flex w-full items-center justify-center rounded-[6px] bg-[#024B79] px-4 py-3 text-sm font-medium text-white transition-colors hover:bg-[#013a5c] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                    @click="close()"
                >
                    <?php echo esc_html($help_btn['title']); ?>
                </a>
            <?php endif; ?>

            <?php if (is_array($referral_btn) && ! empty($referral_btn['url']) && ! empty($referral_btn['title'])) : ?>
                <a
                    href="<?php echo esc_url($referral_btn['url']); ?>"
                    target="<?php echo esc_attr($referral_btn['target'] ?: '_self'); ?>"
                    class="btn flex w-full items-center justify-center rounded-[6px] border border-[#024B79] px-4 py-3 text-sm font-medium text-[#08284B] transition-colors hover:bg-[#F1F8F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                    @click="close()"
                >
                    <?php echo esc_html($referral_btn['title']); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function navSlide(root) {
    return {
        get open() {
            return Alpine.store('nav').open;
        },
        set open(value) {
            Alpine.store('nav').open = value;
        },
        depth: 0,
        parentTitle: '',
        titleStack: [],
        stack: [root],
        slideStyle(idx) {
            return `transform: translateX(${(idx - this.depth) * 100}%);`;
        },
        toggle() {
            this.open = !this.open;

            if (!this.open) {
                this.reset();
            }
        },
        close() {
            this.open = false;
            this.reset();
        },
        forward(children, label) {
            this.stack.push(children);
            this.titleStack.push(label);
            this.parentTitle = label;
            this.depth++;
        },
        back() {
            if (!this.depth) {
                return;
            }

            this.stack.pop();
            this.titleStack.pop();
            this.depth--;
            this.parentTitle = this.titleStack[this.titleStack.length - 1] || '';
        },
        reset() {
            this.stack = [root];
            this.titleStack = [];
            this.depth = 0;
            this.parentTitle = '';
        },
    };
}
</script>
<?php endif; ?>
