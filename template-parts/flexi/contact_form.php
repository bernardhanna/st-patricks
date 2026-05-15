<?php

$section_id = 'contact-form-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$checkboxes = matrix_get_contact_form_your_portal_checkboxes();

if (have_rows('consent_items')) {
    $index = 0;
    while (have_rows('consent_items')) {
        the_row();
        $title = trim((string) get_sub_field('title'));
        if ($title !== '' && isset($checkboxes[$index])) {
            $checkboxes[$index]['title'] = $title;
            $checkboxes[$index]['description'] = trim((string) get_sub_field('description'));
            $checkboxes[$index]['required'] = (bool) get_sub_field('required');
        }
        $index++;
    }
}

$wrapper_classes = ['flex', 'w-full', 'flex-col', 'items-center', 'mx-auto', 'pt-5', 'pb-5', 'max-xl:px-5'];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $wrapper_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }

        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $wrapper_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

$form = matrix_prepare_contact_form([
    'section_id' => $section_id,
    'data_block' => str_replace('_', '-', get_row_layout()) . '-' . get_row_index(),
    'form_style' => get_sub_field('form_style'),
    'background_color' => get_sub_field('background_color'),
    'submit_label' => get_sub_field('submit_label'),
    'success_message' => get_sub_field('success_message'),
    'form_name' => get_sub_field('form_name'),
    'subject' => get_sub_field('email_subject'),
    'recipient_email' => get_sub_field('recipient_email'),
    'bcc_email' => get_sub_field('bcc_email'),
    'save_to_db' => get_sub_field('save_to_db'),
    'date_of_birth_help' => get_sub_field('date_of_birth_help'),
    'privacy_policy_link' => get_sub_field('privacy_policy_link'),
    'privacy_policy_label' => get_sub_field('privacy_policy_label'),
    'checkboxes' => $checkboxes,
    'wrapper_classes' => implode(' ', array_unique($wrapper_classes)),
]);

if ($form['form_style'] !== 'your_portal') {
    return;
}

$dob_help_id = $form['form_id'] . '-dob-help';
?>

<section
    id="<?php echo esc_attr($form['section_id']); ?>"
    data-matrix-block="<?php echo esc_attr($form['data_block']); ?>"
    class="flex overflow-hidden relative w-full"
    style="background-color: <?php echo esc_attr($form['background_color']); ?>;"
>
    <div class="<?php echo esc_attr($form['wrapper_classes']); ?> mx-auto max-w-[578px]">
        <form
            id="<?php echo esc_attr($form['form_id']); ?>"
            class="flex flex-col gap-6 w-full portal-contact-form"
            method="post"
            action="<?php echo esc_url(matrix_get_contact_form_action_url()); ?>"
            data-theme-form="contact-form"
            data-success-message="<?php echo esc_attr($form['success_message']); ?>"
            novalidate
        >
            <input type="hidden" name="action" value="theme_form_submit" />
            <input type="hidden" name="theme_form_nonce" value="<?php echo esc_attr(wp_create_nonce('theme_form_submit')); ?>" />
            <input type="hidden" name="_submission_uid" value="<?php echo esc_attr($form['submission_uid']); ?>" />
            <input type="hidden" name="_theme_form_id" value="<?php echo esc_attr((string) get_row_index()); ?>" />
            <input type="hidden" name="_theme_form_name" value="<?php echo esc_attr($form['form_name']); ?>" />
            <?php if ($form['save_to_db']) { ?>
                <input type="hidden" name="_theme_save_to_db" value="1" />
            <?php } ?>
            <input type="hidden" name="_cfg_subject" value="<?php echo esc_attr($form['subject']); ?>" />
            <?php if ($form['recipient_email'] !== '') { ?>
                <input type="hidden" name="_cfg_to" value="<?php echo esc_attr($form['recipient_email']); ?>" />
            <?php } ?>
            <?php if ($form['bcc_email'] !== '') { ?>
                <input type="hidden" name="_cfg_bcc" value="<?php echo esc_attr($form['bcc_email']); ?>" />
            <?php } ?>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr($form['form_id']); ?>-first-name">
                    First Name<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input
                    class="portal-contact-form__input"
                    type="text"
                    id="<?php echo esc_attr($form['form_id']); ?>-first-name"
                    name="first_name"
                    autocomplete="given-name"
                    placeholder="Joe"
                    required
                />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr($form['form_id']); ?>-last-name">
                    Last Name<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input
                    class="portal-contact-form__input"
                    type="text"
                    id="<?php echo esc_attr($form['form_id']); ?>-last-name"
                    name="last_name"
                    autocomplete="family-name"
                    placeholder="Bloggs"
                    required
                />
            </div>

            <div class="portal-contact-form__row">
                <div class="portal-contact-form__field portal-contact-form__field--half">
                    <div class="portal-contact-form__label-row">
                        <label class="portal-contact-form__label" for="<?php echo esc_attr($form['form_id']); ?>-dob">
                            Date Of Birth<span class="portal-contact-form__required" aria-hidden="true">*</span>
                        </label>
                        <button
                            type="button"
                            class="portal-contact-form__info btn"
                            aria-describedby="<?php echo esc_attr($dob_help_id); ?>"
                            aria-label="More information about date of birth"
                        >
                            <span aria-hidden="true">i</span>
                        </button>
                    </div>
                    <p id="<?php echo esc_attr($dob_help_id); ?>" class="sr-only">
                        <?php echo esc_html($form['date_of_birth_help']); ?>
                    </p>
                    <div class="portal-contact-form__input-wrap">
                        <input
                            class="portal-contact-form__input portal-contact-form__input--with-icon"
                            type="date"
                            id="<?php echo esc_attr($form['form_id']); ?>-dob"
                            name="date_of_birth"
                            required
                        />
                    </div>
                </div>

                <div class="portal-contact-form__field portal-contact-form__field--half">
                    <label class="portal-contact-form__label" for="<?php echo esc_attr($form['form_id']); ?>-eircode">
                        Your Eircode<span class="portal-contact-form__required" aria-hidden="true">*</span>
                    </label>
                    <input
                        class="portal-contact-form__input"
                        type="text"
                        id="<?php echo esc_attr($form['form_id']); ?>-eircode"
                        name="eircode"
                        autocomplete="postal-code"
                        placeholder="001 234 432"
                        required
                    />
                </div>
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr($form['form_id']); ?>-email">
                    Email<span class="portal-contact-form__required" aria-hidden="true">*</span>
                </label>
                <input
                    class="portal-contact-form__input"
                    type="email"
                    id="<?php echo esc_attr($form['form_id']); ?>-email"
                    name="email"
                    autocomplete="email"
                    placeholder="Example@mail.com"
                    required
                />
            </div>

            <div class="portal-contact-form__field">
                <label class="portal-contact-form__label" for="<?php echo esc_attr($form['form_id']); ?>-phone">
                    Phone number (optional)
                </label>
                <div class="portal-contact-form__phone-row">
                    <div class="portal-contact-form__phone-code">
                        <label class="sr-only" for="<?php echo esc_attr($form['form_id']); ?>-phone-country">Country code</label>
                        <select
                            class="portal-contact-form__select"
                            id="<?php echo esc_attr($form['form_id']); ?>-phone-country"
                            name="phone_country_code"
                            aria-label="Country code"
                        >
                            <?php foreach ($form['phone_country_options'] as $code => $label) { ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($code, '+353'); ?>>
                                    <?php echo esc_html($code); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <input
                        class="portal-contact-form__input"
                        type="tel"
                        id="<?php echo esc_attr($form['form_id']); ?>-phone"
                        name="phone_number"
                        autocomplete="tel-national"
                        inputmode="tel"
                    />
                </div>
            </div>

            <?php foreach ($form['checkboxes'] as $checkbox) { ?>
                <?php
                $field_name = (string) ($checkbox['name'] ?? '');
                $field_id = $form['form_id'] . '-' . $field_name;
                $is_required = ! empty($checkbox['required']);
                $is_privacy = ! empty($checkbox['is_privacy']);
                ?>
                <div class="portal-contact-form__consent">
                    <input
                        class="portal-contact-form__checkbox"
                        type="checkbox"
                        id="<?php echo esc_attr($field_id); ?>"
                        name="<?php echo esc_attr($field_name); ?>"
                        value="yes"
                        <?php echo $is_required ? 'required' : ''; ?>
                    />
                    <div class="portal-contact-form__consent-copy">
                        <?php if ($is_privacy) { ?>
                            <label class="portal-contact-form__consent-label" for="<?php echo esc_attr($field_id); ?>">
                                <span><?php echo esc_html((string) ($checkbox['title'] ?? '')); ?></span>
                                <?php if ($form['privacy_policy_url'] !== '') { ?>
                                    <a
                                        class="portal-contact-form__link btn"
                                        href="<?php echo esc_url($form['privacy_policy_url']); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <?php echo esc_html($form['privacy_policy_label']); ?>
                                    </a>
                                <?php } else { ?>
                                    <span><?php echo esc_html($form['privacy_policy_label']); ?></span>
                                <?php } ?>
                            </label>
                        <?php } else { ?>
                            <label class="portal-contact-form__consent-title" for="<?php echo esc_attr($field_id); ?>">
                                <?php echo esc_html((string) ($checkbox['title'] ?? '')); ?>
                            </label>
                            <?php if (! empty($checkbox['description'])) { ?>
                                <p class="portal-contact-form__consent-description">
                                    <?php echo esc_html((string) $checkbox['description']); ?>
                                </p>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <div class="portal-contact-form__submit-wrap">
                <button type="submit" class="portal-contact-form__submit btn">
                    <?php echo esc_html($form['submit_label']); ?>
                </button>
            </div>

            <div class="cf-turnstile" data-size="invisible" aria-hidden="true"></div>
        </form>
    </div>
</section>
