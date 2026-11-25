<?php
/**
 * Report Mailchimp key availability without printing the secret.
 * HTTP: /wp-content/themes/matrix-starter/scripts/probe-mailchimp-key.php?key=probe-mailchimp-key
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'probe-mailchimp-key') {
        http_response_code(403);
        exit;
    }
    define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$describe = static function ($value): array {
    $value = is_string($value) ? trim($value) : '';
    if ($value === '') {
        return ['present' => false, 'length' => 0, 'datacenter' => ''];
    }
    $dc = function_exists('matrix_get_mailchimp_datacenter')
        ? matrix_get_mailchimp_datacenter($value)
        : '';

    return [
        'present' => $value !== '',
        'length' => strlen($value),
        'datacenter' => $dc,
    ];
};

try {
$constant = (defined('MATRIX_MAILCHIMP_KEY') && is_string(MATRIX_MAILCHIMP_KEY))
    ? MATRIX_MAILCHIMP_KEY
    : '';
$acf = function_exists('get_field') ? get_field('mailchimp_api_key', 'option') : null;
$option = function_exists('get_option') ? get_option('options_mailchimp_api_key', '') : '';
$resolved = function_exists('matrix_get_mailchimp_api_key') ? matrix_get_mailchimp_api_key() : '';

echo json_encode([
    'provider' => function_exists('matrix_get_newsletter_provider') ? matrix_get_newsletter_provider() : '',
    'constant_defined' => defined('MATRIX_MAILCHIMP_KEY'),
    'constant' => $describe($constant),
    'acf' => $describe(is_string($acf) ? $acf : ''),
    'acf_type' => gettype($acf),
    'option' => $describe(is_string($option) ? $option : ''),
    'option_type' => gettype($option),
    'resolved' => $describe($resolved),
    'default_list_len' => strlen((string) (function_exists('get_field') ? get_field('mailchimp_list_id', 'option') : '')),
], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo $e::class . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
}
