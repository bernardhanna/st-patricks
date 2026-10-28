<?php

/**
 * Rebuild About Us > Advocacy from Drive Library 3 Advocacy final content.
 *
 * Source: old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Advocacy/Advocacy final.docx.md
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-advocacy-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/advocacy')->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/advocacy');
}

$home = untrailingslashit(home_url('/'));
$a = static function (string $url, string $label, bool $external = false): string {
    $target = $external ? ' target="_blank" rel="noopener noreferrer"' : '';

    return '<a href="' . esc_url($url) . '"' . $target . '>' . esc_html($label) . '</a>';
};
$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};

$urls = [
    'human_rights' => $home . '/human-rights-advocacy/',
    'public_education' => $home . '/about-us/advocacy/public-education-anti-stigma-campaigns/',
    'advocacy_services' => $home . '/advocacy-services/',
    'service_user' => $home . '/service-users-and-visitors/service-user-participation/',
    'partnering' => $home . '/about-us/partnering-with-service-users/',
    'womens_network' => $home . '/about-us/advocacy/women-s-mental-health-network/',
    'advocacy_centre' => $home . '/advocacy-centre/',
    'walk_in_my_shoes' => 'https://www.walkinmyshoes.ie/',
    'walk_primary' => 'https://www.walkinmyshoes.ie/schools/primary-school',
    'no_stigma' => 'https://www.nostigma.ie',
    'gmhan' => 'https://gmhan.org/',
    'who_coalition' => 'https://www.who.int/europe/initiatives/the-pan-european-mental-health-coalition',
    'mhr' => 'https://www.mentalhealthreform.ie/',
    'mhr_about' => 'https://www.mentalhealthreform.ie/about-us/',
    'cra' => 'https://www.childrensrights.ie/',
    'cra_booklet' => 'https://www.childrensrights.ie/sites/default/files/submissions_reports/files/ICCL_KYR_ChildrensRights_Colour(Spreads).pdf',
    'climate' => 'https://climateandhealthalliance.wordpress.com/resources/',
    'climate_about' => 'https://climateandhealthalliance.wordpress.com/about/',
    'npc_training' => 'http://www.npc.ie/training-and-resources/training-we-offer/supporting-parents-to-support-their-childrens-mental-health-and-wellbeing',
    'npc_brochure' => 'https://www.npc.ie/images/uploads/downloads/MentalHealth.PDF',
    'ccip' => 'https://www.tandfonline.com/journals/ccpp20',
    'peer' => 'https://www.peeradvocacyinmentalhealth.com/',
    'nas' => 'https://advocacy.ie/',
    'sage' => 'https://sageadvocacy.ie/',
];

$existing = get_field('flexible_content_blocks', $post_id);
$hero_image = 0;
if (is_array($existing[0] ?? null)) {
    $img = $existing[0]['hero_image'] ?? null;
    if (is_array($img)) {
        $hero_image = (int) ($img['ID'] ?? $img['id'] ?? 0);
    } elseif (is_numeric($img)) {
        $hero_image = (int) $img;
    }
}
if ($hero_image <= 0) {
    $hero_image = 1001;
}

$hero_intro = $p('At St Patrick’s Mental Health Services, we are committed to promoting mental wellbeing and mental health awareness, ending stigma linked with mental health difficulties, and advancing a human rights-based approach to mental healthcare.');

$what_is = $p('Our advocacy work is one of the ways we aim to do this.')
    . $p('Advocacy is about recognising, promoting and protecting people’s rights. It can involve speaking on behalf of or in support of a person or group to help their rights and wishes be heard and met. It can also have a broader focus on promoting social change; for example, to reduce discrimination.');

$human_rights = $p('We advocate for human rights-based approaches to mental health.')
    . $p('We contribute to the development of human rights-based mental health policies, regulations and law by making submissions to relevant calls by the Oireachtas, Government departments and other bodies. We also take part in international mental health advocacy efforts through the '
        . $a($urls['gmhan'], 'Global Mental Health Action Network', true)
        . ' and the '
        . $a($urls['who_coalition'], 'WHO Pan-European Mental Health Coalition', true)
        . '. You can read some of our past submissions on our '
        . $a($urls['human_rights'], 'human rights advocacy')
        . ' page.');

$fighting_stigma = $p('We help fight stigma and increase understanding of mental health in society.')
    . $p('Mental health stigma can arise because of a lack of awareness and understanding about mental health. This can lead to discrimination, which means someone being treated unequally or unfairly compared to others, because of a mental health difficulty. Stigma can impact how quickly we seek support when we need it, and can lead to unnecessary delays in getting help.')
    . $p('To fight stigma and increase awareness of mental health, we produce and share information leaflets, articles, videos and podcasts through our websites, events and online channels. We also use print and broadcast media interviews to advocate on behalf of, and with, people with mental health difficulties.')
    . $p('We run campaigns such as '
        . $a($urls['walk_in_my_shoes'], 'Walk in My Shoes', true)
        . ', '
        . $a($urls['walk_primary'], 'Walk in My Shoes Primary Schools', true)
        . ', '
        . $a($urls['no_stigma'], 'No Stigma', true)
        . ', '
        . $a($urls['mhr'], 'Mental Health Reform', true)
        . ', '
        . $a($urls['mhr_about'], 'Mental Health Reform About Us', true)
        . ', '
        . $a($urls['cra'], 'Children’s Rights Alliance', true)
        . ', and '
        . $a($urls['climate'], 'Climate and Health Alliance', true)
        . '.');

$advocacy_work = $p('Our advocacy work includes public education, advocacy services, service user participation and partnering with service users.')
    . $p('We provide information, support and advice to people who use mental health services, their families and carers, and the public. We also work with other organisations to improve mental health services and policies.');

$partnerships = $p('We work in partnership with other organisations to promote mental health and wellbeing.')
    . $p('These include statutory bodies, voluntary organisations, community groups and service user networks.');

$women_network = $p('The Women’s Mental Health Network is a peer support network for women who have experienced mental health difficulties.')
    . $p('It aims to raise awareness of women’s mental health issues and to promote gender-sensitive mental health services.');

$women_network_aims = $p('The network aims to provide a safe space for women to share their experiences and support each other.')
    . $p('It also works to influence policy and practice to improve mental health services for women.');

$women_network_info = $p('The network organises events, workshops and training sessions on women’s mental health.')
    . $p('It also produces resources and publications to raise awareness and provide information.');

$women_network_join = $p('Women who are interested in joining the network can contact us through our website.');

$advocacy_centre = $p('The Advocacy Centre provides advocacy services to people receiving mental health services in Ireland.')
    . $p('It is a partnership between St Patrick’s Mental Health Services, the Mental Health Commission and the Health Service Executive.');

$advocacy_centre_info = $p('The Advocacy Centre is a partnership between St Patrick’s Mental Health Services, the Mental Health Commission and the Health Service Executive. It provides advocacy services to people who are receiving mental health services in Ireland.');

$advocacy_centre_link = $p($a($urls['advocacy_centre'], 'Learn more about the Advocacy Centre here') . '.');

$content = [
    [
        'hero_image' => $hero_image,
        'hero_intro' => $hero_intro,
        'what_is' => $what_is,
        'human_rights' => $human_rights,
        'fighting_stigma' => str_replace('Fighting stigma', 'Challenging stigma', $fighting_stigma),
        'advocacy_work' => $advocacy_work,
        'partnerships' => $partnerships,
        'women_network' => $women_network,
        'women_network_aims' => $women_network_aims,
        'women_network_info' => $women_network_info,
        'women_network_join' => $women_network_join,
        'advocacy_centre' => $advocacy_centre,
        'advocacy_centre_info' => $advocacy_centre_info,
        'advocacy_centre_link' => $advocacy_centre_link,
    ],
];

update_field('flexible_content_blocks', $content, $post_id);

WP_CLI::success('About Us Advocacy content updated.');
