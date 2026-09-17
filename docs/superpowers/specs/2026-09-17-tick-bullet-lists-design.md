# Tick bullet lists

Replace disc-style unordered list markers with a teal stroke tick icon that matches the site brand. Numbered lists stay unchanged.

## Goals

- Replace default disc bullets on content `ul` lists with a simple teal checkmark.
- Keep `ol` / decimal lists as they are today.
- Match front end and block editor so editing preview matches the live site.
- Align hard-coded dash-style “bullets” (e.g. key contact info rows) with the same tick.

## Non-goals

- Changing list content, structure, or CMS fields.
- Adding a filled circle / badge-style check.
- Restyling navigation, footer, or UI chrome lists that already use `list-none`.
- Replacing every decorative dash or separator that is not a list marker.

## Visual design

- **Icon:** Simple stroke tick (no circle), colour `#6FC9C0` (same teal as heading underlines).
- **Size:** About 16–18px wide, optically aligned to the first line of body text.
- **Spacing:** Keep roughly the current left gutter (`pl-6` / outside marker feel); tick sits in the left gutter; existing item spacing (`mb-2` / similar) stays.
- **Nested `ul`:** Also use the tick (not discs). Nested `ol` remain numbered.

## Approach (hybrid)

### 1. CSS for WYSIWYG / content lists

Centralise unordered-list marker styling so discs become ticks wherever content lists are styled today:

- `assets/css/app.css` (`.wp_editor` / `.entry-content` / `.editor-body-content` / `.blog-single-content` / careers job description, etc.)
- `assets/css/editor.css` for the block editor
- Remove or override Tailwind `list-disc` utilities on WYSIWYG wrappers (e.g. `[&_ul]:list-disc` in flexi templates / accordion content classes) so they do not fight the new marker rules

Implementation detail:

- `ul { list-style: none; }` (padding retained for gutter)
- `ul > li { position: relative; }` with a `::before` tick via inline SVG `background-image` / `mask`, or an equivalent CSS-only stroke tick that uses `#6FC9C0`
- Do **not** apply this to `ol`, or to lists that intentionally use `list-none` for layout (menus, dots, etc.)

### 2. Template parity for custom bullet rows

Where lists are not `<ul>` but rows with a dash character (notably key contact info bullet labels), replace the dash with the same tick SVG (or shared helper) so visual language is consistent.

Prefer a small shared SVG helper (same pattern as existing `matrix_get_*_icon_svg()` helpers) if more than one template needs it.

## Out of scope / leave alone

- Numbered lists (`ol`, `list-decimal`)
- UI lists already using `list-none` (slick dots, nav, form layouts)
- Changing bullet *content* in ACF repeaters beyond the visual marker

## Testing

- Spot-check a content section with nested and flat `ul` lists.
- Spot-check a blog / WYSIWYG page and careers job description if those use discs.
- Confirm `ol` lists still show numbers.
- Confirm key contact dash rows show the teal tick.
- Confirm block editor list preview matches the front end after CSS rebuild.

## Success criteria

- Disc bullets no longer appear on content unordered lists.
- Teal stroke ticks appear consistently on those lists and on custom bullet rows covered above.
- Numbered lists and non-content `list-none` lists are unchanged.
