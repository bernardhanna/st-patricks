<?php
/**
 * Review 2 Service Users link/copy fixes (Dean Clinic + residual).
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-su-links-batch.php?key=fix-su1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-su1') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$log = [];

$patch_all_strings = static function (int $post_id, callable $replacer) use (&$log): int {
    $hits = 0;
    if ($post_id < 1) {
        return 0;
    }

    $content = (string) get_post_field('post_content', $post_id);
    $next = $replacer($content);
    if ($next !== $content) {
        wp_update_post(['ID' => $post_id, 'post_content' => $next]);
        $hits++;
    }

    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return $hits;
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows)) {
        return $hits;
    }

    $walk = static function (&$node) use (&$walk, $replacer, &$hits): void {
        if (is_string($node)) {
            $next = $replacer($node);
            if ($next !== $node) {
                $node = $next;
                $hits++;
            }
            return;
        }
        if (! is_array($node)) {
            return;
        }
        foreach ($node as &$v) {
            $walk($v);
        }
        unset($v);
    };

    $before = wp_json_encode($rows);
    $walk($rows);
    $after = wp_json_encode($rows);
    if ($before !== $after) {
        update_field('flexible_content_blocks', $rows, $post_id);
        clean_post_cache($post_id);
    }

    return $hits;
};

// Dean Clinic copy/link wording
$dean_id = (int) (get_page_by_path('service-users-and-visitors/attending-a-dean-clinic')?->ID ?? 0);
$n = $patch_all_strings($dean_id, static function (string $html): string {
    $next = $html;

    // "…Dean Clinics Contact Us." → "…Dean Clinics" + separate Contact Us link already present,
    // or rewrite so Contact Us is not glued as trailing linked words incorrectly.
    $next = preg_replace(
        '#(addresses and directions for our Dean Clinics)\\s*<a[^>]*>\\s*Contact Us\\s*</a>\\.?#i',
        '$1.</p><p><a href="/contact-us/">Contact Us</a>',
        $next
    ) ?? $next;

    // If Contact Us is the link text for the directions sentence, keep link on Contact Us only after corrected sentence.
    $next = str_replace(
        'you can get addresses and directions for our Dean Clinics Contact Us.',
        'you can get addresses and directions for our Dean Clinics. <a href="/contact-us/">Contact Us</a>.',
        $next
    );
    $next = str_replace(
        'you can get addresses and directions for our Dean Clinics <a href="https://st-patricks.s1.matrix-test.com/contact-us/">Contact Us</a>.',
        'you can get addresses and directions for our Dean Clinics. See <a href="https://st-patricks.s1.matrix-test.com/contact-us/">Contact Us</a>.',
        $next
    );
    $next = str_replace(
        'you can get addresses and directions for our Dean Clinics <a href="/contact-us/">Contact Us</a>.',
        'you can get addresses and directions for our Dean Clinics. See <a href="/contact-us/">Contact Us</a>.',
        $next
    );

    // "reminders you will receive About Your Portal" → "…here" linked to About Your Portal
    $next = preg_replace(
        '#(reminders you will receive)\\s*<a([^>]*)>\\s*About Your Portal\\s*</a>#i',
        '$1 <a$2>here</a>',
        $next
    ) ?? $next;
    $next = str_replace(
        'reminders you will receive About Your Portal.',
        'reminders you will receive <a href="/your-portal/about-your-portal/">here</a>.',
        $next
    );

    return $next;
});
$log[] = "dean-clinic hits=$n";

// Cookie / Data protection footer labels via menu items if present
$menus = wp_get_nav_menus();
foreach ($menus as $menu) {
    $items = wp_get_nav_menu_items($menu->term_id) ?: [];
    foreach ($items as $item) {
        $title = (string) $item->title;
        $changed = false;
        if (strcasecmp($title, 'Cookies and privacy policy') === 0 || strcasecmp($title, 'Cookie and privacy policy') === 0) {
            wp_update_nav_menu_item($menu->term_id, (int) $item->ID, [
                'menu-item-title' => 'Cookie policy',
                'menu-item-object' => $item->object,
                'menu-item-object-id' => $item->object_id,
                'menu-item-type' => $item->type,
                'menu-item-status' => 'publish',
                'menu-item-url' => $item->url,
            ]);
            $changed = true;
            $log[] = "menu {$menu->name}: Cookies → Cookie policy (item {$item->ID})";
        }
        if (strcasecmp($title, 'Data protection policy') === 0) {
            wp_update_nav_menu_item($menu->term_id, (int) $item->ID, [
                'menu-item-title' => 'Data Protection Statement',
                'menu-item-object' => $item->object,
                'menu-item-object-id' => $item->object_id,
                'menu-item-type' => $item->type,
                'menu-item-status' => 'publish',
                'menu-item-url' => $item->url,
            ]);
            $changed = true;
            $log[] = "menu {$menu->name}: Data protection policy → Data Protection Statement (item {$item->ID})";
        }
        unset($changed);
    }
}

// Contact Us intro: Below → Here
$contact_id = (int) (get_page_by_path('contact-us')?->ID ?? 0);
$n = $patch_all_strings($contact_id, static function (string $html): string {
    return str_replace('Below you will find', 'Here you will find', $html);
});
$log[] = "contact-us hits=$n";

// Eating disorders brochure CTA — if plain text in hero button fields, leave structure;
// search for bare brochure phrasing without anchor
$edt_id = (int) (get_page_by_path('programmes-therapies/eating-disorders-treatment-information-programme')?->ID ?? 0);
if ($edt_id && function_exists('get_field')) {
    $rows = get_field('flexible_content_blocks', $edt_id);
    $changed = false;
    if (is_array($rows)) {
        foreach ($rows as &$row) {
            if (($row['acf_fc_layout'] ?? '') !== 'hero_with_breadcrumbs') {
                continue;
            }
            // If content has plain "Get the programme brochure" without link, wrap if brochure URL known in document_link / primary_button
            $content = (string) ($row['content'] ?? '');
            if (str_contains($content, 'Get the programme brochure') && ! str_contains($content, '<a')) {
                $btn = $row['primary_button'] ?? null;
                $url = is_array($btn) ? (string) ($btn['url'] ?? '') : '';
                if ($url === '' && ! empty($row['document_link'])) {
                    $url = is_string($row['document_link']) ? $row['document_link'] : (string) ($row['document_link']['url'] ?? '');
                }
                if ($url !== '') {
                    $row['content'] = str_replace(
                        'Get the programme brochure',
                        '<a class="btn" href="' . esc_url($url) . '">Get the programme brochure</a>',
                        $content
                    );
                    $changed = true;
                    $log[] = 'EDTIP: wrapped brochure text as CTA';
                } else {
                    $log[] = 'EDTIP: brochure text present but no URL found — leave Open';
                }
            }
            // Or primary_button title exists but not shown — ensure title set
            if (is_array($row['primary_button'] ?? null)) {
                $title = trim((string) ($row['primary_button']['title'] ?? ''));
                $url = trim((string) ($row['primary_button']['url'] ?? ''));
                if ($url !== '' && $title === '') {
                    $row['primary_button']['title'] = 'Get the programme brochure';
                    $changed = true;
                    $log[] = 'EDTIP: set primary_button title';
                }
                $log[] = "EDTIP hero button title=[{$title}] url=[{$url}]";
            }
        }
        unset($row);
        if ($changed) {
            update_field('flexible_content_blocks', $rows, $edt_id);
            clean_post_cache($edt_id);
        }
    }
}

echo implode("\n", $log) . "\nDONE\n";
