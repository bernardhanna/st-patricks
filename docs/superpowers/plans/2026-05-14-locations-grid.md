# Locations Grid Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a manual-entry `locations_grid` flexi block that matches Figma node `3093:8474`, supports optional linked cards plus an optional footer CTA, and is demoed on `/flexi/`.

**Architecture:** Keep this block dedicated and small. Put card and CTA normalization in one focused helper file with unit coverage, define the new block in one ACF builder partial, render it through one flexi template, and seed exactly one demo row on `/flexi/` using the six location samples from the Figma.

**Tech Stack:** WordPress PHP templates, ACF Builder flexi block partials, focused Pest/PHPUnit coverage for helper logic, existing theme utility classes, local `wp eval` seeding, and `npm run build` plus curl/browser verification.

---

## File Structure

- `inc/locations-grid-functions.php`
  Keeps card normalization and footer CTA normalization helpers.
- `tests/Unit/LocationsGridTest.php`
  Focused unit coverage for locations grid helper behavior.
- `functions.php`
  Loads the new helper file.
- `acf-fields/partials/blocks/acf_locations_grid.php`
  Defines the manual-entry ACF builder for the new flexi block.
- `template-parts/flexi/locations_grid.php`
  Renders the heading, underline, card grid, linked/static card variants, and footer button.

No CPT, taxonomy, or generic card-grid abstraction should be introduced for this work.

### Task 1: Add helper coverage first

**Files:**
- Create: `inc/locations-grid-functions.php`
- Create: `tests/Unit/LocationsGridTest.php`
- Modify: `functions.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/LocationsGridTest.php` with:

```php
<?php

require_once dirname(__DIR__, 2) . '/inc/locations-grid-functions.php';

test('locations grid normalizes cards and trims card content', function () {
    expect(function_exists('matrix_normalize_locations_grid_cards'))->toBeTrue();

    $cards = matrix_normalize_locations_grid_cards([
        [
            'title' => '  Willow Grove Adolescent Unit  ',
            'link' => [
                'title' => 'View Willow Grove',
                'url' => 'https://example.com/willow-grove',
                'target' => '_blank',
            ],
            'image' => [
                'ID' => 99,
                'url' => 'https://example.com/image.jpg',
                'alt' => '',
                'title' => 'Willow Grove exterior',
            ],
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0])->toMatchArray([
            'title' => 'Willow Grove Adolescent Unit',
            'is_linked' => true,
        ])
        ->and($cards[0]['image']['alt'])->toBe('Willow Grove exterior');
});

test('locations grid leaves cards static when the link is empty', function () {
    $cards = matrix_normalize_locations_grid_cards([
        [
            'title' => 'Dean Clinic Lucan',
            'link' => [
                'title' => '',
                'url' => '',
                'target' => '_self',
            ],
            'image' => null,
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['is_linked'])->toBeFalse();
});

test('locations grid drops cards without a title', function () {
    $cards = matrix_normalize_locations_grid_cards([
        [
            'title' => '   ',
            'link' => [
                'title' => 'Broken',
                'url' => 'https://example.com',
            ],
        ],
    ]);

    expect($cards)->toBe([]);
});

test('locations grid normalizes an optional footer cta link', function () {
    expect(function_exists('matrix_normalize_locations_grid_link'))->toBeTrue();

    $link = matrix_normalize_locations_grid_link([
        'title' => ' Locations Page ',
        'url' => ' /locations/ ',
        'target' => '_self',
    ]);

    expect($link)->toMatchArray([
        'title' => 'Locations Page',
        'url' => '/locations/',
        'target' => '_self',
    ]);

    expect(matrix_normalize_locations_grid_link([
        'title' => 'Missing URL',
        'url' => '',
    ]))->toBeNull();
});
```

- [ ] **Step 2: Run the focused test to verify RED**

Run: `php vendor/bin/pest "tests/Unit/LocationsGridTest.php"`

Expected: FAIL because the locations grid helper file does not exist yet.

- [ ] **Step 3: Add the minimal helper implementation**

Create `inc/locations-grid-functions.php` with:

```php
<?php

function matrix_normalize_locations_grid_cards($rows)
{
    if (! is_array($rows)) {
        return [];
    }

    $cards = [];

    foreach ($rows as $row) {
        $title = trim((string) ($row['title'] ?? ''));

        if ($title === '') {
            continue;
        }

        $image = is_array($row['image'] ?? null) ? $row['image'] : null;
        $link = is_array($row['link'] ?? null) ? $row['link'] : [];
        $url = trim((string) ($link['url'] ?? ''));

        $cards[] = [
            'title' => $title,
            'image' => $image ? [
                'ID' => (int) ($image['ID'] ?? 0),
                'url' => trim((string) ($image['url'] ?? '')),
                'alt' => trim((string) ($image['alt'] ?? '')) !== ''
                    ? trim((string) ($image['alt'] ?? ''))
                    : trim((string) ($image['title'] ?? '')),
            ] : null,
            'link' => [
                'title' => trim((string) ($link['title'] ?? '')),
                'url' => $url,
                'target' => (string) ($link['target'] ?? '_self'),
            ],
            'is_linked' => $url !== '',
        ];
    }

    return $cards;
}

function matrix_normalize_locations_grid_link($link)
{
    if (! is_array($link)) {
        return null;
    }

    $title = trim((string) ($link['title'] ?? ''));
    $url = trim((string) ($link['url'] ?? ''));

    if ($title === '' || $url === '') {
        return null;
    }

    return [
        'title' => $title,
        'url' => $url,
        'target' => (string) ($link['target'] ?? '_self'),
    ];
}
```

Then load it in `functions.php` with:

```php
require_once get_template_directory() . '/inc/locations-grid-functions.php';
```

- [ ] **Step 4: Run the focused test to verify GREEN**

Run: `php vendor/bin/pest "tests/Unit/LocationsGridTest.php"`

Expected: PASS with all locations grid helper tests green.

- [ ] **Step 5: Lint the helper files**

Run:

```bash
php -l "inc/locations-grid-functions.php"
php -l "tests/Unit/LocationsGridTest.php"
```

Expected: both files report `No syntax errors detected`.

### Task 2: Create the ACF builder and block template

**Files:**
- Create: `acf-fields/partials/blocks/acf_locations_grid.php`
- Create: `template-parts/flexi/locations_grid.php`

- [ ] **Step 1: Create the block builder**

Create `acf-fields/partials/blocks/acf_locations_grid.php` with a dedicated `FieldsBuilder` for `locations_grid` using:

- a `Content` tab
- a heading tag select
- a `cards` repeater
- an optional footer button link
- a `Layout` tab
- `padding_settings`

Use exact field names:

- `heading`
- `heading_tag`
- `cards`
- `footer_button_link`

Inside `cards`, use:

- `image`
- `title`
- `link`

- [ ] **Step 2: Create the frontend template**

Create `template-parts/flexi/locations_grid.php` that:

- reads `heading`, `heading_tag`, `cards`, and `footer_button_link`
- normalizes cards through `matrix_normalize_locations_grid_cards()`
- normalizes the footer link through `matrix_normalize_locations_grid_link()`
- renders the standard heading plus underline pattern
- renders a centered wrapper at `max-w-[1018px]`
- renders a 3-column grid on desktop with responsive collapse below that
- renders linked cards as anchors and unlinked cards as static articles

The card markup should include:

```php
<article class="group flex h-full flex-col overflow-hidden rounded-[8px] bg-white shadow-[0px_1px_1px_rgba(0,0,0,0.05)]">
    <div class="h-[240px] w-full overflow-hidden">
        <img ... class="h-full w-full object-cover" />
    </div>
    <div class="bg-[#FBFAF7] p-6">
        <h3 class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
            ...
        </h3>
    </div>
</article>
```

For the footer CTA, use a small outlined button matching the Figma:

```php
<a class="btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-[#08284B]">
    ...
</a>
```

- [ ] **Step 3: Lint the block files**

Run:

```bash
php -l "acf-fields/partials/blocks/acf_locations_grid.php"
php -l "template-parts/flexi/locations_grid.php"
```

Expected: both files report `No syntax errors detected`.

### Task 3: Seed the `/flexi/` demo row

**Files:**
- Modify data only on page ID `329` (`/flexi/`)

- [ ] **Step 1: Add the new block to `/flexi/`**

Use `wp eval` to:

- load page ID `329`
- remove any prior demo row with `acf_fc_layout = locations_grid`
- append one fresh `locations_grid` row to `flexible_content_blocks`

Seed this section content:

- heading: `Our locations`
- footer button:
  - title: `Locations Page`
  - url: `/`

Seed these six cards:

1. `Willow Grove Adolescent Unit`
2. `St Patrick's University Hospital (SPUH)`
3. `St Patrick's Hospital Lucan`
4. `Dean Clinic Galway (optional)`
5. `Dean Clinic Lucan (optional)`
6. `Name of the hospital - no link, clinics are optional`

Use real existing media attachments where possible for demo images. If exact matches do not exist, use nearby realistic images already present in the media library. Make the final card static by leaving its link empty.

- [ ] **Step 2: Verify the row exists in live page output**

Run: `curl -s "http://localhost:10034/flexi/" | rg -n "Our locations|Willow Grove Adolescent Unit|Locations Page"`

Expected: at least one match for the new section heading, one card title, and the footer CTA.

### Task 4: Build and verify

**Files:**
- Review-only unless focused fixes are needed

- [ ] **Step 1: Rebuild front-end assets**

Run: `npm run build`

Expected: exit code `0`; existing asset-size warnings are acceptable if no new errors appear.

- [ ] **Step 2: Run focused verification**

Run:

```bash
php vendor/bin/pest "tests/Unit/LocationsGridTest.php"
php -l "inc/locations-grid-functions.php"
php -l "acf-fields/partials/blocks/acf_locations_grid.php"
php -l "template-parts/flexi/locations_grid.php"
```

Expected: the helper test passes and all touched PHP files lint cleanly.

- [ ] **Step 3: Read lints for changed files**

Use the IDE lint pass on:

- `functions.php`
- `inc/locations-grid-functions.php`
- `tests/Unit/LocationsGridTest.php`
- `acf-fields/partials/blocks/acf_locations_grid.php`
- `template-parts/flexi/locations_grid.php`

Expected: no new linter issues introduced by this block.

- [ ] **Step 4: Browser-check `/flexi/`**

Visit:

- `http://localhost:10034/flexi/`

Confirm:

- heading and underline match the established theme style
- cards render as a clean 3-column grid on desktop
- image proportions and caption panel spacing match the Figma closely
- the final static card has no misleading interactive affordance
- the footer button appears below the grid and matches the small outlined style

## Self-Review

- Spec coverage: helper logic, manual repeater ACF model, frontend rendering, `/flexi/` seeding, and verification are all covered by Tasks 1-4.
- Placeholder scan: no `TODO`, `TBD`, or vague “handle later” steps remain.
- Type consistency: the plan uses the same field names and helper names throughout: `cards`, `footer_button_link`, `matrix_normalize_locations_grid_cards()`, and `matrix_normalize_locations_grid_link()`.

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-05-14-locations-grid.md`.

Two execution options:

1. Subagent-Driven (recommended) - dispatch a fresh subagent per task, review between tasks
2. Inline Execution - execute tasks in this session with checkpoints
