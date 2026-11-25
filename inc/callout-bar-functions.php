<?php

/**
 * Resolve callout bar message text with a simple fallback.
 *
 * @param mixed $message
 * @param mixed $fallback
 * @return string
 */
function matrix_resolve_callout_bar_message($message, $fallback = '')
{
    $resolved_message = trim((string) $message);

    if ($resolved_message !== '') {
        return $resolved_message;
    }

    return trim((string) $fallback);
}

/**
 * Resolve callout bar visual style.
 *
 * @param mixed $style
 * @return string default|navy
 */
function matrix_resolve_callout_bar_style($style = '')
{
    $resolved = strtolower(trim((string) $style));

    return $resolved === 'navy' ? 'navy' : 'default';
}

/**
 * Inline styles for the callout bar background.
 */
function matrix_get_callout_bar_background_style(string $style = 'default'): string
{
    if (matrix_resolve_callout_bar_style($style) === 'navy') {
        return 'background-color: #08284B;';
    }

    return 'background-color: #F1F8F9;';
}

/**
 * Text colour classes for the callout bar message.
 */
function matrix_get_callout_bar_text_class_names(string $style = 'default'): string
{
    if (matrix_resolve_callout_bar_style($style) === 'navy') {
        return 'font-primary text-[18px] font-semibold leading-[28px] text-white [&_a]:text-white [&_a]:underline hover:[&_a]:no-underline';
    }

    return 'font-primary text-[18px] font-semibold leading-[28px] text-black [&_a]:text-[#024B79] [&_a]:underline hover:[&_a]:no-underline';
}

/**
 * Allow a minimal HTML subset in callout messages (links + emphasis).
 */
function matrix_kses_callout_bar_message(string $message): string
{
    if (function_exists('matrix_kses_rich_text')) {
        return matrix_kses_rich_text($message);
    }

    return wp_kses($message, [
        'a' => [
            'href' => true,
            'title' => true,
            'target' => true,
            'rel' => true,
            'class' => true,
        ],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'br' => [],
        'span' => [
            'class' => true,
        ],
    ]);
}
