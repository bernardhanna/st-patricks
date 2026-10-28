# Mailchimp newsletter wiring — Implementation Plan

> **For agentic workers:** Execute locally first, then staging. Do not commit API keys.

**Goal:** Footer → SPUH Regular Newsletter; GP page → GP's newsletter; same credentials on local + staging.

**Spec:** `docs/superpowers/specs/2026-10-16-mailchimp-newsletter-wiring-design.md`

## Task 1: Obtain Mailchimp API key + audience IDs

- [x] Created key **Matrix SPMHS WordPress 2026** (expires 2027-10-16)
- [x] List IDs: SPUH Regular `b78f36cc4c`; GP's newsletter `c272b9ba5f`

## Task 2: Configure local WordPress

- [x] `MATRIX_MAILCHIMP_KEY` in local `wp-config.php`
- [x] `mailchimp_list_id` = `b78f36cc4c`
- [x] `newsletter_provider` = `mailchimp`
- [x] GP page + Training Centre newsletter → `c272b9ba5f`

## Task 3: Audit other signup forms

- [x] Flexi `newsletter`: only GP eNewsletter + Training Centre (both GP list)
- [x] Mailing-list contact forms (not wired this pass):
  - Service User Advisory Network (Join SUAN)
  - Women’s Mental Health Network (Joining the network)

## Task 4: Verify local

- [x] API subscribe smoke-test to both audiences (see session)

## Task 5: Staging (after local)

- [ ] Same key + list IDs on staging Theme Options / wp-config
- [ ] Same GP page list ID
- [ ] Smoke-test both forms
