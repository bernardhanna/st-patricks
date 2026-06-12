# Page build rules (MD specs → seed scripts)

Use these rules when writing page specs in Markdown and the PHP seed scripts that implement them.

## Padding

**Do not set custom `padding_settings` on flexi blocks for now.**

- Omit the `padding_settings` key entirely from seed data.
- Let each block use its template defaults until spacing is reviewed site-wide.
- Do not copy per-breakpoint values (e.g. `mob: 3rem`, `lg: 6.25rem`) from older seed scripts.

```php
// Good — no padding_settings key
[
    'acf_fc_layout' => 'content',
    'heading' => 'Role of the REC',
    // ...
],

// Avoid for now
'padding_settings' => [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
],
```

## Heading hierarchy

**Use a logical outline: one `h1` per page, then `h2` → `h3` → `h4` → `h5`. Do not default every block to `h2`.**

Map source HTML / MD structure to ACF `heading_tag` values:

| Page level | `heading_tag` | Typical use |
|------------|---------------|-------------|
| Page title | `h1` | Hero only (one per page) |
| Major section | `h2` | Top-level content sections (`Role of the REC`, `Contact`, etc.) |
| Subsection | `h3` | Blocks nested under a major section (e.g. team grid under “Role of the REC”) |
| Sub-subsection | `h4` | Rare; use when source has a third level |
| Minor label | `h5` / `h6` | Sidebar widgets, small callouts |

### Example outline (Research Ethics Committee)

```text
h1  Research Ethics Committee          (hero)
h2  In this section                    (useful_links)
h2  Role of the REC                    (content)
h3  See our REC members here            (team_members)
h3  Apply for REC membership           (content)
h2  Applications to the REC             (content)
h2  Sharing your research              (content)
h2  Contact                            (content)
h2  Meeting dates                      (content)
h2  Queries / Referrals                (content_cta)
```

Accordion item titles are **not** separate `heading_tag` fields — they are panel labels. Keep major section headings in the `content` block above the accordion.

### In MD specs

When documenting a page, list the heading level explicitly:

```markdown
## Role of the REC          <!-- h2 → content block -->
### See our REC members     <!-- h3 → team_members block -->
### Apply for membership    <!-- h3 → content block -->
## Applications to the REC  <!-- h2 → content block -->
```

## MD spec checklist

- [ ] One `h1` in the hero only
- [ ] Section headings use `h2`; nested blocks use `h3` (or deeper) as appropriate
- [ ] No `padding_settings` in the seed script
- [ ] Copy matches live site / approved source
- [ ] Internal links use `home_url()`; media/PDFs use `matrix_migrate_live_url()` when not migrated locally

## Seed script conventions

- Require `scripts/lib/page-seed-conventions.php` for shared helpers.
- Use `matrix_page_seed_heading(2)` (etc.) instead of hard-coded `'h2'` strings when the level is derived from the outline.
- Re-seed with `wp eval-file wp-content/themes/matrix-starter/scripts/seed-{page-slug}.php`.
