# Mailchimp newsletter wiring (footer + GP)

**Date:** 2026-10-16  
**Status:** Approved  
**Environments:** Local + staging (`https://st-patricks.s1.matrix-test.com/`) — same Mailchimp credentials on both.

## Problem

Delegated access to the St Patrick's Mental Health Services Mailchimp account is available. Site newsletter / opt-in forms must subscribe to the correct audiences. The theme already supports Mailchimp for footer and flexi `newsletter` blocks; configuration and an explicit GP audience mapping are incomplete.

## Goals

- Footer newsletter subscriptions go to **SPUH Regular Newsletter**.
- GP e-newsletter subscriptions go to **GP's newsletter** (not **GP - email only**).
- Configure **local** and **staging** with the same API key and audience settings.
- Inventory other signup forms (`newsletter` / `contact_form` mailing-list) and record which (if any) still need list mapping later.

## Non-goals

- Multi-account / multi-API-key architecture.
- Wiring contact-form / mailing-list blocks to Mailchimp in this pass (unless audit finds a hard requirement — then a follow-up).
- Legacy or test audiences: HSE managers 2017, TY grad 2016, SUAN Test.
- Replacing the custom theme subscribe stack with MC4WP / Gravity Forms / CF7.
- Changing newsletter UI copy or layout beyond list targeting.

## Mailchimp inventory (current account)

**Account:** St Patrick's Mental Health Services (Pro, datacenter `us2`).

| Audience | Role in this work |
|----------|-------------------|
| SPUH Regular Newsletter | Default / footer |
| GP's newsletter | GP campaign + Training Centre GP block |
| GP - email only | Do not use for site GP signup |
| SUAS Council, SUAN, Walk In My Shoes Newsletter, WIMS Secondary/Primary | Audit only; out of scope unless required later |
| HSE managers 2017, TY grad 2016, SUAN Test | Ignore |

## Existing mechanism (unchanged)

| Concern | Location |
|---------|----------|
| Provider + default list | Theme Options → Newsletter (`newsletter_provider`, `mailchimp_api_key` / `MATRIX_MAILCHIMP_KEY`, `mailchimp_list_id`) |
| Footer form | `template-parts/footer/newsletter.php` (hidden when page has a flexi `newsletter` block) |
| Flexi newsletter block | `acf_newsletter` → `newsletter_list_id`; AJAX `matrix_subscribe_brevo` → Mailchimp when provider is mailchimp |
| GP list resolution | `matrix_resolve_gp_newsletter_list_id()` — prefers list on `/campaigns/subscribe-to-our-gp-enewsletter/`, else first audience name containing `"GP"` (ambiguous; **must** set explicit ID on page) |
| Contact mailing-list forms | Email / optional Brevo only — not Mailchimp |

## Approach: config-only

1. **Credentials** — On local and staging: set Mailchimp API key via Theme Options or `define('MATRIX_MAILCHIMP_KEY', '…');` in `wp-config.php`. Confirm audiences load in ACF list dropdowns.
2. **Default audience** — Set `mailchimp_list_id` to **SPUH Regular Newsletter** so the footer (and any newsletter block with empty list) targets that audience. Align double opt-in with the audience’s Mailchimp setting.
3. **GP audience** — On the GP e-newsletter page (`subscribe-to-our-gp-enewsletter`) and Training Centre GP newsletter row if present: set flexi `newsletter_list_id` to **GP's newsletter** explicitly (store the real list ID from Mailchimp, not rely on name matching).
4. **Audit** — Enumerate pages with flexi `newsletter` or `contact_form` (`form_style = mailing_list`) on staging/local. Document title, URL, block type, and current/desired list. Do not wire SUAS/SUAN/WIMS in this pass unless product asks.
5. **Verify** — Test subscribe: footer → SPUH Regular; GP page → GP's newsletter; confirm contacts appear in Mailchimp. Repeat on staging (password-gated site; use shared WP admin / same Mailchimp key).

## Success criteria

- [ ] Theme Options provider is Mailchimp; API key works on local and staging.
- [ ] Default list is SPUH Regular Newsletter; footer signup creates/updates a contact there.
- [ ] GP page (and Training Centre GP block if used) targets GP's newsletter; test signup lands there.
- [ ] Audit list of other signup forms is written (spreadsheet note or short appendix under this spec’s follow-ups).
- [ ] No accidental use of GP - email only for site GP signup.

## Follow-ups (out of this pass)

- Extend mailing-list contact forms to Mailchimp if SUAS/SUAN/WIMS (or others) must sync.
- Tighten `matrix_resolve_gp_newsletter_list_id()` to prefer an exact audience name (`GP's newsletter`) over any `"GP"` substring, to avoid picking **GP - email only**.
- Optional rename of AJAX action `matrix_subscribe_brevo` (cosmetic; behaviour already provider-aware).
