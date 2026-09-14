<?php

/**
 * Shared docx parse/image helpers used by Orlaith Drive imports.
 */

if (! function_exists('matrix_orlaith_normalize_text')) {
    function matrix_orlaith_normalize_text(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x{00A0}\x{202F}\x{2007}\x{2009}]/u', ' ', $text);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }
}

if (! function_exists('matrix_orlaith_clean_heading')) {
    function matrix_orlaith_clean_heading(string $text): string
    {
        $text = matrix_orlaith_normalize_text(wp_strip_all_tags($text));
        $text = preg_replace('/^H[1-6]:\s*/i', '', $text);

        return trim((string) $text);
    }
}

if (! function_exists('matrix_orlaith_is_skip_line')) {
    function matrix_orlaith_is_skip_line(string $text): bool
    {
        $plain = matrix_orlaith_normalize_text(matrix_orlaith_clean_heading($text));
        if ($plain === '') {
            return true;
        }
        if (preg_match('/^(Page name|Service users and visitors section|Healthcare Professionals folder|About Us folder)/i', $plain)) {
            return true;
        }
        if (preg_match('/section\s+[–-]\s*Level\s*[0-9]/i', $plain)) {
            return true;
        }
        if (preg_match('/^Meta\s*(title|description)/i', $plain)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('matrix_orlaith_heading_match')) {
    /**
     * @return array{0:int,1:string}|null
     */
    function matrix_orlaith_heading_match(string $html): ?array
    {
        if (preg_match('#<(?:p|h[1-6])[^>]*>\s*(?:<strong>)?\s*H([1-6]):\s*(?:</strong>\s*<strong>)?(.*?)</(?:p|h[1-6])>#is', $html, $m)) {
            $inner = preg_replace('#<br\s*/?>.*$#is', '', $m[2]);

            return [(int) $m[1], matrix_orlaith_clean_heading((string) $inner)];
        }

        if (preg_match('#<h([1-6])[^>]*>(.*?)</h\1>#is', $html, $m)) {
            $title = matrix_orlaith_clean_heading((string) $m[2]);
            if ($title !== '' && strlen($title) <= 120) {
                return [(int) $m[1], $title];
            }
        }

        return null;
    }
}

if (! function_exists('matrix_orlaith_flexi_match')) {
    function matrix_orlaith_flexi_match(string $html): string
    {
        $plain = strtolower(matrix_orlaith_normalize_text(wp_strip_all_tags($html)));
        if (! preg_match('/flexi\s*blocks?|flexiblock/i', $plain)) {
            return '';
        }
        if (str_contains($plain, 'form')) {
            return 'form';
        }
        if (str_contains($plain, 'faq') || str_contains($plain, 'accordion')) {
            return 'accordion';
        }
        if (str_contains($plain, 'video')) {
            return 'video';
        }
        if (str_contains($plain, 'useful') || str_contains($plain, 'stories')) {
            return 'useful';
        }

        return $plain;
    }
}

if (! function_exists('matrix_orlaith_youtube_urls')) {
    /**
     * @return list<string>
     */
    function matrix_orlaith_youtube_urls(string $html): array
    {
        preg_match_all('#https?://(?:www\.)?(?:youtube\.com/watch\?[^"<\s]+|youtu\.be/[^"<\s]+)#i', $html, $m);

        return array_values(array_unique($m[0] ?? []));
    }
}

if (! function_exists('matrix_orlaith_parse_docx')) {
    /**
     * @return array{h1:string,intro:string,blocks:array<int,array<string,mixed>>,notes:list<string>}
     */
    function matrix_orlaith_parse_docx(string $docx_path): array
    {
        $tmp = sys_get_temp_dir() . '/orlaith-' . md5($docx_path) . '.html';
        $cmd = 'pandoc ' . escapeshellarg($docx_path) . ' -t html --wrap=none -o ' . escapeshellarg($tmp);
        exec($cmd, $out, $code);
        if ($code !== 0 || ! is_readable($tmp)) {
            return ['h1' => '', 'intro' => '', 'blocks' => [], 'notes' => ['pandoc failed for ' . basename($docx_path)]];
        }
        $html = (string) file_get_contents($tmp);
        $html = preg_replace('/[\x{00A0}\x{202F}\x{2007}\x{2009}]/u', ' ', $html);
        $html = preg_replace('#<img[^>]+src="media/[^"]+"[^>]*>#i', '', $html);
        $html = preg_replace('#<li>\s*<p>(.*?)</p>\s*</li>#is', '<li>$1</li>', $html);
        $html = preg_replace('#</?mark>#i', '', $html);
        $html = preg_replace('#</?u>#i', '', $html);
        $html = preg_replace_callback('#<h2[^>]*>(.*?)</h2>#is', static function ($m) {
            $plain = matrix_orlaith_clean_heading($m[1]);
            if (preg_match('/^H[1-6]:/i', wp_strip_all_tags($m[1]))) {
                return $m[0];
            }
            if (strlen($plain) > 90) {
                return '<p>' . $m[1] . '</p>';
            }

            return $m[0];
        }, $html);
        $html = preg_replace(
            '#<p>\s*<strong>H1:</strong>\s*<strong>(.*?)</strong>\s*<br\s*/?>\s*(.*?)</p>#is',
            '<p><strong>H1: $1</strong></p><p>$2</p>',
            $html
        );

        $chunks = preg_split('/(?=<(?:p|h[1-6])\b)/i', $html) ?: [];
        $h1 = '';
        $intro_parts = [];
        $blocks = [];
        $notes = [];
        $mode = 'preamble';
        $current = null;

        $flush = static function () use (&$blocks, &$current): void {
            if (! is_array($current)) {
                return;
            }
            if (($current['type'] ?? '') === 'content') {
                $current['html'] = trim((string) ($current['html'] ?? ''));
                if ($current['heading'] !== '' || $current['html'] !== '') {
                    $blocks[] = $current;
                }
            } elseif (($current['type'] ?? '') === 'accordion') {
                if (! empty($current['items'])) {
                    $blocks[] = $current;
                }
            } elseif (($current['type'] ?? '') === 'videos' && ! empty($current['urls'])) {
                $blocks[] = $current;
            }
            $current = null;
        };

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            $plain = wp_strip_all_tags($chunk);
            $flexi = matrix_orlaith_flexi_match($chunk);
            if ($flexi !== '') {
                if (str_contains($flexi, 'form')) {
                    $notes[] = 'Registration / Gravity form mentioned in draft — not created automatically.';
                    $mode = 'skip_form';
                    $flush();
                    continue;
                }
                if (str_contains($flexi, 'faq') || str_contains($flexi, 'accordion')) {
                    $flush();
                    $current = ['type' => 'accordion', 'heading' => '', 'items' => [], 'item' => null];
                    $mode = 'accordion';
                    continue;
                }
                if (str_contains($flexi, 'video')) {
                    $flush();
                    $current = ['type' => 'videos', 'heading' => 'Video resources', 'urls' => matrix_orlaith_youtube_urls($chunk)];
                    $mode = 'videos';
                    continue;
                }
                if (str_contains($flexi, 'useful') || str_contains($flexi, 'stories')) {
                    $flush();
                    $mode = 'preamble';
                    continue;
                }
            }
            if (preg_match('/Video embed:\s*(https?:\/\/\S+)/i', $plain, $vm)) {
                $flush();
                $blocks[] = ['type' => 'videos', 'heading' => 'Video', 'urls' => [$vm[1]]];
                continue;
            }
            if ($mode === 'skip_form') {
                continue;
            }
            if (matrix_orlaith_is_skip_line($plain)) {
                continue;
            }

            $heading = matrix_orlaith_heading_match($chunk);
            if ($heading) {
                [$level, $title] = $heading;
                if ($title === '') {
                    continue;
                }
                if ($level === 1 || ($h1 === '' && $level === 0 && $mode === 'preamble')) {
                    $h1 = $title;
                    $mode = 'intro';
                    continue;
                }
                if ($level <= 2 || ($level === 0 && $mode !== 'accordion')) {
                    $flush();
                    $current = ['type' => 'content', 'heading' => $title, 'heading_tag' => 'h2', 'html' => ''];
                    $mode = 'content';
                    continue;
                }
                if ($mode === 'accordion' && $level >= 3) {
                    if (is_array($current['item'] ?? null)) {
                        $current['items'][] = $current['item'];
                    }
                    $current['item'] = ['title' => $title, 'html' => ''];
                    continue;
                }
                if ($mode === 'content' && is_array($current) && $level >= 3) {
                    $tag = 'h' . min(6, $level);
                    $current['html'] .= '<' . $tag . '>' . esc_html($title) . '</' . $tag . '>';
                    continue;
                }
            }

            $youtube = matrix_orlaith_youtube_urls($chunk);
            if ($mode === 'videos' && $youtube !== [] && is_array($current)) {
                $current['urls'] = array_values(array_unique(array_merge($current['urls'], $youtube)));
                continue;
            }

            $body = trim($chunk);
            if ($body === '') {
                continue;
            }
            if ($mode === 'intro') {
                $intro_parts[] = $body;
                continue;
            }
            if ($mode === 'accordion' && is_array($current)) {
                if (! is_array($current['item'] ?? null)) {
                    $current['item'] = ['title' => '', 'html' => ''];
                }
                $current['item']['html'] .= $body;
                continue;
            }
            if ($mode === 'content' && is_array($current)) {
                $current['html'] .= $body;
            }
        }

        if (is_array($current) && ($current['type'] ?? '') === 'accordion' && is_array($current['item'] ?? null)) {
            $current['items'][] = $current['item'];
            unset($current['item']);
        }
        $flush();

        return [
            'h1' => $h1,
            'intro' => implode('', $intro_parts),
            'blocks' => $blocks,
            'notes' => $notes,
        ];
    }
}

if (! function_exists('matrix_orlaith_import_image')) {
    function matrix_orlaith_import_image(string $file, string $title, int $parent_id = 0): int
    {
        if (! is_readable($file) || ! preg_match('/\.(jpe?g|png|gif|webp)$/i', $file)) {
            return 0;
        }
        $key = 'drive:' . md5($file);
        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_key' => '_matrix_drive_source',
            'meta_value' => $key,
            'fields' => 'ids',
        ]);
        if ($existing !== []) {
            return (int) $existing[0];
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = wp_tempnam(basename($file));
        if (! $tmp || ! copy($file, $tmp)) {
            return 0;
        }
        $attachment_id = media_handle_sideload([
            'name' => sanitize_file_name(basename($file)),
            'tmp_name' => $tmp,
        ], $parent_id, $title);
        if (is_wp_error($attachment_id)) {
            @unlink($tmp);

            return 0;
        }
        update_post_meta((int) $attachment_id, '_matrix_drive_source', $key);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_orlaith_first_image')) {
    function matrix_orlaith_first_image(string $dir): string
    {
        $files = glob($dir . '/*.{png,jpg,jpeg,webp,gif}', GLOB_BRACE) ?: [];

        return $files[0] ?? '';
    }
}
