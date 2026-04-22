<?php
// Enable?
if (! get_field('newsletter_enable', 'option')) {
  return;
}

// Unique section id
$section_id = 'newsletter-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Colors
$bg_color = get_field('newsletter_bg_color', 'option') ?: '#024B79'; // spmhs-blue-dark fallback
$accent_line_color = get_field('newsletter_accent_line_color', 'option') ?: '#7ED0E0';

// Padding classes (RENAMED repeater to avoid collisions)
$padding_classes = [];
if (have_rows('newsletter_padding_settings', 'option')) {
  while (have_rows('newsletter_padding_settings', 'option')) {
    the_row();
    $screen = get_sub_field('screen_size');
    $pt     = get_sub_field('padding_top');
    $pb     = get_sub_field('padding_bottom');
    if ($screen !== '' && $pt !== '' && $pt !== null) $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
    if ($screen !== '' && $pb !== '' && $pb !== null) $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
  }
}
$padding_str = implode(' ', $padding_classes);

// Background images (options)
$bg_right = get_field('bg_vector_image', 'option'); // right/top
$bg_right_op = get_field('bg_vector_opacity', 'option');
$bg_right_op = ($bg_right_op === '' || $bg_right_op === null) ? 0.03 : (float) $bg_right_op;

$bg_left = get_field('bg_logo_image', 'option'); // left/bottom
$bg_left_op = get_field('bg_logo_opacity', 'option');
$bg_left_op = ($bg_left_op === '' || $bg_left_op === null) ? 0.03 : (float) $bg_left_op;

// Content
$heading = get_field('newsletter_heading', 'option') ?: 'Latest News, Events, and Expert advice from SPMHS';
$subtext = get_field('newsletter_subtext', 'option'); // WYSIWYG

// Form
$action      = trim((string) get_field('newsletter_action', 'option')); // if empty → Brevo AJAX
$name_label  = get_field('name_label', 'option') ?: 'Full name';
$name_ph     = get_field('name_placeholder', 'option') ?: 'Enter your full name';
$email_label = get_field('email_label', 'option') ?: 'Email';
$email_ph    = get_field('email_placeholder', 'option') ?: 'Joeblogs@mail.com';
$submit_text = get_field('submit_text', 'option') ?: 'Subscribe';
$require_tc  = (bool) get_field('require_terms', 'option');

$terms_prefix = get_field('terms_text_prefix', 'option') ?: 'By signing to our newsletter, you agree to our';
$terms_link   = get_field('terms_link', 'option');
$privacy_link = get_field('privacy_link', 'option');

// Helpers for links
$terms_href   = !empty($terms_link['url'])   ? esc_url($terms_link['url'])     : '#';
$terms_title  = !empty($terms_link['title']) ? esc_html($terms_link['title'])  : 'Terms & Conditions';
$terms_target = !empty($terms_link['target'])? esc_attr($terms_link['target']) : '_self';

$priv_href    = !empty($privacy_link['url'])   ? esc_url($privacy_link['url'])     : '#';
$priv_title   = !empty($privacy_link['title']) ? esc_html($privacy_link['title'])  : 'Privacy Policy';
$priv_target  = !empty($privacy_link['target'])? esc_attr($privacy_link['target']) : '_self';

// Endpoints / nonce for Brevo AJAX
$admin_ajax  = esc_url(admin_url('admin-ajax.php'));
$nonce_brevo = wp_create_nonce('matrix_brevo_subscribe');
?>
<section id="<?php echo esc_attr($section_id); ?>" class="relative overflow-hidden <?php echo esc_attr($padding_str); ?>" style="background-color: <?php echo esc_attr($bg_color); ?>;">
      <!-- Decorative Background Images -->
    <div class="overflow-hidden absolute inset-0 pointer-events-none">
      <svg xmlns="http://www.w3.org/2000/svg" width="231" height="696" viewBox="0 0 231 696" fill="none" class="absolute -top-[10rem] right-0">
        <path opacity="0.03" d="M333.808 192.188C337.24 188.508 342.903 188.508 346.334 192.188C346.334 192.188 361.538 208.426 361.538 225.336C361.538 242.246 346.334 258.485 346.334 258.485C342.875 262.165 337.24 262.165 333.808 258.485C333.808 258.485 318.604 242.217 318.604 225.336C318.604 208.455 333.808 192.188 333.808 192.188ZM420.736 27.8476C420.736 41.3114 431.588 52.906 431.588 52.906C435.02 56.586 440.683 56.586 444.114 52.906C444.114 52.906 454.967 41.3114 454.967 27.8476C454.967 14.3838 444.114 2.75993 444.114 2.75993C440.683 -0.919978 435.02 -0.919978 431.588 2.75993C431.588 2.75993 420.736 14.3546 420.736 27.8476ZM234.354 44.0567C239.264 51.1245 246.071 52.3511 246.071 52.3511C250.953 53.1981 255.5 49.7518 256.225 44.6409C256.225 44.6409 257.23 37.4855 252.32 30.4177C247.41 23.3499 240.603 22.1525 240.603 22.1525C235.749 21.3055 231.174 24.7518 230.448 29.8628C230.448 29.8628 229.444 37.0182 234.354 44.0567ZM74.2506 169.729C85.0748 173.409 96.4012 167.509 96.4012 167.509C100.809 165.202 102.567 159.594 100.279 155.038C100.279 155.038 94.4204 143.298 83.5963 139.618C72.7721 135.938 61.4457 141.837 61.4457 141.837C57.0379 144.145 55.2804 149.752 57.568 154.337C57.568 154.337 63.4264 166.049 74.2506 169.729ZM24.2306 365.407C34.0505 362.077 38.9883 352.206 38.9883 352.206C41.248 347.62 39.5183 342.013 35.1105 339.735C35.1105 339.735 25.5696 334.741 15.7497 338.099C5.92985 341.429 0.992066 351.329 0.992066 351.329C-1.26762 355.915 0.461971 361.522 4.86976 363.8C4.86976 363.8 14.4107 368.765 24.2306 365.407ZM92.1329 559.449C98.8003 549.811 96.9591 536.698 96.9591 536.698C96.2338 531.616 91.6865 528.111 86.8044 528.987C86.8044 528.987 74.3343 531.207 67.6389 540.816C60.9715 550.454 62.8126 563.538 62.8126 563.538C63.538 568.62 68.0853 572.095 72.9673 571.248C72.9673 571.248 85.4375 569.058 92.1329 559.42V559.449ZM255.974 671.862C255.974 659.975 247.103 650.512 247.103 650.512C243.644 646.832 238.008 646.832 234.549 650.512C234.549 650.512 225.706 659.975 225.706 671.862C225.706 683.748 234.549 693.24 234.549 693.24C238.008 696.92 243.644 696.92 247.103 693.24C247.103 693.24 255.974 683.777 255.974 671.862ZM451.647 662.048C444.979 652.44 432.481 650.22 432.481 650.22C427.627 649.373 423.052 652.819 422.327 657.93C422.327 657.93 420.485 671.015 427.153 680.682C433.82 690.29 446.318 692.51 446.318 692.51C451.172 693.386 455.748 689.881 456.445 684.8C456.445 684.8 458.286 671.686 451.619 662.078L451.647 662.048ZM598.582 534.916C589.85 531.966 582.29 535.909 582.29 535.909C577.883 538.216 576.125 543.824 578.413 548.38C578.413 548.38 582.346 556.236 591.05 559.215C599.754 562.165 607.342 558.222 607.342 558.222C611.75 555.915 613.508 550.308 611.22 545.752C611.22 545.752 607.314 537.895 598.582 534.916Z" fill="white"/>
      </svg>

      <svg xmlns="http://www.w3.org/2000/svg" width="240" height="482" viewBox="0 0 240 482" fill="none" class="absolute -bottom-[7rem]">
        <path opacity="0.03" d="M-1.82689 133.096C0.549454 130.547 4.47137 130.547 6.84772 133.096C6.84772 133.096 17.3771 144.341 17.3771 156.052C17.3771 167.763 6.84772 179.008 6.84772 179.008C4.45206 181.557 0.549454 181.557 -1.82689 179.008C-1.82689 179.008 -12.3562 167.742 -12.3562 156.052C-12.3562 144.361 -1.82689 133.096 -1.82689 133.096Z" fill="white"/>
      </svg>
    </div>

    <div class="flex overflow-hidden relative justify-center items-center px-5 py-12 mx-auto lg:py-24 max-w-container_md">
      <!-- Newsletter Container -->
      <div class="relative z-10 py-8 w-full max-w-[578px] ">
        <div class="flex flex-col gap-8 items-center md:gap-12">

          <!-- Header Section -->
          <div class="flex flex-col gap-6 items-start w-full">
            <div class="flex flex-col gap-6 justify-center items-center w-full md:gap-8">
              <!-- Title -->
              <h1 class="text-white text-center font-bold text-3xl sm:text-4xl lg:text-5xl leading-tight tracking-[-0.576px]">
                <?php echo esc_html($heading); ?>
              </h1>

              <!-- Decorative Line -->
              <div class="w-10 h-px" style="background-color: <?php echo esc_attr($accent_line_color); ?>;"></div>
            </div>

            <!-- Subtitle -->
            <?php if (!empty($subtext)): ?>
              <div class="w-full text-base font-medium leading-7 text-center text-white wp_editor">
                <?php echo wp_kses_post($subtext); ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Newsletter Form -->
          <div class="flex flex-col gap-3 justify-end items-start w-full">
            <div class="flex flex-col gap-2 items-end w-full md:flex-row">
              <!-- Full Name -->
              <div class="flex flex-col flex-1 gap-1.5 items-start w-full">
                <label for="<?php echo esc_attr($section_id); ?>-name" class="text-sm font-medium leading-5 text-white">
                  <?php echo esc_html($name_label); ?>
                </label>
                <div class="w-full">
                  <input
                    id="<?php echo esc_attr($section_id); ?>-name"
                    type="text"
                    name="name"
                    placeholder="<?php echo esc_attr($name_ph); ?>"
                    class="flex items-center px-3 py-2 w-full text-base font-normal leading-6 text-white bg-transparent rounded border border-slate-300 placeholder:text-white/60 focus:outline-none focus:ring-2 focus:ring-spmhs-blue-light focus:border-transparent"
                    required
                    <?php if ($action !== ''): ?>
                      form="<?php echo esc_attr($section_id); ?>-form"
                    <?php endif; ?>
                  />
                </div>
              </div>

              <!-- Email + Subscribe -->
              <div class="flex flex-col gap-1.5 items-start w-full md:w-auto">
                <label for="<?php echo esc_attr($section_id); ?>-email" class="text-sm font-medium leading-5 text-white">
                  <?php echo esc_html($email_label); ?>
                </label>
                <div class="flex gap-2 items-center w-full max-sm:flex-col md:w-96">
                  <input
                    id="<?php echo esc_attr($section_id); ?>-email"
                    type="email"
                    name="email"
                    placeholder="<?php echo esc_attr($email_ph); ?>"
                    class="flex flex-1 items-center px-3 py-2 text-base font-normal leading-6 text-white bg-transparent rounded border border-slate-300 placeholder:text-white/60 focus:outline-none focus:ring-2 focus:ring-spmhs-blue-light focus:border-transparent max-sm:w-full"
                    required
                    <?php if ($action !== ''): ?>
                      form="<?php echo esc_attr($section_id); ?>-form"
                    <?php endif; ?>
                  />

                  <?php if ($action === ''): ?>
                    <!-- Brevo AJAX submit -->
                    <button
                      type="button"
                      class="flex gap-2.5 justify-center items-center px-4 py-2 text-sm font-medium leading-6 whitespace-nowrap rounded transition-colors bg-primary-sky text-secondary-darker hover:bg-secondary max-sm:w-full"
                      id="<?php echo esc_attr($section_id); ?>-submit"
                      aria-label="<?php echo esc_attr($submit_text); ?>"
                    >
                      <?php echo esc_html($submit_text); ?>
                    </button>
                  <?php else: ?>
                    <!-- Direct POST submit -->
                    <button
                      form="<?php echo esc_attr($section_id); ?>-form"
                      type="submit"
                      class="flex gap-2.5 justify-center items-center px-4 py-2 text-sm font-medium leading-6 whitespace-nowrap rounded transition-colors bg-primary-sky text-secondary-darker hover:bg-secondary max-sm:w-full"
                      aria-label="<?php echo esc_attr($submit_text); ?>"
                      id="<?php echo esc_attr($section_id); ?>-submit"
                    >
                      <?php echo esc_html($submit_text); ?>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Terms -->
            <?php if ($require_tc): ?>
              <div class="flex items-center gap-3 w-full min-h-[38px]">
                <div class="flex flex-1 gap-2 items-start">
                  <input
                    id="<?php echo esc_attr($section_id); ?>-terms"
                    type="checkbox"
                    class="mt-0.5 w-4 h-4 bg-transparent rounded-sm border cursor-pointer border-slate-300 checked:bg-spmhs-blue-light checked:border-spmhs-blue-light focus:ring-2 focus:ring-spmhs-blue-light focus:ring-offset-0"
                    name="consent"
                    value="1"
                    required
                    <?php if ($action !== ''): ?>
                      form="<?php echo esc_attr($section_id); ?>-form"
                    <?php endif; ?>
                  />
                  <label for="<?php echo esc_attr($section_id); ?>-terms" class="flex-1 text-xs font-medium leading-4 text-white cursor-pointer">
                    <?php echo esc_html($terms_prefix); ?>
                    <a href="<?php echo $terms_href; ?>" class="text-spmhs-blue-light hover:underline" target="<?php echo $terms_target; ?>"><?php echo $terms_title; ?></a>
                    &
                    <a href="<?php echo $priv_href; ?>" class="text-spmhs-blue-light hover:underline" target="<?php echo $priv_target; ?>"><?php echo $priv_title; ?></a>.
                  </label>
                </div>
              </div>
            <?php endif; ?>

            <!-- Live status for screen readers / inline messages -->
            <div id="<?php echo esc_attr($section_id); ?>-status" class="mt-2 text-sm text-white/90" aria-live="polite"></div>
          </div>

          <!-- Hidden forms for submit handlers -->
          <?php if ($action === ''): ?>
            <!-- Brevo AJAX form (used by helper script) -->
            <form
              id="<?php echo esc_attr($section_id); ?>-brevo"
              data-brevo-newsletter
              action="<?php echo $admin_ajax; ?>"
              method="post"
              class="hidden"
              novalidate
            >
              <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce_brevo); ?>" />
              <?php
                $default_lists = (string) get_field('brevo_list_ids', 'option');
                if ($default_lists !== ''):
              ?>
                <input type="hidden" name="list_ids" value="<?php echo esc_attr($default_lists); ?>" />
              <?php endif; ?>
              <input type="hidden" name="name" value="" />
              <input type="hidden" name="email" value="" />
              <?php if ($require_tc): ?>
                <input type="hidden" name="consent" value="" />
              <?php endif; ?>
            </form>
          <?php else: ?>
            <!-- Direct POST form (no novalidate so native validation runs) -->
            <form
              id="<?php echo esc_attr($section_id); ?>-form"
              action="<?php echo esc_url($action); ?>"
              method="post"
              class="hidden"
            >
              <input type="hidden" name="name" value="" />
              <input type="hidden" name="email" value="" />
              <?php if ($require_tc): ?>
                <input type="hidden" name="consent" value="" />
              <?php endif; ?>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
</section>

<script>
(function(){
  var root   = document.getElementById('<?php echo esc_js($section_id); ?>');
  if (!root) return;

  var nameEl   = root.querySelector('#<?php echo esc_js($section_id); ?>-name');
  var emailEl  = root.querySelector('#<?php echo esc_js($section_id); ?>-email');
  var termsEl  = root.querySelector('#<?php echo esc_js($section_id); ?>-terms');
  var submitEl = root.querySelector('#<?php echo esc_js($section_id); ?>-submit');
  var statusEl = root.querySelector('#<?php echo esc_js($section_id); ?>-status');

  var brevoForm = root.querySelector('form#<?php echo esc_js($section_id); ?>-brevo');
  var postForm  = root.querySelector('form#<?php echo esc_js($section_id); ?>-form');

  function validEmail(v){ return /^[^@]+@[^@]+\.[^@]+$/.test(v); }
  function setStatus(msg){ if (statusEl) statusEl.textContent = msg || ''; }

  function submitToBrevo(){
    if (!brevoForm) return;
    var name = (nameEl && nameEl.value || '').trim();
    var email = (emailEl && emailEl.value || '').trim();
    var ok = !!name && validEmail(email);
    if (termsEl) ok = ok && !!termsEl.checked;
    if (!ok) { setStatus('Please fill in all fields and agree to the terms.'); alert('Please fill in all fields and agree to the terms.'); return; }

    brevoForm.querySelector('input[name="name"]').value  = name;
    brevoForm.querySelector('input[name="email"]').value = email;
    var cons = brevoForm.querySelector('input[name="consent"]');
    if (cons) cons.value = termsEl && termsEl.checked ? '1' : '';

    // Use your theme helper if present; else fallback to fetch
    if (window._brevoInitDone) {
      brevoForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
      return;
    }

    // Fallback AJAX (simple)
    var fd = new FormData(brevoForm);
    fd.set('action','matrix_subscribe_brevo');
    fd.set('nonce', window.matrixBrevoNonce || '<?php echo esc_js( wp_create_nonce("matrix_brevo_subscribe") ); ?>');
    fetch(brevoForm.action, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body: fd })
      .then(r => r.json()).then(out => {
        if (out && out.success) {
          setStatus(out.data && out.data.message ? out.data.message : 'Thanks — you’re subscribed!');
          brevoForm.reset();
          if (nameEl) nameEl.value = '';
          if (emailEl) emailEl.value = '';
          if (termsEl) termsEl.checked = false;
        } else {
          setStatus(out && out.data && out.data.message ? out.data.message : 'Sorry, something went wrong.');
          alert(out && out.data && out.data.message ? out.data.message : 'Sorry, something went wrong.');
        }
      })
      .catch(function(){
        setStatus('Sorry, something went wrong.');
        alert('Sorry, something went wrong.');
      });
  }

  function submitDirectPost(){
    if (!postForm) return;
    var name = (nameEl && nameEl.value || '').trim();
    var email = (emailEl && emailEl.value || '').trim();
    var ok = !!name && validEmail(email);
    if (termsEl) ok = ok && !!termsEl.checked;
    if (!ok) { setStatus('Please fill in all fields and agree to the terms.'); alert('Please fill in all fields and agree to the terms.'); return; }

    postForm.querySelector('input[name="name"]').value  = name;
    postForm.querySelector('input[name="email"]').value = email;
    var cons = postForm.querySelector('input[name="consent"]');
    if (cons) cons.value = termsEl && termsEl.checked ? '1' : '';

    // Native validation will also run because inputs are linked via form=""
    postForm.submit();
  }

  function handleEnter(e){
    if (e.key === 'Enter') {
      e.preventDefault();
      <?php if ($action === ''): ?>
        submitToBrevo();
      <?php else: ?>
        submitDirectPost();
      <?php endif; ?>
    }
  }

  if (nameEl)  nameEl.addEventListener('keydown', handleEnter);
  if (emailEl) emailEl.addEventListener('keydown', handleEnter);

  if (submitEl) {
    submitEl.addEventListener('click', function(e){
      <?php if ($action === ''): ?>
        submitToBrevo();
      <?php else: ?>
        // Let native validation run first on click-triggered submit
        // If browser blocks submit due to invalid fields, our JS won't run submit().
        // We use JS submit only to populate hidden fields.
        e.preventDefault();
        submitDirectPost();
      <?php endif; ?>
    });
  }
})();
</script>
