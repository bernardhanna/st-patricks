<?php
/**
 * Fix Advocacy page NPC brochure link (404 on npc.ie PDF → Walk in My Shoes hosted leaflet).
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-advocacy-npc-brochure-link.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$post_id = (int) (get_page_by_path('about-us/advocacy')->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/advocacy');
}

$brochure = 'https://www.walkinmyshoes.ie/media/4613/national-parents-council-leaflet.pdf';
$training = 'https://www.npc.ie/index.php/training-and-resources/training-we-offer/supporting-parents-to-support-their-childrens-mental-health-and-wellbeing';

$npc = '<h4>National Parent\'s Council training</h4>'
    . '<p>In Ireland, the National Parent\'s Council (NPC) is the "only representative organisation for parents of children in primary or early education". It works to empower parents to support their children throughout their early and primary school years and believes that children should have a say in issues affecting their educational lives.</p>'
    . '<p>We have partnered with the NPC to create mental health awareness training for parents of <a href="https://www.walkinmyshoes.ie/schools/primary-school" target="_blank" rel="noopener noreferrer">primary school children</a>. This programme supports parents to encourage and promote positive mental health and wellbeing in their children. It also explores how building resilience in children helps them to manage and cope with the day-to-day stresses of life as they occur. Training takes place around the country; <a href="' . esc_url($training) . '" target="_blank" rel="noopener noreferrer">you can find more information here</a>.</p>'
    . '<p>For more general information about supporting your child\'s mental health and wellbeing, you may <a href="' . esc_url($brochure) . '" target="_blank" rel="noopener noreferrer">find this brochure helpful</a>.</p>';

$blocks = get_field('flexible_content_blocks', $post_id);
if (! is_array($blocks)) {
    WP_CLI::error('No flexible_content_blocks');
}

$changed = false;
foreach ($blocks as &$block) {
    if (($block['acf_fc_layout'] ?? '') !== 'content_accordion') {
        continue;
    }
    foreach ($block['items'] as &$item) {
        foreach ($item['content_rows'] as &$row) {
            $c = (string) ($row['content'] ?? '');
            if ($c === '') {
                continue;
            }
            if (str_contains($c, 'MentalHealth.PDF')) {
                $c = str_replace(
                    [
                        'https://www.npc.ie/images/uploads/downloads/MentalHealth.PDF',
                        'http://www.npc.ie/images/uploads/downloads/MentalHealth.PDF',
                    ],
                    $brochure,
                    $c
                );
                $changed = true;
            }
            if (! str_contains($c, 'find this brochure helpful') && str_contains($c, '<h4>Child Care in Practice</h4>')) {
                $c = str_replace('<h4>Child Care in Practice</h4>', $npc . '<h4>Child Care in Practice</h4>', $c);
                $changed = true;
            }
            $row['content'] = $c;
        }
    }
}
unset($block, $item, $row);

if (! $changed) {
    WP_CLI::warning('No content change applied');
} else {
    update_field('flexible_content_blocks', $blocks, $post_id);
    WP_CLI::success('Advocacy NPC brochure link updated on post ' . $post_id);
}
