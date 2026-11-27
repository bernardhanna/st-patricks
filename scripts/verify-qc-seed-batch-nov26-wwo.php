<?php
/**
 * Verify qc-seed-batch-nov26-wwo outcomes (local/staging).
 *
 *   /wp-content/themes/matrix-starter/scripts/verify-qc-seed-batch-nov26-wwo.php?key=qc-batch-nov26-wwo
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'qc-batch-nov26-wwo') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$plain = static function (string $html): string {
    $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
};

$programme = static function (string $slug): ?WP_Post {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'programmes_therapies',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);

    return $posts[0] ?? null;
};

$pass = 0;
$fail = 0;
$lines = [];
$check = static function (string $label, bool $ok) use (&$pass, &$fail, &$lines): void {
    $lines[] = ($ok ? 'PASS' : 'FAIL') . ' ' . $label;
    if ($ok) {
        $pass++;
    } else {
        $fail++;
    }
};

$dep = $programme('depression-recovery-programme');
if ($dep) {
    $listing = (string) get_field('listing_summary', $dep->ID);
    $content = (string) $dep->post_content;
    $check('depression listing no mood disorder', ! str_contains($listing, 'mood disorder'));
    $check('depression body keeps mood education', str_contains($content, 'mood disorder'));
    $check('depression body no lead programme dup', ! str_starts_with($plain($content), 'The Depression Recovery Programme offers'));
}

$dbt = $programme('dialectical-behavioural-therapy');
if ($dbt) {
    $c = (string) $dbt->post_content;
    $check('dbt no works-to-help intro', ! str_contains($c, 'The DBT Programme works to help you manage'));
    $check('dbt booklet CTA', str_contains($c, 'Download our Dialectical Behaviour Therapy Booklet here'));
}

$ed = $programme('eating-disorder-programme');
if ($ed) {
    $c = (string) $ed->post_content;
    $check('ed no lead offers-treatment dup', ! str_starts_with($plain($c), 'The Eating Disorder Day Care Programme offers'));
    $check('ed anorexia pdf', (bool) preg_match('#anorexia nervosa</a>#i', $c) && (bool) preg_match('#href=["\'][^"\']*anorexia[^"\']*\.pdf#i', $c));
    $check('ed bulimia pdf', (bool) preg_match('#href=["\'][^"\']*bulimia[^"\']*\.pdf#i', $c));
}

$mci = $programme('living-well-with-mild-cognitive-impairment');
if ($mci) {
    $c = (string) $mci->post_content;
    $check('mci no MCI difficulties lead', ! str_starts_with($plain($c), 'People with MCI may notice'));
    $check('mci who delivers', str_contains($c, 'Who delivers the programme'));
    $check('mci who suitable', str_contains($c, 'Who is the programme suitable for'));
}

$path = $programme('pathways-to-wellness-2');
if ($path) {
    $check('pathways no aims lead', ! str_starts_with($plain((string) $path->post_content), 'The programme aims to offer'));
}

$sabe = $programme('psychology-skills-group-for-adolescents');
if ($sabe) {
    $listing = (string) get_field('listing_summary', $sabe->ID);
    $c = (string) $sabe->post_content;
    $check('sabe listing no Neurodiversity means', ! str_contains($listing, 'Neurodiversity means'));
    $check('sabe no psychosis suitable copy', ! str_contains($c, 'experiencing psychosis'));
    $check('sabe suitable 12-17', str_contains($c, 'aged 12 to 17'));
}

$psy = $programme('psychosis-recovery-programme');
if ($psy) {
    $check(
        'psychosis suitable full',
        str_contains((string) $psy->post_content, 'structured education, therapeutic input and recovery-focused support')
    );
}

$wrap = $programme('recovery-wrap-programme');
if ($wrap) {
    $c = (string) $wrap->post_content;
    $check('wrap no empower lead', ! str_starts_with($plain($c), 'The Recovery Programme aims to empower'));
    $check('wrap consultant psychiatrists', str_contains($c, '/about-us/our-team/psychiatrists'));
    $check('wrap brochure CTA', str_contains($c, 'Download our Recovery Programme with WRAP leaflet here'));
}

$sage = $programme('sage');
if ($sage) {
    $listing = (string) get_field('listing_summary', $sage->ID);
    $c = (string) $sage->post_content;
    $check('sage listing no unfinished tailored…', ! preg_match('/It is tailored towards older people[…\.]/u', $listing));
    $check('sage suitable 65', str_contains($c, 'aged 65'));
    $check('sage no blue lead dup', ! str_starts_with($plain($c), 'SAGE is a psychological therapy group'));
}

$trauma = $programme('trauma-programme');
if ($trauma) {
    $c = (string) $trauma->post_content;
    $check('trauma no supports lead', ! str_starts_with($plain($c), 'The Trauma Programme supports'));
    $check('trauma no visible opens-in-new-tab', ! preg_match('/brochure\s*\(opens in a new tab\)/i', $c));
}

$yaf = $programme('young-adult-psychology-groups');
if ($yaf) {
    $c = (string) $yaf->post_content;
    $check('yaf suitable 18-25', str_contains($c, 'aged 18 to 25 who wish to develop'));
    $check('yaf no lead dup', ! str_starts_with($plain($c), 'The Young Adult Formulation Group programme is for'));
}

$yap = $programme('young-adult-programme-2');
if ($yap) {
    $c = (string) $yap->post_content;
    $check('yap no aims to…', ! preg_match('/and aims to[…\.]/u', $c));
    $check('yap referrals h2', str_contains($c, '<h2>How are referrals made?</h2>'));
}

$help = get_page_by_path('getting-help');
if ($help) {
    $blocks = get_field('flexible_content_blocks', $help->ID);
    $ok_h2 = false;
    $ok_no_h3 = true;
    foreach ((array) $blocks as $b) {
        if (($b['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }
        $heading = (string) ($b['heading'] ?? '');
        $content = (string) ($b['content'] ?? '');
        if (str_contains($heading, 'If you have concerns')) {
            $ok_h2 = str_contains($heading, 'But the biggest change can come from just a single step.');
            $ok_no_h3 = ! str_contains($content, '<h3>But the biggest change');
        }
    }
    $check('getting-help H2 has phrase', $ok_h2);
    $check('getting-help no H3 phrase', $ok_no_h3);
}

$cft = get_posts(['name' => 'compassion-focused-therapy-eating-disorders-scale-launches', 'post_type' => 'post', 'posts_per_page' => 1]);
if ($cft) {
    $c = (string) $cft[0]->post_content;
    $check('cft no PLACEHOLDER', ! str_contains($c, 'PLACEHOLDER'));
    $check('cft founder history', (bool) preg_match('#our-history[^>]*>our founder</a>#i', $c));
    $check('cft themes link text', (bool) preg_match('#derived from qualitative themes identified in earlier published doctoral research</a>#i', $c));
}

$suas = get_posts(['name' => '20-years-of-service-user-engagement', 'post_type' => 'post', 'posts_per_page' => 1]);
if ($suas) {
    $c = (string) $suas[0]->post_content;
    $check('suas strategy live URL', str_contains($c, 'https://www.stpatricks.ie/about-us/strategy'));
}

$fax = get_posts(['name' => 'closure-of-fax-referrals', 'post_type' => 'post', 'posts_per_page' => 1]);
if ($fax) {
    $check('fax publish', get_post_status($fax[0]) === 'publish');
    $map = json_decode((string) file_get_contents(get_theme_file_path('inc/data/path-redirect-map.json')), true);
    $check('fax not in redirect map', ! isset($map['closure-of-fax-referrals']));
    $check('gps-referrals/referrals-admissions mapped', isset($map['gps-referrals/referrals-admissions']));
    $c = (string) $fax[0]->post_content;
    $check('fax no legacy gps-referrals RAS', ! str_contains($c, 'gps-referrals/referrals-admissions'));
}

$fd = get_posts(['name' => 'founders-day-conference-unlocking-potential-of-later-life', 'post_type' => 'post', 'posts_per_page' => 1]);
if ($fd) {
    $c = (string) $fd[0]->post_content;
    $check('fd depression mental-health', str_contains($c, '/mental-health/depression'));
    $check('fd anxiety mental-health', str_contains($c, '/mental-health/anxiety'));
    $check('fd psychiatrists team', str_contains($c, '/about-us/our-team/psychiatrists'));
    $check('fd no corrupt mental-health repeat', ! str_contains($c, '/mental-health/mental-health/'));
}

$cbt = $programme('cognitive-behavioural-therapy');
if ($cbt) {
    $listing = (string) get_field('listing_summary', $cbt->ID);
    $check('cbt spacing', str_contains($listing, 'difficulties. Here') && ! str_contains($listing, 'difficulties.Here'));
}

$lines[] = "TOTAL pass={$pass} fail={$fail}";
echo implode("\n", $lines) . "\n";
