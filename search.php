<?php
get_header();

$search_request = isset($_GET) && is_array($_GET) ? $_GET : [];

if (function_exists('wp_unslash')) {
    $search_request = wp_unslash($search_request);
}

$query_var_keys = ['s', 'paged', 'search_type', 'search_sort', 'matrix_search'];

foreach ($query_var_keys as $query_var_key) {
    $query_var_value = function_exists('get_query_var')
        ? get_query_var($query_var_key, null)
        : null;

    if ($query_var_value === null || $query_var_value === '') {
        continue;
    }

    $search_request[$query_var_key] = $query_var_value;
}

$search_results = matrix_prepare_search_results($search_request);

get_template_part('template-parts/search/results', null, [
    'search_results' => $search_results,
]);

get_footer();
