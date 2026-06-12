<?php

/**
 * @var array<string, mixed> $args
 */

$form = is_array($args['form'] ?? null) ? $args['form'] : [];

if ($form === []) {
    return;
}

$render_yes_no = static function (string $name, string $label, array $options, string $form_id, bool $required = true) {
    $group_id = $form_id . '-' . $name;
    ?>
    <fieldset class="portal-contact-form__field">
        <legend class="portal-contact-form__label">
            <?php echo esc_html($label); ?><?php if ($required) { ?><span class="portal-contact-form__required" aria-hidden="true">*</span><?php } ?>
        </legend>
        <div class="portal-contact-form__radio-group" role="radiogroup" aria-labelledby="<?php echo esc_attr($group_id); ?>-legend">
            <?php foreach ($options as $index => $option) { ?>
                <?php $option_id = $group_id . '-' . sanitize_title($option); ?>
                <label class="portal-contact-form__radio-option" for="<?php echo esc_attr($option_id); ?>">
                    <input
                        class="portal-contact-form__radio"
                        type="radio"
                        id="<?php echo esc_attr($option_id); ?>"
                        name="<?php echo esc_attr($name); ?>"
                        value="<?php echo esc_attr($option); ?>"
                        <?php echo $required ? 'required' : ''; ?>
                    />
                    <span><?php echo esc_html($option); ?></span>
                </label>
            <?php } ?>
        </div>
    </fieldset>
    <?php
};
?>

<section
    id="<?php echo esc_attr((string) $form['section_id']); ?>"
    class="flex overflow-hidden relative w-full bg-[#FBF8F3]"
    aria-labelledby="<?php echo esc_attr((string) $form['form_id']); ?>-heading"
>
    <div class="mx-auto flex w-full max-w-[578px] flex-col items-center px-5 py-12 lg:px-0 lg:py-[100px]">
        <h2
            id="<?php echo esc_attr((string) $form['form_id']); ?>-heading"
            class="mb-6 w-full font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px]"
        >
            Apply for this role
        </h2>

        <form
            id="<?php echo esc_attr((string) $form['form_id']); ?>"
            class="flex flex-col gap-6 w-full portal-contact-form"
            method="post"
            action="<?php echo esc_url(matrix_get_contact_form_action_url()); ?>"
            enctype="multipart/form-data"
            data-theme-form="careers-application"
            data-confirm-email="1"
            data-success-message="<?php echo esc_attr((string) $form['success_message']); ?>"
            novalidate
        >
            <input type="hidden" name="action" value="theme_form_submit" />
            <input type="hidden" name="theme_form_nonce" value="<?php echo esc_attr(wp_create_nonce('theme_form_submit')); ?>" />
            <input type="hidden" name="_submission_uid" value="<?php echo esc_attr((string) $form['submission_uid']); ?>" />
            <input type="hidden" name="_theme_form_name" value="<?php echo esc_attr((string) $form['form_name']); ?>" />
            <input type="hidden" name="_theme_save_to_db" value="1" />
            <input type="hidden" name="_cfg_subject" value="<?php echo esc_attr((string) $form['subject']); ?>" />
            <input type="hidden" name="_cfg_to" value="<?php echo esc_attr((string) $form['recipient_email']); ?>" />
            <input type="hidden" name="vacancy_title" value="<?php echo esc_attr((string) $form['job_title']); ?>" />
            <input type="hidden" name="vacancy_id" value="<?php echo esc_attr((string) ($form['post_id'] ?? 0)); ?>" />

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-title">
                    Title<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <select class="portal-contact-form__select" id="<?php echo esc_attr((string) $form['form_id']); ?>-title" name="title" required>
                    <option value="">Select Title</option>
                    <?php foreach ($form['title_options'] as $title_option) { ?>
                        <option value="<?php echo esc_attr($title_option); ?>"><?php echo esc_html($title_option); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-first-name">
                    First Name<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input class="portal-contact-form__input" type="text" id="<?php echo esc_attr((string) $form['form_id']); ?>-first-name" name="first_name" autocomplete="given-name" placeholder="First Name" required />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-last-name">
                    Last Name<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input class="portal-contact-form__input" type="text" id="<?php echo esc_attr((string) $form['form_id']); ?>-last-name" name="last_name" autocomplete="family-name" placeholder="Last Name" required />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-address-line-1">
                    Address<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input class="portal-contact-form__input" type="text" id="<?php echo esc_attr((string) $form['form_id']); ?>-address-line-1" name="address_line_1" autocomplete="address-line1" placeholder="Address" required />
            </div>

            <div class="portal-contact-form__field">
                <label class="sr-only" for="<?php echo esc_attr((string) $form['form_id']); ?>-address-line-2">Address line 2</label>
                <input class="portal-contact-form__input" type="text" id="<?php echo esc_attr((string) $form['form_id']); ?>-address-line-2" name="address_line_2" autocomplete="address-line2" placeholder="Address" />
            </div>

            <div class="portal-contact-form__row">
                <div class="portal-contact-form__field portal-contact-form__field--half">
                    <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-city">
                        City
                    </label>
                    <input class="portal-contact-form__input" type="text" id="<?php echo esc_attr((string) $form['form_id']); ?>-city" name="city" autocomplete="address-level2" placeholder="City" />
                </div>
                <div class="portal-contact-form__field portal-contact-form__field--half">
                    <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-postcode">
                        Post Code
                    </label>
                    <input class="portal-contact-form__input" type="text" id="<?php echo esc_attr((string) $form['form_id']); ?>-postcode" name="post_code" autocomplete="postal-code" placeholder="Post Code" />
                </div>
            </div>

            <div class="portal-contact-form__row">
                <div class="portal-contact-form__field portal-contact-form__field--half">
                    <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-country">
                        Country
                    </label>
                    <select class="portal-contact-form__select" id="<?php echo esc_attr((string) $form['form_id']); ?>-country" name="country">
                        <option value="">Select</option>
                        <?php foreach ($form['country_options'] as $country_option) { ?>
                            <option value="<?php echo esc_attr($country_option); ?>" <?php selected($country_option, 'Ireland'); ?>><?php echo esc_html($country_option); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="portal-contact-form__field portal-contact-form__field--half">
                    <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-county">
                        County
                    </label>
                    <select class="portal-contact-form__select" id="<?php echo esc_attr((string) $form['form_id']); ?>-county" name="county">
                        <option value="">Select</option>
                        <?php foreach ($form['county_options'] as $county_option) { ?>
                            <option value="<?php echo esc_attr($county_option); ?>"><?php echo esc_html($county_option); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-email">
                    Email<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input class="portal-contact-form__input" type="email" id="<?php echo esc_attr((string) $form['form_id']); ?>-email" name="email" autocomplete="email" placeholder="Email" required />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-email-confirm">
                    Please Confirm Email Address<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input class="portal-contact-form__input" type="email" id="<?php echo esc_attr((string) $form['form_id']); ?>-email-confirm" name="email_confirm" autocomplete="email" placeholder="Email" required />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-phone">
                    Primary Phone<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input class="portal-contact-form__input" type="tel" id="<?php echo esc_attr((string) $form['form_id']); ?>-phone" name="primary_phone" autocomplete="tel" inputmode="tel" placeholder="Primary Phone" required />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-linkedin">
                    LinkedIn Profile
                </label>
                <input class="portal-contact-form__input" type="url" id="<?php echo esc_attr((string) $form['form_id']); ?>-linkedin" name="linkedin_profile" placeholder="https://www.linkedin.com/.." />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-source">
                    Applicant Source
                </label>
                <select class="portal-contact-form__select" id="<?php echo esc_attr((string) $form['form_id']); ?>-source" name="applicant_source">
                    <option value="">Select Source</option>
                    <?php foreach ($form['source_options'] as $source_option) { ?>
                        <option value="<?php echo esc_attr($source_option); ?>"><?php echo esc_html($source_option); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-source-reference">
                    Applicant Source Reference
                </label>
                <input class="portal-contact-form__input" type="text" id="<?php echo esc_attr((string) $form['form_id']); ?>-source-reference" name="applicant_source_reference" placeholder="Applicant Source Reference" />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-cv">
                    CV<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input class="portal-contact-form__file" type="file" id="<?php echo esc_attr((string) $form['form_id']); ?>-cv" name="cv" accept=".pdf,.doc,.docx" required />
            </div>

            <?php
            $render_yes_no('nmbi_current_registration', 'Do you hold current registration with the Psychiatric division with Nursing and Midwifery Board of Ireland (NMBI)?', $form['yes_no_options'], (string) $form['form_id']);
            $render_yes_no('nmbi_eligible_registration', 'Are you eligible for registration with the psychiatric division with Nursing and Midwifery Board of Ireland (NMBI)?', $form['yes_no_options'], (string) $form['form_id']);
            $render_yes_no('interest_adult_nurse', 'Are you interested in a General Adult Psychiatric Nurse position?', $form['yes_no_options'], (string) $form['form_id']);
            $render_yes_no('interest_adolescent_nurse', 'Are you interested in an Adolescent Psychiatric Nurse position?', $form['yes_no_options'], (string) $form['form_id']);
            $render_yes_no('interest_both_nurse', 'Are you interested in both General Adult and Adolescent Psychiatric Nurse positions?', $form['yes_no_options'], (string) $form['form_id']);
            ?>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr((string) $form['form_id']); ?>-eligibility">
                    What is your working eligibility status in Ireland?<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <select class="portal-contact-form__select" id="<?php echo esc_attr((string) $form['form_id']); ?>-eligibility" name="working_eligibility_status" required>
                    <option value="">Select Answer</option>
                    <?php foreach ($form['eligibility_options'] as $eligibility_option) { ?>
                        <option value="<?php echo esc_attr($eligibility_option); ?>"><?php echo esc_html($eligibility_option); ?></option>
                    <?php } ?>
                </select>
            </div>

            <?php $render_yes_no('future_positions_consent', 'Do you wish for your personal data to be considered for similar future positions that may arise?', $form['yes_no_options'], (string) $form['form_id'], false); ?>

            <div class="portal-contact-form__consent">
                <input class="portal-contact-form__checkbox" type="checkbox" id="<?php echo esc_attr((string) $form['form_id']); ?>-terms" name="application_terms" value="yes" required />
                <div class="portal-contact-form__consent-copy">
                    <label class="portal-contact-form__consent-label" for="<?php echo esc_attr((string) $form['form_id']); ?>-terms">
                        <span><?php echo esc_html((string) $form['terms_text']); ?></span>
                        <?php if (($form['privacy_policy_url'] ?? '') !== '') { ?>
                            <a class="portal-contact-form__link btn" href="<?php echo esc_url((string) $form['privacy_policy_url']); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html((string) $form['privacy_policy_label']); ?>
                            </a>
                        <?php } ?>
                    </label>
                </div>
            </div>

            <div class="portal-contact-form__submit-wrap">
                <button type="submit" class="portal-contact-form__submit btn">
                    <?php echo esc_html((string) $form['submit_label']); ?>
                </button>
            </div>

            <div class="cf-turnstile" data-size="invisible" aria-hidden="true"></div>
        </form>
    </div>
</section>
