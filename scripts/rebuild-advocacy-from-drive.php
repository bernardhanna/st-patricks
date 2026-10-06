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

$post_id = (int) (get_page_by_path('about-us/advocacy')?->ID ?? 0);
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
    'collaborative' => $home . '/about-us/advocacy/collaborative-efforts/',
    'advocacy_services' => $home . '/advocacy-services/',
    'youth_advocacy' => $home . '/about-us/advocacy/youth-advocacy/',
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
    'ccip' => 'https://www.childcareinpractice.org/about-us',
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
        . ', our flagship education campaign for young people, and '
        . $a($urls['no_stigma'], '#NoStigma', true)
        . ', which reimagines a society free from mental health stigma and discrimination by highlighting the positive impact on people’s lives when it’s absent.')
    . $p('These activities are developed with contributions from and in partnership with service users through our Service User and Supporters Council and our Service User Advisory Network. You can '
        . $a($urls['service_user'], 'learn more about service user partnership')
        . ' here.');

$partnerships = $p('An important part of mental health advocacy is working in partnership with others. We are a member of several advocacy alliances, including '
        . $a($urls['mhr'], 'Mental Health Reform', true)
        . ', '
        . $a($urls['cra'], 'Children’s Rights Alliance', true)
        . ' and the '
        . $a($urls['climate'], 'Climate and Health Alliance', true)
        . ', and run a Women’s Mental Health Network with the National Women’s Council (NWC).')
    . $p('We collaborate with other organisations on projects and shared actions to progress towards the ending of stigma and discrimination, and to advance rights-based approaches to mental health. Through philanthropic funding, we have supported diverse projects and organisations to advance mental health and wellbeing across society.')
    . $p('You can learn more about our partnerships below.')
    . '<h3>Women’s Mental Health Network</h3>'
    . $p('We have come together with the NWC to develop a Women’s Mental Health Network. This is a network of people and organisations with a committed interest in women’s mental health issues.')
    . $p('The Women’s Mental Health Network has two aims:')
    . '<ul>'
    . '<li>To provide a forum for information-sharing and networking</li>'
    . '<li>To advance interdisciplinary and multi-agency collaboration on women’s mental health issues.</li>'
    . '</ul>'
    . $p('We share information through newsletters and online updates, and organise education and networking events.')
    . $p($a($urls['womens_network'], 'Learn more and join the network here') . '.')
    . '<h3>Mental Health Reform</h3>'
    . $p('We are proud to be an associate member of Mental Health Reform and to support its important work.')
    . $p('Mental Health Reform is Ireland’s leading national coalition on mental health, campaigning for the progressive reform of mental health services and supports in Ireland.')
    . $p('You can ' . $a($urls['mhr_about'], 'learn more about Mental Health Reform’s work here', true) . '.')
    . '<h3>Children’s Rights Alliance</h3>'
    . $p('We are a member of the Children’s Rights Alliance, and proudly support its work.')
    . $p('Founded in 1995, the Children’s Rights Alliance unites over 100 members working together to make Ireland one of the best places in the world to be a child. It works to change the lives of all children in Ireland by making sure that their rights are respected and protected in our laws, policies and services.')
    . $p('You can ' . $a($urls['cra'], 'learn more about the Children’s Rights Alliance here', true) . '.')
    . $p('The Children’s Rights Alliance developed a Know Your Rights booklet on children’s rights with the Irish Council for Civil Liberties, and members of our '
        . $a($urls['youth_advocacy'], 'Youth Empowerment Service')
        . ' took part in its development. '
        . $a($urls['cra_booklet'], 'You can access the booklet here', true)
        . '.')
    . '<h3>Climate and Health Alliance</h3>'
    . $p('We are a member of the Irish Climate and Health Alliance, an alliance of over 30 public health organisations and advocacy groups. The Climate and Health Alliance “seeks to highlight the enormous public health harms that arise from climate change while emphasising the significant health benefits that can be unlocked by tackling global warming; provide a platform for health professionals and organisations to act; and advocate for greater government action in addressing the climate crisis so that the health benefits are attained.”')
    . $p('We contributed to its 2026 Clean Air, Healthier Ireland report, which you can access '
        . $a($urls['climate'], 'here', true)
        . ' to learn about the links between air pollution, air quality and our physical and mental health. You can learn more about the Climate and Health Alliance’s work '
        . $a($urls['climate_about'], 'here', true)
        . '.')
    . '<h3>First Fortnight</h3>'
    . $p('We are proud to support and take part in the annual First Fortnight mental health, arts and culture festival. We have participated in a range of creative and cultural activities and events for service users and the public, including onsite music, poetry and spoken word events.')
    . '<h3>National Parents Council</h3>'
    . $p('In Ireland, the National Parent’s Council (NPC) is the “only representative organisation for parents of children in primary or early education”. It works to empower parents to support their children throughout their early and primary school years and believes that children should have a say in issues affecting their educational lives.')
    . $p('We have partnered with the NPC to create mental health awareness training for parents of '
        . $a($urls['walk_primary'], 'primary school children', true)
        . '. This programme supports parents to encourage and promote positive mental health and wellbeing in their children. It also explores how building resilience in children helps them to manage and cope with the day-to-day stresses of life as they occur. Training takes place around the country; '
        . $a($urls['npc_training'], 'learn more about the NPC training here', true)
        . '.')
    . $p('For more general information about supporting your child’s mental health and wellbeing, you may '
        . $a($urls['npc_brochure'], 'find this brochure helpful', true)
        . '.')
    . '<h3>Child Care in Practice</h3>'
    . $p('We are a supporting partner of <em>Child Care in Practice</em>.')
    . $p($a($urls['ccip'], 'Child Care in Practice', true)
        . ' is a leading international peer review journal of multidisciplinary childcare practice. Publishing the best of both practice and research from all professions and disciplines involved in the provision of children’s services, <em>Child Care in Practice</em> fulfils a special role in bringing together the many groups which make up this vital field. The journal is peer-reviewed and published quarterly.');

$personal_advocacy = $p('Independent advocacy services are available for anyone who feels they might benefit from having support to have their voice heard. These services are free and confidential. You can find more information about different advocacy services available below.')
    . '<ul>'
    . '<li>' . $a($urls['peer'], 'Peer Advocacy in Mental Health', true) . '</li>'
    . '<li>' . $a($urls['nas'], 'National Advocacy Service', true) . '</li>'
    . '<li>' . $a($urls['sage'], 'Sage Advocacy', true) . '</li>'
    . '</ul>';

$hero = matrix_orlaith_hero_row('Advocacy', $hero_intro, $hero_image);
$hero['layout_style'] = $hero_image > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'Advocacy';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$flexi = [
    $hero,
    matrix_orlaith_content_row('What is advocacy?', $what_is, 'white'),
    matrix_orlaith_content_row(
        'What advocacy work do we do?',
        $p('Our advocacy work focuses on human rights, fighting stigma, working in partnership, and supporting personal advocacy services.'),
        'cream'
    ),
    matrix_orlaith_accordion_row([
        'Advocating for human rights' => $human_rights,
        'Fighting stigma' => $fighting_stigma,
        'Working in partnership' => $partnerships,
        'Supporting personal advocacy services' => $personal_advocacy,
    ]),
    matrix_orlaith_useful_links_row([
        'Advocacy Centre' => $urls['advocacy_centre'],
        'Human rights advocacy' => $urls['human_rights'],
        'Public education & anti-stigma campaigns' => $urls['public_education'],
        'Collaborative efforts' => $urls['collaborative'],
        'Advocacy services' => $urls['advocacy_services'],
        'Service user participation' => $urls['service_user'],
        'Women’s Mental Health Network' => $urls['womens_network'],
    ]),
];

update_field('flexible_content_blocks', $flexi, $post_id);

// Keep SEO meta close to the Drive brief when Yoast/Rank Math fields exist.
update_post_meta($post_id, '_yoast_wpseo_title', 'Advocacy | Mental health and human rights | St Patrick’s');
update_post_meta($post_id, '_yoast_wpseo_metadesc', 'Learn about advocacy and human rights approaches to mental healthcare at St Patrick’s Mental Health Services.');

$check = get_field('flexible_content_blocks', $post_id);
WP_CLI::success('Rebuilt Advocacy page ' . $post_id . ' → ' . get_permalink($post_id));
foreach ($check as $i => $row) {
    WP_CLI::log(sprintf('[%d] %s %s', $i, $row['acf_fc_layout'] ?? '', $row['heading'] ?? ''));
}
