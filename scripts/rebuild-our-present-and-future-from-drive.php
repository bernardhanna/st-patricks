<?php

/**
 * Rebuild About Us > Our present and future from Drive Library 3.
 *
 * Sources:
 * - Our present and future page.docx
 * - Image for page banner (already in Media Library).docx
 * - Images for videos slider/
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-our-present-and-future-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$post_id = (int) (get_page_by_path('about-us/our-present-and-future')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/our-present-and-future');
}

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};

$ul = static function (array $items): string {
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ul>';
};

$drive_dir = get_template_directory()
    . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Our present and future';
$slider_dir = $drive_dir . '/Images for videos slider';

$sideload_local = static function (string $path, string $title) use ($post_id): int {
    if (! is_readable($path)) {
        return 0;
    }

    $basename = basename($path);
    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => '_matrix_drive_source',
            'value' => $basename,
        ]],
    ]);
    if ($existing !== []) {
        return (int) $existing[0];
    }

    $tmp = wp_tempnam($basename);
    if (! $tmp || ! copy($path, $tmp)) {
        return 0;
    }

    $attachment_id = media_handle_sideload([
        'name' => $basename,
        'tmp_name' => $tmp,
    ], $post_id, $title);

    if (is_wp_error($attachment_id)) {
        @unlink($tmp);

        return 0;
    }

    update_post_meta((int) $attachment_id, '_matrix_drive_source', $basename);

    return (int) $attachment_id;
};

$banner_id = 835;
if (get_post_type($banner_id) !== 'attachment') {
    $banner_id = (int) get_post_thumbnail_id($post_id);
}

$posters = [
    'strategy' => $sideload_local($slider_dir . '/The Future in Mind strategy.png', 'The Future in Mind strategy'),
    'service' => $sideload_local($slider_dir . '/Service delivery.png', 'Service delivery'),
    'advocacy' => $sideload_local($slider_dir . '/Advocacy and education.png', 'Advocacy and education'),
    'research' => $sideload_local($slider_dir . '/Research and Training Video.png', 'Research and Training'),
    'engagement' => $sideload_local($slider_dir . '/Service user engagement.png', 'Service user engagement'),
    'ops' => $sideload_local($slider_dir . '/Operational excellence.png', 'Operational excellence'),
];

$hero_intro = $p("Here in St Patrick's Mental Health Services (SPMHS), our organisational strategy guides our priorities today and our direction for the future. It sets out how we will continue to develop our services, advance mental health promotion, and maximise our impact for individuals, families and communities.");

$strategy = $p('The Future in Mind is our strategic plan for 2023 to 2027.')
    . $p('This strategy is underpinned by a human rights-based and recovery-focused ethos. It commits us to continuing to provide access to the highest quality mental healthcare to as many people as possible who experience mental health difficulties and require our services. We also commit to promoting mentally healthy living and to strengthening understanding and awareness of mental health difficulties.')
    . $p('The strategy was developed in consultation with our service users, staff, Board of Governors and other key stakeholders.');

$objectives = [
    'Mental healthcare' => $p('To provide the highest quality support and treatment to people experiencing mental health difficulties.'),
    'Partnership with service users' => $p('To involve service users as equal partners in the planning, management and evaluation of the treatment and support they receive within SPMHS.'),
    'Research' => $p('To enhance our evidence-based understanding of mental health difficulties, and their treatment, through research.'),
    'Training' => $p('To support staff and organisations working in mental health to maintain and improve their skills and develop new competencies.'),
    'Human rights advocacy' => $p('To ensure mental healthcare, prevention strategies and promotion efforts for children and adults in Ireland are grounded in human rights and adhere to key human rights conventions.'),
    'Education' => $p('To educate and empower more people to live a mentally healthy life by promoting greater public understanding and awareness of mental health and reducing stigma.'),
];

$critical_intro = $p('We recognise what is happening in Ireland and the critical issues that we face in the mental healthcare sector - not only at an organisational level, but as a society, as a European country, and as part of a globally connected world.');

$critical_items = [
    'National issues' => $ul([
        'The availability of mental healthcare services, long waiting lists, and lack of funding and resources remain a challenge.',
        'The quality of mental healthcare in Ireland is inconsistent, with many services not compliant with the statutory quality standards set by the Mental Health Commission.',
        'Stigma around mental health is negatively impacting society and preventing people from seeking support.',
        'Recruitment and retention of staff in mental health services has become more challenging.',
    ]),
    'Global issues' => $ul([
        'Geopolitical uncertainty, such as wealth inequality, war, immigration and political discrimination, affects all of our daily lives.',
        'The COVID-19 pandemic has impacted people’s mental health, with long-term effects yet to truly emerge.',
        'The uncertainty of climate change and its impact are a source of anxiety for many; those experiencing mental health difficulties may be particularly at risk.',
        'Digital literacy and skills deficits need to be addressed so that everyone who would benefit from digital technologies in mental healthcare can have equal access.',
    ]),
];

$values = [
    'Protecting the rights of people experiencing mental health difficulties' => $p('People experiencing mental health difficulties should be treated with respect and dignity, be protected against discrimination, and have full inclusion and equality. Independence, autonomy and the opportunity to make decisions about their care and treatment are essential.'),
    'Quality care' => $p('The treatment of people who experience mental health difficulties should be individualised, grounded in evidence-based best practice, and in line with the principles of recovery-focused care and trauma-informed care.'),
    'Service user involvement' => $p('The unique experiences, skills and abilities of our service users enable them to provide expert advice. Ensuring the active involvement of our service users in their care, and in how our services develop underpins any strategic projects we undertake.'),
    'Innovation' => $p('Developing innovations in treatment and delivery of mental healthcare is vital in responding to the changing mental health needs of the population and in future-proofing mental health services.'),
    'Diversity, equality and inclusion' => $p('Different perspectives should be valued to strengthen workplace culture and service delivery. The mental healthcare workforce should reflect the diversity of its service users, so that, no matter who requires services, there is a member of staff who can identify with them, communicate with them and better serve their individual needs.'),
    'Environmental stewardship' => $p('We have a duty to protect against climate change through embedding sustainability and biodiversity best practice. We must engage service users in the development of collaborative and sustainable mental health services. All efforts should be made to eliminate wasteful activity and make use of low-carbon alternatives. We should contribute our skills and expertise to educate, empower and advocate for positive climate action so that we can, individually and collectively, act as agents of change for our planet.'),
];

$issuu = '<iframe title="The Future in Mind: Strategy 2023-2027" allow="clipboard-write; autoplay; encrypted-media; fullscreen; picture-in-picture" sandbox="allow-top-navigation allow-top-navigation-by-user-activation allow-downloads allow-scripts allow-same-origin allow-popups allow-modals allow-popups-to-escape-sandbox allow-forms" allowfullscreen="true" style="position:absolute;border:none;width:100%;height:100%;left:0;right:0;top:0;bottom:0;" src="https://e.issuu.com/embed.html?d=spmhs_strategy_web_fa&amp;u=stpatricksmentalhealthservices"></iframe>';
if (function_exists('matrix_normalize_absolute_embeds_in_html')) {
    $issuu = matrix_normalize_absolute_embeds_in_html($issuu);
}

$future = $p('What gives us certainty in our plans and the resilience to keep moving forward? The possibility of recovery for all.')
    . $p('Our 2023 to 2027 strategy, The Future in Mind, reflects the many changes seen by society and the ever-changing landscape of mental health in Ireland, to ensure that we are prepared and equipped to respond appropriately to the diverse and dynamic mental health needs of a society that is constantly evolving.')
    . $p('As we continue to implement our strategy, we remain focused on delivering its ambitious aims and advancing our vision of a society where more people are empowered and given an opportunity to live mentally healthy lives.')
    . $issuu;

$hero = matrix_orlaith_hero_row('Our present and future', $hero_intro, $banner_id);

$flexi = [
    $hero,
    matrix_orlaith_content_row('Our strategy today', $strategy, 'white'),
    matrix_orlaith_accordion_row($objectives, 'default', 'Our objectives for 2023 to 2027'),
    matrix_orlaith_video_row(
        'Video resources',
        $p('Watch a series of short videos exploring our strategic objectives.'),
        [
            [
                'url' => 'https://www.youtube.com/watch?v=-8t1qfAk0qc',
                'caption' => 'See how our strategy stays true to our founding principles, while committing us to developing new services and promoting mental health awareness.',
                'poster' => $posters['strategy'],
            ],
            [
                'url' => 'https://www.youtube.com/watch?v=ZkX7DqQE_60',
                'caption' => 'Learn more about how we plan to enhance and expand our service delivery.',
                'poster' => $posters['service'],
            ],
            [
                'url' => 'https://www.youtube.com/watch?v=b2bj9aNjxRE',
                'caption' => 'See how the establishment of an interactive Education Centre and ongoing development of our Advocacy Centre will help us to empower more people to live a mentally healthy life.',
                'poster' => $posters['advocacy'],
            ],
            [
                'url' => 'https://www.youtube.com/watch?v=AjJQxOrmv1o',
                'caption' => 'Learn more about our Academic Institute and our commitment to supporting staff and organisations working in mental health through our new training centre.',
                'poster' => $posters['research'],
            ],
            [
                'url' => 'https://www.youtube.com/watch?v=MZHEqYPaRJk',
                'caption' => 'Find out more about how we are continuing to involve our service users in shaping how we evolve, while also developing new ways to partner with service users.',
                'poster' => $posters['engagement'],
            ],
            [
                'url' => 'https://www.youtube.com/watch?v=msSxhK4RJ7A',
                'caption' => 'See how we are ensuring that our organisation is flexible and adaptable to the ever-evolving needs of the people who use our mental health services.',
                'poster' => $posters['ops'],
            ],
        ]
    ),
    matrix_orlaith_content_row('Critical issues', $critical_intro, 'cream'),
    matrix_orlaith_accordion_row($critical_items),
    matrix_orlaith_accordion_row($values, 'default', 'Our values'),
    matrix_orlaith_content_row('Our future', $future, 'white'),
];

update_field('flexible_content_blocks', $flexi, $post_id);
if ($banner_id > 0) {
    set_post_thumbnail($post_id, $banner_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Our present and future #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $extra = '';
    if ($layout === 'video_showcase' && ! empty($row['slides'])) {
        $extra = ' | slides=' . count($row['slides']);
    }
    if ($layout === 'content_accordion' && ! empty($row['items'])) {
        $extra = ' | items=' . count($row['items']);
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}

WP_CLI::log('Posters: ' . wp_json_encode($posters));
