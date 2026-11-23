<?php
/**
 * Fold Advocacy Centre / Academic Institute / Training Centre into National centre
 * as accordions (with Interactive Education Centre), redirect the standalone pages,
 * embed the research video, and add Future in Mind wording.
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/fix-national-centre-fold.php?key=fix-national-fold
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'fix-national-fold') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$log = [];
$p = static fn (string $html): string => '<p>' . $html . '</p>';
$a = static function (string $url, string $label, bool $blank = false): string {
    $extra = $blank ? ' target="_blank" rel="noopener noreferrer"' : '';

    return '<a href="' . esc_url($url) . '"' . $extra . '>' . esc_html($label) . '</a>';
};

$national_id = (int) (get_page_by_path('about-us/our-present-and-future/national-centre')?->ID
    ?? get_page_by_path('national-centre')?->ID
    ?? 0);
if ($national_id <= 0) {
    echo "MISS national-centre\n";
    return;
}
$log[] = 'OK national #' . $national_id;

$present_id = (int) (get_page_by_path('about-us/our-present-and-future')?->ID ?? 0);

// -------------------------------------------------------------------------
// Banner image on Our present and future (change-requests: Our present.jpg)
// -------------------------------------------------------------------------
if ($present_id > 0) {
    $banner_id = matrix_orlaith_find_image(0, 'Our-present.jpg');
    if ($banner_id <= 0) {
        $banner_id = matrix_orlaith_find_image(0, 'Our present.jpg');
    }
    if ($banner_id <= 0) {
        $banner_id = (int) attachment_url_to_postid(
            home_url('/wp-content/uploads/2026/11/Our-present.jpg')
        );
    }
    if ($banner_id <= 0) {
        $local = get_template_directory()
            . '/new/SPMHS-Content-Gathering-Library/change-requests/Our present.jpg';
        if (is_readable($local)) {
            $tmp = wp_tempnam('Our-present.jpg');
            if ($tmp && copy($local, $tmp)) {
                $sideloaded = media_handle_sideload([
                    'name' => 'Our-present.jpg',
                    'tmp_name' => $tmp,
                ], $present_id, 'Our present');
                if (! is_wp_error($sideloaded)) {
                    $banner_id = (int) $sideloaded;
                }
            }
        }
    }

    if ($banner_id > 0) {
        $rows = get_field('flexible_content_blocks', $present_id);
        if (is_array($rows)) {
            foreach ($rows as $i => $row) {
                $layout = (string) ($row['acf_fc_layout'] ?? '');
                if ($layout !== 'hero_with_breadcrumbs' && $layout !== 'hero') {
                    continue;
                }
                $rows[$i]['hero_image'] = $banner_id;
                $rows[$i]['layout_style'] = 'image_split';
                update_field('flexible_content_blocks', $rows, $present_id);
                set_post_thumbnail($present_id, $banner_id);
                clean_post_cache($present_id);
                $log[] = 'OK present banner #' . $banner_id;
                break;
            }
        }
    } else {
        $log[] = 'WARN present banner image not found';
    }
}

// -------------------------------------------------------------------------
// Build National centre content
// -------------------------------------------------------------------------
$research_url = home_url('/about-us/research/');
$who_url = 'https://www.who.int/publications/i/item/9789240031029';
$fim_url = 'https://www.stpatricks.ie/media/3720/the-future-in-mind-strategy-st-patricks.pdf';
// Prefer a local Future in Mind PDF if present in media.
$fim_local = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 5,
    'post_mime_type' => 'application/pdf',
    's' => 'future in mind',
    'fields' => 'ids',
]);
if ($fim_local !== []) {
    $local_url = (string) wp_get_attachment_url((int) $fim_local[0]);
    if ($local_url !== '') {
        $fim_url = $local_url;
    }
}

$hero_img = (int) get_post_thumbnail_id($national_id);
if ($hero_img <= 0) {
    $hero_img = matrix_orlaith_find_image(0, 'National Centre.png');
}
if ($hero_img <= 0) {
    $hero_img = matrix_orlaith_find_image(4035, 'National-Centre-1.png');
}

$poster = matrix_orlaith_find_image(0, 'Research and Training Video.png');
if ($poster <= 0) {
    $poster = $hero_img;
}

$hero_intro = $p('St Patrick’s Mental Health Services is committed to developing a national centre for mentally healthy living.');

$body = $p('The national centre for mentally healthy living will provide mental health and wellbeing initiatives for the general population, as well as a range of services for people experiencing mental health difficulties.')
    . $p('Phase one of the building works to transform the Historic Building of St Patrick’s University Hospital into a national centre for mentally healthy living was completed over 2025 and 2026. This included a programme of conservation and essential maintenance work to repair the fabric of the Historic Building. In time, the ground floor of this building will be home to:')
    . '<ul>'
    . '<li>an Interactive Education Centre</li>'
    . '<li>an Advocacy Centre</li>'
    . '<li>an Academic Institute</li>'
    . '<li>a Training Centre</li>'
    . '<li>clinical rooms and spaces for service delivery.</li>'
    . '</ul>'
    . $p($a($fim_url, 'Read more about The Future in Mind here', true) . '.');

$education = $p('The Interactive Education Centre will tackle misinformation about mental health; challenge stigma; and educate people, particularly young people, about the practical tools we can all use throughout our lives to support our wellbeing and mental health. It will also immerse them in the history of our founder, Jonathan Swift, and the hospital building. The education centre will be free of charge to the public, and will open up compassionate conversations around mental health and equip visitors with the knowledge and tools to better understand and support their mental health.');

$advocacy = $p('The Advocacy Centre is a core component of our national centre for mentally healthy living.')
    . $p('Through the centre, we strive to promote greater understanding of mental health difficulties in Ireland and beyond and to remove the stigma associated with such difficulties.')
    . $p('We aim to advance the rights of people experiencing mental health difficulties to be treated with dignity, respect and without discrimination, educating on mental health rights and equality issues.')
    . $p('We also work to strengthen and develop collaborative advocacy partnerships and continue our contributions to consultations and submissions that inform policy development.')
    . $p('The Advocacy Centre will complement and contribute to the activities of the national centre, and especially the activities of the Education Centre, through:')
    . '<ul>'
    . '<li>promoting human rights issues in relation to mental health</li>'
    . '<li>educating on mental health rights and equality issues</li>'
    . '<li>engaging the public to support positive change in areas such as climate change and mental health, among others.</li>'
    . '</ul>'
    . $p('It is intended that the Advocacy Centre will align with European child mental health advocacy goals and with the vision of the '
        . $a($who_url, 'World Health Organisation (WHO) Mental Health Action Plan', true)
        . ', and will have a particular focus on child mental health.');

$academic = $p('The Academic Institute here at St Patrick’s Mental Health Services (SPMHS) works to support and promote mental health research.')
    . $p('Our Academic Institute was established in 2022 and proactively works to promote research and to build a strong research culture in SPMHS. It acts as a central hub for all academic work within SPMHS, and supports staff and students engaged in research activity.')
    . $p('It has oversight of hospital-based research activity to ensure that the wellbeing and rights of service users, their families and staff, as well as SPMHS’ resources, are safeguarded at all times.')
    . $p('We strive to progress the Academic Institute into an international research centre, pioneering research in the field of mental health. It will play a crucial role in investigating and enhancing mental healthcare through '
        . $a($research_url, 'service-user led research')
        . '.')
    . '<h3>Research Strategy</h3>'
    . $p('A Research Strategy for our organisation has been developed in collaboration with a service user and carer-led steering group for the Academic Institute and with input from a Clinical Advisory Group.')
    . $p('Our Research Strategy for 2023 to 2027 has the five key goals of establishing research governance; providing clear and supportive pathways to conduct research; devising new ways to communicate research; supporting education and training; and fostering a culture of research excellence.')
    . $p('This Research Strategy seeks to address both the opportunities and the challenges faced in conducting research and to build on the great strengths and talents of SPMHS staff. By creating a positive culture of research, we can continue to improve outcomes for the people who use our services.');

$training = $p('St Patrick’s Mental Health Services is establishing a dedicated Training Centre to advance the skills and competencies of those working in the mental healthcare sector.')
    . $p('We will expand our training for mental health professionals through the Training Centre. We will also further develop comprehensive continuing professional development (CPD) programmes for people working in mental health.')
    . $p('Our Training Centre will strengthen our existing training partnerships, while also creating new opportunities for the skills development of mental health professionals throughout Ireland.');

$hero = matrix_orlaith_hero_row('National Centre', $hero_intro, $hero_img);
$hero['layout_style'] = $hero_img > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'National Centre';
$hero['breadcrumb_source'] = 'auto';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$flexi = [
    $hero,
    matrix_orlaith_content_row('', $body, 'white'),
    matrix_orlaith_accordion_row([
        'Interactive Education Centre' => $education,
        'Advocacy Centre' => $advocacy,
        'Academic Institute' => $academic,
        'Training Centre' => $training,
    ], 'default', ''),
    matrix_orlaith_video_row(
        'Watch | Learn more about research and training',
        '',
        [[
            'url' => 'https://www.youtube.com/watch?v=AjJQxOrmv1o',
            'title' => 'Research and Training',
            'caption' => 'Learn more about our Academic Institute and our commitment to supporting staff and organisations working in mental health through our new training centre.',
            'poster' => $poster,
        ]]
    ),
    matrix_orlaith_useful_links_row([
        'Our present and future' => 'about-us/our-present-and-future',
        'Our research' => 'about-us/research',
        'Advocacy' => 'about-us/advocacy',
        'New hospital' => 'about-us/our-present-and-future/new-hospital',
    ]),
];

update_field('flexible_content_blocks', $flexi, $national_id);
if ($hero_img > 0) {
    set_post_thumbnail($national_id, $hero_img);
}
clean_post_cache($national_id);
$log[] = 'OK national accordion + video rebuilt';

// -------------------------------------------------------------------------
// Draft + redirect standalone sub-pages (keep healthcare Training Centre)
// -------------------------------------------------------------------------
$fold_paths = [
    'about-us/our-present-and-future/advocacy-centre',
    'about-us/our-present-and-future/academic-institute',
    'about-us/our-present-and-future/traning-centre',
    'about-us/our-present-and-future/training-centre',
];
$national_url = '/about-us/our-present-and-future/national-centre/';

foreach ($fold_paths as $path) {
    $page = get_page_by_path($path);
    if (! $page instanceof WP_Post) {
        $log[] = 'SKIP missing ' . $path;
        continue;
    }
    // Never draft the healthcare-professionals Training Centre.
    $permalink = (string) get_permalink($page);
    if (str_contains($permalink, '/healthcare-professionals/')) {
        $log[] = 'SKIP healthcare training #' . $page->ID;
        continue;
    }
    wp_update_post([
        'ID' => (int) $page->ID,
        'post_status' => 'draft',
    ]);
    clean_post_cache((int) $page->ID);
    $log[] = 'OK drafted #' . $page->ID . ' ' . $path;
}

$map_path = get_template_directory() . '/inc/data/path-redirect-map.json';
$map = json_decode((string) file_get_contents($map_path), true);
if (! is_array($map)) {
    $map = [];
}
$redirect_keys = [
    'about-us/our-present-and-future/advocacy-centre',
    'advocacy-centre',
    'about-us/our-present-and-future/academic-institute',
    'academic-institute',
    'about-us/our-present-and-future/traning-centre',
    'about-us/our-present-and-future/training-centre',
    'traning-centre',
];
foreach ($redirect_keys as $key) {
    // Don't override healthcare training centre redirects if any point there.
    if ($key === 'training-centre' || $key === 'healthcare-professionals/training-centre') {
        continue;
    }
    $map[$key] = $national_url;
}
// Explicit: old typo slug → national (override previous training-centre remap).
$map['about-us/our-present-and-future/traning-centre'] = $national_url;
$map['about-us/our-present-and-future/training-centre'] = $national_url;

file_put_contents(
    $map_path,
    wp_json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);
$log[] = 'OK redirect map updated → national-centre';

// Drop from menus if present.
$menus = wp_get_nav_menus();
foreach ($menus as $menu) {
    $items = wp_get_nav_menu_items($menu->term_id);
    if (! is_array($items)) {
        continue;
    }
    foreach ($items as $item) {
        if ((string) $item->object !== 'page') {
            continue;
        }
        $object_id = (int) $item->object_id;
        foreach ($fold_paths as $path) {
            $page = get_page_by_path($path);
            if ($page && (int) $page->ID === $object_id) {
                wp_delete_post((int) $item->ID, true);
                $log[] = 'OK removed menu item #' . $item->ID . ' from ' . $menu->name;
            }
        }
    }
}

echo implode("\n", $log) . "\n";
