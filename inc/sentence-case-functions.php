<?php

/**
 * Sentence-case labels: capitalize the first word only, keep known proper nouns / acronyms.
 *
 * @param array{
 *   acronyms?: list<string>,
 *   acronym_forms?: array<string, string>,
 *   protected?: list<string>,
 *   small_words?: list<string>
 * } $options
 */
function matrix_format_sentence_case(string $title, array $options = []): string
{
    $title = trim($title);
    if ($title === '') {
        return '';
    }

    $acronyms = array_values(array_unique(array_merge([
        'GP', 'GPs', 'CBT', 'CFT', 'DBT', 'OCD', 'ACT', 'SPMHS', 'SPIRE', 'REC', 'BOSS',
        'HIV', 'ADHD', 'PTSD', 'COVID-19', 'FAQ', 'FAQs', 'SPUH', 'SPL', 'HSE', 'WHO',
        'IT', 'SUITS', 'NCCP', 'MDT',
    ], $options['acronyms'] ?? [])));

    $acronym_forms = array_merge([
        'gp' => 'GP',
        'gps' => 'GPs',
        'faq' => 'FAQ',
        'faqs' => 'FAQs',
    ], $options['acronym_forms'] ?? []);

    $small_words = $options['small_words'] ?? [
        'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'as', 'is',
        'it', 'be', 'are', 'was', 'were', 'what', 'how', 'why', 'when', 'our', 'your', 'their', 'its', 'into', 'through',
    ];

    $protected = array_values(array_unique(array_merge([
        "St Patrick's Mental Health Services",
        "St Patrick's University Hospital",
        "St Patrick's Hospital Lucan",
        "St Patrick's at Home",
        "St Patrick's",
        'Trinity College Dublin',
        'Your Portal',
        'Dr Colman Noctor',
        'Dr Declan Lyons',
        'Assisted Decision-Making (Capacity) Act',
        'Eventbrite',
        'Dean Clinics',
        'Dean Clinic',
        'Jonathan Swift',
        'Willow Grove',
        'Walk in My Shoes',
        'Academic Institute',
        'Board of Governors',
        'Ireland',
        'Irish',
        'Dublin',
        'Cork',
        'Galway',
        'Lucan',
        'Mahon',
    ], $options['protected'] ?? [])));

    // Longest phrases first so shorter overlaps do not win.
    usort($protected, static function (string $a, string $b): int {
        return mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8');
    });

    $placeholders = [];
    foreach ($protected as $phrase) {
        $pattern = '/' . preg_quote($phrase, '/') . '/iu';
        $title = preg_replace_callback($pattern, static function () use (&$placeholders, $phrase): string {
            $key = '%%MTRX' . count($placeholders) . '%%';
            $placeholders[$key] = $phrase;

            return $key;
        }, $title) ?? $title;
    }

    $title = preg_replace('/\btrinity college dublin\b/iu', '%%MTRXTRINITY%%', $title) ?? $title;
    $placeholders['%%MTRXTRINITY%%'] = 'Trinity College Dublin';

    $st_patrick = "/\bst patrick(?:'|'|'|\x{2019})s(?: mental health services| university hospital| hospital lucan| at home)?\b/iu";
    $title = preg_replace_callback($st_patrick, static function (array $m): string {
        $matched = $m[0];
        if (stripos($matched, 'mental health services') !== false) {
            return '%%MTRXSPMHS%%';
        }
        if (stripos($matched, 'university hospital') !== false) {
            return '%%MTRXSPUH%%';
        }
        if (stripos($matched, 'hospital lucan') !== false) {
            return '%%MTRXSPL%%';
        }
        if (stripos($matched, 'at home') !== false) {
            return '%%MTRXATHOME%%';
        }

        return '%%MTRXSTPAT%%';
    }, $title) ?? $title;
    $placeholders['%%MTRXSPMHS%%'] = "St Patrick's Mental Health Services";
    $placeholders['%%MTRXSPUH%%'] = "St Patrick's University Hospital";
    $placeholders['%%MTRXSPL%%'] = "St Patrick's Hospital Lucan";
    $placeholders['%%MTRXATHOME%%'] = "St Patrick's at Home";
    $placeholders['%%MTRXSTPAT%%'] = "St Patrick's";

    $title = preg_replace_callback('/\bdr\s+([a-z]+(?:\s+[a-z]+)*)\b/iu', static function (array $m): string {
        $parts = preg_split('/\s+/', trim($m[1])) ?: [];
        $name = implode(' ', array_map(
            static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($part, 1, null, 'UTF-8'),
            $parts
        ));

        return 'Dr ' . $name;
    }, $title) ?? $title;

    $title = preg_replace_callback('/:\s+(\p{Lu})(\p{Ll}+)/u', static function (array $m) use ($acronyms): string {
        $word = $m[1] . $m[2];
        foreach ($acronyms as $acr) {
            if (strcasecmp($word, $acr) === 0) {
                return ': ' . $acr;
            }
        }

        return ': ' . mb_strtolower($word, 'UTF-8');
    }, $title) ?? $title;

    $segments = preg_split('/(\s+)/u', $title, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
    $word_index = 0;
    foreach ($segments as $i => $segment) {
        if ($segment === '' || preg_match('/^\s+$/u', $segment)) {
            continue;
        }
        if (preg_match('/^%%MTRX/u', $segment)) {
            ++$word_index;

            continue;
        }
        if (! preg_match('/\p{L}/u', $segment)) {
            continue;
        }
        if (! preg_match('/^([^\p{L}]*)([\p{L}][\p{L}\'-]*?)([^\p{L}]*)$/u', $segment, $parts)) {
            continue;
        }

        [, $prefix, $word, $suffix] = $parts;
        $lower = mb_strtolower($word, 'UTF-8');
        $stem = preg_replace("/'s$/u", '', $lower) ?? $lower;
        $upper_stem = strtoupper($stem);

        if ($word_index === 0) {
            $word = mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($lower, 1, null, 'UTF-8');
            if (isset($acronym_forms[$lower])) {
                $word = $acronym_forms[$lower];
            } elseif (in_array($upper_stem, $acronyms, true) || in_array(strtoupper($lower), $acronyms, true)) {
                $acr = in_array(strtoupper($lower), $acronyms, true) ? strtoupper($lower) : $upper_stem;
                $word = $acr . (str_ends_with($lower, "'s") ? "'s" : '');
            } elseif (preg_match('/^[A-Z]{2,}$/', $parts[2])) {
                $word = $parts[2];
            }
        } elseif (in_array($lower, $small_words, true)) {
            $word = $lower;
        } elseif (isset($acronym_forms[$lower])) {
            $word = $acronym_forms[$lower];
        } elseif (in_array($upper_stem, $acronyms, true) || in_array(strtoupper($lower), $acronyms, true)) {
            $acr = in_array(strtoupper($lower), $acronyms, true) ? strtoupper($lower) : $upper_stem;
            $word = $acr . (str_ends_with($lower, "'s") ? "'s" : '');
        } elseif (preg_match('/^[A-Z]{2,}$/', $word)) {
            // Keep existing acronyms already stored in uppercase.
        } else {
            $word = $lower;
        }

        $segments[$i] = $prefix . $word . $suffix;
        ++$word_index;
    }

    $title = implode('', $segments);
    foreach ($placeholders as $key => $phrase) {
        $title = str_replace($key, $phrase, $title);
    }

    return $title;
}

/**
 * Useful-link / nav label sentence case (presentation layer).
 */
function matrix_format_useful_link_title(string $title): string
{
    return matrix_format_sentence_case($title);
}
