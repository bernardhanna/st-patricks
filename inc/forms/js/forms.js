/* Theme-Forms helper v3 — AJAX submit + banners + CAPTCHA (reCAPTCHA v3 / Cloudflare Turnstile)
 *
 * Provide globals via enqueue:
 *   window.themeFormsCaptchaProvider  = 'none' | 'recaptcha_v3' | 'turnstile'
 *   window.themeFormsRecaptchaV3      = 'YOUR_RECAPTCHA_SITE_KEY'
 *   window.themeFormsTurnstileSiteKey = 'YOUR_TURNSTILE_SITE_KEY'
 */

(function () {
  // Prevent double-init if the file is accidentally enqueued twice.
  if (window._themeFormsInitDone) return;
  window._themeFormsInitDone = true;

  document.addEventListener('DOMContentLoaded', () => {
    let provider = window.themeFormsCaptchaProvider;
    if (!provider || typeof provider !== 'string') provider = 'none';
    provider = provider.toLowerCase();

    let tsSiteKey = window.themeFormsTurnstileSiteKey;
    if (tsSiteKey && typeof tsSiteKey === 'object') {
      tsSiteKey = tsSiteKey.value || tsSiteKey.key || '';
    }
    if (typeof tsSiteKey !== 'string') tsSiteKey = '';
    tsSiteKey = tsSiteKey.trim();

    const reSiteKey = (typeof window.themeFormsRecaptchaV3 === 'string' ? window.themeFormsRecaptchaV3.trim() : '');

    document.querySelectorAll('form[data-theme-form]').forEach(form => {
      form.addEventListener('submit', ev => {
        if (!form.checkValidity()) { ev.preventDefault(); form.reportValidity(); return; }
        ev.preventDefault();
        if (form.dataset.submitting === '1') return; // prevent double submit

        const lock = (on) => {
          form.dataset.submitting = on ? '1' : '0';
          form.classList.toggle('is-submitting', !!on);
          form.querySelectorAll('button, [type="submit"]').forEach(b => b.disabled = !!on);
        };

        // Abort any prior in-flight request for this form
        if (form._submitController) { try { form._submitController.abort(); } catch (e) { } }
        form._submitController = new AbortController();

        const send = () => {
          if (form.getAttribute('data-confirm-email') === '1') {
            const email = form.querySelector('[name="email"]');
            const emailConfirm = form.querySelector('[name="email_confirm"]');
            if (email instanceof HTMLInputElement && emailConfirm instanceof HTMLInputElement) {
              if (email.value.trim() !== emailConfirm.value.trim()) {
                showBanner(form, 'Email addresses do not match.', false);
                emailConfirm.focus();
                return;
              }
            }
          }

          lock(true);

          const data = new FormData(form);
          data.append('is_ajax', '1');

          fetch(form.getAttribute('action'), {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: form._submitController.signal
          })
            .then(async r => {
              const ct = (r.headers.get('content-type') || '').toLowerCase();
              const body = ct.includes('application/json') ? await r.json() : await r.text();
              const ok = typeof body === 'object' ? !!body.success : false;

              if (ok) {
                const successMessage = form.getAttribute('data-success-message') || 'Thanks! Your message has been sent.';
                showBanner(form, successMessage, true);
                form.reset();
                // Reset Turnstile invisible widget if one was rendered
                if (window.turnstile && form._tsWidgetId) {
                  try { window.turnstile.reset(form._tsWidgetId); } catch (e) { }
                }
              } else {
                let msg = 'Sorry, something went wrong. Please try again.';
                if (typeof body === 'object' && body && body.data) {
                  const d = body.data;
                  if (d.mail_error) msg += ` (Mail error: ${d.mail_error})`;
                  if (d.to) msg += ` [to: ${d.to}]`;
                }
                showBanner(form, msg, false);
              }
            })
            .catch((e) => {
              if (e && e.name === 'AbortError') return;
              showBanner(form, 'Sorry, something went wrong. Please try again.', false);
            })
            .finally(() => lock(false));
        };

        // --- CAPTCHA branches ---
        // Google reCAPTCHA v3
        if (provider === 'recaptcha_v3' && window.grecaptcha && reSiteKey) {
          grecaptcha.ready(() => {
            grecaptcha.execute(reSiteKey, { action: 'submit' }).then(token => {
              let inp = form.querySelector('input[name="g-recaptcha-response"]');
              if (!inp) {
                inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'g-recaptcha-response';
                form.appendChild(inp);
              }
              inp.value = token;
              send();
            });
          });
          return;
        }

        // Cloudflare Turnstile (visible or invisible)
        if (provider === 'turnstile' && window.turnstile && tsSiteKey) {
          const existingToken = form.querySelector('input[name="cf-turnstile-response"]');
          if (existingToken && existingToken.value) { send(); return; }

          const placeholder = form.querySelector('.cf-turnstile');

          if (!form._tsWidgetId) {
            form._tsWidgetId = window.turnstile.render(placeholder || form, {
              sitekey: tsSiteKey,
              size: placeholder ? (placeholder.getAttribute('data-size') || 'normal') : 'invisible',
              theme: placeholder ? (placeholder.getAttribute('data-theme') || 'auto') : 'auto',
              callback: (token) => {
                let inp = form.querySelector('input[name="cf-turnstile-response"]');
                if (!inp) {
                  inp = document.createElement('input');
                  inp.type = 'hidden';
                  inp.name = 'cf-turnstile-response';
                  form.appendChild(inp);
                }
                inp.value = token;
                send();
              },
              'error-callback': () => showBanner(form, 'Captcha failed, please try again.', false),
            });
          }

          try {
            // Only execute automatically if invisible
            if (!placeholder) window.turnstile.execute(form._tsWidgetId);
          } catch (e) {
            send();
          }
          return;
        }

        // No CAPTCHA
        send();
      });
    });

    function showBanner(form, text, ok) {
      form.parentElement.querySelectorAll('.theme-form-alert').forEach(el => el.remove());
      const div = document.createElement('div');
      div.className = `theme-form-alert ${ok ? 'is-success' : 'is-error'}`;
      div.textContent = text;
      form.after(div);
      setTimeout(() => div.remove(), 6000);
    }

    function formatPortalDobDisplay(iso) {
      if (!iso) return '';
      const [year, month, day] = iso.split('-');
      if (!year || !month || !day) return '';
      return `${day}/${month}/${year}`;
    }

    function parsePortalDobDisplay(value) {
      const match = value.trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
      if (!match) return '';

      const day = Number(match[1]);
      const month = Number(match[2]);
      const year = Number(match[3]);
      const date = new Date(year, month - 1, day);

      if (
        date.getFullYear() !== year
        || date.getMonth() !== month - 1
        || date.getDate() !== day
      ) {
        return '';
      }

      return `${String(year).padStart(4, '0')}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }

    function maskPortalDobInput(value) {
      const digits = value.replace(/\D/g, '').slice(0, 8);
      if (digits.length <= 2) return digits;
      if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
      return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
    }

    function validatePortalDobDisplay(displayInput, picker) {
      const value = displayInput.value.trim();
      if (!value) {
        displayInput.setCustomValidity('');
        if (picker instanceof HTMLInputElement) picker.value = '';
        return true;
      }

      const iso = parsePortalDobDisplay(value);
      if (!iso) {
        displayInput.setCustomValidity('Enter a valid date in DD/MM/YYYY format.');
        return false;
      }

      displayInput.setCustomValidity('');
      if (picker instanceof HTMLInputElement) picker.value = iso;
      return true;
    }

    function openPortalDobPicker(picker) {
      if (!(picker instanceof HTMLInputElement)) return;

      if (typeof picker.showPicker === 'function') {
        try {
          picker.showPicker();
          return;
        } catch (e) { }
      }

      picker.focus();
      picker.click();
    }

    document.querySelectorAll('[data-portal-dob-display]').forEach(displayInput => {
      if (!(displayInput instanceof HTMLInputElement)) return;

      const field = displayInput.closest('.portal-contact-form__date-field');
      if (!field) return;

      const picker = field.querySelector('[data-portal-dob-picker]');
      const trigger = field.querySelector('[data-portal-dob-picker-trigger]');
      const form = displayInput.closest('form');

      trigger?.addEventListener('click', () => openPortalDobPicker(picker));

      displayInput.addEventListener('input', () => {
        const cursor = displayInput.selectionStart;
        const before = displayInput.value;
        displayInput.value = maskPortalDobInput(before);
        if (cursor !== null) {
          const delta = displayInput.value.length - before.length;
          displayInput.setSelectionRange(cursor + delta, cursor + delta);
        }
        validatePortalDobDisplay(displayInput, picker);
      });

      displayInput.addEventListener('blur', () => {
        validatePortalDobDisplay(displayInput, picker);
      });

      if (picker instanceof HTMLInputElement) {
        picker.addEventListener('change', () => {
          displayInput.value = formatPortalDobDisplay(picker.value);
          validatePortalDobDisplay(displayInput, picker);
        });
      }

      form?.addEventListener('submit', () => {
        validatePortalDobDisplay(displayInput, picker);
      }, { capture: true });
    });

    document.querySelectorAll('[data-portal-dob-info]').forEach(button => {
      button.addEventListener('click', () => {
        const message = (button.getAttribute('data-portal-dob-toast') || '').trim();
        if (!message) return;

        const field = button.closest('.portal-contact-form__label-row');
        if (!field) return;

        const toastId = `${button.getAttribute('aria-controls') || 'portal-dob-help'}-toast`;
        field.querySelectorAll('.portal-contact-form__toast').forEach(el => el.remove());

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = 'portal-contact-form__toast is-visible';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.textContent = message;
        field.appendChild(toast);
        button.setAttribute('aria-expanded', 'true');

        window.clearTimeout(button._portalDobToastTimer);
        button._portalDobToastTimer = window.setTimeout(() => {
          toast.remove();
          button.setAttribute('aria-expanded', 'false');
        }, 6000);
      });
    });
  });

  // === NEWSLETTER (Brevo) ===
  if (!window._brevoInitDone) {
    window._brevoInitDone = true;

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('form[data-brevo-newsletter]').forEach(form => {
        form.addEventListener('submit', ev => {
          if (!form.checkValidity()) { ev.preventDefault(); form.reportValidity(); return; }
          ev.preventDefault();

          const fd = new FormData(form);
          fd.set('action', 'matrix_subscribe_brevo');
          fd.set('nonce', window.matrixBrevoNonce || '');

          // Abort previous if any
          if (form._brevoController) { try { form._brevoController.abort(); } catch (e) { } }
          form._brevoController = new AbortController();

          fetch((window.ajaxurl || form.action), {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
            signal: form._brevoController.signal
          })
            .then(async r => {
              let out;
              try { out = await r.json(); } catch (e) { out = { success: false }; }
              const ok = out && out.success;
              showBanner(
                form,
                (out && out.data && out.data.message)
                  ? out.data.message
                  : (ok ? 'Thanks, subscribed!' : 'Sorry, something went wrong.'),
                ok
              );
              if (ok) form.reset();
            })
            .catch((e) => {
              if (e && e.name === 'AbortError') return;
              showBanner(form, 'Sorry, something went wrong.', false);
            });
        });
      });

      function showBanner(form, text, ok) {
        const div = document.createElement('div');
        div.className = `theme-form-alert ${ok ? 'is-success' : 'is-error'}`;
        div.textContent = text;
        form.after(div);
        setTimeout(() => div.remove(), 6000);
      }
    });
  }
})();
