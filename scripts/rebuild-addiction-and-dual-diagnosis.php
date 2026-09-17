<?php

/**
 * Rebuild Addiction & Dual Diagnosis from live stpatricks.ie content.
 *
 * Targets:
 * - page #198 /addiction-and-dual-diagnosis/
 * - care_treatment #3089 /care-treatment/addiction-and-dual-diagnosis/
 */

require_once __DIR__ . '/lib/orlaith-page-helpers.php';

if (! function_exists('matrix_rebuild_addiction_ul')) {
    /**
     * @param list<string> $items
     */
    function matrix_rebuild_addiction_ul(array $items): string
    {
        $html = '<ul>';
        foreach ($items as $item) {
            $html .= '<li>' . $item . '</li>';
        }

        return $html . '</ul>';
    }
}

if (! function_exists('matrix_rebuild_addiction_rows')) {
    /**
     * @return list<array<string, mixed>>
     */
    function matrix_rebuild_addiction_rows(): array
    {
        $hero_id = matrix_orlaith_find_image(784, 'addiction-services.jpg');
        $booklet_url = (string) (wp_get_attachment_url(862) ?: '');
        $dual_pdf_url = (string) (wp_get_attachment_url(865) ?: '');
        $how_to_access_url = matrix_orlaith_permalink('how-to-access');
        $spuh_url = matrix_orlaith_permalink('locations/st-patricks-university-hospital');
        $lucan_url = matrix_orlaith_permalink('st-patricks-lucan');
        $dean_url = matrix_orlaith_permalink('outpatient-clinics/dean-clinic-st-patricks');

        $addiction_html = '<p>Addictive disorders are common disorders that involve the overuse of alcohol or drugs. The number of people experiencing an addiction problem in Ireland is large and this number continues to rise.</p>'
            . '<p>Alcohol consumption has risen more in Ireland than in any other country in Europe and we are currently one of the highest consumers of alcohol per head of population in the world. There has also been a rise in the abuse of other drugs including marijuana and tranquillizers. At present, approximately 5% of the adult population is alcohol-dependent and a further 7% is alcohol abusive. There has also been a notable rise in binge drinking among young men and young women.</p>';

        $dual_html = '<p>Dual diagnosis is a term that indicates the presence of two medical conditions. Within the area of mental health and psychiatry, the term dual diagnosis is used to describe the co-existence of a mental health disorder and an alcohol or drug problem. The psychiatric problem simply will not go away unless it has been treated, regardless of the treatment done on the actual addiction. There is evidence to support that if both the addiction and the underlying psychological problem are treated, the prognosis for recovery is very good.</p>';

        $mood_html = matrix_rebuild_addiction_ul([
            'There is a very significant interaction between alcohol dependence and other addictions and mood disorders. Abstinence from alcohol for a period of weeks may be all that is required to lift somebody\'s mood in a significant number of addicted service users.',
            'Alcohol, even in moderate quantities, can cause a depressive episode in a vulnerable person. This can occur on the same night, the next day or even a few days later.',
        ])
            . '<p>Alcohol can also make suicidal ideas more intense in someone with a history of depression. Alcohol and other addictive substances may also lead to episodes of elation in vulnerable people. Some people can become depressed even as they successfully battle an addiction. Some can experience craving as a feeling of depression and others can become depressed as a result of problems which worsen during the period of addiction.</p>';

        $alcohol_signs = matrix_rebuild_addiction_ul([
            'Being unable to limit the amount of alcohol you drink.',
            'Wanting to cut down on how much you drink or making unsuccessful attempts to do so.',
            'Spending a lot of time drinking, getting alcohol or recovering from alcohol use.',
            'Feeling a strong craving or urge to drink alcohol.',
            'Failing to fulfil major obligations at work, school or home due to repeated alcohol use.',
            'Continuing to drink alcohol even though you know it\'s causing physical, social or interpersonal problems.',
            'Giving up or reducing social and work activities and hobbies.',
            'Using alcohol in situations where it\'s not safe, such as when driving or swimming.',
            'Developing a tolerance to alcohol so you need more to feel its effect, or you have a reduced effect from the same amount.',
            'Experiencing withdrawal symptoms, such as nausea, sweating and shaking, when you don\'t drink, or drinking to avoid these symptoms.',
        ]);

        $drug_signs = matrix_rebuild_addiction_ul([
            'Feeling that you have to use the drug regularly; this can be daily or even several times a day.',
            'Having intense urges for the drug.',
            'Over time, needing more of the drug to get the same effect.',
            'Making certain that you maintain a supply of the drug.',
            'Spending money on the drug, even though you can\'t afford it.',
            'Not meeting obligations and work responsibilities, or cutting back on social or recreational activities because of drug use.',
            'Doing things to get the drug that you normally wouldn\'t do, such as stealing.',
            'Driving or doing other risky activities when you\'re under the influence of the drug.',
            'Focusing more and more time and energy on getting and using the drug.',
            'Failing in your attempts to stop using the drug.',
            'Experiencing withdrawal symptoms when you attempt to stop taking the drug.',
        ]);

        $treatment_html = '<p><strong>Addictive disorders are treatable.</strong></p>'
            . '<p>For some individuals it is enough to give information and feedback for them to tackle the addiction themselves. For others, a full treatment programme is required. Although there is no single cause of an addictive disorder it can arise in someone with:</p>'
            . matrix_rebuild_addiction_ul([
                'A strong family history of addiction',
                'Someone with an early exposure',
                'Someone who starts drinking at an early age',
                'Someone with a high individual tolerance to alcohol',
                'Someone who grows up in a highly permissive culture for alcohol and other substances of abuse',
                'Some people self-medicate anxiety or a depression problem and this fuels the addiction',
            ]);

        $therapies_html = '<p>Primary therapies and groups used:</p>'
            . matrix_rebuild_addiction_ul([
                'Alcoholics Anonymous &ndash; 01 8420 700',
                'AWARE &ndash; 1800 80 48 48',
                'National Drugs Team &ndash; 1800 295 295',
                'Gamblers Anonymous &ndash; 01 8721 133',
                'Lifering &ndash; 1800 938 768',
                'Narcotics Anonymous &ndash; 01 6728 000',
                'Samaritans &ndash; 1850 60 90 90',
                'Shine &ndash; 01 8601 620',
                'Women\'s Aid &ndash; 1800 341 900',
            ])
            . '<h3>Books</h3>'
            . matrix_rebuild_addiction_ul([
                'Alcoholics Anonymous (Big Blue Book) &ndash; AA',
                'Overcoming Alcohol Misuse &ndash; Conor Farren',
                'The Language of Letting Go &ndash; Melody Beattie',
                'Get Your Loved One Sober &ndash; Robert Myers / Brenda L Wolfe',
            ])
            . '<p>Check the Information Centre Book Shop for availability and a wider selection of books.</p>';

        $locations_html = '<p>At St Patrick&rsquo;s Mental Health Services we provide outpatient, day, inpatient and aftercare services for addictions and dual diagnosis. The Temple Centre is our holistic treatment centre where users are provided with the appropriate level of care depending on the severity of their addiction and stage of recovery. St Patrick&rsquo;s Mental Health Services and the Temple Centre have been accredited by the Mental Health Commission in Ireland, ensuring high standards in the delivery of mental health services.</p>'
            . '<h3>St Patrick&rsquo;s University Hospital, Dublin</h3>'
            . '<p>At St Patrick&rsquo;s Mental Health Services we provide outpatient, day, inpatient and aftercare services for addictions and dual diagnosis. The Temple Centre is our holistic treatment centre where users are provided with the appropriate level of care depending on the severity of their addiction and stage of recovery. <a href="' . esc_url($spuh_url) . '">Find out more</a></p>'
            . '<h3>The Temple Centre</h3>'
            . '<p>The Temple Centre team is multidisciplinary consisting of consultant psychiatrists, registrars, nursing staff, counsellors, social workers, psychologists and occupational therapists. Treatment needs are routinely evaluated by the team and collaborated with the service user from the point of entry to discharge from the service. Programmes are delivered at St Patrick&rsquo;s University Hospital and <a href="' . esc_url($lucan_url) . '">St Patrick&rsquo;s, Lucan</a>.</p>'
            . '<h3>Dean Clinic</h3>'
            . '<p>This is only for individual therapies &ndash; all programmes are in St Patrick&rsquo;s University Hospital and St Patrick&rsquo;s, Lucan. <a href="' . esc_url($dean_url) . '">Find out more</a></p>';

        return [
            matrix_orlaith_hero_row(
                'Addiction & Dual Diagnosis',
                '<p>Outpatient, day, inpatient and aftercare services for addictions and dual diagnosis at St Patrick&rsquo;s Mental Health Services.</p>',
                $hero_id
            ),
            matrix_orlaith_content_row('Addiction', $addiction_html),
            matrix_orlaith_content_row('Dual diagnosis', $dual_html, 'cream'),
            matrix_orlaith_content_row('Mood and Alcohol', $mood_html),
            matrix_orlaith_accordion_row([
                'Alcohol addiction signs, symptoms or behaviours' => $alcohol_signs,
                'Drug addiction signs, symptoms or behaviours' => $drug_signs,
            ], 'default', 'Signs and symptoms'),
            matrix_orlaith_content_row('Treatment approaches', $treatment_html, 'cream'),
            matrix_orlaith_content_row('Primary therapies and groups used', $therapies_html),
            matrix_orlaith_content_row('Locations', $locations_html, 'cream', 0, 'image_left', [
                'primary_button' => [
                    'title' => 'Continue to How to access',
                    'url' => $how_to_access_url,
                    'target' => '',
                ],
                'primary_button_variant' => 'filled',
            ]),
            matrix_orlaith_content_row(
                'Download',
                '<p>Download our addiction service information leaflets:</p>',
                'white',
                0,
                'image_left',
                [
                    'primary_button' => $booklet_url !== '' ? [
                        'title' => 'Download Addiction Services Booklet',
                        'url' => $booklet_url,
                        'target' => '_blank',
                    ] : '',
                    'primary_button_variant' => 'filled',
                    'secondary_button' => $dual_pdf_url !== '' ? [
                        'title' => 'Download Dual Diagnosis Programme',
                        'url' => $dual_pdf_url,
                        'target' => '_blank',
                    ] : '',
                    'secondary_button_variant' => 'filled',
                ]
            ),
            matrix_orlaith_useful_links_row([
                'How to access' => 'how-to-access',
                'Homecare service' => 'care-treatment/homecare-service',
                'Remote Services' => 'care-treatment/remote-services',
                'Anxiety Disorders Programme' => 'care-treatment/anxiety-disorders-programme',
                'Bipolar Education Programme' => 'care-treatment/bipolar-education-programme',
                'Depression Recovery Programme' => 'care-treatment/depression-recovery-programme',
                'Eating Disorders Programme' => 'care-treatment/eating-disorders-programme',
                'Psychosis Recovery Programme' => 'care-treatment/psychosis-recovery-programme',
                'Young Adult Service' => 'care-treatment/young-adult-service',
                'Older Adult Service' => 'care-treatment/older-adult-service',
            ]),
        ];
    }
}

$targets = [198, 3089];
$rows = matrix_rebuild_addiction_rows();

foreach ($targets as $post_id) {
    $post = get_post($post_id);
    if (! $post instanceof WP_Post) {
        WP_CLI::warning("Post {$post_id} not found.");
        continue;
    }

    update_field('flexible_content_blocks', $rows, $post_id);
    wp_update_post([
        'ID' => $post_id,
        'post_title' => 'Addiction & Dual Diagnosis',
        'post_excerpt' => 'Outpatient, day, inpatient and aftercare services for addictions and dual diagnosis.',
    ]);

    $count = count((array) get_field('flexible_content_blocks', $post_id));
    WP_CLI::success("Updated {$post_id} ({$post->post_type}) " . get_permalink($post_id) . " blocks={$count}");
}
