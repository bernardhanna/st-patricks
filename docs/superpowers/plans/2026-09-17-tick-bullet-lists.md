# Tick Bullet Lists Implementation Plan

> **For agentic workers:** Execute inline in this session (user requested implement and build).

**Goal:** Replace disc-style `ul` markers with a teal stroke tick; leave numbered lists alone; match custom dash bullets.

**Architecture:** Shared CSS `::before` tick on content `ul > li`, remove competing `list-disc` utilities, shared SVG helper for hard-coded bullet rows.

**Tech Stack:** Tailwind/PostCSS theme CSS, PHP templates, Pest unit tests.

## Global Constraints

- Tick colour `#6FC9C0`, stroke only (no circle)
- Do not change `ol` / `list-decimal`
- Do not restyle `list-none` UI lists (nav, slick dots)

---

### Task 1: Shared tick SVG helper

**Files:**
- Modify: `inc/key-contact-info-functions.php`
- Modify: `template-parts/flexi/partials/key-contact-info-contact-details.php`
- Modify: `template-parts/flexi/partials/contact-directory-contact-details.php`

- [x] Add `matrix_get_list_tick_icon_svg()` and use in both dash-bullet templates

### Task 2: CSS tick markers + drop list-disc

**Files:**
- Modify: `assets/css/app.css`
- Modify: `assets/css/editor.css`
- Modify: `inc/content-section-functions.php`
- Modify: `inc/content-accordion-functions.php`
- Modify: `template-parts/flexi/wysiwyg.php`
- Modify: `template-parts/flexi/faqs.php`
- Modify: `tests/Unit/ContentSectionTest.php`
- Modify: `tests/Unit/ContentAccordionTest.php`

- [x] Add tick CSS; replace `list-disc` with `list-none` where content lists are styled

### Task 3: Build and verify

- [x] `npm run build:css`
- [x] Run relevant Pest tests
