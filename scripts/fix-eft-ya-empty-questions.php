<?php

/**
 * Fill empty EFT-YA programme questions from the live Young Adult Psychology Groups copy.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-eft-ya-empty-questions.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$post = get_page_by_path('emotion-focused-therapy-for-young-adults', OBJECT, 'programmes_therapies');
if (! $post instanceof WP_Post) {
    $found = get_posts([
        'post_type' => 'programmes_therapies',
        'name' => 'emotion-focused-therapy-for-young-adults',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    $post = $found[0] ?? null;
}

if (! $post instanceof WP_Post) {
    WP_CLI::error('Emotion-Focused Therapy for Young Adults post not found.');
}

$content = <<<'HTML'
<p>Emotion-Focused Therapy for Young Adults (EFT-YA) is a specialist group programme for young adults aged 18 to 25 who are experiencing a sense of feeling stuck in their thoughts, emotions or behaviours.</p>
<p>Many people describe this as feeling unable to move forward in life, often alongside self-critical thoughts, anxiety, frustration, hopelessness or loneliness. EFT-YA supports service users to better understand these experiences and develop new ways of responding to them.</p>
<h2>What does the programme involve?</h2>
<p>The programme focuses on understanding emotional experiences, addressing self-critical patterns and developing healthier ways of relating to oneself and others.</p>
<h2>Who delivers the programme?</h2>
<p>The programme is delivered by psychologists from St Patrick’s Mental Health Services (SPMHS), with support from the wider multidisciplinary team (MDT) where appropriate.</p>
<h2>Who is the programme suitable for?</h2>
<p>EFT-YA is for young adults aged 18 to 25 who are feeling stuck and who are willing to work through painful emotions. The group can be beneficial for any young adult who:</p>
<ul>
<li>struggles with a harshly contemptuous (insulting) or anxious self-critic</li>
<li>can express emotions once they are evoked</li>
<li>feels compassion for others, although not necessarily for themselves.</li>
</ul>
<p>It is open to young adults who are receiving care from an SPMHS consultant psychiatrist and multidisciplinary team (MDT).</p>
<h2>How long is the programme?</h2>
<p>EFT-YA is a 14-week group programme.</p>
<h2>How are referrals made?</h2>
<p>Referrals are made by the psychologist within the person’s MDT.</p>
HTML;

$updated = wp_update_post([
    'ID' => (int) $post->ID,
    'post_content' => $content,
], true);

if (is_wp_error($updated)) {
    WP_CLI::error($updated->get_error_message());
}

WP_CLI::success('Filled EFT-YA programme questions on post #' . $post->ID);
