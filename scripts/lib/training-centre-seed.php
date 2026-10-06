<?php

/**
 * Shared Training Centre (healthcare-professionals/training-centre) flexi rows.
 *
 * @param array{
 *   hero_image?: int,
 *   poster?: int,
 *   webinars_url?: string,
 *   youtube_url?: string,
 *   clinician_insights_url?: string,
 *   privacy_url?: string
 * } $args
 *
 * Sign-up is the GP newsletter flexi (not a contact_form). A page-level newsletter
 * block also suppresses the general footer subscribe form.
 * @return list<array<string, mixed>>
 */
function matrix_training_centre_flexi_rows(array $args = []): array
{
    $p = static function (string $html): string {
        return '<p>' . $html . '</p>';
    };
    $a = static function (string $url, string $label, string $target = '_blank'): string {
        $attrs = ' href="' . esc_url($url) . '"';
        if ($target !== '') {
            $attrs .= ' target="' . esc_attr($target) . '" rel="noopener noreferrer"';
        }

        return '<a' . $attrs . '>' . esc_html($label) . '</a>';
    };
    $ul = static function (array $items): string {
        $html = '<ul>';
        foreach ($items as $item) {
            $html .= '<li>' . $item . '</li>';
        }

        return $html . '</ul>';
    };

    $home = untrailingslashit(home_url('/'));
    $hero_image = (int) ($args['hero_image'] ?? 0);
    $poster = (int) ($args['poster'] ?? 0);
    $webinars = (string) ($args['webinars_url'] ?? ($home . '/healthcare-professionals/webinars-events/'));
    $youtube = (string) ($args['youtube_url'] ?? 'https://www.youtube.com/channel/UCOI_6n3TndtZlW34C4RCdQw');
    $insights = (string) ($args['clinician_insights_url'] ?? ($home . '/healthcare-professionals/clinician-insights/'));

    $hero_intro = $p('St Patrick’s Mental Health Services (SPMHS) offers a wide range of mental health education supports for GPs and healthcare professionals.');

    $resources = $p('We have developed a range of resources to help GPs in their practice with patients who present with mental health difficulties.')
        . $p('If you have any questions about mental health information supports or continuous professional development (CPD) opportunities for GPs, please get in touch.');

    $webinars_html = $p('Each year, we host a GP Webinar Series. These webinars are presented by clinicians from across our services, who, in each webinar, focus on a mental health topic relevant to GP practice. Each webinar also includes a question and answer session with the presenting clinicians.')
        . $p('The webinars are recognised for Accredited CE (CE) by the Irish College of General Practitioners (ICGP), with available points confirmed ahead of each webinar. Please note that Accredited CE is only available to those who attend the live webinar.')
        . $p('Registration for the webinars is free.')
        . $p('Check our '
            . $a($webinars, 'events calendar', '')
            . ' to see and register for upcoming GP Webinars.');

    $films = $p('We also host a range of on-demand mental health information films for GPs.')
        . $p('These films cover a wide range of mental health topics relevant to the GP surgery, including:')
        . $ul([
            'recognising and assessing different mental health difficulties',
            'supporting people living with mental health difficulties',
            'exploring different types of therapy, medication management and treatment approaches.',
        ])
        . $p('Visit our YouTube channel '
            . $a($youtube, 'here')
            . ' to get the full playlist, or find out more about and watch the films '
            . $a($webinars, 'here', '')
            . '.');

    $insights_html = $p('Clinical staff from across our mental health services regularly contribute to our specialist blogs and articles for GPs and healthcare professionals. These articles cover diverse mental health topics to support patients presenting with mental health difficulties. '
            . $a($insights, 'Read our clinician insights here', '')
            . '.');

    $newsletter_intro = $p('We issue a quarterly digital newsletter especially tailored to GPs, covering mental health news, research findings, service updates and clinical insights. Sign up using the form below.');

    return [
        matrix_orlaith_hero_row('Training Centre', $hero_intro, $hero_image),
        matrix_orlaith_content_row('Resources for GPs', $resources, 'white', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button(
                'Email communications@stpatricks.ie',
                'mailto:communications@stpatricks.ie'
            ),
            'primary_button_variant' => 'filled',
        ]),
        matrix_orlaith_content_row('GP Webinar Series', $webinars_html, 'white', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('Upcoming webinars and events', $webinars),
            'primary_button_variant' => 'filled',
        ]),
        matrix_orlaith_content_row('Mental health films for GPs', $films, 'cream', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('SPMHS on YouTube', $youtube, '_blank'),
            'primary_button_variant' => 'filled',
        ]),
        matrix_orlaith_content_row('Clinician insights', $insights_html, 'white', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('Read clinician insights', $insights),
            'primary_button_variant' => 'filled',
        ]),
        matrix_orlaith_video_row(
            'Research and training',
            $p('Watch how our Academic Institute and Training Centre support staff and organisations working in mental health.'),
            [[
                'url' => 'https://www.youtube.com/watch?v=AjJQxOrmv1o',
                'caption' => 'Learn more about our Academic Institute and our commitment to supporting staff and organisations working in mental health through our new training centre.',
                'poster' => $poster,
            ]]
        ),
        matrix_orlaith_gp_newsletter_row($newsletter_intro),
    ];
}
