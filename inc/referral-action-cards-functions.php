<?php

function matrix_normalize_referral_action_card($card, $defaults = [])
{
    $button = is_array($card['button'] ?? null) ? $card['button'] : [];
    $background_color = trim((string) ($card['background_color'] ?? ''));
    $action_icon = trim((string) ($card['action_icon'] ?? ''));

    if ($background_color === '') {
        $background_color = (string) ($defaults['background_color'] ?? '#FFFFFF');
    }

    if (! in_array($action_icon, ['external', 'download'], true)) {
        $action_icon = (string) ($defaults['action_icon'] ?? 'external');
    }

    $button_url = trim((string) ($button['url'] ?? ''));

    return [
        'title' => trim((string) ($card['title'] ?? '')),
        'description' => trim((string) ($card['description'] ?? '')),
        'button' => [
            'title' => trim((string) ($button['title'] ?? '')),
            'url' => $button_url,
            'target' => matrix_normalize_link_target($button_url, (string) ($button['target'] ?? '')),
        ],
        'action_icon' => $action_icon,
        'background_color' => $background_color,
    ];
}

function matrix_referral_action_card_has_button($card)
{
    $button = is_array($card['button'] ?? null) ? $card['button'] : [];

    return trim((string) ($button['title'] ?? '')) !== ''
        && trim((string) ($button['url'] ?? '')) !== '';
}

function matrix_get_referral_action_card_icon_svg($icon_type)
{
    $icon_type = trim((string) $icon_type);

    if ($icon_type === 'download') {
        return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 10.5V12.5C2 12.7761 2.22386 13 2.5 13H13.5C13.7761 13 14 12.7761 14 12.5V10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 3V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 7.5L8 10L10.5 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 10L10 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 3H12V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 9V12.5C10 12.7761 9.77614 13 9.5 13H3.5C3.22386 13 3 12.7761 3 12.5V6.5C3 6.22386 3.22386 6 3.5 6H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}
