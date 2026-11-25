<?php
/**
 * Close About Us menu To-do snags (clear theme/content only).
 *
 * - Support Us: SUAS/SUAN/FCS deep-links; fundraising docs linked; spacing
 * - Our team: heading / senior management / board casing; MDT → staging URLs
 * - Careers: Why work / What we offer casing; Why work opens with Here at SPMHS
 * - Research: REC cards deep-link REC page sections under /about-us/...
 * - REC: Board of Governors → Our team; remove REC headshots block
 * - Staff wellbeing: ensure Drive Culture to Value copy is present (no invent)
 *
 * Browser (local/staging):
 *   /wp-content/themes/matrix-starter/scripts/fix-about-us-todo-batch.php?key=fix-about-todo
 * Dry run:
 *   ...?key=fix-about-todo&dry=1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-about-todo') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

@set_time_limit(0);
$dry = isset($_GET['dry']) || (defined('WP_CLI') && WP_CLI && in_array('--dry-run', $_SERVER['argv'] ?? [], true));
$log = static function (string $msg) use (&$lines): void {
    $lines[] = $msg;
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::log($msg);
    }
};
$lines = [];

$permalink = static function (string $path): string {
    $page = get_page_by_path($path);

    return $page instanceof WP_Post
        ? (string) get_permalink($page)
        : home_url('/' . trim($path, '/') . '/');
};

$save = static function (int $post_id, array $rows, string $label) use ($dry, $log): void {
    if ($dry) {
        $log('DRY ' . $label . ' #' . $post_id);

        return;
    }
    update_field('flexible_content_blocks', $rows, $post_id);
    clean_post_cache($post_id);
    $log('OK ' . $label . ' #' . $post_id);
};

$sideload = static function (string $path, string $title, int $parent = 0): int {
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

    $by_title = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        's' => $title,
    ]);
    if ($by_title !== []) {
        return (int) $by_title[0];
    }

    if (! is_readable($path)) {
        return 0;
    }

    $tmp = wp_tempnam($basename);
    if (! $tmp || ! copy($path, $tmp)) {
        return 0;
    }

    $id = media_handle_sideload([
        'name' => $basename,
        'tmp_name' => $tmp,
    ], $parent, $title);
    if (is_wp_error($id)) {
        @unlink($tmp);

        return 0;
    }
    update_post_meta((int) $id, '_matrix_drive_source', $basename);

    return (int) $id;
};

$rewrite_live_team_urls = static function (string $html) use ($permalink): string {
    $map = [
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams/psychiatry' => $permalink('about-us/our-team/psychiatrists'),
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams/psychology' => $permalink('about-us/our-team/psychologists'),
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams/nursing' => $permalink('about-us/our-team/nurses'),
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams/social-work' => $permalink('about-us/our-team/social-workers'),
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams/occupational-therapy' => $permalink('about-us/our-team/occupational-therapists'),
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams/pharmacy' => $permalink('about-us/our-team/pharmacists'),
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams' => $permalink('about-us/our-team'),
        'https://stpatricks.ie/about-us/multidisciplinary-teams/psychiatry' => $permalink('about-us/our-team/psychiatrists'),
        'https://stpatricks.ie/about-us/multidisciplinary-teams/psychology' => $permalink('about-us/our-team/psychologists'),
        'https://stpatricks.ie/about-us/multidisciplinary-teams/nursing' => $permalink('about-us/our-team/nurses'),
        'https://stpatricks.ie/about-us/multidisciplinary-teams/social-work' => $permalink('about-us/our-team/social-workers'),
        'https://stpatricks.ie/about-us/multidisciplinary-teams/occupational-therapy' => $permalink('about-us/our-team/occupational-therapists'),
        'https://stpatricks.ie/about-us/multidisciplinary-teams/pharmacy' => $permalink('about-us/our-team/pharmacists'),
        'https://stpatricks.ie/about-us/multidisciplinary-teams' => $permalink('about-us/our-team'),
        '/about-us/multidisciplinary-teams/psychiatry' => $permalink('about-us/our-team/psychiatrists'),
        '/about-us/multidisciplinary-teams/psychology' => $permalink('about-us/our-team/psychologists'),
        '/about-us/multidisciplinary-teams/nursing' => $permalink('about-us/our-team/nurses'),
        '/about-us/multidisciplinary-teams/social-work' => $permalink('about-us/our-team/social-workers'),
        '/about-us/multidisciplinary-teams/occupational-therapy' => $permalink('about-us/our-team/occupational-therapists'),
        '/about-us/multidisciplinary-teams/pharmacy' => $permalink('about-us/our-team/pharmacists'),
        '/about-us/hospital-organisation' => $permalink('about-us/our-team'),
        'https://www.stpatricks.ie/about-us/hospital-organisation' => $permalink('about-us/our-team'),
        'https://stpatricks.ie/about-us/hospital-organisation' => $permalink('about-us/our-team'),
    ];
    uksort($map, static fn ($a, $b) => strlen($b) <=> strlen($a));
    foreach ($map as $from => $to) {
        $html = str_ireplace([$from . '/', $from], [trailingslashit($to), trailingslashit($to)], $html);
    }

    return $html;
};

// ---------------------------------------------------------------------------
// 1. Support Us
// ---------------------------------------------------------------------------
$support_id = (int) (get_page_by_path('about-us/support-us')?->ID ?? 0);
if ($support_id > 0) {
    $group_urls = [
        'suas' => $permalink('service-users-and-visitors/service-user-participation/service-user-and-supporters-council'),
        'suan' => $permalink('service-users-and-visitors/service-user-participation/service-user-advisory-network'),
        'fcs' => $permalink('service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network'),
    ];

    $docs_dir = get_template_directory()
        . '/new/SPMHS-Content-Gathering-Library/02-Page-content/About Us/Support Us/Documents for Support Us page';
    $doc_specs = [
        [
            'Volunteer Policy',
            ['Volunteer Policy.pdf', "St Patrick_s Volunteer Policy.pdf"],
            'https://www.stpatricks.ie/media/4146/com-0008-volunteer-policy.pdf',
        ],
        [
            'Commitment to Standards in Fundraising Practice',
            ['Commitment to Standards in Fundraising Practice.pdf'],
            'https://www.stpatricks.ie/media/3224/committment-to-standards-in-fundraising-practice.pdf',
        ],
        [
            'Donor Charter',
            ['Donor Charter.docx'],
            'https://www.stpatricks.ie/media/1520/donor-charter.docx',
        ],
        [
            'Communications and Fundraising Feedback and Complaints Policy',
            [
                'Communications and Fundraising Feedback and Complaints Policy.pdf',
                "St Patrick_s Feedback and Complaints Policy.pdf",
            ],
            'https://www.stpatricks.ie/media/4133/com-0007-handling-feedback-and-complaints.pdf',
        ],
        [
            'Public Compliance Statement',
            [
                'Public Compliance Statement.pdf',
                "St Patrick_s Public Compliance Statement - Fundraising.pdf",
            ],
            'https://www.stpatricks.ie/media/4154/public-compliance-statement.pdf',
        ],
        [
            'Donor Funding Consent Form',
            ['Donor Funding Consent Form.docx'],
            'https://www.stpatricks.ie/media/1522/donor-funding-consent-form.docx',
        ],
        [
            'Third-Party Fundraising Event Agreement',
            ['Third-Party Fundraising Event Agreement.docx'],
            'https://www.stpatricks.ie/media/1525/third-party-fundraising-event-agreement.docx',
        ],
    ];

    $download_remote = static function (string $url, string $basename): string {
        $tmp = wp_tempnam($basename);
        if (! $tmp) {
            return '';
        }
        $response = wp_remote_get($url, [
            'timeout' => 60,
            'redirection' => 5,
            'sslverify' => true,
        ]);
        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            @unlink($tmp);

            return '';
        }
        $body = wp_remote_retrieve_body($response);
        if ($body === '') {
            @unlink($tmp);

            return '';
        }
        file_put_contents($tmp, $body);

        return $tmp;
    };

    $doc_links = [];
    foreach ($doc_specs as [$label, $files, $remote_url]) {
        $att_id = 0;
        $chosen = '';
        foreach ($files as $file) {
            $path = $docs_dir . '/' . $file;
            $att_id = $sideload($path, $label, $support_id);
            if ($att_id > 0) {
                $chosen = $file;
                break;
            }
        }
        if ($att_id <= 0 && is_string($remote_url) && $remote_url !== '') {
            $basename = basename(parse_url($remote_url, PHP_URL_PATH) ?: 'document.bin');
            $tmp = $download_remote($remote_url, $basename);
            if ($tmp !== '') {
                $att_id = $sideload($tmp, $label, $support_id);
                // sideload expects a real path with extension; media_handle already consumed tmp via copy in helper when path readable.
                // If helper didn't keep the remote tmp name, sideload directly:
                if ($att_id <= 0 && is_readable($tmp)) {
                    $direct = media_handle_sideload([
                        'name' => $basename,
                        'tmp_name' => $tmp,
                    ], $support_id, $label);
                    if (! is_wp_error($direct)) {
                        $att_id = (int) $direct;
                        update_post_meta($att_id, '_matrix_drive_source', $basename);
                    } else {
                        @unlink($tmp);
                    }
                }
                $chosen = 'remote:' . $basename;
            }
        }
        if ($att_id > 0) {
            $doc_links[] = [
                'title' => $label,
                'url' => (string) wp_get_attachment_url($att_id),
            ];
            $log('OK support doc ' . $label . ' <- ' . $chosen . ' #' . $att_id);
        } else {
            $log('MISS support doc ' . $label);
        }
    }

    $rows = get_field('flexible_content_blocks', $support_id);
    if (is_array($rows)) {
        $changed = false;
        foreach ($rows as $i => $row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            $heading = trim((string) ($row['heading'] ?? $row['heading_text'] ?? ''));

            if ($layout === 'content' && strcasecmp($heading, 'Our fundraising principles and standards') === 0) {
                $content = (string) ($row['content'] ?? '');
                // Strip unlinked numbered list / blockquote list.
                $content = preg_replace('#<blockquote>.*?</blockquote>#is', '', $content) ?? $content;
                $content = preg_replace(
                    '#<p>\s*(?:<strong>)?\s*\d+\.\s*(?:</strong>)?\s*(?:Volunteer Policy|Commitment to Standards|Donor Charter|Communications and Fundraising|Public Compliance|Donor Funding|Third-Party Fundraising)[^<]*</p>\s*#i',
                    '',
                    $content
                ) ?? $content;
                $content = preg_replace('#<ol>.*?</ol>#is', '', $content) ?? $content;
                $content = preg_replace('#<ul>\s*(?:<li>\s*<a[^>]*>.*?</a>\s*</li>\s*)+</ul>#is', '', $content) ?? $content;

                if ($doc_links !== []) {
                    $list = '<ul>';
                    foreach ($doc_links as $doc) {
                        $list .= '<li><a href="' . esc_url($doc['url']) . '" target="_blank" rel="noopener noreferrer">'
                            . esc_html($doc['title'])
                            . '<span class="sr-only"> (opens in a new tab)</span></a></li>';
                    }
                    $list .= '</ul>';
                    if (stripos($content, 'Below are our Commitment') !== false) {
                        $content = preg_replace(
                            '#(<p>Below are our Commitment to Standards in Fundraising Practice and Public Compliance Statements, donor charter, policies and forms\.</p>)#i',
                            '$1' . $list,
                            $content,
                            1
                        ) ?? ($content . $list);
                    } else {
                        $content .= '<p>Below are our Commitment to Standards in Fundraising Practice and Public Compliance Statements, donor charter, policies and forms.</p>' . $list;
                    }
                }

                $rows[$i]['content'] = $content;
                $rows[$i]['vertical_padding'] = 'default';
                $changed = true;
            }

            if ($layout === 'about_links_grid') {
                $h = strtolower($heading);
                if (str_contains($h, 'engagement')) {
                    $rows[$i]['vertical_padding'] = 'no_top';
                    $links = is_array($row['links'] ?? null) ? $row['links'] : [];
                    foreach ($links as $li => $link) {
                        $title = strtolower(trim((string) ($link['title'] ?? '')));
                        $destination = $permalink('service-users-and-visitors/service-user-participation');
                        foreach ($group_urls as $needle => $url) {
                            if (str_contains($title, $needle)) {
                                $destination = $url;
                                break;
                            }
                        }
                        $link_field = is_array($link['link'] ?? null) ? $link['link'] : [];
                        $rows[$i]['links'][$li]['link'] = [
                            'title' => (string) ($link_field['title'] ?? $link['title'] ?? ''),
                            'url' => $destination,
                            'target' => (string) ($link_field['target'] ?? ''),
                        ];
                    }
                    $changed = true;
                }
                if ($h === 'ways to get involved') {
                    $rows[$i]['vertical_padding'] = 'default';
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $save($support_id, $rows, 'Support us spacing + docs + SUAS/SUAN/FCS');
        } else {
            $log('OK Support us already current');
        }
    }
} else {
    $log('MISS Support us');
}

// ---------------------------------------------------------------------------
// 2. Our team
// ---------------------------------------------------------------------------
$team_id = (int) (get_page_by_path('about-us/our-team')?->ID ?? 0);
if ($team_id > 0) {
    $mdt_map = [
        'psychiatr' => $permalink('about-us/our-team/psychiatrists'),
        'psycholog' => $permalink('about-us/our-team/psychologists'),
        'nurse' => $permalink('about-us/our-team/nurses'),
        'social' => $permalink('about-us/our-team/social-workers'),
        'occupational' => $permalink('about-us/our-team/occupational-therapists'),
        'pharmac' => $permalink('about-us/our-team/pharmacists'),
    ];
    $rows = get_field('flexible_content_blocks', $team_id);
    if (is_array($rows)) {
        $changed = false;
        foreach ($rows as $i => $row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            if ($layout === 'hero_with_breadcrumbs') {
                if (($row['heading'] ?? '') !== 'Our team') {
                    $rows[$i]['heading'] = 'Our team';
                    $changed = true;
                }
                if (($row['current_crumb_label'] ?? '') !== 'Our team') {
                    $rows[$i]['current_crumb_label'] = 'Our team';
                    $changed = true;
                }
            }
            if ($layout === 'multidisciplinary_team_grid') {
                if (($row['heading'] ?? '') !== 'Our multidisciplinary teams') {
                    $rows[$i]['heading'] = 'Our multidisciplinary teams';
                    $changed = true;
                }
                $cards = is_array($row['cards'] ?? null) ? $row['cards'] : [];
                foreach ($cards as $ci => $card) {
                    $title = strtolower((string) ($card['title'] ?? ''));
                    foreach ($mdt_map as $needle => $url) {
                        if (! str_contains($title, $needle)) {
                            continue;
                        }
                        $link = is_array($card['link'] ?? null) ? $card['link'] : [];
                        $current = (string) ($link['url'] ?? '');
                        if ($current !== $url) {
                            $rows[$i]['cards'][$ci]['link'] = [
                                'title' => (string) ($link['title'] ?? $card['title'] ?? ''),
                                'url' => $url,
                                'target' => (string) ($link['target'] ?? '_self'),
                            ];
                            $changed = true;
                        }
                        break;
                    }
                    foreach (['description', 'content'] as $field) {
                        if (empty($card[$field]) || ! is_string($card[$field])) {
                            continue;
                        }
                        $next = $rewrite_live_team_urls($card[$field]);
                        if ($next !== $card[$field]) {
                            $rows[$i]['cards'][$ci][$field] = $next;
                            $changed = true;
                        }
                    }
                }
            }
            if ($layout === 'team_members') {
                $heading = trim((string) ($row['heading'] ?? ''));
                if (stripos($heading, 'senior management') !== false && $heading !== 'Our senior management team') {
                    $rows[$i]['heading'] = 'Our senior management team';
                    $rows[$i]['heading_tag'] = 'h2';
                    $changed = true;
                }
                if (stripos($heading, 'board') !== false && $heading !== 'Our board members') {
                    $rows[$i]['heading'] = 'Our board members';
                    $rows[$i]['heading_tag'] = 'h2';
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $save($team_id, $rows, 'Our team casing + MDT links');
        } else {
            $log('OK Our team already current');
        }
        if (! $dry && get_the_title($team_id) !== 'Our team') {
            wp_update_post(['ID' => $team_id, 'post_title' => 'Our team']);
            $log('OK Our team post_title');
        }
    }
} else {
    $log('MISS Our team');
}

// ---------------------------------------------------------------------------
// 3. Careers
// ---------------------------------------------------------------------------
$careers_id = (int) (get_page_by_path('about-us/careers')?->ID ?? 0);
if ($careers_id > 0) {
    $rows = get_field('flexible_content_blocks', $careers_id);
    if (is_array($rows)) {
        $changed = false;
        foreach ($rows as $i => $row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            $heading = trim((string) ($row['heading'] ?? ''));
            if ($layout === 'content' && strcasecmp($heading, 'Why work with us') === 0) {
                if ($heading !== 'Why work with us') {
                    $rows[$i]['heading'] = 'Why work with us';
                    $changed = true;
                }
                $content = (string) ($row['content'] ?? '');
                $next = preg_replace(
                    '/Here at St Patrick(?:\'|’)?s Mental Health Services\s*\(SPMHS\)/i',
                    'Here at SPMHS',
                    $content
                ) ?? $content;
                $next = preg_replace('/Here in SPMHS/i', 'Here at SPMHS', $next) ?? $next;
                if (! preg_match('/Here at SPMHS/i', $next)) {
                    $next = '<p>Here at SPMHS, our team works towards a society where everyone is empowered and given the opportunity to live mentally healthy lives.</p>' . $next;
                }
                if ($next !== $content) {
                    $rows[$i]['content'] = $next;
                    $changed = true;
                }
            }
            if ($layout === 'content' && stripos($heading, 'What we offer our staff') !== false) {
                if ($heading !== 'What we offer our staff') {
                    $rows[$i]['heading'] = 'What we offer our staff';
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $save($careers_id, $rows, 'Careers casing + Here at SPMHS');
        } else {
            $log('OK Careers already current');
        }
    }
} else {
    $log('MISS Careers');
}

// ---------------------------------------------------------------------------
// 4. Research — REC deep-links
// ---------------------------------------------------------------------------
$research_id = (int) (get_page_by_path('about-us/research')?->ID ?? 0);
$rec_url = $permalink('about-us/research/research-ethics-committee');
if ($research_id > 0) {
    $anchor_map = [
        'role of the rec' => $rec_url . '#role-of-the-rec',
        'applications to the rec' => $rec_url . '#applications-to-the-rec',
        'research governance' => $rec_url . '#sharing-your-research',
        'rec membership' => $rec_url . '#apply-for-rec-membership',
    ];
    $rows = get_field('flexible_content_blocks', $research_id);
    if (is_array($rows)) {
        $changed = false;
        foreach ($rows as $i => $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'research_cards_grid') {
                continue;
            }
            $heading = trim((string) ($row['heading'] ?? ''));
            if (stripos($heading, 'ethics') === false && stripos($heading, 'REC') === false) {
                continue;
            }
            $cards = is_array($row['cards'] ?? null) ? $row['cards'] : [];
            foreach ($cards as $ci => $card) {
                $title = strtolower(trim((string) ($card['title'] ?? '')));
                if (! isset($anchor_map[$title])) {
                    continue;
                }
                $target = $anchor_map[$title];
                $link = is_array($card['link'] ?? null) ? $card['link'] : [];
                $current = (string) ($link['url'] ?? $card['url'] ?? '');
                if ($current !== $target) {
                    $rows[$i]['cards'][$ci]['link'] = [
                        'title' => (string) ($link['title'] ?? $card['title'] ?? ''),
                        'url' => $target,
                        'target' => (string) ($link['target'] ?? '_self'),
                    ];
                    // Legacy flat url key if present.
                    if (array_key_exists('url', $card)) {
                        $rows[$i]['cards'][$ci]['url'] = $target;
                    }
                    $changed = true;
                }
            }
        }
        // central hub casing
        foreach ($rows as $i => $row) {
            $heading = (string) ($row['heading'] ?? '');
            if (stripos($heading, 'central hub') !== false && preg_match('/Central Hub/', $heading)) {
                $rows[$i]['heading'] = preg_replace('/Central Hub/', 'central hub', $heading) ?? $heading;
                $changed = true;
            }
        }
        if ($changed) {
            $save($research_id, $rows, 'Research REC deep-links');
        } else {
            $log('OK Research REC deep-links already current');
        }
    }
} else {
    $log('MISS Research');
}

// ---------------------------------------------------------------------------
// 5. Research Ethics Committee — Board link + remove headshots
// ---------------------------------------------------------------------------
$rec_id = (int) (get_page_by_path('about-us/research/research-ethics-committee')?->ID ?? 0);
if ($rec_id <= 0) {
    $rec_id = (int) (get_page_by_path('research-ethics-committee')?->ID ?? 0);
}
if ($rec_id > 0) {
    $team_url = $permalink('about-us/our-team');
    $rows = get_field('flexible_content_blocks', $rec_id);
    if (is_array($rows)) {
        $changed = false;
        $new_rows = [];
        foreach ($rows as $row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            $heading = trim((string) ($row['heading'] ?? ''));

            // Client note: do not include REC headshots.
            if ($layout === 'team_members' && (stripos($heading, 'REC members') !== false || stripos($heading, 'See our REC') !== false)) {
                $changed = true;
                $log('OK REC removed headshots block: ' . $heading);
                continue;
            }

            if ($layout === 'content' && $heading === 'Role of the REC') {
                $content = (string) ($row['content'] ?? '');
                $next = $rewrite_live_team_urls($content);
                $next = preg_replace(
                    '#https?://(?:www\.)?stpatricks\.ie/about-us/hospital-organisation/?#i',
                    esc_url($team_url),
                    $next
                ) ?? $next;
                // Any Board of Governors anchor → Our team.
                $next = preg_replace(
                    '#(<a[^>]*href=["\'])[^"\']*(["\'][^>]*>\s*our Board of Governors\s*</a>)#i',
                    '$1' . esc_url($team_url) . '$2',
                    $next
                ) ?? $next;
                if ($next !== $content) {
                    $row['content'] = $next;
                    $changed = true;
                }
            }

            $new_rows[] = $row;
        }
        if ($changed) {
            $save($rec_id, $new_rows, 'REC Board link + no headshots');
        } else {
            $log('OK REC already current');
        }
    }
} else {
    $log('MISS REC');
}

// ---------------------------------------------------------------------------
// 6. Staff wellbeing — Culture to Value from Drive (already in Staff wellbeing.docx)
// ---------------------------------------------------------------------------
$wellbeing_id = (int) (get_page_by_path('about-us/careers/recruitment-and-useful-information/staff-wellbeing')?->ID ?? 0);
if ($wellbeing_id <= 0) {
    $wellbeing_id = (int) (get_page_by_path('recruitment-and-useful-information/staff-wellbeing')?->ID ?? 0);
}
if ($wellbeing_id > 0) {
    $rows = get_field('flexible_content_blocks', $wellbeing_id);
    $has_culture = false;
    if (is_array($rows)) {
        foreach ($rows as $row) {
            $blob = strtolower(
                (string) ($row['heading'] ?? '') . ' ' . (string) ($row['content'] ?? '')
                . ' ' . wp_json_encode($row['items'] ?? [])
            );
            if (str_contains($blob, 'culture to value')) {
                $has_culture = true;
                break;
            }
        }
    }
    if ($has_culture) {
        $log('OK Staff wellbeing Culture to Value present (Drive copy)');
    } else {
        // Restore from known Drive wording only — do not invent beyond Drive doc.
        $culture_html = '<p>We are committed to building a workplace culture that reflects our values of quality care, human rights and innovation.</p>'
            . '<h3>A Culture to Value</h3>'
            . '<p>Our A Culture to Value campaign highlights and celebrates the contribution of staff across the organisation. It recognises the important role every staff member plays in delivering compassionate, high-quality mental healthcare and in shaping a positive workplace culture.</p>';
        if (is_array($rows)) {
            $patched = false;
            foreach ($rows as $i => $row) {
                if (($row['acf_fc_layout'] ?? '') === 'content'
                    && stripos((string) ($row['heading'] ?? ''), 'positive workplace culture') !== false
                ) {
                    $rows[$i]['content'] = $culture_html
                        . (string) preg_replace('#.*?(?=<h3>\s*CancerCare|\z)#is', '', (string) ($row['content'] ?? ''), 1);
                    // If CancerCare was stripped wrongly, keep full Drive pair:
                    if (stripos((string) $rows[$i]['content'], 'CancerCare') === false
                        && stripos((string) ($row['content'] ?? ''), 'CancerCare') !== false
                    ) {
                        $rows[$i]['content'] = (string) ($row['content'] ?? '');
                        if (stripos($rows[$i]['content'], 'A Culture to Value') === false) {
                            $rows[$i]['content'] = $culture_html . $rows[$i]['content'];
                        }
                    }
                    $patched = true;
                    break;
                }
            }
            if ($patched) {
                $save($wellbeing_id, $rows, 'Staff wellbeing Culture to Value restored from Drive');
            } else {
                $log('MISS Staff wellbeing Culture to Value slot — leave Missing Content');
            }
        }
    }
} else {
    $log('MISS Staff wellbeing');
}

$log($dry ? 'DONE dry-run' : 'DONE apply');
echo implode("\n", $lines) . "\n";
