<?php

$not_found = is_array($args['not_found'] ?? null) ? $args['not_found'] : matrix_prepare_not_found_page();
$heading = is_array($not_found['heading'] ?? null) ? $not_found['heading'] : [];
$search_base_url = (string) ($not_found['search_base_url'] ?? home_url('/'));
$search_query = trim((string) ($not_found['search_query'] ?? ''));
$home_url = (string) ($not_found['home_url'] ?? home_url('/'));
$useful_links = is_array($not_found['useful_links'] ?? null) ? $not_found['useful_links'] : null;
$section_id = 'not-found-page-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
?>

<main id="<?php echo esc_attr($section_id); ?>" class="w-full site-main">
    <section class="bg-white" aria-labelledby="<?php echo esc_attr($section_id); ?>-heading">
        <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-8 px-5 py-12 xl:px-0 xl:py-[100px]">
            <?php
            get_template_part('template-parts/partials/search-results-intro', null, [
                'search_base_url' => $search_base_url,
                'search_query' => $search_query,
                'heading' => $heading,
                'search_input_id' => $section_id . '-search',
            ]);
            ?>

            <div class="flex flex-col gap-6">
                <a
                    href="<?php echo esc_url($home_url); ?>"
                    class="btn inline-flex w-fit items-center justify-center rounded-[6px] bg-[#024B79] px-4 py-2 text-[14px] font-medium leading-[24px] text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                >
                    Go back to Home page
                </a>
            </div>
        </div>
    </section>

    <?php
    if (is_array($useful_links)) {
        echo matrix_render_useful_links_section($useful_links); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    ?>
</main>
