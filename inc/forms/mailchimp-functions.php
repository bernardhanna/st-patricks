<?php

function matrix_get_mailchimp_api_key()
{
    if (defined('MATRIX_MAILCHIMP_KEY') && MATRIX_MAILCHIMP_KEY) {
        return trim((string) MATRIX_MAILCHIMP_KEY);
    }

    if (function_exists('get_field')) {
        return trim((string) get_field('mailchimp_api_key', 'option'));
    }

    return '';
}

function matrix_get_mailchimp_datacenter($api_key = '')
{
    $api_key = $api_key !== '' ? $api_key : matrix_get_mailchimp_api_key();
    if ($api_key === '') {
        return '';
    }

    $parts = explode('-', $api_key);
    $dc = strtolower(trim((string) end($parts)));

    return preg_match('/^[a-z]{2,}\d+$/', $dc) ? $dc : '';
}

/**
 * @return array<string, string> List ID => name
 */
function matrix_parse_mailchimp_lists_payload($payload)
{
    $lists = [];

    if (! is_array($payload) || empty($payload['lists']) || ! is_array($payload['lists'])) {
        return $lists;
    }

    foreach ($payload['lists'] as $list) {
        if (! is_array($list)) {
            continue;
        }

        $id = function_exists('matrix_sanitize_newsletter_list_id')
            ? matrix_sanitize_newsletter_list_id($list['id'] ?? '')
            : preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($list['id'] ?? ''));
        $name = trim((string) ($list['name'] ?? ''));

        if ($id !== '') {
            $lists[$id] = $name !== '' ? $name : ('Audience ' . $id);
        }
    }

    return $lists;
}

/**
 * @return array<string, string>
 */
function matrix_get_mailchimp_audiences($force_refresh = false)
{
    $cache_key = 'matrix_mailchimp_audiences';

    if (! $force_refresh && function_exists('get_transient')) {
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $api_key = matrix_get_mailchimp_api_key();
    $dc = matrix_get_mailchimp_datacenter($api_key);
    if ($api_key === '' || $dc === '' || ! function_exists('wp_remote_get')) {
        return [];
    }

    $lists = [];
    $offset = 0;
    $count = 100;

    do {
        $url = sprintf(
            'https://%s.api.mailchimp.com/3.0/lists?count=%d&offset=%d',
            rawurlencode($dc),
            $count,
            $offset
        );
        $response = wp_remote_get($url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('anystring:' . $api_key),
            ],
            'timeout' => 12,
        ]);

        $code = (int) wp_remote_retrieve_response_code($response);
        $payload = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($code < 200 || $code >= 300 || ! is_array($payload)) {
            break;
        }

        $page = matrix_parse_mailchimp_lists_payload($payload);
        $lists += $page;
        $total = absint($payload['total_items'] ?? 0);
        $offset += $count;
    } while ($page !== [] && $offset < $total);

    if ($lists !== [] && function_exists('set_transient')) {
        set_transient($cache_key, $lists, 15 * (defined('MINUTE_IN_SECONDS') ? MINUTE_IN_SECONDS : 60));
    }

    return $lists;
}

function matrix_resolve_subscribe_mailchimp_list_id($request = null)
{
    $request = is_array($request) ? $request : $_POST;

    $signed = function_exists('matrix_get_signed_newsletter_list_id_from_request')
        ? matrix_get_signed_newsletter_list_id_from_request($request)
        : '';
    if ($signed !== '') {
        return $signed;
    }

    return function_exists('get_field')
        ? (function_exists('matrix_sanitize_newsletter_list_id')
            ? matrix_sanitize_newsletter_list_id(get_field('mailchimp_list_id', 'option'))
            : trim((string) get_field('mailchimp_list_id', 'option')))
        : '';
}

/**
 * @param array<string, string> $merge_fields
 * @return array{ok: bool, code: int, message: string}
 */
function matrix_add_mailchimp_subscriber($email, array $merge_fields = [], $list_id = '')
{
    $email = sanitize_email((string) $email);
    if ($email === '' || ! is_email($email)) {
        return [
            'ok' => false,
            'code' => 400,
            'message' => 'Please enter a valid email address.',
        ];
    }

    $api_key = matrix_get_mailchimp_api_key();
    $dc = matrix_get_mailchimp_datacenter($api_key);
    $list_id = function_exists('matrix_sanitize_newsletter_list_id')
        ? matrix_sanitize_newsletter_list_id($list_id)
        : preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $list_id);

    if ($api_key === '' || $dc === '') {
        return [
            'ok' => false,
            'code' => 500,
            'message' => 'Missing Mailchimp API key.',
        ];
    }

    if ($list_id === '') {
        return [
            'ok' => false,
            'code' => 500,
            'message' => 'Newsletter is not configured.',
        ];
    }

    $double_opt_in = function_exists('get_field')
        ? (bool) get_field('mailchimp_double_opt_in', 'option')
        : true;
    $status = $double_opt_in ? 'pending' : 'subscribed';

    $hash = md5(strtolower($email));
    $url = sprintf(
        'https://%s.api.mailchimp.com/3.0/lists/%s/members/%s',
        rawurlencode($dc),
        rawurlencode($list_id),
        rawurlencode($hash)
    );

    $body = [
        'email_address' => $email,
        'status_if_new' => $status,
        'status' => $status,
        'merge_fields' => array_filter($merge_fields, static function ($value) {
            return $value !== null && $value !== '';
        }),
    ];

    $response = wp_remote_request($url, [
        'method' => 'PUT',
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode('anystring:' . $api_key),
        ],
        'timeout' => 12,
        'body' => wp_json_encode($body),
    ]);

    $code = (int) wp_remote_retrieve_response_code($response);
    $raw = (string) wp_remote_retrieve_body($response);
    $json = json_decode($raw, true);
    $title = is_array($json) ? strtolower((string) ($json['title'] ?? '')) : '';

    if (in_array($code, [200, 201], true) || str_contains($title, 'member exists')) {
        $message = function_exists('get_field')
            ? (string) get_field('mailchimp_success_message', 'option')
            : '';
        if ($message === '' && function_exists('get_field')) {
            $message = (string) get_field('brevo_default_confirm_message', 'option');
        }

        if ($message === '') {
            $message = $double_opt_in
                ? 'Thanks — please check your email to confirm your subscription.'
                : 'Thanks — you’re subscribed!';
        }

        return [
            'ok' => true,
            'code' => $code ?: 200,
            'message' => $message,
        ];
    }

    $error = is_array($json) && ! empty($json['detail'])
        ? (string) $json['detail']
        : (is_array($json) && ! empty($json['title']) ? (string) $json['title'] : 'Subscription failed.');
    $fallback = function_exists('get_field') ? (string) get_field('mailchimp_error_message', 'option') : '';
    if ($fallback === '' && function_exists('get_field')) {
        $fallback = (string) get_field('brevo_error_message', 'option');
    }

    return [
        'ok' => false,
        'code' => $code ?: 400,
        'message' => $fallback !== '' ? $fallback : $error,
    ];
}

if (function_exists('add_filter')) {
    add_filter('acf/load_field/name=newsletter_list_id', function ($field) {
        if (! is_array($field)) {
            return $field;
        }

        $provider = function_exists('matrix_get_newsletter_provider')
            ? matrix_get_newsletter_provider()
            : 'mailchimp';
        $field['choices'] = [];

        if ($provider === 'brevo' && function_exists('matrix_get_brevo_contact_lists')) {
            $field['label'] = 'Brevo list';
            $field['instructions'] = 'Choose which mailing list this form subscribes to. Leave empty to use the default list from Theme Options → Newsletter.';
            foreach (matrix_get_brevo_contact_lists() as $id => $name) {
                $field['choices'][(string) $id] = $name . ' (#' . $id . ')';
            }
        } else {
            $field['label'] = 'Mailchimp audience';
            $field['instructions'] = 'Choose which Mailchimp audience this form subscribes to. Leave empty to use the default audience from Theme Options → Newsletter.';
            foreach (matrix_get_mailchimp_audiences() as $id => $name) {
                $field['choices'][(string) $id] = $name . ' (' . $id . ')';
            }
        }

        $current = $field['value'] ?? '';
        if ($current !== '' && $current !== null && ! isset($field['choices'][(string) $current])) {
            $field['choices'][(string) $current] = 'List ' . $current . ' (currently selected)';
        }

        if ($field['choices'] === []) {
            $field['instructions'] = $provider === 'brevo'
                ? 'No Brevo lists found. Add MATRIX_BREVO_KEY to wp-config.php, or paste an API key under Theme Options → Newsletter.'
                : 'No Mailchimp audiences found. Add MATRIX_MAILCHIMP_KEY to wp-config.php (key ending in -us21, etc.), or paste an API key under Theme Options → Newsletter.';
        }

        return $field;
    });
}
