<?php

/**
 * Template for the Sitemap page (slug: sitemap).
 */

get_header();

$sitemap = matrix_prepare_sitemap_page(get_queried_object_id());

get_template_part('template-parts/sitemap/page', null, [
    'sitemap' => $sitemap,
]);

get_footer();
