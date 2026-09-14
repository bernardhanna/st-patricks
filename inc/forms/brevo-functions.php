<?php

function matrix_get_brevo_api_key()
{
    if (defined('MATRIX_BREVO_KEY') && MATRIX_BREVO_KEY) {
        return trim((string) MATRIX_BREVO_KEY);
    }

    if (function_exists('get_field')) {
        return trim((string) get_field('brevo_api_key', 'option'));
    }

    return '';
}

/**
 * @return array<int, string> List ID => name
 */
function matrix_parse_brevo_lists_payload($payload)
{
    $lists = [];

    if (! is_array($payload) || empty($payload['lists']) || ! is_array($payload['lists'])) {
        return $lists;
    }

    foreach ($payload['lists'] as $list) {
        if (! is_array($list)) {
            continue;
        }

        $id = absint($list['id'] ?? 0);
        $name = trim((string) ($list['name'] ?? ''));

        if ($id > 0) {
            $lists[$id] = $name !== '' ? $name : ('List #' . $id);
        }
    }

    return $lists;
}

/**
 * @param list<int|string> $values
 * @return list<int>
 */
function matrix_parse_brevo_list_ids($values)
{
    $ids = [];

    if (is_string($values) || is_numeric($values)) {
        $values = preg_split('/[,\s;]+/', (string) $values) ?: [];
    }

    if (! is_array($values)) {
        return [];
    }

    foreach ($values as $value) {
        $id = absint($value);
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return array_values(array_unique($ids));
}

/**
 * @return array<int, string>
 */
function matrix_get_brevo_contact_lists($force_refresh = false)
{
    $cache_key = 'matrix_brevo_contact_lists';

    if (! $force_refresh && function_exists('get_transient')) {
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $api_key = matrix_get_brevo_api_key();
    if ($api_key === '' || ! function_exists('wp_remote_get')) {
        return [];
    }

    $lists = [];
    $limit = 50;
    $offset = 0;
    $max_lists = 500;

    do {
        $url = 'https://api.brevo.com/v3/contacts/lists?limit=' . $limit . '&offset=' . $offset;
        $response = wp_remote_get($url, [
            'headers' => [
                'accept' => 'application/json',
                'api-key' => $api_key,
            ],
            'timeout' => 12,
        ]);

        if (is_wp_error($response)) {
            break;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $payload = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($code < 200 || $code >= 300 || ! is_array($payload)) {
            break;
        }

        $page = matrix_parse_brevo_lists_payload($payload);
        if ($page === []) {
            break;
        }

        foreach ($page as $id => $name) {
            $lists[$id] = $name;
        }

        $offset += $limit;
        $count = absint($payload['count'] ?? 0);
        $fetched_all = $count > 0 ? $offset >= $count : count($page) < $limit;
    } while (! $fetched_all && count($lists) < $max_lists);

    if ($lists !== [] && function_exists('set_transient')) {
        $ttl = defined('MINUTE_IN_SECONDS') ? 10 * MINUTE_IN_SECONDS : 600;
        set_transient($cache_key, $lists, $ttl);
    }

    return $lists;
}

function matrix_brevo_list_signature($list_id)
{
    $list_id = absint($list_id);
    $salt = function_exists('wp_salt') ? (string) wp_salt('auth') : 'matrix-brevo';

    return hash_hmac('sha256', (string) $list_id, $salt);
}

function matrix_brevo_list_signature_is_valid($list_id, $signature)
{
    $list_id = absint($list_id);
    $signature = trim((string) $signature);

    if ($list_id <= 0 || $signature === '') {
        return false;
    }

    return hash_equals(matrix_brevo_list_signature($list_id), $signature);
}

function matrix_get_signed_brevo_list_id_from_request($request = null)
{
    $request = is_array($request) ? $request : $_POST;
    $list_id = absint($request['_cfg_brevo_list_id'] ?? 0);
    $signature = sanitize_text_field((string) ($request['_cfg_brevo_sig'] ?? ''));

    if (! matrix_brevo_list_signature_is_valid($list_id, $signature)) {
        return 0;
    }

    return $list_id;
}

/**
 * @param array<string, string> $attributes
 * @param list<int> $list_ids
 * @return array{ok: bool, code: int, message: string}
 */
function matrix_add_brevo_contact($email, array $attributes = [], array $list_ids = [])
{
    $email = sanitize_email((string) $email);
    if ($email === '' || ! is_email($email)) {
        return [
            'ok' => false,
            'code' => 400,
            'message' => 'Please enter a valid email address.',
        ];
    }

    $api_key = matrix_get_brevo_api_key();
    if ($api_key === '') {
        return [
            'ok' => false,
            'code' => 500,
            'message' => 'Missing Brevo API key.',
        ];
    }

    $body = [
        'email' => $email,
        'updateEnabled' => true,
        'attributes' => array_filter($attributes, static function ($value) {
            return $value !== null && $value !== '';
        }),
    ];

    $list_ids = matrix_parse_brevo_list_ids($list_ids);
    if ($list_ids !== []) {
        $body['listIds'] = $list_ids;
    }

    $response = wp_remote_post('https://api.brevo.com/v3/contacts', [
        'headers' => [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'api-key' => $api_key,
        ],
        'timeout' => 12,
        'body' => wp_json_encode($body),
        'method' => 'POST',
    ]);

    $code = (int) wp_remote_retrieve_response_code($response);
    $raw = (string) wp_remote_retrieve_body($response);

    if (in_array($code, [200, 201, 204], true)) {
        $message = function_exists('get_field')
            ? (string) get_field('brevo_default_confirm_message', 'option')
            : '';

        return [
            'ok' => true,
            'code' => $code,
            'message' => $message !== '' ? $message : 'Thanks — you’re subscribed!',
        ];
    }

    $json = json_decode($raw, true);
    $error = is_array($json) && ! empty($json['message']) ? (string) $json['message'] : 'Subscription failed.';
    $fallback = function_exists('get_field') ? (string) get_field('brevo_error_message', 'option') : '';

    return [
        'ok' => false,
        'code' => $code ?: 400,
        'message' => $error !== '' ? $error : ($fallback !== '' ? $fallback : 'Sorry, something went wrong. Please try again.'),
    ];
}

function matrix_theme_form_has_brevo_consent(array $fields)
{
    foreach (['consent_mailing_list', 'consent_newsletter', 'consent'] as $key) {
        $value = strtolower(trim((string) ($fields[$key] ?? '')));
        if (in_array($value, ['1', 'yes', 'true', 'on'], true)) {
            return true;
        }
    }

    return false;
}

function matrix_maybe_subscribe_theme_form_to_brevo(array $fields, $request = null)
{
    $list_id = matrix_get_signed_brevo_list_id_from_request($request);
    if ($list_id <= 0 || ! matrix_theme_form_has_brevo_consent($fields)) {
        return;
    }

    $email = sanitize_email((string) ($fields['email'] ?? ''));
    if ($email === '' || ! is_email($email)) {
        return;
    }

    matrix_add_brevo_contact($email, array_filter([
        'FIRSTNAME' => trim((string) ($fields['first_name'] ?? '')),
        'LASTNAME' => trim((string) ($fields['last_name'] ?? '')),
        'ROLE' => trim((string) ($fields['role'] ?? '')),
        'CONSENT' => 'yes',
        'CONSENT_IP' => sanitize_text_field((string) ($_SERVER['REMOTE_ADDR'] ?? '')),
        'CONSENT_AT' => function_exists('current_time') ? current_time('mysql') : gmdate('Y-m-d H:i:s'),
    ]), [$list_id]);
}

if (function_exists('add_filter')) {
add_filter('acf/load_field/name=brevo_list_id', function ($field) {
    if (! is_array($field)) {
        return $field;
    }

    $lists = matrix_get_brevo_contact_lists();
    $field['choices'] = [];

    foreach ($lists as $id => $name) {
        $field['choices'][(string) $id] = $name . ' (#' . $id . ')';
    }

    $current = $field['value'] ?? '';
    if ($current !== '' && $current !== null && ! isset($field['choices'][(string) $current])) {
        $field['choices'][(string) $current] = 'List #' . $current . ' (currently selected)';
    }

    if ($field['choices'] === []) {
        $field['instructions'] = 'No Brevo lists found. Add MATRIX_BREVO_KEY to wp-config.php, or paste an API key under Theme Options → Newsletter.';
    }

    return $field;
});
}
