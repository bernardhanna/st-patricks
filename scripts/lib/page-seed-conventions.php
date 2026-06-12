<?php

/**
 * Shared conventions for page seed scripts.
 *
 * @see docs/superpowers/page-build-rules.md
 */

if (! function_exists('matrix_page_seed_heading')) {
    /**
     * Return a valid flexi heading tag for the given outline level (1–6).
     */
    function matrix_page_seed_heading(int $level): string
    {
        $level = max(1, min(6, $level));

        return 'h' . $level;
    }
}

if (! function_exists('matrix_page_seed_strip_padding')) {
    /**
     * Remove padding_settings from a flexi row so templates use defaults.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function matrix_page_seed_strip_padding(array $row): array
    {
        unset($row['padding_settings']);

        return $row;
    }
}

if (! function_exists('matrix_page_seed_strip_padding_from_rows')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function matrix_page_seed_strip_padding_from_rows(array $rows): array
    {
        return array_map('matrix_page_seed_strip_padding', $rows);
    }
}
