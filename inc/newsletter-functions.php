<?php

if (! function_exists('matrix_get_newsletter_signup_url')) {
    function matrix_get_newsletter_signup_url(): string
    {
        $page = get_page_by_path('subscribe-to-our-gp-enewsletter');

        if ($page) {
            $permalink = get_permalink($page);

            if ($permalink !== '') {
                return $permalink;
            }
        }

        return home_url('/subscribe-to-our-gp-enewsletter/');
    }
}

if (! function_exists('matrix_prepare_newsletter_subtext')) {
    function matrix_prepare_newsletter_subtext(string $html): string
    {
        if ($html === '' || stripos($html, 'click here') === false) {
            return $html;
        }

        $url = esc_url(matrix_get_newsletter_signup_url());
        $dom = new DOMDocument();

        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR
        );
        libxml_clear_errors();

        if (! $loaded) {
            return $html;
        }

        $wrapper = $dom->getElementsByTagName('div')->item(0);

        if (! $wrapper instanceof DOMElement) {
            return $html;
        }

        if (! matrix_link_first_newsletter_click_here_text_node($dom, $wrapper, $url)) {
            return $html;
        }

        $processed = '';

        foreach ($wrapper->childNodes as $child) {
            $processed .= $dom->saveHTML($child);
        }

        return $processed;
    }
}

if (! function_exists('matrix_link_first_newsletter_click_here_text_node')) {
    function matrix_link_first_newsletter_click_here_text_node(DOMDocument $dom, DOMNode $node, string $url): bool
    {
        if ($node instanceof DOMElement && strtolower($node->tagName) === 'a') {
            return false;
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText && preg_match('/\bclick here\b/i', $child->nodeValue, $matches, PREG_OFFSET_CAPTURE)) {
                $match = $matches[0][0];
                $offset = $matches[0][1];
                $before = substr($child->nodeValue, 0, $offset);
                $after = substr($child->nodeValue, $offset + strlen($match));
                $anchor = $dom->createElement('a');

                $anchor->setAttribute('href', $url);
                $anchor->appendChild($dom->createTextNode($match));

                if ($before !== '') {
                    $node->insertBefore($dom->createTextNode($before), $child);
                }

                $node->insertBefore($anchor, $child);

                if ($after !== '') {
                    $node->insertBefore($dom->createTextNode($after), $child);
                }

                $node->removeChild($child);

                return true;
            }

            if ($child->hasChildNodes() && matrix_link_first_newsletter_click_here_text_node($dom, $child, $url)) {
                return true;
            }
        }

        return false;
    }
}
