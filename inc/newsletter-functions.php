<?php

if (! function_exists('matrix_default_newsletter_email_placeholder')) {
    function matrix_default_newsletter_email_placeholder(): string
    {
        return 'Enter email address';
    }
}

if (! function_exists('matrix_is_inaccessible_email_placeholder')) {
    function matrix_is_inaccessible_email_placeholder(string $value): bool
    {
        $normalized = strtolower(trim($value));

        return $normalized === ''
            || $normalized === 'joeblogs@mail.com'
            || $normalized === 'joeblogs@mail';
    }
}

if (! function_exists('matrix_get_newsletter_email_placeholder')) {
    function matrix_get_newsletter_email_placeholder(): string
    {
        $value = function_exists('get_field')
            ? trim((string) get_field('email_placeholder', 'option'))
            : '';

        if (matrix_is_inaccessible_email_placeholder($value)) {
            return matrix_default_newsletter_email_placeholder();
        }

        return $value;
    }
}

if (! function_exists('matrix_get_newsletter_provider')) {
    function matrix_get_newsletter_provider(): string
    {
        $value = function_exists('get_field')
            ? strtolower(trim((string) get_field('newsletter_provider', 'option')))
            : '';

        return $value === 'brevo' ? 'brevo' : 'mailchimp';
    }
}

if (! function_exists('matrix_sanitize_newsletter_list_id')) {
    function matrix_sanitize_newsletter_list_id($value): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $value) ?? '';
    }
}

if (! function_exists('matrix_newsletter_list_signature')) {
    function matrix_newsletter_list_signature(string $list_id, string $provider = ''): string
    {
        $list_id = matrix_sanitize_newsletter_list_id($list_id);
        $provider = $provider !== '' ? $provider : matrix_get_newsletter_provider();
        $salt = function_exists('wp_salt') ? (string) wp_salt('auth') : 'matrix-newsletter';

        return hash_hmac('sha256', $provider . '|' . $list_id, $salt);
    }
}

if (! function_exists('matrix_newsletter_list_signature_is_valid')) {
    function matrix_newsletter_list_signature_is_valid(string $list_id, string $signature, string $provider = ''): bool
    {
        $list_id = matrix_sanitize_newsletter_list_id($list_id);
        $signature = trim($signature);

        if ($list_id === '' || $signature === '') {
            return false;
        }

        return hash_equals(matrix_newsletter_list_signature($list_id, $provider), $signature);
    }
}

if (! function_exists('matrix_get_signed_newsletter_list_id_from_request')) {
    function matrix_get_signed_newsletter_list_id_from_request($request = null): string
    {
        $request = is_array($request) ? $request : $_POST;
        $list_id = matrix_sanitize_newsletter_list_id($request['_cfg_newsletter_list_id'] ?? '');
        $signature = sanitize_text_field((string) ($request['_cfg_newsletter_sig'] ?? ''));

        if (! matrix_newsletter_list_signature_is_valid($list_id, $signature)) {
            return '';
        }

        return $list_id;
    }
}

if (! function_exists('matrix_find_newsletter_list_id_by_name')) {
    /**
     * @param array<string, string> $audiences
     */
    function matrix_find_newsletter_list_id_by_name($audiences, string $needle): string
    {
        if (! is_array($audiences) || $needle === '') {
            return '';
        }

        $needle = strtolower($needle);

        foreach ($audiences as $id => $name) {
            if (str_contains(strtolower((string) $name), $needle)) {
                return matrix_sanitize_newsletter_list_id((string) $id);
            }
        }

        return '';
    }
}

if (! function_exists('matrix_newsletter_list_id_from_flexi_rows')) {
    /**
     * @param mixed $rows
     */
    function matrix_newsletter_list_id_from_flexi_rows($rows): string
    {
        if (! is_array($rows)) {
            return '';
        }

        foreach ($rows as $row) {
            if (! is_array($row) || ($row['acf_fc_layout'] ?? '') !== 'newsletter') {
                continue;
            }

            $id = matrix_sanitize_newsletter_list_id($row['newsletter_list_id'] ?? ($row['brevo_list_id'] ?? ''));
            if ($id !== '') {
                return $id;
            }
        }

        return '';
    }
}

if (! function_exists('matrix_resolve_gp_newsletter_list_id')) {
    /**
     * Prefer the Mailchimp audience already set on the GP e-newsletter campaign page,
     * then any audience whose name includes "GP".
     *
     * @param array<string, string>|null $audiences
     */
    function matrix_resolve_gp_newsletter_list_id($audiences = null): string
    {
        if (function_exists('get_page_by_path') && function_exists('get_field')) {
            $page = get_page_by_path('subscribe-to-our-gp-enewsletter');
            $page_id = is_object($page) ? (int) ($page->ID ?? 0) : 0;
            if ($page_id > 0) {
                $from_page = matrix_newsletter_list_id_from_flexi_rows(get_field('flexible_content_blocks', $page_id));
                if ($from_page !== '') {
                    return $from_page;
                }
            }
        }

        if ($audiences === null && function_exists('matrix_get_mailchimp_audiences')) {
            $audiences = matrix_get_mailchimp_audiences();
        }

        return matrix_find_newsletter_list_id_by_name(is_array($audiences) ? $audiences : [], 'GP');
    }
}

if (! function_exists('matrix_page_has_newsletter_block')) {
    function matrix_page_has_newsletter_block($post_id = null): bool
    {
        $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

        if ($post_id < 1 || ! function_exists('get_field')) {
            return false;
        }

        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            if (is_array($row) && ($row['acf_fc_layout'] ?? '') === 'newsletter') {
                return true;
            }
        }

        return false;
    }
}
