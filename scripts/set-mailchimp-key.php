<?php
/**
 * One-off: store the Mailchimp API key on this environment.
 * POST mailchimp_key. Does not print the secret.
 *
 * HTTP: /wp-content/themes/matrix-starter/scripts/set-mailchimp-key.php?key=set-mailchimp-key
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'set-mailchimp-key') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: application/json; charset=utf-8');
}

$api_key = trim((string) ($_POST['mailchimp_key'] ?? ''));
if (! preg_match('/^[a-f0-9]{16,}-[a-z]{2,}\d+$/i', $api_key)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid-key']);
    exit;
}

$saved_option = function_exists('update_field')
    ? (bool) update_field('mailchimp_api_key', $api_key, 'option')
    : (bool) update_option('options_mailchimp_api_key', $api_key, false);

if (get_option('options_mailchimp_api_key') !== $api_key) {
    update_option('options_mailchimp_api_key', $api_key, false);
}

$config_status = 'skipped';
$config_path = ABSPATH . 'wp-config.php';
$config = is_readable($config_path) ? file_get_contents($config_path) : false;
if (! is_string($config)) {
    $config_status = 'unreadable';
} elseif (str_contains($config, 'MATRIX_MAILCHIMP_KEY')) {
    $config_status = 'already-present';
} else {
    $snippet = "\nif ( ! defined( 'MATRIX_MAILCHIMP_KEY' ) ) {\n\tdefine( 'MATRIX_MAILCHIMP_KEY', '" . $api_key . "' );\n}\n\n";
    $anchors = [
        "require_once ABSPATH . 'wp-settings.php';",
        "require_once( ABSPATH . 'wp-settings.php' );",
        "require_once(ABSPATH . 'wp-settings.php');",
    ];
    $replaced = false;
    foreach ($anchors as $anchor) {
        if (str_contains($config, $anchor)) {
            $config = str_replace($anchor, $snippet . $anchor, $config);
            $replaced = true;
            break;
        }
    }
    if (! $replaced) {
        $config_status = 'anchor-missing';
    } elseif (! is_writable($config_path)) {
        $config_status = 'not-writable';
    } else {
        $config_status = file_put_contents($config_path, $config) === false ? 'write-failed' : 'written';
    }
}

$dc = matrix_get_mailchimp_datacenter($api_key);
$ping = 'skipped';
if ($dc !== '' && function_exists('wp_remote_get')) {
    $response = wp_remote_get('https://' . rawurlencode($dc) . '.api.mailchimp.com/3.0/ping', [
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode('anystring:' . $api_key),
        ],
        'timeout' => 12,
    ]);
    $code = (int) wp_remote_retrieve_response_code($response);
    $ping = $code === 200 ? 'ok' : ('http-' . $code);
}

echo json_encode([
    'ok' => true,
    'saved_option' => $saved_option || get_option('options_mailchimp_api_key') === $api_key,
    'config' => $config_status,
    'datacenter' => $dc,
    'ping' => $ping,
], JSON_PRETTY_PRINT);
