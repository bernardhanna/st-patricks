<?php

/**
 * Shared spreadsheet helpers for content inventory workbooks.
 *
 * Extracted from scripts/export-local-content-inventory.php so the sitemap-driven
 * workbook can reuse the same styling, status controls and URL handling.
 */

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

if (! defined('MATRIX_WORKBOOK_STAGING_HOME')) {
    define('MATRIX_WORKBOOK_STAGING_HOME', 'http://st-patricks.s1.matrix-test.com');
}

if (! function_exists('matrix_workbook_status_options')) {
    /**
     * Status dropdown choices, matching the Content status column on the WP list tables.
     *
     * @return array<int, string>
     */
    function matrix_workbook_status_options(): array
    {
        return ['To do', 'In progress', 'Done', 'Delete'];
    }
}

if (! function_exists('matrix_workbook_status_colours')) {
    /**
     * Background / foreground colours per status label.
     *
     * Mirrors matrix_export_status_column_styles() so the workbook reads like wp-admin.
     *
     * @return array<string, array{bg: string, fg: string}>
     */
    function matrix_workbook_status_colours(): array
    {
        return [
            'To do' => ['bg' => 'FFF3CD', 'fg' => '856404'],
            'In progress' => ['bg' => 'CCE5FF', 'fg' => '004085'],
            'Done' => ['bg' => 'D4EDDA', 'fg' => '155724'],
            'Delete' => ['bg' => 'F8D7DA', 'fg' => '721C24'],
        ];
    }
}

if (! function_exists('matrix_workbook_status_label_from_meta')) {
    /**
     * Read the same matrix_content_status meta the admin Content status column renders.
     *
     * Unset or unrecognised values fall back to "To do", as wp-admin does.
     */
    function matrix_workbook_status_label_from_meta(int $post_id): string
    {
        $meta_key = defined('MATRIX_EXPORT_STATUS_META_KEY') ? MATRIX_EXPORT_STATUS_META_KEY : 'matrix_content_status';
        $status = (string) get_post_meta($post_id, $meta_key, true);

        switch ($status) {
            case 'inprogress':
                return 'In progress';
            case 'done':
                return 'Done';
            case 'delete':
                return 'Delete';
            default:
                return 'To do';
        }
    }
}

if (! function_exists('matrix_workbook_status_set_by')) {
    /**
     * Email of whoever marked the item Done.
     *
     * The plugin only records an author for the "done" status and clears it otherwise,
     * so To do / In progress / Delete rows have nobody attributed.
     */
    function matrix_workbook_status_set_by(int $post_id): string
    {
        $meta_key = defined('MATRIX_EXPORT_STATUS_DONE_BY_META_KEY')
            ? MATRIX_EXPORT_STATUS_DONE_BY_META_KEY
            : 'matrix_content_status_done_by';

        return trim((string) get_post_meta($post_id, $meta_key, true));
    }
}

if (! function_exists('matrix_workbook_to_staging_url')) {
    /**
     * Rewrite a local URL onto the staging host.
     */
    function matrix_workbook_to_staging_url(string $url, string $local_home): string
    {
        if ($url === '') {
            return '';
        }

        $staging_home = MATRIX_WORKBOOK_STAGING_HOME;

        if ($local_home !== '') {
            $url = str_replace($local_home, $staging_home, $url);
        }

        $url = (string) preg_replace('#^https?://localhost(?::\d+)?#i', $staging_home, $url);
        $url = (string) preg_replace('#^https?://127\.0\.0\.1(?::\d+)?#i', $staging_home, $url);

        return $url;
    }
}

if (! function_exists('matrix_workbook_pretty_url')) {
    /**
     * Public-facing URL for a post, falling back to a sample permalink for non-published items.
     */
    function matrix_workbook_pretty_url(WP_Post $post): string
    {
        if ($post->post_status === 'publish') {
            $url = (string) get_permalink($post);

            if ($url !== '') {
                return $url;
            }
        }

        if (function_exists('get_sample_permalink')) {
            $sample = get_sample_permalink($post->ID);

            if (is_array($sample) && isset($sample[0], $sample[1])) {
                $url = str_replace(['%pagename%', '%postname%'], (string) $sample[1], (string) $sample[0]);
                $url = (string) preg_replace('#%[^%]+%#', '', $url);

                if ($url !== '') {
                    return $url;
                }
            }
        }

        if (is_post_type_hierarchical($post->post_type)) {
            $uri = get_page_uri($post);

            if (is_string($uri) && $uri !== '') {
                return home_url(user_trailingslashit($uri));
            }
        }

        if ($post->post_name !== '') {
            return home_url(user_trailingslashit($post->post_name));
        }

        return (string) get_permalink($post);
    }
}

if (! function_exists('matrix_workbook_sanitize_sheet_title')) {
    /**
     * Excel sheet titles cap at 31 chars and ban : \ / ? * [ ]
     */
    function matrix_workbook_sanitize_sheet_title(string $title, string $fallback = 'Sheet'): string
    {
        $title = trim(str_replace([':', '\\', '/', '?', '*', '[', ']'], '-', $title));

        if ($title === '') {
            $title = $fallback;
        }

        if (strlen($title) > 31) {
            $title = rtrim(substr($title, 0, 31));
        }

        return $title;
    }
}

if (! function_exists('matrix_workbook_header_style')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_workbook_header_style(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E244B'],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
    }
}

if (! function_exists('matrix_workbook_apply_status_controls')) {
    /**
     * Attach the status dropdown and colour coding to a column range.
     */
    function matrix_workbook_apply_status_controls(Worksheet $sheet, int $last_row, string $status_col): void
    {
        if ($last_row < 2) {
            return;
        }

        $options = matrix_workbook_status_options();

        $validation = $sheet->getCell($status_col . '2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Status');
        $validation->setPrompt('Choose ' . implode(', ', $options));
        $validation->setShowErrorMessage(true);
        $validation->setFormula1('"' . implode(',', $options) . '"');
        $validation->setSqref($status_col . '2:' . $status_col . $last_row);

        $conditionals = [];

        foreach (matrix_workbook_status_colours() as $label => $colours) {
            $conditional = new Conditional();
            $conditional->setConditionType(Conditional::CONDITION_CELLIS);
            $conditional->setOperatorType(Conditional::OPERATOR_EQUAL);
            $conditional->addCondition('"' . $label . '"');
            $conditional->getStyle()->getFill()->setFillType(Fill::FILL_SOLID);
            $conditional->getStyle()->getFill()->getStartColor()->setRGB($colours['bg']);
            $conditional->getStyle()->getFont()->getColor()->setRGB($colours['fg']);
            $conditional->getStyle()->getFont()->setBold(true);
            $conditionals[] = $conditional;
        }

        $range = $status_col . '2:' . $status_col . $last_row;
        $sheet->getStyle($range)->setConditionalStyles($conditionals);
        $sheet->getStyle($range)->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }
}

if (! function_exists('matrix_workbook_hyperlink_column')) {
    /**
     * Turn every non-empty cell in a column into a clickable link.
     */
    function matrix_workbook_hyperlink_column(Worksheet $sheet, string $column, int $last_row): void
    {
        for ($row = 2; $row <= $last_row; $row++) {
            $value = (string) $sheet->getCell($column . $row)->getValue();

            if ($value === '') {
                continue;
            }

            $sheet->getCell($column . $row)->getHyperlink()->setUrl($value);
            $sheet->getStyle($column . $row)->applyFromArray([
                'font' => ['color' => ['rgb' => '024B79'], 'underline' => true],
            ]);
        }
    }
}
