<?php

/**
 * Shared helpers for stpatricks.ie content migration.
 */

if (! function_exists('matrix_migrate_csv_path')) {
    function matrix_migrate_csv_path(): string
    {
        return get_template_directory() . '/old/internal_all.csv';
    }
}

if (! function_exists('matrix_migrate_html_dir')) {
    function matrix_migrate_html_dir(): string
    {
        return get_template_directory() . '/old/html';
    }
}

if (! function_exists('matrix_migrate_live_url')) {
    function matrix_migrate_live_url(string $path_or_url): string
    {
        if (str_starts_with($path_or_url, 'http://') || str_starts_with($path_or_url, 'https://')) {
            return $path_or_url;
        }

        return 'https://www.stpatricks.ie' . (str_starts_with($path_or_url, '/') ? $path_or_url : '/' . $path_or_url);
    }
}

if (! function_exists('matrix_migrate_frozen_path_prefixes')) {
    /**
     * New-site page paths that must not be read or written during migration.
     *
     * @return string[]
     */
    function matrix_migrate_frozen_path_prefixes(): array
    {
        return [
            'register-for-your-portal',
            'service-users-and-visitors/about-mental-health',
            'service-users-and-visitors/medication',
            'your-portal',
            'about-us/our-history',
            'about-us/policies-and-publications',
            'about-us',
            'about-us/overview',
            'about-us/our-team',
            'careers',
            'about-us/media-queries',
            'current-research-projects',
            'directions-and-parking',
            'healthcare-professionals',
            'make-a-referral-cta',
            'news-and-events',
            'past-research-projects',
            'programmes-therapies',
            'service-users-and-visitors',
            'what-we-offer',
            'sitemap',
            'accessibility',
            'cookie-privacy-policy',
            'data-protection-policy',
        ];
    }
}

if (! function_exists('matrix_migrate_deferred_path_prefixes')) {
    /**
     * Old-site sections deferred for later review.
     *
     * @return string[]
     */
    function matrix_migrate_deferred_path_prefixes(): array
    {
        return [
            'annual-report-2017',
            'annual-report-2018',
            'outcomes-report-2017',
            'outcomes-report-2018',
        ];
    }
}

if (! function_exists('matrix_migrate_path_matches_prefixes')) {
    function matrix_migrate_path_matches_prefixes(string $path, array $prefixes): bool
    {
        $path = trim($path, '/');

        foreach ($prefixes as $prefix) {
            $prefix = trim($prefix, '/');

            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('matrix_migrate_html_filename_to_path')) {
    function matrix_migrate_html_filename_to_path(string $filename): string
    {
        if (! preg_match('/^original_https_www\.stpatricks\.ie_(.+)\.html$/', $filename, $matches)) {
            return '';
        }

        return str_replace('_', '/', rawurldecode($matches[1]));
    }
}

if (! function_exists('matrix_migrate_normalize_asset_url')) {
    /**
     * Strip image resize query args so duplicate media URLs dedupe correctly.
     */
    function matrix_migrate_normalize_asset_url(string $url): string
    {
        $parts = wp_parse_url($url);

        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return $url;
        }

        $path = $parts['path'] ?? '';

        return strtolower($parts['scheme'] . '://' . $parts['host'] . $path);
    }
}

if (! function_exists('matrix_migrate_asset_cache_key')) {
    function matrix_migrate_asset_cache_key(string $url): string
    {
        return 'migrate-' . md5(matrix_migrate_normalize_asset_url($url));
    }
}

if (! function_exists('matrix_migrate_import_attachment')) {
    function matrix_migrate_import_attachment(string $url, string $title = '', bool $dry_run = false): int
    {
        $url = matrix_migrate_normalize_asset_url($url);

        if ($url === '') {
            return 0;
        }

        $cache_key = matrix_migrate_asset_cache_key($url);

        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_migrate_cache_key',
                    'value' => $cache_key,
                ],
            ],
            'fields' => 'ids',
        ]);

        if ($existing !== []) {
            return (int) $existing[0];
        }

        if ($dry_run) {
            return 0;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($url, 60);

        if (is_wp_error($tmp)) {
            return 0;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename((string) $path) : 'migrated-asset.bin';

        if ($title === '') {
            $title = preg_replace('/\.[^.]+$/', '', $filename) ?: 'Migrated asset';
        }

        $file_array = [
            'name' => sanitize_file_name($filename),
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, sanitize_text_field($title));

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);

            return 0;
        }

        update_post_meta($attachment_id, '_matrix_migrate_cache_key', $cache_key);
        update_post_meta($attachment_id, '_matrix_migrate_source_url', $url);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_migrate_read_csv_rows')) {
    /**
     * @return array<int, array<string, string>>
     */
    function matrix_migrate_read_csv_rows(): array
    {
        $csv_path = matrix_migrate_csv_path();

        if (! is_readable($csv_path)) {
            return [];
        }

        $handle = fopen($csv_path, 'r');

        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);

            return [];
        }

        $headers = array_map(static function (string $header): string {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;

            return trim($header, " \t\n\r\0\x0B\"'");
        }, $headers);
        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($headers)) {
                continue;
            }

            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);

        return $rows;
    }
}

if (! function_exists('matrix_migrate_is_dry_run')) {
    function matrix_migrate_is_dry_run(): bool
    {
        if (getenv('MATRIX_MIGRATE_DRY_RUN') === '1') {
            return true;
        }

        $argv = $GLOBALS['argv'] ?? [];

        return in_array('dry-run', $argv, true);
    }
}

if (! function_exists('matrix_migrate_old_html_skip_prefixes')) {
    /**
     * Old-site paths that must not be imported as posts or pages.
     *
     * @return string[]
     */
    function matrix_migrate_old_html_skip_prefixes(): array
    {
        return array_merge(
            [
                'about-us',
                'gps-referrals',
                'careers',
                'directions-and-parking',
                'research',
                'sitemap',
                'care-treatment/your-portal',
                'care-treatment/medication',
                'care-treatment/programmes-therapies',
                'care-treatment/our-services',
                'care-treatment/outpatient-clinics',
                'care-treatment/day-programmes',
                'care-treatment/st-patricks-at-home',
                'care-treatment/adolescent-mental-health-services/your-stay',
            ],
            matrix_migrate_deferred_path_prefixes()
        );
    }
}

if (! function_exists('matrix_migrate_should_skip_old_path')) {
    function matrix_migrate_should_skip_old_path(string $path): bool
    {
        $path = trim($path, '/');

        if ($path === '' || str_contains($path, '?') || str_contains($path, '%3F')) {
            return true;
        }

        return matrix_migrate_path_matches_prefixes($path, matrix_migrate_old_html_skip_prefixes());
    }
}

if (! function_exists('matrix_migrate_classify_old_path')) {
    /**
     * @return 'skip'|'post'|'page'
     */
    function matrix_migrate_classify_old_path(string $path): string
    {
        $path = trim($path, '/');

        if (matrix_migrate_should_skip_old_path($path)) {
            return 'skip';
        }

        if (preg_match('#^media-centre/(blogs-articles|news|podcasts|videos)/\d{4}/[a-z]+/.+#', $path)) {
            return 'post';
        }

        if (preg_match('#^st-patricks-mental-health-services-enewsletter/.+#', $path)) {
            return 'post';
        }

        $listing_pages = [
            'media-centre',
            'media-centre/blogs-articles',
            'media-centre/news',
            'media-centre/podcasts',
            'media-centre/videos',
        ];

        if (in_array($path, $listing_pages, true)) {
            return 'skip';
        }

        return 'page';
    }
}

if (! function_exists('matrix_migrate_post_category_for_path')) {
    function matrix_migrate_post_category_for_path(string $path): string
    {
        if (preg_match('#^media-centre/news/#', $path)) {
            return 'news';
        }

        if (preg_match('#^media-centre/podcasts/#', $path)) {
            return 'podcasts';
        }

        if (preg_match('#^media-centre/videos/#', $path)) {
            return 'videos';
        }

        if (preg_match('#^st-patricks-mental-health-services-enewsletter/#', $path)) {
            return 'newsletter';
        }

        return 'blog';
    }
}

if (! function_exists('matrix_migrate_list_html_files')) {
    /**
     * @return array<int, array{file: string, path: string}>
     */
    function matrix_migrate_list_html_files(): array
    {
        $dir = matrix_migrate_html_dir();

        if (! is_dir($dir)) {
            return [];
        }

        $items = [];

        foreach (scandir($dir) ?: [] as $filename) {
            if (! str_ends_with($filename, '.html')) {
                continue;
            }

            $path = matrix_migrate_html_filename_to_path($filename);

            if ($path === '') {
                continue;
            }

            $items[] = [
                'file' => $dir . '/' . $filename,
                'path' => $path,
            ];
        }

        return $items;
    }
}

if (! function_exists('matrix_migrate_parse_html_document')) {
    function matrix_migrate_parse_html_document(string $html): ?DOMDocument
    {
        if ($html === '') {
            return null;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        return $loaded ? $dom : null;
    }
}

if (! function_exists('matrix_migrate_dom_inner_html')) {
    function matrix_migrate_dom_inner_html(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument?->saveHTML($child) ?? '';
        }

        return $html;
    }
}

if (! function_exists('matrix_migrate_dom_text')) {
    function matrix_migrate_dom_text(?DOMNode $node): string
    {
        if (! $node instanceof DOMNode) {
            return '';
        }

        return trim(html_entity_decode($node->textContent ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

if (! function_exists('matrix_migrate_extract_parsed_page')) {
    /**
     * @return array<string, mixed>|null
     */
    function matrix_migrate_extract_parsed_page(string $html, string $old_path): ?array
    {
        $dom = matrix_migrate_parse_html_document($html);

        if (! $dom instanceof DOMDocument) {
            return null;
        }

        $xpath = new DOMXPath($dom);

        $title = matrix_migrate_dom_text($xpath->query('//title')->item(0));
        $meta_description = '';
        $meta_node = $xpath->query('//meta[@name="description"]/@content')->item(0);

        if ($meta_node instanceof DOMNode) {
            $meta_description = trim((string) $meta_node->nodeValue);
        }

        $canonical = '';
        $canonical_node = $xpath->query('//link[@rel="canonical"]/@href')->item(0);

        if ($canonical_node instanceof DOMNode) {
            $canonical = trim((string) $canonical_node->nodeValue);
        }

        $og_image = '';
        $og_node = $xpath->query('//meta[@property="og:image"]/@content')->item(0);

        if ($og_node instanceof DOMNode) {
            $og_image = trim((string) $og_node->nodeValue);
        }

        $h1 = matrix_migrate_dom_text($xpath->query('//section[contains(@class,"hero")]//h1')->item(0));

        if ($h1 === '') {
            $h1 = $title;
        }

        $intro = matrix_migrate_dom_text($xpath->query('//div[contains(@class,"inner-content-main")]//p[contains(@class,"intro")]')->item(0));

        $date_text = matrix_migrate_dom_text($xpath->query('//div[contains(@class,"article-details")]//p[contains(@class,"date")]')->item(0));
        $category_text = matrix_migrate_dom_text($xpath->query('//div[contains(@class,"article-details")]//small[contains(@class,"category")]')->item(0));

        $body_parts = [];
        $main = $xpath->query('//div[contains(@class,"inner-content-main")]')->item(0);

        if ($main instanceof DOMNode) {
            $sections = $xpath->query('.//section[contains(@class,"pb-rte") or contains(@class,"pb-accordion")]', $main);

            foreach ($sections as $section) {
                if (! $section instanceof DOMElement) {
                    continue;
                }

                $class = $section->getAttribute('class');

                if (str_contains($class, 'pb-rte')) {
                    $rte = $xpath->query('.//div[contains(@class,"rte-styles")]', $section)->item(0);

                    if ($rte instanceof DOMNode) {
                        $chunk = matrix_migrate_rewrite_html_urls(matrix_migrate_dom_inner_html($rte));

                        if (trim(strip_tags($chunk)) !== '') {
                            $body_parts[] = $chunk;
                        }
                    }

                    continue;
                }

                if (str_contains($class, 'pb-accordion')) {
                    $heading = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"section-head")]//h2', $section)->item(0));

                    if ($heading !== '') {
                        $body_parts[] = '<h2>' . esc_html($heading) . '</h2>';
                    }

                    foreach ($xpath->query('.//li[contains(@class,"accordion-item")]', $section) as $item) {
                        if (! $item instanceof DOMNode) {
                            continue;
                        }

                        $item_heading = matrix_migrate_dom_text($xpath->query('.//a[contains(@class,"accordion-title")]//h3|.//a[contains(@class,"accordion-title")]', $item)->item(0));
                        $item_body = $xpath->query('.//div[contains(@class,"accordion-content")]', $item)->item(0);

                        if ($item_heading !== '') {
                            $body_parts[] = '<h3>' . esc_html($item_heading) . '</h3>';
                        }

                        if ($item_body instanceof DOMNode) {
                            $body_parts[] = matrix_migrate_rewrite_html_urls(matrix_migrate_dom_inner_html($item_body));
                        }
                    }
                }
            }
        }

        $body_html = trim(implode("\n", $body_parts));

        if ($body_html === '') {
            return null;
        }

        return [
            'old_path' => $old_path,
            'title' => html_entity_decode($h1 !== '' ? $h1 : $title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'meta_description' => html_entity_decode($meta_description, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'canonical' => $canonical,
            'intro' => html_entity_decode($intro, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'body_html' => $body_html,
            'og_image' => $og_image,
            'date_text' => $date_text,
            'category_text' => $category_text,
        ];
    }
}

if (! function_exists('matrix_migrate_parse_post_date')) {
    function matrix_migrate_parse_post_date(string $date_text, string $old_path): string
    {
        if ($date_text !== '' && preg_match('/(\d{1,2})\s+([A-Za-z]+),?\s+(\d{4})/', $date_text, $matches)) {
            $timestamp = strtotime($matches[1] . ' ' . $matches[2] . ' ' . $matches[3]);

            if ($timestamp !== false) {
                return gmdate('Y-m-d H:i:s', $timestamp);
            }
        }

        if (preg_match('#/(\d{4})/([a-z]+)/#', $old_path, $matches)) {
            $timestamp = strtotime('1 ' . $matches[2] . ' ' . $matches[1]);

            if ($timestamp !== false) {
                return gmdate('Y-m-d H:i:s', $timestamp);
            }
        }

        return current_time('mysql');
    }
}

if (! function_exists('matrix_migrate_attachment_id_for_source_path')) {
    function matrix_migrate_attachment_id_for_source_path(string $path_or_url): int
    {
        $path = (string) parse_url($path_or_url, PHP_URL_PATH);

        if ($path === '') {
            $path = $path_or_url;
        }

        $normalized = matrix_migrate_normalize_asset_url(matrix_migrate_live_url($path));
        $cache_key = matrix_migrate_asset_cache_key($normalized);

        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_migrate_cache_key',
                    'value' => $cache_key,
                ],
            ],
            'fields' => 'ids',
        ]);

        if ($existing !== []) {
            return (int) $existing[0];
        }

        return matrix_migrate_import_attachment($normalized, '');
    }
}

if (! function_exists('matrix_migrate_legacy_media_path_from_url')) {
    function matrix_migrate_legacy_media_path_from_url(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        $path = str_starts_with($url, '/') ? $url : (string) parse_url($url, PHP_URL_PATH);

        if ($path === '' || ! preg_match('#^/media/\d+/.+#', $path)) {
            return '';
        }

        $path = (string) strtok($path, '?');

        if (str_ends_with($path, '/') && preg_match('#\.\w+/$#', $path)) {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}

if (! function_exists('matrix_migrate_missing_media_fallback_map')) {
    /**
     * Legacy media files that are no longer available at source.
     *
     * @return array<string, string>
     */
    function matrix_migrate_missing_media_fallback_map(): array
    {
        return [
            '/media/3504/group-radical-openness-programme-brochure.pdf' => '/programmes-therapies/',
            '/media/1200/spmhs-carers-booklet-2015.pdf' => '/carers-supporters-information-guide/',
        ];
    }
}

if (! function_exists('matrix_migrate_resolve_media_url')) {
    function matrix_migrate_resolve_media_url(string $url): string
    {
        $path = matrix_migrate_legacy_media_path_from_url($url);

        if ($path === '') {
            return $url;
        }

        $attachment_id = matrix_migrate_attachment_id_for_source_path($path);

        if ($attachment_id > 0) {
            return (string) (wp_get_attachment_url($attachment_id) ?: $url);
        }

        $fallbacks = matrix_migrate_missing_media_fallback_map();

        if (isset($fallbacks[$path])) {
            return (string) home_url($fallbacks[$path]);
        }

        return $url;
    }
}

if (! function_exists('matrix_migrate_legacy_path_redirect_map')) {
    /**
     * Old IA paths that were not migrated 1:1 but have a known destination.
     *
     * @return array<string, string>
     */
    function matrix_migrate_legacy_path_redirect_map(): array
    {
        return [
            'care-treatment/our-services/depression-recovery-programme' => '/programmes-therapies/',
            'care-treatment/our-services/young-adult-service' => '/young-adults/',
            'care-treatment/our-services/addiction-and-dual-diagnosis' => '/addiction-dual-diagnosis/',
            'mental-health/eating-disorders' => '/eating-disorders/',
            'mental-health/anxiety' => '/anxiety/',
            'care-treatment/inpatient-hospital-care/homecare' => '/about-our-st-patricks-at-home-service/',
            'care-treatment/our-services/homecare-service' => '/about-our-st-patricks-at-home-service/',
            'gps-referrals/referrals-admissions' => '/make-a-referral-cta/',
            'getting-help/insurance-information' => '/health-insurance-plans/',
            'getting-help/faqs' => '/faqs/',
            'advocacy/public-education-anti-stigma-campaigns/youth-advocacy-service' => '/youth-advocacy/',
            'advocacy/public-education-anti-stigma-campaigns/walk-in-my-shoes' => 'https://www.walkinmyshoes.ie/',
            'advocacy/advocacy-services/youth-advocacy' => '/youth-advocacy/',
            'advocacy/service-user-participation' => '/service-user-participation/',
            'get-involved/service-user-participation/service-user-satisfaction-survey' => '/service-user-experience-survey/',
            'nostigma/home' => '/nostigma/',
            'nostigma/shareyourexperience/guidelines' => '/shareyourexperience/',
            'getting-help/learning-resource-hub/2018/february/adhd-information-booklet' => '/information-centre/',
            'getting-help/learning-resource-hub/2018/february/dual-diagnosis-programme' => '/addiction-dual-diagnosis/',
            'media-centre/press-releases/2017/november/update-to-statement' => '/news-and-events/',
            'media-centre/events/2022/june/lgbtq-women-mental-health-network-event' => '/women-s-mental-health-network/',
            'media-centre/events/2022/february/campaign-launch-for-primary-school-mental-health-support-service' => '/news-and-events/',
            'media-centre/events/2022/april/gp-webinar-compassion-for-gps' => '/news-and-events/',
            'media-centre/podcasts/2020/june/the-irish-times-women-s-podcast-covid-19-and-the-impact-on-womens-mental-health' => '/women-s-mental-health-network/',
        ];
    }
}

if (! function_exists('matrix_migrate_old_path_permalink_map')) {
    /**
     * @return array<string, string>
     */
    function matrix_migrate_old_path_permalink_map(): array
    {
        static $map = null;

        if (is_array($map)) {
            return $map;
        }

        $map = [];

        $posts = get_posts([
            'post_type' => ['page', 'post', 'research_projects'],
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => '_matrix_migrate_old_path',
        ]);

        foreach ($posts as $post) {
            if (! $post instanceof WP_Post) {
                continue;
            }

            $old_path = trim((string) get_post_meta($post->ID, '_matrix_migrate_old_path', true), '/');

            if ($old_path === '') {
                continue;
            }

            $map[$old_path] = (string) get_permalink($post);
        }

        return $map;
    }
}

if (! function_exists('matrix_migrate_path_from_url')) {
    function matrix_migrate_path_from_url(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $host = (string) parse_url($url, PHP_URL_HOST);

            if ($host !== '' && ! str_contains($host, 'stpatricks.ie') && ! str_contains($host, 'localhost')) {
                return '';
            }

            return trim((string) parse_url($url, PHP_URL_PATH), '/');
        }

        return trim($url, '/');
    }
}

if (! function_exists('matrix_migrate_normalize_resolved_url')) {
    function matrix_migrate_normalize_resolved_url(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        return (string) preg_replace(
            '#(\.(?:pdf|docx?|xlsx?|pptx?|zip|jpe?g|png|gif|webp|svg|mp4|mp3))/$#i',
            '$1',
            $url
        );
    }
}

if (! function_exists('matrix_migrate_home_url_for_path')) {
    function matrix_migrate_home_url_for_path(string $path): string
    {
        $path = trim($path, '/');

        if ($path === '') {
            return (string) home_url('/');
        }

        $url = (string) home_url('/' . $path);

        if (preg_match('#\.\w+$#', basename($path))) {
            return matrix_migrate_normalize_resolved_url(untrailingslashit($url));
        }

        return matrix_migrate_normalize_resolved_url($url);
    }
}

if (! function_exists('matrix_migrate_resolve_migrated_url')) {
    function matrix_migrate_resolve_migrated_url(string $url): string
    {
        $url = trim($url);

        if ($url === '' || $url === '#') {
            return '';
        }

        if (str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            return $url;
        }

        if (preg_match('#/media/\d+#', $url)) {
            $media_url = matrix_migrate_resolve_media_url($url);

            if ($media_url !== $url) {
                return matrix_migrate_normalize_resolved_url($media_url);
            }
        }

        $path = matrix_migrate_path_from_url($url);

        if ($path === '') {
            return $url;
        }

        if (str_starts_with($path, 'media/')) {
            $media_url = matrix_migrate_resolve_media_url('/' . $path);

            if ($media_url !== $url && $media_url !== '/' . $path) {
                return matrix_migrate_normalize_resolved_url($media_url);
            }
        }

        if (str_starts_with($path, 'wp-content/uploads/')) {
            return matrix_migrate_home_url_for_path($path);
        }

        $old_path_map = matrix_migrate_old_path_permalink_map();

        if (isset($old_path_map[$path])) {
            return $old_path_map[$path];
        }

        foreach (matrix_migrate_frozen_redirect_map() as $old_path => $destination) {
            if ($path === $old_path) {
                return str_starts_with($destination, 'http') ? $destination : (string) home_url($destination);
            }
        }

        foreach (matrix_migrate_legacy_path_redirect_map() as $old_path => $destination) {
            if ($path === $old_path) {
                return str_starts_with($destination, 'http') ? $destination : (string) home_url($destination);
            }
        }

        $slug = basename($path);
        $page = get_page_by_path($slug, OBJECT, 'page');

        if ($page instanceof WP_Post) {
            return (string) get_permalink($page);
        }

        $posts = get_posts([
            'post_type' => 'post',
            'name' => $slug,
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if ($posts !== [] && $posts[0] instanceof WP_Post) {
            return (string) get_permalink($posts[0]);
        }

        if (preg_match('#^research/research-projects/[^/]+$#', $path)) {
            $research_posts = get_posts([
                'post_type' => 'research_projects',
                'name' => $slug,
                'post_status' => 'publish',
                'posts_per_page' => 1,
            ]);

            if ($research_posts !== [] && $research_posts[0] instanceof WP_Post) {
                return (string) get_permalink($research_posts[0]);
            }
        }

        return matrix_migrate_home_url_for_path($path);
    }
}

if (! function_exists('matrix_migrate_rewrite_internal_url')) {
    function matrix_migrate_rewrite_internal_url(string $url): string
    {
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            return $url;
        }

        $resolved_media = matrix_migrate_resolve_media_url($url);

        if ($resolved_media !== $url) {
            return $resolved_media;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            if (matrix_migrate_legacy_media_path_from_url($url) !== '') {
                return $url;
            }

            if (! str_contains($url, 'stpatricks.ie') && ! str_contains($url, 'localhost')) {
                return $url;
            }
        }

        if (str_starts_with($url, '/media/')) {
            return $url;
        }

        return matrix_migrate_resolve_migrated_url($url);
    }
}

if (! function_exists('matrix_migrate_rewrite_html_urls')) {
    function matrix_migrate_rewrite_html_urls(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $html = (string) preg_replace_callback(
            '/\s(href|src)=([\'"])([^\'"]+)\2/i',
            static function (array $matches): string {
                $attribute = $matches[1];
                $quote = $matches[2];
                $url = $matches[3];
                $rewritten = matrix_migrate_rewrite_internal_url($url);

                if ($rewritten === $url) {
                    return $matches[0];
                }

                return ' ' . $attribute . '=' . $quote . esc_url($rewritten) . $quote;
            },
            $html
        );

        return matrix_migrate_rewrite_embedded_media_paths($html);
    }
}

if (! function_exists('matrix_migrate_rewrite_embedded_media_paths')) {
    function matrix_migrate_rewrite_embedded_media_paths(string $html): string
    {
        if ($html === '' || ! str_contains($html, '/media/')) {
            return $html;
        }

        return (string) preg_replace_callback(
            '#/media/\d+/[^"\'\s<]+#',
            static function (array $matches): string {
                $resolved = matrix_migrate_resolve_media_url($matches[0]);

                return $resolved !== $matches[0] ? $resolved : $matches[0];
            },
            $html
        );
    }
}

if (! function_exists('matrix_migrate_fix_string_urls')) {
    function matrix_migrate_fix_string_urls(string $value): string
    {
        if ($value === '' || ! str_contains($value, '/media/')) {
            return $value;
        }

        $data = maybe_unserialize($value);

        if (is_array($data)) {
            $fixed = matrix_migrate_fix_urls_in_mixed($data);

            return maybe_serialize($fixed);
        }

        if (str_contains($value, '<')) {
            return matrix_migrate_rewrite_html_urls($value);
        }

        if (preg_match('#/media/\d+#', $value)) {
            $resolved = matrix_migrate_resolve_media_url($value);

            if ($resolved !== $value) {
                return $resolved;
            }
        }

        return $value;
    }
}

if (! function_exists('matrix_migrate_fix_urls_in_mixed')) {
    /**
     * @param mixed $value
     * @return mixed
     */
    function matrix_migrate_fix_urls_in_mixed($value)
    {
        if (is_string($value)) {
            return matrix_migrate_fix_string_urls($value);
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            if (is_string($item) && ($key === 'url' || preg_match('#/media/\d+#', $item))) {
                $value[$key] = matrix_migrate_rewrite_internal_url($item);

                if (str_contains((string) $value[$key], '<')) {
                    $value[$key] = matrix_migrate_rewrite_html_urls((string) $value[$key]);
                }
            } else {
                $value[$key] = matrix_migrate_fix_urls_in_mixed($item);
            }
        }

        return $value;
    }
}

if (! function_exists('matrix_migrate_fix_post_media_urls')) {
    function matrix_migrate_fix_post_media_urls(int $post_id): int
    {
        if ($post_id < 1) {
            return 0;
        }

        $changes = 0;
        $post = get_post($post_id);

        if ($post instanceof WP_Post) {
            $content = matrix_migrate_rewrite_html_urls(matrix_migrate_fix_string_urls($post->post_content));

            if ($content !== $post->post_content) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $content,
                ]);
                $changes++;
            }

            $excerpt = matrix_migrate_fix_string_urls((string) $post->post_excerpt);

            if ($excerpt !== (string) $post->post_excerpt) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_excerpt' => $excerpt,
                ]);
                $changes++;
            }
        }

        $meta_rows = get_post_meta($post_id);

        foreach ($meta_rows as $meta_key => $values) {
            if (! is_array($values) || str_starts_with((string) $meta_key, '_')) {
                continue;
            }

            foreach ($values as $index => $meta_value) {
                if (! is_string($meta_value) || ! str_contains($meta_value, '/media/')) {
                    continue;
                }

                $fixed = matrix_migrate_fix_string_urls($meta_value);

                if ($fixed === $meta_value) {
                    continue;
                }

                if ($index === 0) {
                    update_post_meta($post_id, $meta_key, $fixed);
                } else {
                    update_post_meta($post_id, $meta_key, $fixed, (string) $meta_value);
                }

                $changes++;
            }
        }

        return $changes;
    }
}

if (! function_exists('matrix_migrate_fixup_post_link_urls')) {
    function matrix_migrate_fixup_post_link_urls(int $post_id): int
    {
        if ($post_id < 1 || ! function_exists('get_field') || ! function_exists('update_field')) {
            return 0;
        }

        $changes = matrix_migrate_fix_post_media_urls($post_id);
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows) || $rows === []) {
            return $changes;
        }

        $fixed = matrix_migrate_fix_urls_in_mixed($rows);

        if ($fixed !== $rows) {
            update_field('flexible_content_blocks', $fixed, $post_id);
            $changes++;
        }

        return $changes;
    }
}

if (! function_exists('matrix_migrate_filter_fix_media_urls_in_content')) {
    function matrix_migrate_filter_fix_media_urls_in_content(string $content): string
    {
        if ($content === '' || ! str_contains($content, '/media/')) {
            return $content;
        }

        $post_id = get_the_ID();

        if ($post_id < 1 || get_post_meta($post_id, '_matrix_migrate_old_path', true) === '') {
            return $content;
        }

        return matrix_migrate_rewrite_html_urls($content);
    }
}

if (function_exists('add_filter')) {
    add_filter('the_content', 'matrix_migrate_filter_fix_media_urls_in_content', 15);
}

if (! function_exists('matrix_migrate_find_by_old_path')) {
    function matrix_migrate_find_by_old_path(string $old_path, string $post_type): int
    {
        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_key' => '_matrix_migrate_old_path',
            'meta_value' => $old_path,
            'fields' => 'ids',
        ]);

        return $posts !== [] ? (int) $posts[0] : 0;
    }
}

if (! function_exists('matrix_migrate_unique_slug')) {
    function matrix_migrate_unique_slug(string $base_slug, string $old_path, array &$registry): string
    {
        $slug = sanitize_title($base_slug);

        if ($slug === '') {
            $slug = sanitize_title(str_replace('/', '-', $old_path));
        }

        if (! isset($registry[$slug])) {
            $registry[$slug] = $old_path;

            return $slug;
        }

        if ($registry[$slug] === $old_path) {
            return $slug;
        }

        $parts = explode('/', trim($old_path, '/'));

        if (count($parts) >= 2) {
            $slug = sanitize_title($parts[count($parts) - 2] . '-' . $parts[count($parts) - 1]);
        }

        $original = $slug;
        $counter = 2;

        while (isset($registry[$slug]) && $registry[$slug] !== $old_path) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        $registry[$slug] = $old_path;

        return $slug;
    }
}

if (! function_exists('matrix_migrate_ensure_category')) {
    function matrix_migrate_ensure_category(string $slug, string $name): int
    {
        $term = get_category_by_slug($slug);

        if ($term instanceof WP_Term) {
            return (int) $term->term_id;
        }

        $result = wp_insert_term($name, 'category', ['slug' => $slug]);

        if (is_wp_error($result)) {
            return 0;
        }

        return (int) ($result['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_migrate_page_flexi_rows')) {
    /**
     * @param array<string, mixed> $parsed
     * @return array<int, array<string, mixed>>
     */
    function matrix_migrate_page_flexi_rows(array $parsed, int $hero_image_id = 0, string $html = ''): array
    {
        if ($html !== '' && function_exists('matrix_migrate_build_structured_flexi_rows') && function_exists('matrix_migrate_extract_structured_page')) {
            $old_path = (string) ($parsed['old_path'] ?? '');
            $structured = matrix_migrate_extract_structured_page($html, $old_path);

            if (is_array($structured)) {
                return matrix_migrate_build_structured_flexi_rows($structured, $hero_image_id);
            }
        }

        $title = (string) ($parsed['title'] ?? '');
        $intro = (string) ($parsed['intro'] ?? '');
        $body_html = (string) ($parsed['body_html'] ?? '');

        $hero = array_merge(matrix_get_utility_page_hero_config($title, $intro), [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'show_breadcrumbs' => 1,
            'text_max_width' => 'default',
            'manual_breadcrumbs' => [
                [
                    'breadcrumb_link' => [
                        'title' => 'Home',
                        'url' => home_url('/'),
                        'target' => '',
                    ],
                ],
            ],
            'hero_image' => $hero_image_id,
        ]);

        if ($intro === '' && $body_html !== '') {
            $hero['content'] = '';
        }

        $padding = [
            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
        ];

        return [
            $hero,
            [
                'acf_fc_layout' => 'wysiwyg',
                'text_content' => $body_html,
                'padding_settings' => $padding,
            ],
        ];
    }
}

if (! function_exists('matrix_migrate_frozen_redirect_map')) {
    /**
     * Old paths for designed pages that moved to new IA.
     *
     * @return array<string, string>
     */
    function matrix_migrate_frozen_redirect_map(): array
    {
        return [
            'care-treatment/your-portal' => '/your-portal/',
            'care-treatment/your-portal/register' => '/register-for-your-portal/',
            'care-treatment/medication' => '/service-users-and-visitors/medication/',
            'care-treatment/programmes-therapies' => '/programmes-therapies/',
            'care-treatment/our-services' => '/what-we-offer/',
            'care-treatment/day-programmes' => '/what-we-offer/day-programmes/',
            'care-treatment/outpatient-clinics' => '/what-we-offer/outpatient-care-dean-clinics/',
            'care-treatment/st-patricks-at-home' => '/service-users-and-visitors/about-our-st-patricks-at-home-service/',
            'care-treatment/adolescent-mental-health-services/your-stay' => '/service-users-and-visitors/your-stay-in-hospital-as-an-adolescent/',
            'getting-help/faqs' => '/service-users-and-visitors/frequently-asked-questions-faqs/',
            'gps-referrals' => '/healthcare-professionals/',
            'media-centre/news' => '/news-and-events/',
            'research' => '/about-us/research/',
            'research/spire' => '/research/spire/',
            'research-library-spire' => '/research/spire/',
            'research/research-projects' => '/research-projects/',
            'research/current-projects' => '/current-research-projects/',
            'research/past-projects' => '/past-research-projects/',
            'careers' => '/careers/',
            'directions-and-parking' => '/directions-and-parking/',
            'about-us' => '/about-us/',
            'about-us/overview' => '/about-us/overview/',
            'about-us/our-history' => '/about-us/our-history/',
            'about-us/our-team' => '/about-us/our-team/',
            'about-us/policies-and-publications' => '/about-us/policies-and-publications/',
            'about-us/media-queries' => '/about-us/media-queries/',
            'accessibility' => '/accessibility/',
            'privacy-notice' => '/cookie-privacy-policy/',
            'about-us/policies-and-publications/data-protection' => '/data-protection-policy/',
        ];
    }
}

if (! function_exists('matrix_migrate_redirects_csv_path')) {
    function matrix_migrate_redirects_csv_path(): string
    {
        return get_template_directory() . '/old/rankmath-redirects.csv';
    }
}
