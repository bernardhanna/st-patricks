# Research Cards Grid Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a reusable manual-entry `research_cards_grid` flexi block from the approved Figma node `2780:3926`, seed it onto `/flexi/`, and verify the live result against the local WordPress page.

**Architecture:** Implement a dedicated block with one small helper that normalizes repeater rows into template-ready cards and one optional CTA link array. Keep the ACF model manual-only, render a straightforward responsive grid, and avoid turning this into a generic cards framework.

**Tech Stack:** WordPress theme PHP, ACF Builder, Pest/PHPUnit, Tailwind utility classes, local asset build via npm/webpack, `wp eval` seeding, browser verification against the local WordPress site.

---

### Task 1: Add helper coverage first

**Files:**
- Create: `inc/research-cards-grid-functions.php`
- Modify: `functions.php`
- Create: `tests/Unit/ResearchCardsGridTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/ResearchCardsGridTest.php` with focused coverage for:

- trimming card titles and summaries
- skipping invalid rows without a usable title
- normalizing optional ACF link arrays
- preserving image arrays for template rendering

Use this test content:

```php
<?php

require_once dirname(__DIR__, 2) . '/inc/research-cards-grid-functions.php';

test('research cards grid normalizes valid cards and skips incomplete rows', function () {
    expect(function_exists('matrix_normalize_research_cards_grid_cards'))->toBeTrue();

    $cards = matrix_normalize_research_cards_grid_cards([
        [
            'title' => '  View Ethics Guidance  ',
            'summary' => '  Supporting copy for the first card.  ',
            'image' => ['url' => 'https://example.com/one.jpg', 'alt' => 'Ethics guidance'],
            'link' => ['url' => 'https://example.com/guidance', 'title' => 'Read more', 'target' => '_blank'],
        ],
        [
            'title' => '   ',
            'summary' => 'This row should be ignored.',
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['title'])->toBe('View Ethics Guidance')
        ->and($cards[0]['summary'])->toBe('Supporting copy for the first card.')
        ->and($cards[0]['link']['url'])->toBe('https://example.com/guidance')
        ->and($cards[0]['link']['target'])->toBe('_blank')
        ->and($cards[0]['image']['url'])->toBe('https://example.com/one.jpg');
});

test('research cards grid link helper returns null for incomplete links', function () {
    expect(matrix_normalize_research_cards_grid_link(null))->toBeNull()
        ->and(matrix_normalize_research_cards_grid_link(['title' => 'Missing URL']))->toBeNull();
});
```

- [ ] **Step 2: Run the test to verify RED**

Run: `php vendor/bin/pest "tests/Unit/ResearchCardsGridTest.php"`

Expected: FAIL because the helper file or functions do not exist yet.

- [ ] **Step 3: Add the minimal helper and load it from `functions.php`**

Create `inc/research-cards-grid-functions.php` with these functions:

```php
<?php

function matrix_normalize_research_cards_grid_link($link)
{
    if (! is_array($link) || empty($link['url'])) {
        return null;
    }

    $title = trim((string) ($link['title'] ?? ''));

    return [
        'url' => (string) $link['url'],
        'title' => $title !== '' ? $title : (string) $link['url'],
        'target' => trim((string) ($link['target'] ?? '')) ?: '_self',
    ];
}

function matrix_normalize_research_cards_grid_cards($rows)
{
    $cards = [];

    foreach ((array) $rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $title = trim((string) ($row['title'] ?? ''));

        if ($title === '') {
            continue;
        }

        $cards[] = [
            'title' => $title,
            'summary' => trim((string) ($row['summary'] ?? '')),
            'image' => is_array($row['image'] ?? null) ? $row['image'] : null,
            'link' => matrix_normalize_research_cards_grid_link($row['link'] ?? null),
        ];
    }

    return $cards;
}
```

Add this require near the other helper includes in `functions.php`:

```php
require_once get_template_directory() . '/inc/research-cards-grid-functions.php';
```

- [ ] **Step 4: Run the test to verify GREEN**

Run: `php vendor/bin/pest "tests/Unit/ResearchCardsGridTest.php"`

Expected: PASS.

### Task 2: Add the new flexi block

**Files:**
- Create: `acf-fields/partials/blocks/acf_research_cards_grid.php`
- Create: `template-parts/flexi/research_cards_grid.php`

- [ ] **Step 1: Create the ACF Builder definition**

Create `acf-fields/partials/blocks/acf_research_cards_grid.php` with a dedicated `FieldsBuilder` that returns `research_cards_grid` and uses the same `Content`, `Design`, and `Layout` tab structure as the recent flexi blocks.

Add these fields:

- `Content` tab:
  - `heading_tag`
  - `heading`
  - `intro`
  - `cards` repeater with `image`, `title`, `summary`, `link`
  - `footer_button_link`
- `Design` tab:
  - `background_color`
  - `heading_color`
  - `intro_color`
  - `card_title_color`
  - `card_body_color`
  - `button_border_color`
  - `button_text_color`
- `Layout` tab:
  - shared `padding_settings`

Use these defaults:

```php
'heading' => 'Our Research Ethics Committee'
'background_color' => '#FFFFFF'
'heading_color' => '#1E244B'
'intro_color' => '#08284B'
'card_title_color' => '#1E244B'
'card_body_color' => '#1E244B'
'button_border_color' => '#024B79'
'button_text_color' => '#08284B'
```

- [ ] **Step 2: Create the frontend template**

Create `template-parts/flexi/research_cards_grid.php` and render:

- standard section shell with generated id and `data-matrix-block`
- `max-w-[1018px]` inner wrapper plus shared `padding_settings`
- heading and underline using the established treatment
- intro copy above the cards
- responsive grid:
  - `grid-cols-1` on narrow mobile
  - `sm:grid-cols-2`
  - `xl:grid-cols-4`
- per-card image, title, and summary
- optional whole-card link when a card link exists
- optional section CTA below the grid, right-aligned on larger screens

Use these class patterns:

```php
$grid_classes = 'mt-8 grid grid-cols-1 gap-8 sm:grid-cols-2 xl:mt-12 xl:grid-cols-4';
$card_base_classes = 'group flex h-full flex-col gap-4';
$linked_card_classes = $card_base_classes . ' focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
```

For the title row, only append the arrow icon if the card link exists. For the CTA, use the existing button style family:

```php
class="btn inline-flex w-fit whitespace-nowrap items-center justify-center rounded-md border px-4 py-2 text-[14px] font-medium leading-[24px] transition-colors duration-200"
```

- [ ] **Step 3: Run syntax checks**

Run:

```bash
php -l "acf-fields/partials/blocks/acf_research_cards_grid.php"
php -l "template-parts/flexi/research_cards_grid.php"
php -l "inc/research-cards-grid-functions.php"
php -l "tests/Unit/ResearchCardsGridTest.php"
php -l "functions.php"
```

Expected: `No syntax errors detected` for all files.

### Task 3: Seed the demo page

**Files:**
- Modify data only on page ID `329` (`/flexi/`)

- [ ] **Step 1: Append the block to `/flexi/`**

Use `wp eval` to:

- load page ID `329`
- fetch four existing image attachments from the media library
- remove any prior demo row with `acf_fc_layout = research_cards_grid`
- append one fresh `research_cards_grid` row

Seed this section content:

- heading: `Our Research Ethics Committee`
- intro: `Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini incididunt ut lab, sed do eiusmod tempore et dolore magn.`
- CTA:
  - title: `View Ethics Committee`
  - url: `/`

Seed these four cards:

1. `View ethics guidance` / `Short supporting copy for the first ethics resource card.`
2. `Committee membership` / `Short supporting copy for the second ethics resource card.`
3. `Meeting schedule` / `Short supporting copy for the third ethics resource card.`
4. `Submission checklist` / `Short supporting copy for the fourth ethics resource card.`

Use `/` as the demo link for each card so the linked-card state is visible.

- [ ] **Step 2: Verify the row exists in page content**

Run: `curl -s "http://localhost:10034/flexi/" | rg -n "research-cards-grid|Our Research Ethics Committee|View Ethics Committee"`

Expected: at least one match for the new section heading or block marker.

### Task 4: Build and verify

**Files:**
- Review only

- [ ] **Step 1: Rebuild front-end assets**

Run: `npm run build`

Expected: exit code `0`.

- [ ] **Step 2: Run focused verification**

Run:

```bash
php vendor/bin/pest "tests/Unit/ResearchCardsGridTest.php"
php -l "acf-fields/partials/blocks/acf_research_cards_grid.php"
php -l "template-parts/flexi/research_cards_grid.php"
```

Expected: the helper test passes and both PHP files lint cleanly.

- [ ] **Step 3: Read lints for changed files**

Check IDE diagnostics for:

- `acf-fields/partials/blocks/acf_research_cards_grid.php`
- `template-parts/flexi/research_cards_grid.php`
- `inc/research-cards-grid-functions.php`

Expected: no newly introduced diagnostics.

- [ ] **Step 4: Capture browser verification of `/flexi/`**

Use browser automation to verify:

- heading, underline, and intro match the approved layout
- four-card desktop grid renders in one row at large widths
- CTA sits beneath the cards and aligns right on large screens
- responsive collapse behaves as 4 -> 2 -> 1 columns
- linked cards and the CTA show visible keyboard focus without bounce or lift effects

### Task 5: Final review

**Files:**
- Review only

- [ ] **Step 1: Confirm the block still matches the approved scope**

Checklist:

- dedicated `research_cards_grid` block
- manual repeater cards only
- image, title, summary, and optional per-card link
- optional section CTA
- seeded onto `/flexi/`
- no slider, carousel, or generic “cards framework” abstraction

- [ ] **Step 2: Summarize any known deviations**

If demo images or copy differ from the original Figma placeholders, record that explicitly in the final handoff so the user can decide what to refine after the first implementation pass.
