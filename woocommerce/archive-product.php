<?php
/**
 * Template: Shop / Product Archive
 * File: your-theme/woocommerce/archive-product.php
 */

defined('ABSPATH') || exit;

get_header('shop');

$shop_title = woocommerce_page_title(false);
?>

<main class="flex relative flex-col gap-2.5 items-center px-0 pt-[8rem] lg:pt-[15rem] pb-32 w-full lg:min-h-screen">
  <div class="flex relative flex-col gap-16 justify-center items-start w-full mx-auto max-w-[1140px] px-5 max-md:gap-10 max-sm:gap-8">

    <!-- Breadcrumb -->
    <nav class="flex relative gap-2.5 items-center max-sm:flex-wrap max-sm:gap-1.5" aria-label="<?php esc_attr_e('Breadcrumb', 'matrix-starter'); ?>">
      <ol class="flex gap-2.5 items-center max-sm:flex-wrap max-sm:gap-1.5">
        <?php
        if (function_exists('woocommerce_breadcrumb')) {
          woocommerce_breadcrumb([
            // chevron ONLY between items
            'delimiter'   => '<li aria-hidden="true"><svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.99023 12.2104L9.99023 8.21045L5.99023 4.21045" stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg></li>',
            'wrap_before' => '',
            'wrap_after'  => '',
            'before'      => '<li class="text-xs font-semibold leading-4 text-primary"><span class="hover:underline focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">',
            'after'       => '</span></li>',
            'home'        => esc_html__('Home', 'matrix-starter'),
          ]);
        } else {
          echo '<li class="text-xs font-semibold leading-4"><span><a class="text-primary hover:underline" href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'matrix-starter') . '</a></span></li>';
        }
        ?>
      </ol>
    </nav>

    <style>
      /* active crumb (last) */
      nav[aria-label="Breadcrumb"] ol > li:last-child > span {
        color: #5F7176; /* text-[#5F7176] */
        text-decoration: none !important;
      }
    </style>

    <!-- Title & Filters Header -->
    <header class="flex relative justify-between items-start self-stretch pb-4 border-b border-solid border-b-[#D6DFE4]">
      <div class="flex relative justify-between items-center w-full max-md:flex-col max-md:gap-5">

        <!-- Page Title -->
        <div class="flex relative items-center w-full md:w-1/2">
          <h1 class="relative text-2xl font-medium text-zinc-900 max-sm:text-xl">
            <?php echo esc_html($shop_title); ?>
          </h1>
        </div>

        <!-- Filters + sort -->
        <div class="flex relative flex-col justify-start items-start w-full mob:items-center mob:justify-between mob:flex-row max-md:gap-5 md:w-1/2">

          <!-- Results count -->
          <div class="flex relative gap-3 items-center max-md:relative max-sm:flex-wrap max-sm:gap-2.5">
            <span class="relative text-xs font-semibold leading-4 text-gray-500">
              <?php if (function_exists('woocommerce_result_count')) { woocommerce_result_count(); } ?>
            </span>
          </div>

          <!-- Sort By -->
          <div class="flex relative gap-2.5 items-center max-md:flex max-md:relative max-sm:flex max-sm:flex-wrap max-sm:gap-2.5">
            <label for="sort-select" class="relative text-xs font-semibold leading-4 text-slate-600">
              <?php esc_html_e('Sort By:', 'matrix-starter'); ?>
            </label>
            <div class="flex relative">
              <?php if (function_exists('woocommerce_catalog_ordering')) { woocommerce_catalog_ordering(); } ?>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Content area -->
    <div class="flex relative justify-between items-start self-stretch max-md:flex-col max-md:gap-8">

      <!-- Sidebar Filters -->
      <aside class="flex relative flex-col gap-10 items-start md:max-w-[240px] min-w-[240px] w-full md:w-[25%] md:border-r border-solid md:border-r-[#D6DFE4] max-md:pb-5 max-md:w-full max-md:border-b max-md:border-solid max-md:border-b-[#D6DFE4] max-sm:p-4"
             aria-label="<?php esc_attr_e('Product filters', 'matrix-starter'); ?>">

        <div class="w-full md:pr-5">
          <button class="flex justify-between items-center py-2 w-full filter-toggle btn"
                  aria-expanded="true"
                  type="button">
            <h4 class="text-base font-bold leading-4 text-zinc-900">
              <?php esc_html_e('Product type', 'matrix-starter'); ?>
            </h4>
            <svg class="transition-transform duration-200 ease-out transform filter-caret"
                 xmlns="http://www.w3.org/2000/svg" width="14" height="13" viewBox="0 0 14 13" fill="none" aria-hidden="true">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M7.25471 0.880081C7.17324 0.961351 7.1086 1.0579 7.06449 1.16419C7.02038 1.27048 6.99768 1.38443 6.99768 1.49951C6.99768 1.61459 7.02038 1.72854 7.06449 1.83483C7.1086 1.94112 7.17324 2.03767 7.25471 2.11894L11.8864 6.7489L7.25471 11.3789C7.17337 11.4602 7.10884 11.5568 7.06482 11.6631C7.0208 11.7693 6.99814 11.8833 6.99814 11.9983C6.99814 12.1133 7.0208 12.2272 7.06482 12.3335C7.10884 12.4398 7.17337 12.5364 7.25471 12.6177C7.33606 12.6991 7.43263 12.7636 7.53891 12.8076C7.64519 12.8516 7.75911 12.8743 7.87414 12.8743C7.98918 12.8743 8.10309 12.8516 8.20938 12.8076C8.31566 12.7636 8.41223 12.6991 8.49357 12.6177L13.743 7.36833C13.8244 7.28706 13.8891 7.19051 13.9332 7.08422C13.9773 6.97793 14 6.86398 14 6.7489C14 6.63382 13.9773 6.51987 13.9332 6.41358C13.8891 6.30729 13.8244 6.21074 13.743 6.12947L8.49357 0.880081C8.4123 0.798605 8.31576 0.733962 8.20946 0.689856C8.10317 0.645749 7.98922 0.623047 7.87414 0.623047C7.75906 0.623047 7.64511 0.645749 7.53882 0.689856C7.43253 0.733962 7.33599 0.798605 7.25471 0.880081Z" fill="#1B1B1B"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M13.125 6.74892C13.125 6.51688 13.0328 6.29435 12.8687 6.13028C12.7047 5.9662 12.4821 5.87402 12.2501 5.87402H0.876414C0.644377 5.87402 0.421843 5.9662 0.257767 6.13028C0.0936918 6.29435 0.00151539 6.51688 0.00151539 6.74892C0.00151539 6.98096 0.0936918 7.20349 0.257767 7.36757C0.421843 7.53165 0.644377 7.62382 0.876414 7.62382H12.2501C12.4821 7.62382 12.7047 7.53165 12.8687 7.36757C13.0328 7.20349 13.125 6.98096 13.125 6.74892Z" fill="#1B1B1B"/>
            </svg>
          </button>

          <?php if (is_active_sidebar('shop-filters')) : ?>
            <!-- No-JS fallback: open on desktop, hidden on mobile -->
            <div class="hidden mt-2 filter-panel md:block">
              <?php dynamic_sidebar('shop-filters'); ?>
            </div>
          <?php else : ?>
            <ul class="hidden mt-2 space-y-3 filter-panel md:block">
              <li><a href="#" class="block text-base leading-5 text-zinc-900 hover:text-primary">Lifting equipment</a></li>
              <li><a href="#" class="block text-base leading-5 text-zinc-900 hover:text-primary">Wheel service equipment</a></li>
              <li><a href="#" class="block text-base leading-5 text-zinc-900 hover:text-primary">Product category 4</a></li>
              <li><a href="#" class="block text-base leading-5 text-zinc-900 hover:text-primary">Product category 5</a></li>
              <li><a href="#" class="block text-base leading-5 text-zinc-900 hover:text-primary">Product category 6</a></li>
            </ul>
          <?php endif; ?>
        </div>
      </aside>

      <script>
        document.addEventListener('DOMContentLoaded', function () {
          document.querySelectorAll('aside .filter-toggle').forEach(function(btn){
            var aside = btn.closest('aside');
            if (!aside) return;

            var panel = aside.querySelector('.filter-panel');
            var caret = aside.querySelector('.filter-caret');
            if (!panel) return;

            var mq = window.matchMedia('(min-width: 768px)');
            var userToggled = false;

            function applyState(open) {
              btn.setAttribute('aria-expanded', open ? 'true' : 'false');
              // Force block when open to override Tailwind 'hidden'
              panel.style.display = open ? 'block' : 'none';
              if (caret) caret.style.transform = open ? 'rotate(90deg)' : 'rotate(0deg)';
            }

            // Initial state: open ≥768px, closed <768px
            applyState(mq.matches);

            // Click to toggle
            btn.addEventListener('click', function () {
              userToggled = true;
              var open = btn.getAttribute('aria-expanded') === 'true';
              applyState(!open);
            });

            // Update on breakpoint change if user hasn't interacted
            function mqHandler(e){ if (!userToggled) applyState(e.matches); }
            if (mq.addEventListener) mq.addEventListener('change', mqHandler);
            else if (mq.addListener) mq.addListener(mqHandler);

            // Fallback on resize
            window.addEventListener('resize', function(){
              if (!userToggled) applyState(window.innerWidth >= 768);
            });
          });
        });
      </script>

      <!-- Products + Pagination -->
      <section class="flex relative flex-col gap-10 items-center w-[75%] max-md:w-full" aria-label="<?php esc_attr_e('Product listing', 'matrix-starter'); ?>">

        <div class="flex relative flex-col gap-8 items-start max-md:w-full">
          <?php if (woocommerce_product_loop()) : ?>

            <!-- Products (UL is now the grid via Tailwind CSS) -->
            <div class="w-full">
              <?php
                woocommerce_product_loop_start();
                while (have_posts()) :
                  the_post();
                  wc_get_template_part('content', 'product');
                endwhile;
                woocommerce_product_loop_end();
              ?>
            </div>

          <?php else : ?>
            <?php do_action('woocommerce_no_products_found'); ?>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <nav class="flex relative gap-4 items-center max-sm:flex-wrap max-sm:justify-center" aria-label="<?php esc_attr_e('Pagination', 'matrix-starter'); ?>">
          <?php woocommerce_pagination(); ?>
        </nav>

      </section>
    </div>
  </div>
</main>

<style>
  .btn { outline: none; }
  .btn:focus, .btn:focus-visible { outline: none; }
  .line-clamp-4{ display:-webkit-box; -webkit-line-clamp:4; -webkit-box-orient:vertical; overflow:hidden; }
</style>

<?php get_footer('shop'); ?>
