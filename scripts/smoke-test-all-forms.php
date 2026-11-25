<?php
/**
 * Inventory + smoke-test theme forms on this environment.
 *
 * - contact_form / careers application: config checks (no outbound email)
 * - newsletter (page + footer defaults): live Mailchimp/Brevo ping + one subscribe
 *
 * HTTP: /wp-content/themes/matrix-starter/scripts/smoke-test-all-forms.php?key=smoke-test-all-forms
 * Optional: &live_subscribe=1 to actually hit Mailchimp/Brevo with a throwaway address
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'smoke-test-all-forms') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: application/json; charset=utf-8');
}

$live_subscribe = isset($_GET['live_subscribe']) && (string) $_GET['live_subscribe'] === '1';
$test_email = 'form-smoke+' . gmdate('YmdHis') . '@matrixinternet.ie';
$results = [
    'site' => home_url('/'),
    'provider' => function_exists('matrix_get_newsletter_provider') ? matrix_get_newsletter_provider() : '',
    'live_subscribe' => $live_subscribe,
    'test_email' => $live_subscribe ? $test_email : null,
    'summary' => ['pass' => 0, 'fail' => 0, 'skip' => 0],
    'forms' => [],
    'checks' => [],
];

$add = static function (string $status, string $name, string $detail, array $meta = []) use (&$results): void {
    $results['checks'][] = array_merge([
        'status' => $status,
        'name' => $name,
        'detail' => $detail,
    ], $meta);
    if ($status === 'pass') {
        $results['summary']['pass']++;
    } elseif ($status === 'fail') {
        $results['summary']['fail']++;
    } else {
        $results['summary']['skip']++;
    }
};

$walk = static function (array $rows, int $post_id, string $permalink, string $title) use (&$results): void {
    foreach ($rows as $index => $row) {
        if (! is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'contact_form') {
            $style = (string) ($row['form_style'] ?? '');
            $results['forms'][] = [
                'kind' => 'contact_form',
                'style' => $style,
                'heading' => trim((string) ($row['heading'] ?? '')),
                'form_name' => trim((string) ($row['form_name'] ?? '')),
                'recipient_email' => trim((string) ($row['recipient_email'] ?? '')),
                'enable_brevo' => ! empty($row['enable_brevo']),
                'brevo_list_id' => (string) ($row['brevo_list_id'] ?? ''),
                'page_id' => $post_id,
                'title' => $title,
                'url' => $permalink,
                'row' => $index + 1,
            ];
        }
        if ($layout === 'newsletter') {
            $results['forms'][] = [
                'kind' => 'newsletter',
                'heading' => trim((string) ($row['heading'] ?? '')),
                'list_id' => trim((string) ($row['newsletter_list_id'] ?? ($row['brevo_list_id'] ?? ''))),
                'page_id' => $post_id,
                'title' => $title,
                'url' => $permalink,
                'row' => $index + 1,
            ];
        }
    }
};

$posts = get_posts([
    'post_type' => array_values(array_diff(get_post_types(['public' => true], 'names'), ['attachment'])),
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
]);

foreach ($posts as $post) {
    $pid = (int) $post->ID;
    $permalink = (string) get_permalink($pid);
    $title = (string) get_the_title($pid);
    $flexi = get_field('flexible_content_blocks', $pid);
    if (is_array($flexi)) {
        $walk($flexi, $pid, $permalink, $title);
    }
}

// Footer / options newsletter always counts as one form surface.
$results['forms'][] = [
    'kind' => 'newsletter_footer',
    'heading' => trim((string) get_field('newsletter_heading', 'option')),
    'list_id' => trim((string) get_field('mailchimp_list_id', 'option')),
    'page_id' => 0,
    'title' => 'Sitewide footer newsletter',
    'url' => home_url('/'),
    'row' => 0,
];

// Careers application template check.
$careers = get_posts([
    'post_type' => 'page',
    'post_status' => 'publish',
    'numberposts' => -1,
    'meta_key' => '_wp_page_template',
    'meta_value' => 'page-templates/application-form.php',
]);
foreach ($careers as $career) {
    $results['forms'][] = [
        'kind' => 'careers_application',
        'heading' => (string) get_the_title($career),
        'page_id' => (int) $career->ID,
        'title' => (string) get_the_title($career),
        'url' => (string) get_permalink($career),
        'row' => 0,
    ];
}

// Global newsletter provider readiness.
$provider = $results['provider'];
if ($provider === 'mailchimp') {
    $key = function_exists('matrix_get_mailchimp_api_key') ? matrix_get_mailchimp_api_key() : '';
    $dc = function_exists('matrix_get_mailchimp_datacenter') ? matrix_get_mailchimp_datacenter($key) : '';
    if ($key === '' || $dc === '') {
        $add('fail', 'mailchimp_key', 'Mailchimp API key missing or has no datacenter suffix.');
    } else {
        $ping = wp_remote_get('https://' . rawurlencode($dc) . '.api.mailchimp.com/3.0/ping', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('anystring:' . $key),
            ],
            'timeout' => 12,
        ]);
        $code = (int) wp_remote_retrieve_response_code($ping);
        if ($code === 200) {
            $add('pass', 'mailchimp_ping', 'Mailchimp ping ok (' . $dc . ').');
        } else {
            $add('fail', 'mailchimp_ping', 'Mailchimp ping failed HTTP ' . $code . '.');
        }
    }
} elseif ($provider === 'brevo') {
    $key = function_exists('matrix_get_brevo_api_key') ? matrix_get_brevo_api_key() : '';
    if ($key === '') {
        $add('fail', 'brevo_key', 'Brevo API key missing.');
    } else {
        $add('pass', 'brevo_key', 'Brevo API key present.');
    }
} else {
    $add('fail', 'newsletter_provider', 'Unknown newsletter provider: ' . $provider);
}

$newsletter_tested = [];

foreach ($results['forms'] as $form) {
    $kind = $form['kind'];
    $label = ($form['heading'] !== '' ? $form['heading'] : $form['title']) . ' @ ' . ($form['url'] ?? '');

    if ($kind === 'contact_form') {
        $to = trim((string) ($form['recipient_email'] ?? ''));
        if ($to === '' || ! is_email($to)) {
            $add('fail', 'contact_recipient', 'Missing/invalid recipient_email.', [
                'form' => $label,
                'style' => $form['style'] ?? '',
            ]);
        } else {
            $add('pass', 'contact_recipient', 'Recipient configured: ' . $to, [
                'form' => $label,
                'style' => $form['style'] ?? '',
            ]);
        }

        if (! empty($form['enable_brevo'])) {
            $list = trim((string) ($form['brevo_list_id'] ?? ''));
            if ($list === '') {
                $add('fail', 'contact_brevo_list', 'Brevo enabled but list id empty.', ['form' => $label]);
            } else {
                $add('pass', 'contact_brevo_list', 'Brevo list ' . $list, ['form' => $label]);
            }
        }

        continue;
    }

    if ($kind === 'careers_application') {
        $add('pass', 'careers_application', 'Application template page present.', ['form' => $label]);
        continue;
    }

    if ($kind === 'newsletter' || $kind === 'newsletter_footer') {
        $list_id = trim((string) ($form['list_id'] ?? ''));
        if ($list_id === '' && $kind === 'newsletter_footer' && $provider === 'mailchimp') {
            $list_id = trim((string) get_field('mailchimp_list_id', 'option'));
        }
        if ($list_id === '') {
            $add('fail', 'newsletter_list', 'No audience/list id configured.', ['form' => $label]);
            continue;
        }

        $add('pass', 'newsletter_list', 'List id ' . $list_id, ['form' => $label]);

        if (! $live_subscribe) {
            $add('skip', 'newsletter_subscribe', 'Skipped live subscribe (pass &live_subscribe=1).', ['form' => $label]);
            continue;
        }

        $key = $provider . ':' . $list_id;
        if (isset($newsletter_tested[$key])) {
            $add('skip', 'newsletter_subscribe', 'Already tested this list this run.', ['form' => $label]);
            continue;
        }
        $newsletter_tested[$key] = true;

        if ($provider === 'mailchimp' && function_exists('matrix_add_mailchimp_subscriber')) {
            $result = matrix_add_mailchimp_subscriber($test_email, [
                'FNAME' => 'Form',
                'LNAME' => 'Smoke',
            ], $list_id);
            if (! empty($result['ok'])) {
                $add('pass', 'newsletter_subscribe', (string) ($result['message'] ?? 'subscribed'), [
                    'form' => $label,
                    'list_id' => $list_id,
                ]);
            } else {
                $add('fail', 'newsletter_subscribe', (string) ($result['message'] ?? 'subscribe failed'), [
                    'form' => $label,
                    'list_id' => $list_id,
                    'code' => $result['code'] ?? null,
                ]);
            }
        } elseif ($provider === 'brevo' && function_exists('matrix_add_brevo_contact')) {
            $result = matrix_add_brevo_contact($test_email, [
                'FIRSTNAME' => 'Form',
                'LASTNAME' => 'Smoke',
            ], [(int) $list_id]);
            if (! empty($result['ok'])) {
                $add('pass', 'newsletter_subscribe', (string) ($result['message'] ?? 'subscribed'), [
                    'form' => $label,
                    'list_id' => $list_id,
                ]);
            } else {
                $add('fail', 'newsletter_subscribe', (string) ($result['message'] ?? 'subscribe failed'), [
                    'form' => $label,
                    'list_id' => $list_id,
                    'code' => $result['code'] ?? null,
                ]);
            }
        } else {
            $add('fail', 'newsletter_subscribe', 'Subscribe helper missing for provider ' . $provider, ['form' => $label]);
        }
    }
}

$results['form_count'] = count($results['forms']);
$results['ok'] = $results['summary']['fail'] === 0;

echo wp_json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
