# Webinars Archive Design

## Goal

Create a dedicated `webinars` content type and a searchable, filterable archive surface that matches Figma node `2888:2722`. The same surface should be reusable both on the real webinars index page and as a flexi demo block on `/flexi/`.

## Scope

This work includes:

- a new `webinars` CPT
- a new taxonomy used by the filter chips
- a real webinars archive page
- a flexi block wrapper that renders the same archive surface
- local demo seed content for both archive and `/flexi/`

This work does **not** attempt to generalize the existing blog archive into a universal engine. Reuse should happen at the pattern/architecture level, not through premature abstraction.

## Source Design

- Figma file: `0Fd6tsq9KbzxPzgmp3rokR`
- Node: `2888:2722`

The design shows:

- search input and search button
- filter label with rounded chips
- two-column card grid
- term-colored pill badges
- event/webinar card background variations
- pagination controls

## Content Model

### Post type

Create:

- `webinars`

The CPT should support at minimum:

- title
- editor/excerpt/summary content
- featured image if needed later
- ordering if useful for admin

### Taxonomy

Create a taxonomy for the chips:

- `webinar_type`

Initial intended terms:

- `events`
- `webinars`

The user has confirmed that the `All / Events / Webinars` chips should be taxonomy-driven **within the webinars CPT**, not a mixed-content archive.

### Card fields

Each webinar card should expose:

- title
- summary/excerpt
- date
- time
- taxonomy term badge
- link to the webinar single page

If date/time are not stored in the standard post fields, add dedicated custom fields for them.

## Architecture

### Recommended structure

Build a webinars-specific archive system that mirrors the blog archive approach:

- helper/query functions for archive state resolution
- shared webinars archive template
- archive page template for `/webinars/`
- flexi wrapper that feeds the same shared template

This keeps:

- one query model
- one renderer
- one card design implementation

without forcing the blog and webinars archives into the same generic renderer.

### Reuse boundaries

Reuse:

- the server-backed filter/search/pagination pattern from `blog_filter_archive`
- the same style of thin flexi wrapper plus shared renderer split

Do not reuse:

- blog-specific query vars
- blog-specific card markup
- blog-specific term/query assumptions

## Query Model

The archive should query only:

- post type `webinars`

Filtering/searching should use:

- taxonomy filter for `webinar_type`
- search query for webinar posts
- page number for pagination

Suggested query var names:

- `webinar_type`
- `webinar_search`
- `webinar_page`

The archive should sanitize and validate:

- allowed taxonomy slugs
- page number
- search string

## Rendering

### Top controls

The top row should render:

- search field
- search submit button
- filter label
- taxonomy chips

Interaction should be server-backed, with Alpine used only for light UI state where helpful, consistent with the blog archive approach.

### Cards

Render a two-column card grid on desktop.

Each card should include:

- taxonomy badge
- title
- date and time lines
- short summary

The whole card or title should link through to the webinar single page.

### Card styling

The Figma uses two card families:

- green-tinted treatment for `events`
- lavender-tinted treatment for `webinars`

This should map directly from taxonomy term styling rather than manual per-card settings in the archive.

### Pagination

Render pagination below the card grid.

Pagination should preserve:

- current taxonomy filter
- current search term

The pagination visual treatment should follow the Figma, not necessarily the blog archive exactly, though the underlying logic can be shared in spirit.

## Flexi Block

Create a dedicated flexi block that renders the same webinars archive surface.

The block should be a thin wrapper around the shared webinars archive template, primarily responsible for:

- optional copy/config fields if needed
- current page base URL
- wrapper classes/padding
- selecting which taxonomy terms are shown if configurability is desired

The flexi block exists for demo/reuse purposes and should not become a separate implementation of the archive.

## Archive Page

Create the real webinars index page using the same shared surface.

Expected route:

- `/webinars/`

The archive page should use the CPT and taxonomy directly rather than requiring flexi content.

## Local Demo / Seed Data

Seed:

- webinar posts
- at least the `events` and `webinars` taxonomy terms
- enough posts to demonstrate filtering, search, and pagination

Also add the webinars archive block to `/flexi/` so the design can be validated there immediately.

## Accessibility

Ensure:

- search input has a programmatic label
- filter chips expose active state accessibly
- pagination has proper nav labeling
- card links/focus states remain clear

## Responsive Behavior

Desktop:

- search and filter controls sit on one row
- cards render in two columns

Smaller screens:

- controls stack cleanly
- chips wrap naturally
- cards collapse to one column

## Testing / Verification

Implementation should verify:

- CPT and taxonomy register correctly
- filter/search state resolves and sanitizes correctly
- query args match selected term/search/page
- pagination preserves active filters
- `/webinars/` renders the surface correctly
- `/flexi/` renders the same surface correctly
- responsive layout matches the intended one-column mobile behavior

## Notes

- This is a dedicated webinars archive, not a blog variation
- Prefer a right-sized webinars-specific surface over a new generic archive abstraction
