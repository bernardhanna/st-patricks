<?php

$search_base_url = (string) ($args['search_base_url'] ?? (function_exists('home_url') ? home_url('/') : '/'));
$search_query = trim((string) ($args['search_query'] ?? ''));
$heading = is_array($args['heading'] ?? null) ? $args['heading'] : [];
$heading_prefix = trim((string) ($heading['prefix'] ?? 'Search Results'));
$heading_query = trim((string) ($heading['query'] ?? ''));
$show_search_form = (bool) ($args['show_search_form'] ?? true);
$render_form_tag = (bool) ($args['render_form_tag'] ?? true);
$search_input_id = trim((string) ($args['search_input_id'] ?? ''));

if ($search_input_id === '') {
    $search_input_id = 'site-search-query-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
}

$search_field_markup = '';
if ($show_search_form) {
    ob_start();
    ?>
    <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">Search site content</label>
    <input
        id="<?php echo esc_attr($search_input_id); ?>"
        type="search"
        name="s"
        value="<?php echo esc_attr($search_query); ?>"
        placeholder="Search site content"
        class="min-h-[40px] min-w-0 w-full flex-1 rounded-[6px] border border-[#E2E8F0] px-3 py-2 font-primary text-[16px] leading-[24px] text-[#08284B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79]"
    />
    <button
        type="submit"
        class="btn inline-flex h-[40px] shrink-0 items-center justify-center gap-2 rounded-[6px] bg-[#08284B] px-4 text-[14px] font-medium leading-[24px] text-white"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" class="shrink-0">
            <path d="M7.333 12.667A5.333 5.333 0 1 0 7.333 2a5.333 5.333 0 0 0 0 10.667Z" stroke="currentColor" stroke-width="1.25"/>
            <path d="M11.333 11.333 14 14" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
        </svg>
        Search
    </button>
    <?php
    $search_field_markup = (string) ob_get_clean();
}
?>

<div class="flex w-full min-w-0 flex-col gap-8">
    <?php if ($show_search_form) { ?>
        <?php if ($render_form_tag) { ?>
            <form action="<?php echo esc_url($search_base_url); ?>" method="get" class="flex w-full min-w-0 max-w-full flex-col gap-3 min-[430px]:flex-row min-[430px]:items-stretch lg:max-w-[384px]">
                <?php echo $search_field_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </form>
        <?php } else { ?>
            <div class="flex w-full min-w-0 max-w-full flex-col gap-3 min-[430px]:flex-row min-[430px]:items-stretch lg:max-w-[384px]">
                <?php echo $search_field_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        <?php } ?>
    <?php } ?>

    <h1 class="break-words font-primary text-[1.75rem] font-bold leading-[1.75rem] tracking-[-0.021rem] text-[#08284B] md:text-[36px] md:leading-[40px] md:tracking-[-0.432px] lg:text-[48px] lg:leading-[48px] lg:tracking-[-0.576px]">
        <?php if ($heading_query !== '') { ?>
            <?php echo esc_html($heading_prefix); ?>
            <span class="font-normal italic"><?php echo esc_html(" '" . $heading_query . "'"); ?></span>
        <?php } else { ?>
            <?php echo esc_html($heading_prefix); ?>
        <?php } ?>
    </h1>
</div>
