<?php

/**
 * Shared WordPress function stubs for the DB-free unit suite.
 *
 * Pest runs every tests/Unit/*.php file in a single PHP process. Because a PHP
 * function can never be undefined once declared, the old pattern of each test
 * file declaring its own `if (! function_exists('foo')) { function foo() {...} }`
 * leaked across files: the first declaration won and every later test silently
 * ran against the wrong stub, making results depend on file order.
 *
 * This layer fixes that. Each WordPress function is declared exactly once here
 * and reads its behaviour from a per-test registry. Tests set the behaviour they
 * need via __wp_stub(); the registry is reset before every test (see Pest.php),
 * so nothing leaks. Real WordPress (the Integration suite) is untouched: every
 * stub is guarded by function_exists().
 */

if (! defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

if (! isset($GLOBALS['__wp_stubs'])) {
    $GLOBALS['__wp_stubs'] = [];
}

/**
 * Register the behaviour of a stubbed WordPress function for the current test.
 *
 * @param mixed $value A literal return value, or a callable invoked with the
 *                     function's arguments.
 */
function __wp_stub(string $name, $value): void
{
    $GLOBALS['__wp_stubs'][$name] = $value;
}

/** Forget every registered stub. Called before each test. */
function __wp_stub_reset(): void
{
    $GLOBALS['__wp_stubs'] = [];
}

/** Resolve a stub's value, falling back to $default when it is not registered. */
function __wp_stub_value(string $name, $default, array $args = [])
{
    if (! array_key_exists($name, $GLOBALS['__wp_stubs'])) {
        return $default;
    }

    $value = $GLOBALS['__wp_stubs'][$name];

    return is_callable($value) ? $value(...$args) : $value;
}

/* -------------------------------------------------------------------------
 | Escaping, i18n and template helpers (deterministic — no registry needed)
 | ------------------------------------------------------------------------- */

if (! function_exists('esc_html')) {
    function esc_html($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_textarea')) {
    function esc_textarea($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_url')) {
    function esc_url($value)
    {
        return (string) $value;
    }
}

if (! function_exists('esc_url_raw')) {
    function esc_url_raw($value)
    {
        return (string) $value;
    }
}

if (! function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return (string) $text;
    }
}

if (! function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default')
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default')
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('_e')) {
    function _e($text, $domain = 'default')
    {
        echo (string) $text;
    }
}

if (! function_exists('selected')) {
    function selected($selected, $current = true, $echo = true)
    {
        $result = ((string) $selected === (string) $current) ? ' selected="selected"' : '';

        if ($echo) {
            echo $result;
        }

        return $result;
    }
}

if (! function_exists('checked')) {
    function checked($checked, $current = true, $echo = true)
    {
        $result = ((string) $checked === (string) $current) ? ' checked="checked"' : '';

        if ($echo) {
            echo $result;
        }

        return $result;
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args)
    {
        return $value;
    }
}

/* -------------------------------------------------------------------------
 | WordPress data getters (registry-backed, default to "WordPress absent")
 | ------------------------------------------------------------------------- */

if (! function_exists('home_url')) {
    function home_url($path = '')
    {
        return __wp_stub_value('home_url', (string) $path, [$path]);
    }
}

if (! function_exists('get_permalink')) {
    function get_permalink($post = 0)
    {
        return __wp_stub_value('get_permalink', '', [$post]);
    }
}

if (! function_exists('get_the_title')) {
    function get_the_title($post = 0)
    {
        return __wp_stub_value('get_the_title', '', [$post]);
    }
}

if (! function_exists('get_the_excerpt')) {
    function get_the_excerpt($post = 0)
    {
        return __wp_stub_value('get_the_excerpt', '', [$post]);
    }
}

if (! function_exists('get_field')) {
    function get_field($field, $post_id = false)
    {
        return __wp_stub_value('get_field', null, [$field, $post_id]);
    }
}

if (! function_exists('get_posts')) {
    function get_posts($args = [])
    {
        return __wp_stub_value('get_posts', [], [$args]);
    }
}

if (! function_exists('get_pages')) {
    function get_pages($args = [])
    {
        return __wp_stub_value('get_pages', [], [$args]);
    }
}

if (! function_exists('get_page_by_path')) {
    function get_page_by_path($path, $output = OBJECT, $post_type = 'page')
    {
        return __wp_stub_value('get_page_by_path', null, [$path, $output, $post_type]);
    }
}

if (! function_exists('get_post_type_object')) {
    function get_post_type_object($post_type)
    {
        return __wp_stub_value('get_post_type_object', null, [$post_type]);
    }
}

if (! function_exists('get_option')) {
    function get_option($option, $default = false)
    {
        return __wp_stub_value('get_option', '', [$option, $default]);
    }
}

if (! function_exists('get_post_field')) {
    function get_post_field($field, $post_id = null, $context = 'display')
    {
        return __wp_stub_value('get_post_field', '', [$field, $post_id, $context]);
    }
}

if (! function_exists('get_post_type')) {
    function get_post_type($post = null)
    {
        return __wp_stub_value('get_post_type', '', [$post]);
    }
}

if (! function_exists('get_post_type_archive_link')) {
    function get_post_type_archive_link($post_type)
    {
        return __wp_stub_value('get_post_type_archive_link', '', [$post_type]);
    }
}

if (! function_exists('get_template_part')) {
    function get_template_part($slug, $name = null, $args = [])
    {
        return __wp_stub_value('get_template_part', null, [$slug, $name, $args]);
    }
}
