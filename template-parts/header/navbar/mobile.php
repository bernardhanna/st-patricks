<?php

use Log1x\Navi\Navi;
use Illuminate\Support\Collection;

$primary_navigation = $primary_navigation ?? Navi::make()->build('primary');

$enable_hamburger = get_field('enable_hamburger', 'option');
$hamburger_style  = get_field('hamburger_style', 'option');

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
$contact_email = get_field('contact_email_link', 'option');
$enable_search = ! empty($nav_settings['enable_search']);

$faq_links = [
    ['title' => "Healthcare FAQ's", 'url' => home_url('/healthcare-professionals/frequently-asked-questions/')],
    ['title' => 'Our locations and parking', 'url' => home_url('/about-us/our-locations/')],
    ['title' => "Service user FAQ's", 'url' => home_url('/service-users-and-visitors/frequently-asked-questions-faqs/')],
    ['title' => 'Make a payment', 'url' => home_url('/service-users-and-visitors/make-a-payment-external-link-to-stripe/')],
];

$logo_id = get_theme_mod('custom_logo');
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
$logo_alt = $logo_id ? get_post_meta($logo_id, '_wp_attachment_image_alt', true) : get_bloginfo('name');

$nav_slide_config = [
    'menu' => $menu_data,
    'faqLinks' => $faq_links,
    'enableSearch' => $enable_search,
];
?>

<?php if ($enable_hamburger && $menu_data !== []) : ?>
<div
    x-data="navSlide(<?php echo esc_attr(wp_json_encode($nav_slide_config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>)"
    x-cloak
    class="flex items-center gap-3 xl:hidden"
    @keydown.escape.window="close()"
>
    <?php get_template_part('template-parts/header/navbar/cart'); ?>

    <button
        type="button"
        class="relative z-[130] hamburger <?php echo esc_attr($hamburger_style); ?>"
        :class="{ 'is-active': open }"
        x-show="!open"
        @click="toggle"
        :aria-expanded="open ? 'true' : 'false'"
        aria-controls="mobile-nav-panel"
        aria-label="Menu"
    >
        <span class="hamburger-box"><span class="hamburger-inner"></span></span>
    </button>

    <div
        id="mobile-nav-panel"
        x-show="open"
        x-transition:enter="transition transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform ease-in duration-200"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-0 z-[140] flex w-full flex-col font-primary"
        role="dialog"
        aria-modal="true"
        aria-label="Mobile navigation"
        x-cloak
    >
        <div class="flex shrink-0 items-center justify-between gap-3 bg-[#08284B] px-5 py-4">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="shrink-0" @click="close()">
                <?php if ($logo_url !== '') : ?>
                    <img
                        src="<?php echo esc_url($logo_url); ?>"
                        alt="<?php echo esc_attr($logo_alt); ?>"
                        class="h-9 w-[140px] object-contain brightness-0 invert"
                    />
                <?php else : ?>
                    <span class="text-base font-semibold text-white"><?php echo esc_html(get_bloginfo('name')); ?></span>
                <?php endif; ?>
            </a>

            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-[6px] text-white transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-white focus-visible:ring-offset-[#08284B]"
                @click="close()"
                aria-label="Close menu"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <div class="flex min-h-0 flex-1 flex-col bg-[#F1F8F9]">
            <template x-if="enableSearch && depth === 0">
                <div class="shrink-0 border-b border-[#E2E8F0] bg-[#F1F8F9] px-5 py-4">
                    <form role="search" class="relative" @submit.prevent="submitSearch()">
                        <label for="mobile-nav-search-input" class="sr-only">Search by keyword, symptom, or page</label>
                        <input
                            id="mobile-nav-search-input"
                            x-ref="searchInput"
                            x-model="query"
                            type="search"
                            placeholder="Search by keyword, symptom, or page"
                            class="w-full rounded-[6px] border border-[#E2E8F0] bg-white py-3 pl-4 pr-12 text-base text-[#08284B] placeholder:text-[#64748B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#024B79]"
                            @focus="searchFocused = true"
                            @blur="handleSearchBlur()"
                            @input="handleQueryChange()"
                        />
                        <button
                            type="submit"
                            class="absolute right-3 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded text-[#08284B] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#024B79]"
                            aria-label="Search"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20.9999 21L16.6499 16.65" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>

                    <div
                        x-show="searchFocused && query.trim().length < 2"
                        x-transition.opacity
                        class="mt-2 overflow-hidden rounded-[6px] border border-[#E2E8F0] bg-white"
                        x-cloak
                    >
                        <ul class="m-0 list-none p-0" role="list">
                            <template x-for="faq in faqLinks" :key="faq.title">
                                <li class="border-b border-[#E2E8F0] last:border-b-0">
                                    <a
                                        :href="faq.url"
                                        class="flex items-center justify-between gap-3 px-4 py-4 text-base font-medium text-[#08284B] transition-colors hover:bg-[#F1F8F9] focus-visible:bg-[#F1F8F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#024B79]"
                                        @click="close()"
                                        @mousedown.prevent
                                    >
                                        <span x-text="faq.title"></span>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0">
                                            <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div
                        x-show="query.trim().length >= 2"
                        x-transition.opacity
                        class="mt-2 overflow-hidden rounded-[6px] border border-[#E2E8F0] bg-white"
                        x-cloak
                    >
                        <div x-show="loading" class="px-4 py-4 text-sm text-[#64748B]">Searching...</div>
                        <div x-show="!loading && error" class="px-4 py-4 text-sm text-red-600" x-text="error"></div>
                        <ul x-show="!loading && !error && results.length" class="m-0 list-none p-0" role="list">
                            <template x-for="item in results" :key="item.id + '-' + item.subtype">
                                <li class="border-b border-[#E2E8F0] last:border-b-0">
                                    <a
                                        :href="item.url"
                                        class="flex items-center justify-between gap-3 px-4 py-4 text-base font-medium text-[#08284B] transition-colors hover:bg-[#F1F8F9] focus-visible:bg-[#F1F8F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#024B79]"
                                        @click="close()"
                                    >
                                        <span x-text="item.title"></span>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0">
                                            <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <div x-show="!loading && !error && !results.length" class="px-4 py-4 text-sm text-[#64748B]">
                            No results found. Try a different keyword.
                        </div>
                    </div>
                </div>
            </template>

            <div
                class="shrink-0 border-b border-[#E2E8F0] bg-[#F1F8F9] px-5 py-4"
                x-show="depth > 0"
                x-transition.opacity
                x-cloak
            >
                <button
                    type="button"
                    class="inline-flex items-center gap-2 text-base font-semibold text-[#024B79] transition-colors hover:text-[#08284B] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                    @click="back()"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span x-text="parentTitle"></span>
                </button>
            </div>

            <div class="relative min-h-0 flex-1 overflow-hidden">
                <template x-for="(level, idx) in stack" :key="idx">
                    <ul
                        class="absolute inset-0 m-0 list-none overflow-y-auto bg-[#F1F8F9] px-0 py-0 transition-transform duration-300 ease-out"
                        :style="slideStyle(idx)"
                        role="list"
                    >
                        <template x-for="(item, i) in level" :key="i">
                            <li class="border-b border-[#E2E8F0] last:border-b-0">
                                <template x-if="item.children.length">
                                    <div class="flex items-center justify-between gap-3 px-5 py-4">
                                        <a
                                            :href="item.url"
                                            class="min-w-0 flex-1 text-base leading-6 text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                                            :class="depth === 0 ? 'font-semibold' : 'font-medium'"
                                            x-text="item.label"
                                            @click="close()"
                                        ></a>
                                        <button
                                            type="button"
                                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[6px] text-[#08284B] hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
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
                                        class="flex items-center justify-between gap-3 px-5 py-4 text-base font-medium leading-6 text-[#08284B] transition-colors hover:bg-white hover:text-[#024B79] focus-visible:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#024B79]"
                                        :class="item.active ? 'bg-white font-semibold text-[#024B79]' : ''"
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
        </div>

        <div class="shrink-0 border-t border-[#E2E8F0] bg-white">
            <?php if ((is_array($topbar_phone) && ! empty($topbar_phone['url']) && ! empty($topbar_phone['title'])) || (is_array($contact_email) && ! empty($contact_email['url']) && ! empty($contact_email['title']))) : ?>
                <div class="space-y-3 px-5 pb-4 pt-5">
                    <?php if (is_array($topbar_phone) && ! empty($topbar_phone['url']) && ! empty($topbar_phone['title'])) : ?>
                        <a
                            href="<?php echo esc_url($topbar_phone['url']); ?>"
                            class="flex items-center gap-3 text-sm font-medium leading-5 text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                            @click="close()"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0 text-[#024B79]">
                                <path d="M21.9999 16.92V19.92C22.0011 20.1985 21.944 20.4742 21.8324 20.7294C21.7209 20.9845 21.5572 21.2136 21.352 21.4019C21.1468 21.5901 20.9045 21.7335 20.6407 21.8227C20.3769 21.9119 20.0973 21.9451 19.8199 21.92C16.7428 21.5856 13.7869 20.5342 11.1899 18.85C8.77376 17.3147 6.72527 15.2662 5.18993 12.85C3.49991 10.2412 2.44818 7.271 2.11993 4.18001C2.09494 3.90347 2.12781 3.62477 2.21643 3.36163C2.30506 3.09849 2.4475 2.85669 2.6347 2.65163C2.82189 2.44656 3.04974 2.28271 3.30372 2.17053C3.55771 2.05834 3.83227 2.00027 4.10993 2.00001H7.10993C7.59524 1.99523 8.06572 2.16708 8.43369 2.48354C8.80166 2.79999 9.04201 3.23945 9.10993 3.72001C9.23656 4.68007 9.47138 5.62273 9.80993 6.53001C9.94448 6.88793 9.9736 7.27692 9.89384 7.65089C9.81408 8.02485 9.6288 8.36812 9.35993 8.64001L8.08993 9.91001C9.51349 12.4136 11.5864 14.4865 14.0899 15.91L15.3599 14.64C15.6318 14.3711 15.9751 14.1859 16.3491 14.1061C16.723 14.0263 17.112 14.0555 17.4699 14.19C18.3772 14.5286 19.3199 14.7634 20.2799 14.89C20.7657 14.9585 21.2093 15.2032 21.5265 15.5775C21.8436 15.9518 22.0121 16.4296 21.9999 16.92Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span><?php echo esc_html($topbar_phone['title']); ?></span>
                        </a>
                    <?php endif; ?>

                    <?php if (is_array($contact_email) && ! empty($contact_email['url']) && ! empty($contact_email['title'])) : ?>
                        <a
                            href="<?php echo esc_url($contact_email['url']); ?>"
                            class="flex items-center gap-3 text-sm font-medium leading-5 text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                            @click="close()"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="shrink-0 text-[#024B79]">
                                <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span><?php echo esc_html($contact_email['title']); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="space-y-3 px-5 pb-5">
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
                        class="btn flex w-full items-center justify-center rounded-[6px] border border-[#024B79] bg-white px-4 py-3 text-sm font-medium text-[#08284B] transition-colors hover:bg-[#F1F8F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                        @click="close()"
                    >
                        <?php echo esc_html($referral_btn['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function navSlide(config) {
    const root = Array.isArray(config) ? config : (config.menu || []);
    const faqLinks = config.faqLinks || [];
    const enableSearch = config.enableSearch !== false;

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
        faqLinks,
        enableSearch,
        searchFocused: false,
        query: '',
        results: [],
        loading: false,
        error: '',
        abortController: null,
        searchTimeout: null,
        searchBlurTimeout: null,
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
            this.clearSearchState();
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
        handleSearchBlur() {
            if (this.searchBlurTimeout) {
                clearTimeout(this.searchBlurTimeout);
            }

            this.searchBlurTimeout = setTimeout(() => {
                this.searchFocused = false;
            }, 150);
        },
        handleQueryChange() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            const term = this.query.trim();

            if (term.length < 2) {
                this.results = [];
                this.error = '';
                this.loading = false;

                if (this.abortController) {
                    this.abortController.abort();
                    this.abortController = null;
                }

                return;
            }

            this.searchTimeout = setTimeout(() => this.fetchResults(term), 220);
        },
        async fetchResults(term) {
            if (this.abortController) {
                this.abortController.abort();
            }

            this.abortController = new AbortController();
            this.loading = true;
            this.error = '';

            try {
                const url = `${window.location.origin}/wp-json/wp/v2/search?search=${encodeURIComponent(term)}&per_page=8&type=post&subtype=post,page`;
                const res = await fetch(url, {
                    signal: this.abortController.signal,
                    headers: { Accept: 'application/json' },
                });

                if (!res.ok) {
                    throw new Error(`Search failed (${res.status})`);
                }

                const data = await res.json();
                this.results = Array.isArray(data) ? data : [];
            } catch (err) {
                if (err.name !== 'AbortError') {
                    this.error = 'Could not load search results.';
                    this.results = [];
                }
            } finally {
                this.loading = false;
            }
        },
        submitSearch() {
            const term = this.query.trim();

            if (!term) {
                return;
            }

            window.location.href = `${window.location.origin}/search/?s=${encodeURIComponent(term)}`;
        },
        clearSearchState() {
            this.searchFocused = false;
            this.query = '';
            this.results = [];
            this.error = '';
            this.loading = false;

            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = null;
            }

            if (this.abortController) {
                this.abortController.abort();
                this.abortController = null;
            }
        },
        reset() {
            this.stack = [root];
            this.titleStack = [];
            this.depth = 0;
            this.parentTitle = '';
            this.clearSearchState();
        },
    };
}
</script>
<?php endif; ?>
