# Referral Action Cards Design

## Goal

Create a new flexible content block that matches Figma node `2888:3770`: a fixed two-card action section for referral-related calls to action, with compact copy, colored card backgrounds, and small icon buttons.

## Scope

This is a new flexi block, not a variant of an existing one.

Create:

- `referral_action_cards`

The block should stay intentionally narrow in scope:

- exactly two cards
- each card has editable copy and button content
- each card can choose its own icon type and background color
- the section is added to `/flexi/` after implementation

This work does **not** attempt to become a generic repeater-based card system.

## Source Design

- Figma file: `0Fd6tsq9KbzxPzgmp3rokR`
- Node: `2888:3770`

The design shows:

- a simple white section background
- a centered content container
- two equal-width cards side by side
- pastel card backgrounds
- one strong title and one supporting paragraph per card
- a compact dark-blue button at the bottom of each card
- one external-link icon treatment
- one download icon treatment

## Reuse Strategy

Do **not** build this as a repeater or as a generalized “card grid” block.

The user chose a fixed two-card scope, so the cleanest implementation is:

- one dedicated block
- one left-card field group
- one right-card field group

This keeps:

- field labels obvious for editors
- markup simple and stable
- the Figma composition intact

## Content Model

### Block-level fields

Add:

- `section_id` only if this is already a normal pattern in similar blocks
- `padding_settings`

No section heading is needed because the Figma does not show one.

### Left card fields

Add a grouped field set for the first card:

- `left_title`
- `left_description`
- `left_button`
- `left_action_icon`
- `left_background_color`

### Right card fields

Add a mirrored grouped field set for the second card:

- `right_title`
- `right_description`
- `right_button`
- `right_action_icon`
- `right_background_color`

### Button model

Use the standard theme link/button field pattern for each card’s CTA:

- label
- URL
- target

### Icon model

Each card should support a small controlled icon type selector with exactly:

- `external`
- `download`

This is used only to swap the inline icon inside the button.

## ACF Behavior

The authoring experience should remain very small and obvious.

Recommended structure:

- Content tab
  - left card group
  - right card group
- Layout tab
  - padding settings

Provide sensible defaults matching the Figma:

- left background: `#CEF2EE`
- right background: `#E4F4D6`
- left icon: `external`
- right icon: `download`

## Rendering

### Section layout

Render:

- a centered container at the established content width
- 2-column card grid on desktop
- 1-column stack on smaller screens

The section should visually breathe with generous vertical spacing above it, matching the surrounding flexi block patterns.

### Card structure

Each card should render:

1. title
2. supporting paragraph
3. button row

The button should sit naturally below the copy, with spacing close to the Figma.

### Button treatment

Buttons should:

- use the site’s existing dark-blue button styling conventions
- include the selected inline icon
- avoid novelty animation or exaggerated hover effects

### Icon treatment

Implement the icons inline in the template using SVG paths, not external icon packages.

There are only two icon variants needed:

- external link
- download

## Styling Notes

- use the established site typography, spacing, and button language
- keep the cards visually light and flat, with only the subtle shadow shown in Figma
- keep border radius and card padding aligned with nearby theme patterns
- do not add bounce, scale, or decorative hover behavior

## Interaction

- if a card has a valid button link, render the CTA as a link/button
- if a button is missing a URL, the CTA can fall back to a non-rendered button rather than a dead link
- button target should respect the chosen link target

## Responsive Behavior

Desktop:

- 2 equal-width cards
- consistent gap between cards
- vertical alignment should feel balanced even if copy lengths differ slightly

Mobile:

- cards stack to one column
- button remains compact and left-aligned
- typography scales down only as needed to stay aligned with the rest of the theme

## Demo Requirement

After implementation, add the new block to:

- `/flexi/`

using the referral-form sample content from the Figma so the section can be validated immediately.

## Accessibility

Ensure:

- each button has clear accessible text
- decorative SVG icons are marked `aria-hidden="true"`
- color contrast remains acceptable for text and buttons
- keyboard focus on both CTAs is clearly visible

## Testing / Verification

Implementation should verify:

- the block renders exactly two cards
- each icon type renders the correct SVG
- missing/partial button data does not produce broken links
- desktop and mobile layouts match the intended two-column to one-column shift
- `/flexi/` includes a working demo row for the new block

## Notes

- Prefer a right-sized dedicated block over a reusable repeater abstraction
- Keep the markup and field model simple; this section’s value is in faithful visual implementation, not generality
