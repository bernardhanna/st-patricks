<?php

/**
 * Healthcare Professionals landing page seed data (Figma 2888:2478).
 * Mirrors the About Us landing pattern: hero + about_links_grid cards.
 */

if (! function_exists('matrix_seed_hp_landing_hero_intro')) {
    function matrix_seed_hp_landing_hero_intro(): string
    {
        return "At St Patrick's Mental Health Services (SPMHS), we support GPs and referrers with referral pathways, clinical resources, and practical information to help you refer patients to our mental health services.";
    }
}

if (! function_exists('matrix_seed_hp_landing_card_definitions')) {
    /**
     * @return array<int, array{title: string, url: string, tone: string, figma: string, cache: string}>
     */
    function matrix_seed_hp_landing_card_definitions(string $home): array
    {
        $home = trailingslashit($home);

        return [
            [
                'title' => 'Refer an Adult for Inpatient Care',
                'url' => $home . 'healthcare-professionals/refer-an-adult-for-inpatient-care/',
                'tone' => 'bg1',
                'figma' => 'https://www.figma.com/api/mcp/asset/bcc1f8bb-8c55-43f0-85bd-52db81e29cda',
                'cache' => 'hp-landing-grid-refer-adult-2888-2478',
            ],
            [
                'title' => 'Refer an Adolescent for Inpatient Care',
                'url' => $home . 'healthcare-professionals/refer-an-adolescent-for-inpatient-care/',
                'tone' => 'bg1',
                'figma' => 'https://www.figma.com/api/mcp/asset/9f96547a-998d-44c2-954d-838aa09831de',
                'cache' => 'hp-landing-grid-refer-adolescent-2888-2478',
            ],
            [
                'title' => "Refer to the St Patrick's at Home Service",
                'url' => $home . 'healthcare-professionals/refer-to-the-st-patricks-at-home-service/',
                'tone' => 'bg1',
                'figma' => 'https://www.figma.com/api/mcp/asset/f1a00937-e189-492d-8527-cbbd236356fd',
                'cache' => 'hp-landing-grid-refer-at-home-2888-2478',
            ],
            [
                'title' => 'Refer to Outpatient Care',
                'url' => $home . 'healthcare-professionals/refer-for-outpatient-care/',
                'tone' => 'bg2',
                'figma' => 'https://www.figma.com/api/mcp/asset/2a39fcd1-cc22-45ff-afc6-f78235d37fbf',
                'cache' => 'hp-landing-grid-refer-outpatient-2888-2478',
            ],
            [
                'title' => 'Refer to a Day Programme',
                'url' => $home . 'healthcare-professionals/refer-to-a-day-programme/',
                'tone' => 'bg2',
                'figma' => 'https://www.figma.com/api/mcp/asset/1211f090-c2fa-41bb-9e93-c78cb55d1349',
                'cache' => 'hp-landing-grid-refer-day-programme-2888-2478',
            ],
            [
                'title' => 'Clinical Insights',
                'url' => $home . 'healthcare-professionals/clinician-insights/',
                'tone' => 'bg2',
                'figma' => 'https://www.figma.com/api/mcp/asset/1f79ece7-5c18-4c09-ad62-c902ceb592f3',
                'cache' => 'hp-landing-grid-clinical-insights-2888-2478',
            ],
            [
                'title' => 'Frequently Asked Questions',
                'url' => $home . 'healthcare-professionals/frequently-asked-questions/',
                'tone' => 'bg3',
                'figma' => 'https://www.figma.com/api/mcp/asset/0a8f1acc-26f3-4c86-ba3d-9ea6effb99fa',
                'cache' => 'hp-landing-grid-faqs-2888-2478',
            ],
            [
                'title' => 'Training Centre',
                'url' => $home . 'healthcare-professionals/training-centre/',
                'tone' => 'bg3',
                'figma' => '',
                'cache' => 'hp-landing-grid-training-centre-2888-2478',
                'source_path' => '/media/1869/st-patricks-mental-health-services-garden.jpg',
            ],
            [
                'title' => 'Contact Numbers',
                'url' => $home . 'healthcare-professionals/contact-numbers/',
                'tone' => 'bg3',
                'figma' => 'https://www.figma.com/api/mcp/asset/9f265cc3-6a87-409a-910a-33366708aa0c',
                'cache' => 'healthcare-professionals-hero-2780-4288',
            ],
            [
                'title' => 'Webinars & Events',
                'url' => $home . 'healthcare-professionals/webinars-events/',
                'tone' => 'bg4',
                'figma' => 'https://www.figma.com/api/mcp/asset/1f79ece7-5c18-4c09-ad62-c902ceb592f3',
                'cache' => 'healthcare-professionals-webinar-2780-4338',
            ],
        ];
    }
}
