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
