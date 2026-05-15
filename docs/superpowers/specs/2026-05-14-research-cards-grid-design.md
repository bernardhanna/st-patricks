# Research Cards Grid Design

## Goal

Create a reusable Figma-derived flexi block for the manual four-card research-style section shown in node `2780:3926`, then use this spec as the source of truth for implementing the block and seeding it onto `/flexi/`. The block should match the current “Our Research Ethics Committee” composition: heading, underline, short intro, four image-led cards, and an optional CTA button below the grid.

## Scope

This spec covers one new flexi block:

- `research_cards_grid`

This block is intentionally manual-entry only. It does not introduce a CPT or taxonomy and does not attempt to become a generic site-wide cards framework.

## Source Design

- Figma file: `0Fd6tsq9KbzxPzgmp3rokR`
- Primary node: `2780:3926`

Visual reference:

- section heading in the established theme style
- standard aqua underline treatment below the heading
- one short intro paragraph above the cards
- four equal-width cards in a single desktop row
- each card uses an image, title, and short summary
- optional CTA button aligned below the grid
- white section background by default

## Architecture

Implement a dedicated flexi block instead of extending an existing posts or cards block. The user asked for this section specifically, and the content source is manual. A dedicated block keeps the ACF UI clean and avoids prematurely abstracting multiple unrelated card layouts into one generic system.

Files to create or modify during the future build:

- ACF builder: `acf-fields/partials/blocks/acf_research_cards_grid.php`
- Frontend template: `template-parts/flexi/research_cards_grid.php`
- Optional helper: `inc/research-cards-grid-functions.php`
- Optional tests: `tests/Unit/ResearchCardsGridTest.php`

## Content Model

### Content tab

- `heading_tag`
- `heading`
- `intro`
- `cards` repeater with:
  - `image`
  - `title`
  - `summary`
  - `link`
- `footer_button_link` as an optional section-level CTA

The block should encourage exactly four cards in the demo and design-matched use case, but the repeater should remain flexible enough to support fewer or more cards when necessary.

### Design tab

- `background_color` with default `#FFFFFF`
- `heading_color` with default `#1E244B`
- `intro_color` with default `#08284B`
- `card_title_color` with default `#1E244B`
- `card_body_color` with default `#1E244B`
- `button_border_color` with default `#024B79`
- `button_text_color` with default `#08284B`

Keep the design controls narrow and relevant to this specific layout. Do not introduce card shadow, border-radius, hover-animation, or complex variant toggles unless later designs clearly require them.

### Layout tab

- shared `padding_settings` repeater

## Frontend Behavior

- Render the standard flexi `<section>` shell with generated id and `data-matrix-block`
- Use the established `max-w-[1018px]` content width and `max-xl:px-5`
- Match the existing underline treatment: `mt-4 h-[4px] w-10 bg-[#6FC9C0]`
- Render the intro paragraph above the cards when populated
- Render the cards as a responsive grid, not a slider or carousel
- Use the card image at the top, followed by the title and summary
- Use the optional card link to determine whether the card is interactive
- Render the optional section CTA below the cards, aligned to the right on larger screens to match the Figma composition

### Card interaction

- If a card has a populated `link`, the whole card should link through
- Linked cards should retain a clear focus state
- If a card has no `link`, render it as a static `article`
- The title can include the arrow affordance only when the card is actually linked

## Responsive Behavior

No dedicated mobile node was provided for this section, so the implementation should use a conservative responsive interpretation:

- desktop: 4-column grid
- tablet / small desktop: 2-column grid
- narrow mobile: 1-column stack
- maintain image-first card structure at all sizes
- preserve readable spacing and avoid decorative hover motion

## Demo Workflow Rule

Every new section generated from Figma should also be demonstrated on the `/flexi/` page as part of the same task. This block should be seeded with four representative manual cards plus the optional CTA button so the layout can be validated immediately.

## Seeding Plan

During the future build:

- create one `research_cards_grid` block on page `329` (`/flexi/`)
- populate four cards with representative images, titles, summaries, and links
- populate the section heading, intro, and CTA using the “Our Research Ethics Committee” example from node `2780:3926`

## Accessibility

- preserve semantic heading levels from the selected `heading_tag`
- make linked cards one clear keyboard target
- keep visible focus treatment on linked cards and the CTA button
- use WordPress media alt text for card images with sensible fallbacks
- keep source order identical to visual order

## Testing

- add a focused helper test if card normalization or CTA resolution is extracted into a helper
- lint the future PHP files
- verify linked and non-linked card rendering
- verify the card grid collapses cleanly across desktop, tablet, and mobile widths
- capture `/flexi/` screenshots during the future build

## Notes

- This spec intentionally stays manual-entry only because the user selected a repeater-based content model
- This spec is written now for later implementation and is not being committed unless requested
