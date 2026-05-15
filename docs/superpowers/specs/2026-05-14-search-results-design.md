# Search Results Design

## Goal

Create a dedicated global search results page that matches Figma nodes `2888:2936` and `2888:3131`, replacing the current `is_search()` fallback into the blog archive path. The same surface should handle both populated and no-results states within one search-specific implementation.

## Scope

This work includes:

- a real `search.php` template for native WordPress search requests
- a search-specific helper/query layer
- a shared search results renderer covering both results and no-results states
- support for global multi-type search with a type filter dropdown
- support for search sorting and pagination

This work does **not** include:

- turning the blog archive into a generic search/archive engine
- changing the existing navbar search entry point beyond routing it into the new search surface
- adding a flexi block version of the search results page

## Source Design

- Figma file: `0Fd6tsq9KbzxPzgmp3rokR`
- Results node: `2888:2936`
- No-results node: `2888:3131`

The design shows:

- breadcrumb row on a light blue background
- compact search form above the heading
- results heading with italicized query term
- sort dropdown
- type filter dropdown
- stacked result cards
- pagination controls
- dedicated no-results empty state with a “Go back to Home page” button

## Current State

The current theme routes `is_search()` through `index.php` into `template-parts/blog/index.php`, which makes search behave like a blog/category archive instead of a dedicated search surface.

This search redesign should break that coupling and give search its own route, helper layer, and renderer.

## Search Scope

The user confirmed that this should be a global multi-type search rather than a blog-only search.

Initial supported result types should be:

- `all`
- `blog`
- `page`
- `webinars`

These map to post type groupings, not taxonomy chips.

## Architecture

### Recommended structure

Build a dedicated search system with:

- search helper functions for state resolution and result normalization
- a search-specific template part renderer
- `search.php` as the WordPress entry template

This keeps:

- one search state model
- one renderer for both results and no-results states
- one normalization layer for mixed content types

without forcing blog archive assumptions into a search page.

### Reuse boundaries

Reuse:

- the breadcrumb styling language from recent archive surfaces
- the compact search input/button pattern already used in the navbar search
- the project’s existing typography, spacing, and color conventions

Do not reuse:

- blog category chip logic
- blog archive query var names
- blog-specific archive card templates

## Query Model

The search page should continue to use native WordPress search as the entry point:

- `s` remains the search term

Additional search-specific query vars should be introduced for UI state:

- `search_type`
- `search_sort`
- `paged`

Suggested initial filter values:

- `all`
- `blog`
- `page`
- `webinars`

Suggested initial sort values:

- `relevance`
- `date`

The helper layer should sanitize and validate:

- search term
- allowed type values
- allowed sort values
- page number

## Post Type Mapping

The filter dropdown should map friendly search types onto real query constraints:

- `all` => all allowed search post types
- `blog` => `post`
- `page` => `page`
- `webinars` => `webinars`

This mapping should live in the helper layer so the template does not need to know WordPress query details.

## Rendering

### Top surface

The page should render:

- breadcrumb row: `Home > Search > Results`
- compact search form showing the current query
- H1 matching the Figma copy pattern:
  - results: `Search result for 'query'`
  - no-results: `We couldn’t find a match for 'query'`

The italicized term treatment should follow the Figma while using the theme’s existing typography conventions.

### Controls

Below the heading, render:

- sort label + dropdown
- filter label + dropdown

These should submit as server-backed controls rather than introducing a client-only filtering system.

### Result cards

Render stacked cards on desktop and mobile, matching the search results design rather than the blog archive grid.

Each result card should support:

- optional thumbnail image
- optional type badge
- title
- optional date line when the content type has a meaningful date
- excerpt/summary
- destination URL

The result list should be normalized so mixed post types can render through one card template.

### No-results state

When the query returns no matches:

- keep the compact search form visible
- swap in the dedicated empty-state heading
- render the “Go back to Home page” button
- omit result controls, cards, and pagination

This should be a conditional branch inside the same search surface, not a separate page template.

### Pagination

When results exist, render pagination below the result list.

Pagination should preserve:

- `s`
- `search_type`
- `search_sort`

## Result Normalization

Mixed post types should be converted into a consistent view model before rendering.

Each normalized result item should expose:

- `title`
- `url`
- `image`
- `image_alt`
- `type_key`
- `type_label`
- `date_label`
- `excerpt`

Suggested type labels for the initial pass:

- `Blog`
- `Page`
- `Webinar`

Badge rendering can remain optional for types where the label does not improve the design.

## Copy / Empty State

The no-results state should be driven by the actual search term and use the same query string already present in the form input.

The home-page CTA should link to:

- `home_url('/')`

If the query is empty or whitespace-only, the helper layer should fall back to a sensible heading label such as `Search Results`.

## Accessibility

Ensure:

- the search input has a programmatic label
- the current breadcrumb item is announced correctly
- sort and filter controls have visible labels and accessible names
- result cards retain clear focus styling
- pagination nav has proper labeling
- the no-results home CTA is keyboard reachable and clearly named

## Responsive Behavior

Desktop:

- breadcrumb row spans full content width
- search form sits above the heading
- sort and filter controls share a row
- results render as stacked horizontal cards

Smaller screens:

- search form remains compact but wraps cleanly
- heading reflows over multiple lines
- sort and filter controls stack
- cards collapse into a narrower single-column layout

## Testing / Verification

Implementation should verify:

- native search requests render `search.php`
- search state resolves and sanitizes correctly
- type filter constrains the query correctly
- sort changes ordering correctly
- mixed content types normalize into one card model
- no-results state appears when there are zero matches
- pagination preserves active search/filter/sort state
- visual parity is checked against both Figma nodes

## Notes

- This is a dedicated search experience, not a variant of the blog archive
- Keep the first pass right-sized: global multi-type search, type filter, sort, pagination, and the no-results state
- Prefer a search-specific helper + renderer split over extending the archive abstraction further
