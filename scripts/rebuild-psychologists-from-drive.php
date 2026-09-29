<?php

/**
 * Rebuild About Us > Psychologists from Drive Library 3 content.
 *
 * Sources:
 * - old/content/.../Clinical psychologists/Pscyhology page.docx
 * - old/content/.../Clinical psychologists/Psychology page layout.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-psychologists-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/clinical-psychologists')?->ID
    ?? get_page_by_path('about-us/psychologists')?->ID
    ?? 0);

if ($post_id === 0) {
    $found = get_posts([
        'name' => 'clinical-psychologists',
        'post_type' => 'page',
        'post_status' => 'any',
        'numberposts' => 1,
    ]);
    $post_id = $found !== [] ? (int) $found[0]->ID : 0;
}

if ($post_id === 0) {
    WP_CLI::error('Could not find Psychologists page');
}

$home = untrailingslashit(home_url('/'));
$img_id = 4026;
$drive_img = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Clinical psychologists/Psychologists.png';

$found_img = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    's' => 'Psychologists',
]);
if ($found_img !== []) {
    $img_id = (int) $found_img[0];
} elseif (is_file($drive_img)) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $tmp = wp_tempnam('Psychologists.png');
    copy($drive_img, $tmp);
    $sideloaded = media_handle_sideload([
        'name' => 'Psychologists.png',
        'tmp_name' => $tmp,
    ], $post_id);
    if (! is_wp_error($sideloaded)) {
        $img_id = (int) $sideloaded;
    }
}

$day_programmes = $home . '/what-we-offer/day-programmes/';
$apply = $home . '/recruitment-and-useful-information/how-to-apply-for-a-role/';
$careers = $home . '/careers/';

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$hero_intro = $p('Welcome to our Psychology Department here in St Patrick’s Mental Health Services (SPMHS).');

$about = $p('Our Psychology Department is made up of a team of clinical and counselling psychologists who provide a range of treatment to adolescents, adults, and older adults.')
    . $p('As a team, we believe that psychological distress is often an understandable response to the person’s past and present experiences. Psychological formulation is a framework that links the connections between a person’s individual behaviours, experiences and characteristics. Rather than focusing solely on a person’s diagnosis, we take a formulation-based, trauma-informed approach to psychological distress.')
    . $p('We work collaboratively with service users, using a compassionate, narrative and person-centred approach. Together, we develop a shared understanding of the person’s unique life experiences that may have contributed to their distress. This work forms the basis for guiding personalised and effective interventions.');

$groups = $p('We provide a range of evidence-based outpatient group programmes and therapies such as Compassion-Focused Therapy (CFT), Dialectical Behaviour Therapy and Group Schema Therapy. The aim of these groups is to empower individuals both personally and interpersonally.')
    . $p('Our programmes are also designed for specific age ranges. For example, we deliver an Adolescent Psychology Skill Group, which invites both adolescents and parents to attend, and an Emotion-Focused Therapy group for young adults. Our older adult psychology-led groups offer both brief and longer-term group psychological interventions, including Coping for Older Adults, SAGE and CFT for Older Adults.');

$research = $p('As clinicians, we are guided by evidence-based practices to offer the highest standard of care. We also contribute to the evidence body by conducting research that furthers our understanding of the unique needs of service users.')
    . $p('Research has allowed us to refine and improve existing interventions. Our research is both qualitative and quantitative. This allows for service users to give feedback and help us develop and evaluate innovative group interventions, such as Group Radical Openness, the Formulation Group, the Trauma Programme, and CFT for Eating Disorders.');

$training = $p('We are committed to attending external expert supervision and ongoing Continuous Professional Development, so that our wide range of specialised interventions are delivered to the highest standard.')
    . $p('We also place a real value on training the next generation of psychologists. We currently sponsor two trainees a year as part of the University College Dublin Doctoral Programme in Clinical Psychology.');

$team_intro = $p('Adjunct Professor Clodagh Dowling is our Director of Psychology.')
    . $p('You can see our full team below. If you are interested in joining our Psychology Department, we would love to hear from you.')
    . $p('You can find our current vacancies ' . $a($careers, 'further below') . '.')
    . $p('If a role you are interested in is currently not listed, you can make an enquiry to our Human Resources Department.');

$team_members = [
    'Adam Nolan, Psychologist in Clinical Training',
    'Aisling O’Neill, Psychologist in Clinical Training',
    'Adjunct Associate Professor Dr Conal Twomey, Senior Clinical Psychologist',
    'Adjunct Professor Dr Karen Looney, Principal Clinical Psychologist and Neuropsychologist',
    'Dr Aideen O’Neill, Senior Clinical Psychologist',
    'Dr Aoife Durcan, Senior Counselling Psychologist',
    'Dr Aoife O’Laoide, Senior Psychologist',
    'Dr Alison Clarke, Clinical Psychologist',
    'Dr Caroline Wheeler, Counselling Psychologist',
    'Dr Claire Atkins, Senior Clinical Psychologist',
    'Dr Claire Coffey, Senior Clinical Psychologist',
    'Dr Claire O’Sullivan, Senior Clinical Psychologist',
    'Dr Eimear Crowe, Senior Clinical Psychologist',
    'Dr Emer Long, Clinical Psychologist',
    'Dr Fionnuala McEnery, Senior Clinical Psychologist',
    'Dr Georgina Heffernan, Psychologist in Clinical Training',
    'Dr Hazel McCarthy, Senior Clinical Psychologist',
    'Dr James McElvaney, Senior Counselling Psychologist',
    'Dr Jessica O’Sullivan, Senior Clinical Psychologist',
    'Dr Julie Dorgan, Senior Psychologist',
    'Dr Karen Neylon, Senior Clinical Psychologist',
    'Dr Kevin O’Hanrahan, Senior Clinical Psychologist',
    'Dr Liesl Dart, Senior Clinical Psychologist',
    'Dr Mairéad Losty, Senior Clinical Psychologist',
    'Dr Maeve O’Connor, Clinical Psychologist',
    'Dr Mary Nolan, Senior Clinical Psychologist',
    'Dr Muireann O’Donnell, Senior Clinical Psychologist',
    'Dr Niamh Willis, Clinical Psychologist',
    'Dr Rachel Egan, Principal Clinical Psychologist',
    'Dr Rachel Frost, Clinical Psychologist',
    'Dr Rachel Parkinson, Senior Clinical Psychologist',
    'Dr Ruth Groarke, Senior Clinical Psychologist',
    'Dr Ruth Kevlin, Senior Clinical Psychologist',
    'Dr Sarah-Louise Tarpey, Senior Clinical Psychologist',
    'Dr Violet Johnstone, Senior Counselling Psychologist',
    'Dylan Moore, Principal Counselling Psychologist',
    'Elizabeth O’Brien, Psychologist in Clinical Training',
    'Emily Cleary, Psychologist in Clinical Training',
    'Maria McMorrow, Administrator',
    'Professor Gary O’Reilly, Principal Clinical Psychologist',
    'Tara Deehan, Senior Counselling Psychologist',
];

$team_list = '<ul class="list-plain">';
foreach ($team_members as $member) {
    $team_list .= '<li>' . esc_html($member) . '</li>';
}
$team_list .= '</ul>';

$hero = matrix_orlaith_hero_row('Psychologists', $hero_intro, $img_id);
$hero['layout_style'] = $img_id > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'Psychologists';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$flexi = [
    $hero,
    matrix_orlaith_content_row('', $about, 'white'),
    matrix_orlaith_content_row('Group programmes', $groups, 'cream', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('See more on our programmes', $day_programmes . '#select-programme-or-therapy'),
        'primary_button_variant' => 'filled',
    ]),
    matrix_orlaith_content_row('Research', $research, 'white'),
    matrix_orlaith_content_row('Training and development', $training, 'cream'),
    matrix_orlaith_content_row('Our team', $team_intro, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Make an enquiry', $apply),
        'primary_button_variant' => 'filled',
    ]),
    matrix_orlaith_accordion_row([
        'Team members' => $team_list,
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Psychologists',
    'post_name' => 'psychologists',
]);

update_field('flexible_content_blocks', $flexi, $post_id);
clean_post_cache($post_id);

WP_CLI::success('Rebuilt Psychologists page ' . $post_id . ' → ' . get_permalink($post_id));
$check = get_field('flexible_content_blocks', $post_id);
foreach ($check as $i => $row) {
    $extra = '';
    if (($row['acf_fc_layout'] ?? '') === 'content_accordion') {
        $html = $row['items'][0]['content_rows'][0]['content'] ?? '';
        $extra = ' (' . substr_count($html, '<li>') . ' members)';
    }
    WP_CLI::log(sprintf('[%d] %s %s%s', $i, $row['acf_fc_layout'] ?? '', $row['heading'] ?? '', $extra));
}
