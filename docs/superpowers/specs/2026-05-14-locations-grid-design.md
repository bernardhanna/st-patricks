# Locations Grid Design

## Goal

Create a new flexible content block that matches Figma node `3093:8474`: a heading-led locations section with a three-column image card grid and an optional footer CTA.

## Scope

This is a new flexi block, not a variant of an existing one.

Create:

- `locations_grid`

The block should stay intentionally narrow in scope:

- heading plus underline
- manual repeater-driven location cards
- optional linked cards
- optional footer button
- the section is added to `/flexi/` after implementation

This work does **not** attempt to reuse `team_members`, create a new CPT, or generalize into a broader card-grid system.

## Source Design

- Figma file: `0Fd6tsq9KbzxPzgmp3rokR`
- Node: `3093:8474`

The design shows:

- a white section background
- the standard heading and teal underline treatment
- a 3-column card grid
- image-first cards with a light caption panel beneath
- six sample location cards
- a small outlined footer button labeled `Locations Page`

## Reuse Strategy

Do **not** extend `team_members` or `research_cards_grid`.

The user chose manual repeater authoring, so the cleanest implementation is:

- one dedicated block
- one small card repeater
- one optional footer CTA

This keeps:

- editor UX simple
- image/title/link data easy to manage
- the visual composition close to the Figma
- the block independent from unrelated team/research concerns

## Content Model

### Block-level fields

Add:

- `heading`
- `heading_tag`
- `cards`
- `footer_button_link`
- `padding_settings`

No intro copy is required because the Figma section does not include one.

### Card model

Each repeater row should include:

- `image`
- `title`
- `link`

The `link` is optional:

- when present, the whole card can be clickable
- when absent, the card renders as static content

## ACF Behavior

The authoring experience should remain small and obvious.

Recommended structure:

- Content tab
  - heading
  - heading tag
  - cards repeater
  - footer button link
- Layout tab
  - padding settings

The cards repeater should use a row layout with a clear `Add Location Card` button label.

## Rendering

### Section layout

Render:

- a centered wrapper at the established `1018px` content width
- heading
- standard underline
- card grid
- optional footer button below the grid

### Card structure

Each card should render:

1. image
2. caption panel
3. location title

The image should fill the top panel cleanly using `object-cover`.

The lower caption area should use the light off-white surface from the Figma:

- `#FBFAF7`

### Card interaction

If a card has a valid link:

- render the full card as an anchor
- keep hover/focus treatment restrained and consistent with the rest of the site

If a card has no link:

- render it as a static `article`

No arrows or decorative hover motion are required by the design.

### Footer CTA

If `footer_button_link` is present:

- render a small outlined button below the grid
- use the same dark blue border/text treatment shown in the Figma

If the link is missing:

- omit the footer CTA entirely

## Styling Notes

- use the existing heading/underline pattern already established elsewhere in the theme
- use project typography classes and colors instead of raw Figma export styles
- keep the cards clean, light, and flat, with only the subtle shadow shown in the design
- do not add bounce, lift, or decorative hover effects

## Responsive Behavior

Desktop:

- 3-column grid
- even gaps between cards
- image and caption proportions should stay visually balanced

Tablet and mobile:

- reduce to fewer columns naturally
- cards should stack cleanly without introducing custom carousel or slider behavior
- title wrapping should remain legible

## Demo Requirement

After implementation, add the new block to:

- `/flexi/`

using the six sample location cards from the Figma and a footer button labeled `Locations Page`.

## Accessibility

Ensure:

- each image has a meaningful alt fallback
- linked cards have visible focus states
- static cards are not given fake interactive affordances
- the footer CTA has clear accessible text

## Testing / Verification

Implementation should verify:

- the repeater cards normalize into a clean renderable array
- linked and unlinked cards both render correctly
- the footer CTA only renders when populated
- desktop and responsive grid behavior match the intended layout
- `/flexi/` includes a working demo row for the new block

## Notes

- Prefer a right-sized dedicated block over reuse that weakens other block boundaries
- Keep the field model and template simple; this section is mostly about faithful visual composition
