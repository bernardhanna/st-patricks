<?php

/**
 * Related Cards flexi block helpers.
 */

if (! function_exists('matrix_normalize_asset_url')) {
    function matrix_normalize_asset_url(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (function_exists('matrix_migrate_normalize_resolved_url')) {
            return matrix_migrate_normalize_resolved_url($url);
        }

        return (string) preg_replace(
            '#(\.(?:pdf|docx?|xlsx?|pptx?|zip|jpe?g|png|gif|webp|svg|mp4|mp3))/$#i',
            '$1',
            $url
        );
    }
}

if (! function_exists('matrix_normalize_related_card_link')) {
    /**
     * @param mixed $link
     * @return array{title: string, url: string, target: string}|null
     */
    function matrix_normalize_related_card_link($link): ?array
    {
        if (! is_array($link)) {
            return null;
        }

        $url = trim((string) ($link['url'] ?? ''));

        if ($url === '' || $url === '#') {
            return null;
        }

        $url = matrix_normalize_asset_url($url);

        $title = trim((string) ($link['title'] ?? ''));

        if ($title === '') {
            $title = __('Learn more', 'matrix-starter');
        }

        $target = matrix_normalize_link_target($url, (string) ($link['target'] ?? ''));

        return [
            'title' => $title,
            'url' => $url,
            'target' => $target,
        ];
    }
}

if (! function_exists('matrix_normalize_related_cards')) {
    /**
     * @param mixed $cards
     * @return array<int, array{title: string, description: string, image_id: int, link: array{title: string, url: string, target: string}}>
     */
    function matrix_normalize_related_cards($cards): array
    {
        if (! is_array($cards)) {
            return [];
        }

        $normalized = [];

        foreach ($cards as $card) {
            if (! is_array($card)) {
                continue;
            }

            $title = trim((string) ($card['title'] ?? ''));
            $link = matrix_normalize_related_card_link($card['link'] ?? null);

            if ($title === '' || $link === null) {
                continue;
            }

            $image_id = (int) ($card['image'] ?? 0);

            if ($image_id < 1) {
                $image_url = trim((string) ($card['image_url'] ?? ''));

                if ($image_url !== '' && function_exists('matrix_migrate_attachment_id_from_url')) {
                    $image_id = matrix_migrate_attachment_id_from_url($image_url);
                }
            }

            $normalized[] = [
                'title' => $title,
                'description' => trim((string) ($card['description'] ?? '')),
                'image_id' => max(0, $image_id),
                'link' => $link,
            ];
        }

        return $normalized;
    }
}
