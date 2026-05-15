# Blog Filter Archive Design

## Goal

Create a reusable Figma-derived blog archive surface that supports category filtering, keyword search, editorial card listing, and pagination. This should work as a dedicated flexi block on `/flexi/` and also serve as the visual/query foundation for the real blog index and blog archive pages.

## Scope

This spec covers one new block-oriented archive surface:

- `blog_filter_archive`

It is intentionally **not** an extension of `latest_posts`. The existing `latest_posts` block is a fixed editorial teaser grid. This new surface is a true archive/listing interface with category chips, search, cards, and pagination.

This spec also covers the requirement that the same rendered pattern should be reusable on:

- the `/flexi/` demo page
- the main blog index page
- category-filtered blog archive pages

## Source Design

- Figma file: `0Fd6tsq9KbzxPzgmp3rokR`
- Node: `2888:2584`

Visual reference:

- white section background
- top control row with left-aligned `Filter by:` label and pill-style category chips
- right-aligned search input with dark search button
- twelve editorial cards shown in a three-column desktop grid
- card image on top
- category badge beneath image
- title, date, and short excerpt beneath the badge
- pagination row below the grid using circular controls and numbered pages

The provided frame visually shows `All`, `News`, and `Events`, but the implementation should use the native WordPress `category` taxonomy dynamically rather than hardcoding those exact labels.

## Architecture

Implement this as a dedicated archive-style rendering layer with shared query and presentation helpers. The same query argument builder, filter-state resolver, and card-rendering markup should support both:

- the flexi block version
- the main blog archive/index templates

The user specifically asked for Alpine.js-based category filtering and search. Alpine should own the client-side UI state, but the results should remain **server-backed** through real WordPress query vars and `WP_Query`, not through a fake client-only filtered array.

This architecture is preferred because it:

- scales to the full blog dataset
- keeps pagination real
- allows shareable/filterable archive URLs
- avoids loading the entire archive into the browser
- keeps the `/flexi/` demo and archive pages visually aligned

Files likely needed during implementation:

- ACF builder: `acf-fields/partials/blocks/acf_blog_filter_archive.php`
- Helper/query file: `inc/blog-filter-archive-functions.php`
- Frontend block template: `template-parts/flexi/blog_filter_archive.php`
- Shared/archive partials or updated archive template(s):
  - `template-parts/blog/index.php`
  - optionally one or more new partials under `template-parts/blog/`
- Tests: `tests/Unit/BlogFilterArchiveTest.php`

## Data Model

### Content tab

- `heading_tag`
- `heading`
- `filter_label` defaulting to `Filter by:`
- `search_placeholder` defaulting to `Search news and events`
- `search_button_label` defaulting to `Search`
- `posts_per_page` defaulting to `12`
- optional `allowed_categories` using native WordPress `category`
- optional `empty_state_message`
- optional `pagination_base_mode` only if needed to support different contexts cleanly

### Design tab

- `background_color`
- `filter_label_color`
- `chip_text_color`
- `chip_border_color`
- `active_chip_background_color`
- `active_chip_text_color`
- `search_input_text_color`
- `search_input_border_color`
- `search_button_background_color`
- `search_button_text_color`
- `card_background_color`
- `card_title_color`
- `card_meta_color`
- `card_excerpt_color`
- optional badge tone defaults for common editorial categories

### Layout tab

- shared `padding_settings`

## Query Model

The block should query standard WordPress `post` entries only.

### Filter state inputs

The live archive state should be resolved from:

- selected category slug or `all`
- search query string
- current page number

The flexi block should still be able to define its own defaults and allowed category set, but the active result set should be driven by request/query vars so the UI works consistently with archive pages.

### Query rules

- base query targets `post`
- published posts only
- newest first by default
- `posts_per_page` defaults to `12`
- if an allowed-category list is configured, only categories inside that allowlist should appear as chips or be queryable from this surface
- if a category filter is selected, narrow the query to that category
- if a search term is present, apply it to the same query using WordPress search behavior
- if both category and search are present, both constraints apply together
- pagination must reflect the actual filtered result count

## Alpine Interaction Model

Alpine should manage the UI state for:

- active category chip
- current search input value
- optional form submission/reset helpers

Recommended interaction behavior:

- chip click updates the category value and submits the form or redirects with updated query vars
- search submit updates the search query and resets pagination to page `1`
- changing category also resets pagination to page `1`
- active chip styling is derived from Alpine state
- search input value is hydrated from the current request state so reloads keep context

Alpine should **not** be responsible for filtering a preloaded list of all posts in memory.

## Rendering Structure

### Header / control row

Render a single control row matching the Figma:

- left cluster:
  - filter label
  - pill chips for `All` plus resolved category list
- right cluster:
  - text search input
  - dark search button with icon if appropriate

On smaller screens the row can stack, but the category chips and search field should keep their visual hierarchy rather than collapsing into a totally different mobile-only pattern.

### Card grid

Render archive cards with:

- featured image
- category badge
- title
- date
- excerpt

Each card should link to the post, with the title as the primary link target and the image optionally linked as well if that matches surrounding theme patterns.

The design shows twelve cards, so the default block configuration should produce twelve results per page unless overridden.

### Pagination

Render real pagination below the cards:

- previous / next arrow controls
- numbered pages
- active page treatment matching the circular Figma style

Pagination must respect current category and search query vars so navigating pages preserves the active filters.

## Reuse Strategy

This block should be the first implementation of a shared blog archive presentation layer, not a one-off flexi-only block.

The implementation should avoid duplicating a separate archive UI inside `template-parts/blog/index.php`. Instead:

- extract filter-state resolution into a helper
- extract query-arg building into a helper
- extract card-grid and pagination rendering into reusable partials or shared template logic

The flexi block should use that shared layer now, and the blog index/archive templates should be able to adopt the same layer with minimal extra work later.

## Responsive Behavior

Desktop behavior from Figma:

- three-column card grid
- full filter/search row in a single line

Expected responsive behavior:

- three columns on larger desktop widths
- two columns at intermediate tablet widths
- one column on narrow mobile widths
- filter/search row may wrap or stack, but category chips should remain readable and search should stay usable

The pagination row should remain tappable and not collapse into unreadable tiny controls on mobile.

## Demo Workflow Rule

Every new Figma-driven section should be demonstrated on `/flexi/` as part of the same task. This block must therefore be added to `/flexi/` with enough real post data and native categories to prove:

- category chip filtering
- search query state
- archive-style pagination

## Seed / Demo Plan

During implementation:

- add one `blog_filter_archive` block to `/flexi/`
- use the native blog `category` taxonomy
- show a realistic set of categories as chips
- seed it so the page clearly demonstrates both category filtering and keyword search
- ensure there are enough posts available locally for at least one paginated view, if possible

If local content volume is too small for genuine pagination, the implementation should still build real pagination logic and note the local-content limitation during verification.

## Accessibility

- preserve semantic heading levels from the selected `heading_tag`
- use real form semantics for the search control
- category chips should be accessible as buttons or links with clear active state
- maintain visible focus styles on chips, search input, search button, cards, and pagination controls
- preserve meaningful featured-image alt text from WordPress media metadata
- ensure keyboard users can operate category selection, submit searches, and paginate cleanly

## Testing

Implementation should include focused helper coverage for:

- category allowlist resolution
- query arg building for category + search + pagination
- request-state normalization
- pagination URL preservation for active filters

Implementation should also verify:

- PHP lint on new files
- `/flexi/` rendering
- archive rendering if the shared template is adopted in the same pass
- Alpine state correctly reflects current query vars
- category/search combinations return the expected result set

## Notes

- This block should use the native WordPress `category` taxonomy
- It should not replace `latest_posts`; both blocks should coexist with distinct purposes
- Alpine is required for the interaction layer, but the data/query layer should remain server-backed
- The design currently shows editorial tones for `News` and `Events`; implementation should allow category badge tones to map cleanly to real taxonomy terms
