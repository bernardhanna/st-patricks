<?php

/**
 * Seed About Mental Health page (Figma 2780:4050).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-mental-health.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/mental-health-functions.php';

$post_id = (int) (get_page_by_path('service-users-and-visitors/about-mental-health')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at service-users-and-visitors/about-mental-health.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_import_remote_image')) {
    function matrix_seed_import_remote_image(string $url, string $title, string $cache_key): int
    {
        if ($url === '') {
            return 0;
        }

        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_figma_key',
                    'value' => $cache_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            return (int) $existing[0]->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($url, 30);

        if (is_wp_error($tmp)) {
            return 0;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename($path) : 'figma-asset.jpg';

        if (! preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $filename)) {
            $filename .= '.jpg';
        }

        $file_array = [
            'name' => sanitize_file_name($filename),
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, $title);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);

            return 0;
        }

        update_post_meta($attachment_id, '_matrix_seed_figma_key', $cache_key);
        update_post_meta($attachment_id, '_matrix_seed_figma_url', $url);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_seed_resolve_image')) {
    function matrix_seed_resolve_image(string $figma_url, string $cache_key, string $title): int
    {
        $id = matrix_seed_import_remote_image($figma_url, $title, $cache_key);

        if ($id > 0) {
            return $id;
        }

        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'post_mime_type' => 'image',
            'orderby' => 'ID',
            'order' => 'DESC',
        ]);

        return $attachments !== [] ? (int) $attachments[0]->ID : 0;
    }
}

if (! function_exists('matrix_seed_accordion_item')) {
    function matrix_seed_accordion_item(string $title, string $content, bool $starts_open = false): array
    {
        return [
            'title' => $title,
            'starts_open' => $starts_open ? 1 : 0,
            'content_rows' => [
                [
                    'row_type' => 'text',
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $content,
                ],
            ],
        ];
    }
}

if (! function_exists('matrix_seed_amh_url')) {
    function matrix_seed_amh_url(string $path): string
    {
        return esc_url(home_url('/' . trim($path, '/') . '/'));
    }
}

if (! function_exists('matrix_seed_amh_link')) {
    function matrix_seed_amh_link(string $path, string $label): string
    {
        return '<a href="' . matrix_seed_amh_url($path) . '">' . esc_html($label) . '</a>';
    }
}

$home = home_url('/');
$service_users_url = home_url('/service-users-and-visitors/');
$mental_health_url = home_url('/mental-health/');
$anxiety_url = matrix_mental_health_condition_url('anxiety');
$programme_url = matrix_seed_amh_url('care-treatment/anxiety-disorders-programme');
$faqs_url = matrix_seed_amh_url('service-users-and-visitors/frequently-asked-questions-faqs');
$information_centre_url = matrix_seed_amh_url('information-centre');
$anxiety_referrals_post_url = get_permalink(1231) ?: matrix_seed_amh_url('new-research-shows-surge-in-number-of-referrals-to-mental-health-services-for-anxiety');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/930ef003-aa87-435d-ab1d-2cea7d7c0b7c',
    'anxiety_image' => 'https://www.figma.com/api/mcp/asset/8b68e6e2-0965-4ab5-9d74-d57cd3e2b713',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'about-mental-health-hero-2780-4050', 'About Mental Health hero');
$anxiety_image_id = matrix_seed_resolve_image($figma['anxiety_image'], 'about-mental-health-anxiety-2780-4050', 'What is anxiety illustration');

$hero_intro = "There is no health without mental health. Here at St Patrick's Mental Health Services, we have been looking after the mental wellbeing of Ireland for over 270 years. Having access to the facts that will help you understand your mental health can play a key part in your road to recovery.";

$what_is_anxiety_intro = '<p><strong>We all experience anxiety from time to time; it is often a normal response to stressful situations.</strong></p>';
$what_is_anxiety_body = '<p>When you are in a challenging situation, your body releases hormones, such as adrenaline, which cause physical reactions in your body. This is known as the ‘fight or flight response’; it is your body’s way of ensuring we are alert and can respond to danger.</p>'
    . '<p>At times, feeling high levels of anxiety and the ‘fight or flight response’ is very normal.</p>'
    . '<p>Anxiety can be a problem if you begin to regularly feel anxious when there is no threat present or your feelings of anxiety are greater than the actual danger of a situation. If you are having extreme feelings of anxiety for a long time or they are stopping you from doing your usual activities, you may be living with an anxiety disorder.</p>'
    . '<p>Anxiety disorders are <a href="' . esc_url($anxiety_referrals_post_url) . '">very common</a>. While we don’t have exact figures for Ireland, roughly one in nine people will experience an anxiety disorder in their lifetime.</p>'
    . '<p>The main features of anxiety disorders are:</p>'
    . '<ul>'
    . '<li>Altered physical sensations (palpitations, nausea)</li>'
    . '<li>Altered thoughts (irrational thinking, worry)</li>'
    . '<li>Altered behaviour (restlessness, avoidance)</li>'
    . '<li>Altered emotions (fear, panic).</li>'
    . '</ul>';

$causes_body = '<p>Anxiety disorders can begin at any time in our lives, from childhood and adolescence to adulthood.</p>'
    . '<p>Anxiety can be the primary, or main, mental health problem. This means that symptoms happen independently of ' . matrix_seed_amh_link('mental-health', 'other mental health problems') . ' and tend to follow a set pattern over several months or years; they can also feel more intense if we are going through stressful times.</p>'
    . '<p>Primary anxiety disorders are thought to result from a combination of our genetics and life stresses, which create a vicious cycle. Physical reactions in the brain and body interact with both distorted thoughts about danger and patterns of behavior, such as avoidance, to grow and maintain a state of anxiety.</p>'
    . '<p>Anxiety can also be a secondary problem, which means that it is a symptom of another mental health disorder. Often, one anxiety disorder can be related to another, such as agoraphobia combined with panic disorder. There are also links between anxiety disorders and other mental health conditions, such as ' . matrix_seed_amh_link('mental-health/depression', 'depression') . ' or ' . matrix_seed_amh_link('mental-health/addiction-dual-diagnosis', 'substance misuse') . '; in these cases, the underlying problem should be treated rather than the symptoms of anxiety alone.</p>';

$treatment_intro = '<p><strong>Our Anxiety Disorders Programme provides care in an outpatient, day patient or inpatient setting, according to your needs. Please note that PTSD is not part of the Anxiety Disorders Programme; however, our Anxiety Disorders Service will assess PTSD and treat it individually.</strong></p>';
$treatment_body = '<p>' . matrix_seed_amh_link('programmes-therapies/cognitive-behavioural-therapy', 'Cognitive Behavioural Therapy') . ' (CBT) is highly effective in treating anxiety disorders. By learning about the vicious cycle of anxiety and challenging unhelpful beliefs and behaviours, you can gradually master your fears, grow your confidence and regain your functioning through CBT.</p>'
    . '<p>CBT can also be supported by other forms of help, such as <a href="https://www.youtube.com/watch?v=wYAUm3XL7dM" target="_blank" rel="noopener noreferrer">mindfulness</a>, meditation and occupational therapy.</p>'
    . '<p>You may also benefit from taking ' . matrix_seed_amh_link('service-users-and-visitors/medication', 'medication') . '. For example, sedative anti-anxiety drugs can be used for a short time to ease the worst anxiety symptoms. Serotonin boosting anti-depressant drugs can also be very helpful in easing anxiety and supporting CBT work.</p>';

$programme_body = '<p>The Anxiety Disorders Programme was established by St Patrick\'s Mental Health Services in April 2005 and caters for a wide range of anxiety disorders. It is delivered by a multidisciplinary team offering service users a combination of cognitive behavioural therapy (CBT), psychiatry, and occupational therapy.</p>'
    . '<p>The Programme is facilitated in the Thomson Centre, St Patrick\'s University Hospital. Specialised anxiety disorder assessments and individual cognitive behavioural psychotherapy is provided at Dean St Patrick\'s Clinic.</p>'
    . '<p>The Anxiety Disorders Programme, based on Cognitive Behavioural Therapy (CBT) and compassion mindfulness-based approaches, focuses on addressing the physical, psychological and behavioural aspects of anxiety disorders using group psychotherapy.</p>';

$anxiety_types_intro = '<p>There are several recognised types of anxiety disorders. The sections below explain some of the most common forms and how they can affect daily life.</p>';

$accordion_items = [
    matrix_seed_accordion_item(
        'Generalised Anxiety Disorder',
        '<p>Generalised Anxiety Disorder (GAD) is a mental health condition where you find it difficult to control worry.</p>'
        . '<p>If you are living with GAD, you may spend a lot of time worrying about everyday situations, rather than single events or specific threats. You may also be very concerned about what might go wrong in the future. The anxiety coming from this worry can cause a lot of distress and may affect your social, work and home life.</p>'
        . '<p>Symptoms include distress, sleep disturbance and difficulty concentrating. GAD is one of the most common anxiety disorders, affecting between 2% and 8% of the population. It can start at any time in life from childhood to adulthood.</p>'
    ),
    matrix_seed_accordion_item(
        'Panic disorder',
        '<p>A panic attack is the body\'s way of responding to the ‘flight or fight’ response system being triggered when there is no actual external threat or danger present.</p>'
        . '<p>Approximately 20% of people will experience at least one panic attack at some time in their lives. Panic disorder is when you have sudden episodes of severe anxiety or panic, linked with a fear of death or collapse. The key feature is the sudden onset of panic attacks with no clear reason or trigger.</p>'
    ),
    matrix_seed_accordion_item(
        'Agoraphobia',
        '<p>Agoraphobia is a condition where you feel anxious about being in situations where help is not easily available or that might be difficult to escape from.</p>'
        . '<p>Examples include going outside of home alone, being in crowded public places, using public transport, or being in enclosed spaces such as tunnels or lifts. Panic disorder is commonly linked with agoraphobia.</p>'
    ),
    matrix_seed_accordion_item(
        'Social anxiety',
        '<p>Social anxiety is when you feel intensely anxious and self-conscious in social situations. This is marked by fears of being judged negatively or appearing foolish.</p>'
        . '<p>More than one in eight people will experience social anxiety disorder at some point in their lives. For most people, it begins in the teenage years, but it can happen at any time.</p>'
    ),
    matrix_seed_accordion_item(
        'Obsessive Compulsive Disorder',
        '<p>Obsessive Compulsive Disorder (OCD) is a mental health condition where you have repeated and upsetting thoughts and behaviours. You often have a higher sense of responsibility for preventing harm and an intense awareness of risk and danger.</p>'
        . '<p>It is thought that 1% of the population lives with OCD at some point in their lives. OCD can begin in childhood, but it usually starts during teenage or early adult years.</p>'
    ),
    matrix_seed_accordion_item(
        'Post-Traumatic Stress Disorder',
        '<p>Post-Traumatic Stress Disorder (PTSD) is an anxiety disorder that develops after very frightening, upsetting or overwhelming events.</p>'
        . '<p>If you are living with PTSD, you may have distressing memories or flashbacks of the event, avoid reminders of the event, withdraw from other people, be more alert to threat and danger, or have disturbed sleep.</p>'
    ),
    matrix_seed_accordion_item(
        'Specific phobias',
        '<p>A specific phobia is when we feel an intense fear of a particular object or situation that poses little or no actual danger.</p>'
        . '<p>Phobias are often present from childhood and affect roughly 10% of the population. The most common specific phobias include fear of closed spaces, water, animals, heights, blood or injury, and fear of becoming sick with or dying from a specific illness.</p>',
        true
    ),
];

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Service users and visitors', 'url' => $service_users_url, 'target' => '']],
        ],
        'current_crumb_label' => 'About Mental Health',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'About Mental Health',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => [
            'title' => 'Explore mental health conditions',
            'url' => $mental_health_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'What is anxiety?',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => $what_is_anxiety_intro,
        'content' => $what_is_anxiety_body,
        'primary_button' => [
            'title' => 'Read more about anxiety',
            'url' => $anxiety_url,
            'target' => '',
        ],
        'primary_button_variant' => 'outline',
        'image' => $anxiety_image_id,
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'What causes anxiety disorders?',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $causes_body,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Is there treatment for anxiety?',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => $treatment_intro,
        'content' => $treatment_body,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Anxiety Disorders Programme',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $programme_body,
        'primary_button' => [
            'title' => 'Anxiety Disorders Programme',
            'url' => $programme_url,
            'target' => '',
        ],
        'primary_button_variant' => 'outline',
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Are there different types of anxiety disorders?',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $anxiety_types_intro,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'section_background' => '#FBFAF7',
        'panel_background' => '#FFFFFF',
        'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'items' => $accordion_items,
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded About Mental Health page (%d) with %d flexi blocks.',
            $post_id,
            $saved_count
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $post_id,
            count($flexi_rows),
            $saved_count
        ));
    }

    WP_CLI::log('Page: ' . get_permalink($post_id));
}
