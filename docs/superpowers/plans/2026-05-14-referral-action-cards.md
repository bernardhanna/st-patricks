# Referral Action Cards Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a fixed two-card `referral_action_cards` flexi block that matches Figma node `2888:3770`, supports external/download button icon variants, and is demoed on `/flexi/`.

**Architecture:** Keep this block dedicated and small. Put card normalization and icon rendering helpers in one focused helper file with unit coverage, define the block in one ACF builder partial, render it through one flexi template, and seed exactly one demo row on `/flexi/` using the Healthlink/referral-form sample content.

**Tech Stack:** WordPress PHP templates, ACF Builder flexi block partials, focused Pest/PHPUnit coverage for helper logic, existing theme utility classes and inline SVG patterns, `wp eval` for local seeding, and `npm run build` plus curl/browser verification.

---

## File Structure

- `inc/referral-action-cards-functions.php`
  Keeps card normalization and icon-variant helper logic.
- `tests/Unit/ReferralActionCardsTest.php`
  Focused unit coverage for the helper behavior.
- `functions.php`
  Loads the new helper file.
- `acf-fields/partials/blocks/acf_referral_action_cards.php`
  Defines the fixed two-card ACF builder for the new flexi block.
- `template-parts/flexi/referral_action_cards.php`
  Renders the section markup and button/icon variants.

No new CPT, taxonomy, or generic repeater abstraction should be introduced for this work.

### Task 1: Add helper coverage first

**Files:**
- Create: `inc/referral-action-cards-functions.php`
- Create: `tests/Unit/ReferralActionCardsTest.php`
- Modify: `functions.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/ReferralActionCardsTest.php` with:

```php
<?php

require_once dirname(__DIR__, 2) . '/inc/referral-action-cards-functions.php';

test('referral action card normalization applies defaults and trims text', function () {
    expect(function_exists('matrix_normalize_referral_action_card'))->toBeTrue();

    $card = matrix_normalize_referral_action_card([
        'title' => '  Make a Referral via Healthlink  ',
        'description' => '  The fastest route for referrals.  ',
        'button' => [
            'title' => 'Go to Healthlink',
            'url' => 'https://example.com/healthlink',
            'target' => '_blank',
        ],
        'action_icon' => 'external',
        'background_color' => '#CEF2EE',
    ], [
        'background_color' => '#FFFFFF',
        'action_icon' => 'external',
    ]);

    expect($card)->toMatchArray([
        'title' => 'Make a Referral via Healthlink',
        'description' => 'The fastest route for referrals.',
        'action_icon' => 'external',
        'background_color' => '#CEF2EE',
    ]);
});

test('referral action card falls back to a safe icon and default color', function () {
    $card = matrix_normalize_referral_action_card([
        'title' => 'Download form',
        'action_icon' => 'weird-value',
        'background_color' => '',
    ], [
        'background_color' => '#E4F4D6',
        'action_icon' => 'download',
    ]);

    expect($card['action_icon'])->toBe('download')
        ->and($card['background_color'])->toBe('#E4F4D6');
});

test('referral action card helper can detect whether a button is renderable', function () {
    expect(function_exists('matrix_referral_action_card_has_button'))->toBeTrue();

    expect(matrix_referral_action_card_has_button([
        'button' => [
            'title' => 'Download Adult Referral Form',
            'url' => 'https://example.com/form.pdf',
        ],
    ]))->toBeTrue()
      ->and(matrix_referral_action_card_has_button([
          'button' => [
              'title' => 'Broken',
              'url' => '',
          ],
      ]))->toBeFalse();
});

test('referral action card icon helper returns inline svg for supported icon types', function () {
    expect(function_exists('matrix_get_referral_action_card_icon_svg'))->toBeTrue();

    $external = matrix_get_referral_action_card_icon_svg('external');
    $download = matrix_get_referral_action_card_icon_svg('download');

    expect($external)->toContain('<svg')
        ->and($external)->toContain('aria-hidden="true"')
        ->and($download)->toContain('<svg')
        ->and($download)->toContain('aria-hidden="true"');
});
```

- [ ] **Step 2: Run the focused test to verify RED**

Run: `php vendor/bin/pest "tests/Unit/ReferralActionCardsTest.php"`

Expected: FAIL because the referral action card helper file does not exist yet.

- [ ] **Step 3: Add the minimal helper implementation**

Create `inc/referral-action-cards-functions.php` with:

```php
<?php

function matrix_normalize_referral_action_card($card, $defaults = [])
{
    $button = is_array($card['button'] ?? null) ? $card['button'] : [];
    $title = trim((string) ($card['title'] ?? ''));
    $description = trim((string) ($card['description'] ?? ''));
    $background_color = trim((string) ($card['background_color'] ?? ''));
    $action_icon = trim((string) ($card['action_icon'] ?? ''));

    if ($background_color === '') {
        $background_color = (string) ($defaults['background_color'] ?? '#FFFFFF');
    }

    if (! in_array($action_icon, ['external', 'download'], true)) {
        $action_icon = (string) ($defaults['action_icon'] ?? 'external');
    }

    return [
        'title' => $title,
        'description' => $description,
        'button' => [
            'title' => trim((string) ($button['title'] ?? '')),
            'url' => trim((string) ($button['url'] ?? '')),
            'target' => (string) ($button['target'] ?? '_self'),
        ],
        'action_icon' => $action_icon,
        'background_color' => $background_color,
    ];
}

function matrix_referral_action_card_has_button($card)
{
    $button = is_array($card['button'] ?? null) ? $card['button'] : [];

    return trim((string) ($button['title'] ?? '')) !== ''
        && trim((string) ($button['url'] ?? '')) !== '';
}

function matrix_get_referral_action_card_icon_svg($icon_type)
{
    $icon_type = trim((string) $icon_type);

    if ($icon_type === 'download') {
        return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 10.5V12.5C2 12.7761 2.22386 13 2.5 13H13.5C13.7761 13 14 12.7761 14 12.5V10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 3V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 7.5L8 10L10.5 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 10L10 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 3H12V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 9V12.5C10 12.7761 9.77614 13 9.5 13H3.5C3.22386 13 3 12.7761 3 12.5V6.5C3 6.22386 3.22386 6 3.5 6H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}
```

Then load it in `functions.php` with:

```php
require_once get_template_directory() . '/inc/referral-action-cards-functions.php';
```

- [ ] **Step 4: Run the focused test to verify GREEN**

Run: `php vendor/bin/pest "tests/Unit/ReferralActionCardsTest.php"`

Expected: PASS with all referral action card helper tests green.

- [ ] **Step 5: Lint the helper files**

Run:

```bash
php -l "inc/referral-action-cards-functions.php"
php -l "tests/Unit/ReferralActionCardsTest.php"
```

Expected: both files report `No syntax errors detected`.

### Task 2: Create the ACF builder and block template

**Files:**
- Create: `acf-fields/partials/blocks/acf_referral_action_cards.php`
- Create: `template-parts/flexi/referral_action_cards.php`

- [ ] **Step 1: Create the block builder**

Create `acf-fields/partials/blocks/acf_referral_action_cards.php` with a dedicated `FieldsBuilder` for `referral_action_cards` using:

- a `Content` tab
- a left-card group
- a right-card group
- a `Layout` tab
- `padding_settings`

Use exact field names:

- `left_title`
- `left_description`
- `left_button`
- `left_action_icon`
- `left_background_color`
- `right_title`
- `right_description`
- `right_button`
- `right_action_icon`
- `right_background_color`

Use icon choices:

- `external`
- `download`

Set sensible defaults:

- left background `#CEF2EE`
- right background `#E4F4D6`
- left icon `external`
- right icon `download`

- [ ] **Step 2: Create the frontend template**

Create `template-parts/flexi/referral_action_cards.php` that:

- reads the left/right card fields
- normalizes each card through `matrix_normalize_referral_action_card()`
- renders a centered wrapper at `max-w-[1018px]`
- renders a 2-column grid on desktop and a single column on smaller screens
- uses `matrix_get_referral_action_card_icon_svg()` for the button icon
- only renders a CTA when `matrix_referral_action_card_has_button()` returns true

The card markup should include:

```php
<article class="flex h-full flex-col rounded-[8px] p-6 shadow-sm" style="background-color: ...;">
    <h3 class="font-primary text-[24px] font-semibold leading-[32px] tracking-[-0.144px] text-[#1E244B]">
        ...
    </h3>
    <p class="mt-4 font-primary text-[16px] font-medium leading-[28px] text-[#1E244B]">
        ...
    </p>
    <div class="mt-6">
        <a class="inline-flex h-[40px] items-center gap-2 rounded-[6px] bg-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-white">
            <?php echo matrix_get_referral_action_card_icon_svg(...); ?>
            <span>...</span>
        </a>
    </div>
</article>
```

- [ ] **Step 3: Lint the block files**

Run:

```bash
php -l "acf-fields/partials/blocks/acf_referral_action_cards.php"
php -l "template-parts/flexi/referral_action_cards.php"
```

Expected: both files report `No syntax errors detected`.

### Task 3: Seed the `/flexi/` demo row

**Files:**
- Modify: local database content only via `wp eval`

- [ ] **Step 1: Add the new block to the `/flexi/` page**

Run a `wp eval` script that:

- finds the page at path `flexi`
- reads the existing `flexible_content` field
- appends one `referral_action_cards` row if one is not already present

Use demo content matching the Figma:

- left title: `Make a Referral via Healthlink`
- left description: `The fastest and most efficient method is to send referrals via Healthlink or through your practice management system.`
- left button label: `Go to Healthlink`
- left icon: `external`
- left background: `#CEF2EE`
- right title: `Download our Adult Referral Form`
- right description: `Complete our Adult Referral form and submit via Healthmail - referrals@stpatricks.ie`
- right button label: `Download Adult Referral Form`
- right icon: `download`
- right background: `#E4F4D6`

- [ ] **Step 2: Verify the row exists**

Run:

```bash
wp --path="/Users/bernardhanna/Local Sites/st-patricks/app/public" eval '$page = get_page_by_path("flexi"); $rows = get_field("flexible_content", $page->ID); echo is_array($rows) ? count($rows) : 0;'
```

Expected: a numeric row count is returned and the field remains readable.

### Task 4: Build and verify the full block

**Files:**
- Modify as needed: `template-parts/flexi/referral_action_cards.php`

- [ ] **Step 1: Run focused helper coverage**

Run: `php vendor/bin/pest "tests/Unit/ReferralActionCardsTest.php"`

Expected: PASS.

- [ ] **Step 2: Build theme assets**

Run: `npm run build`

Expected: build completes successfully; existing asset-size warnings are acceptable if no new errors appear.

- [ ] **Step 3: Verify the block appears on `/flexi/`**

Run:

```bash
curl -s "http://localhost:10034/flexi/" | rg -n "Make a Referral via Healthlink|Download our Adult Referral Form|Go to Healthlink|Download Adult Referral Form"
```

Expected: the response includes both card titles and both button labels.

- [ ] **Step 4: Browser-check desktop and mobile**

Visit:

- `http://localhost:10034/flexi/`

Confirm:

- two cards render side by side on desktop
- cards stack to one column on mobile
- button icon variants render correctly
- card spacing, pastel backgrounds, and button scale match the Figma closely

- [ ] **Step 5: Run lints on recently touched files**

Use the IDE lint pass on:

- `inc/referral-action-cards-functions.php`
- `tests/Unit/ReferralActionCardsTest.php`
- `acf-fields/partials/blocks/acf_referral_action_cards.php`
- `template-parts/flexi/referral_action_cards.php`

Expected: no new linter issues introduced by this block.

## Self-Review

- Spec coverage: helper logic, icon types, fixed two-card ACF model, frontend rendering, `/flexi/` demo, and verification are all covered by Tasks 1-4.
- Placeholder scan: no `TODO`, `TBD`, or vague “handle later” steps remain.
- Type consistency: the plan uses the same field names and icon values throughout: `left_*`, `right_*`, `external`, and `download`.

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-05-14-referral-action-cards.md`.

Two execution options:

1. Subagent-Driven (recommended) - dispatch a fresh subagent per task, review between tasks
2. Inline Execution - execute tasks in this session with checkpoints
