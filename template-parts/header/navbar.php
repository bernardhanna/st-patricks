<?php
/**
 * Navbar
 * - Removes all Vue attributes
 * - Uses Navi for menus
 * - Keeps Alpine store and template parts
 * - Buttons + search controlled via ACF options
 */

$logo_id  = get_theme_mod('custom_logo');
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
$logo_alt = $logo_id ? get_post_meta($logo_id, '_wp_attachment_image_alt', true) : get_bloginfo('name');

$nav_settings      = get_field('navigation_settings_start', 'option') ?: [];
$enable_search     = ! empty($nav_settings['enable_search']);
$help_btn          = $nav_settings['looking_help_button'] ?? null;   // ACF link array
$referral_btn      = $nav_settings['referral_button'] ?? null;       // ACF link array

// Map dropdown images to menu item IDs
$dropdown_image_map = [];
if (! empty($nav_settings['dropdown_images'])) {
  foreach ($nav_settings['dropdown_images'] as $row) {
    $mid = $row['menu_item'] ?? null;
    $img = $row['image']     ?? null;
    if ($mid && ! empty($img['url'])) {
      $dropdown_image_map[(int) $mid] = $img;
    }
  }
}

use Log1x\Navi\Navi;
$primary_navigation = Navi::make()->build('primary');
?>

<!-- Alpine store (once) -->
<script>
document.addEventListener('alpine:init', () => {
  if (!Alpine.store('nav')) Alpine.store('nav', { open: false });
  Alpine.data('navbarSearch', () => ({
    searchOpen: false,
    query: '',
    results: [],
    loading: false,
    error: '',
    abortController: null,
    searchTimeout: null,
    faqLinks: [
      { title: "Healthcare FAQ's", url: '#healthcare-faq' },
      { title: 'Our locations and parking', url: '#locations-parking' },
      { title: "Service user FAQ's", url: '#service-user-faq' },
      { title: 'Make a payment', url: '#make-payment' },
    ],

    openSearch() {
      this.searchOpen = true;
      document.body.style.overflow = 'hidden';
      this.$nextTick(() => {
        if (this.$refs.searchInput) this.$refs.searchInput.focus();
      });
    },

    closeSearch() {
      this.searchOpen = false;
      this.clearSearch(false);
      if (!Alpine.store('nav').open) document.body.style.overflow = '';
    },

    clearSearch(keepFocus = true) {
      this.query = '';
      this.results = [];
      this.error = '';
      this.loading = false;
      if (this.abortController) {
        this.abortController.abort();
        this.abortController = null;
      }
      if (keepFocus) {
        this.$nextTick(() => {
          if (this.$refs.searchInput) this.$refs.searchInput.focus();
        });
      }
    },

    handleQueryChange() {
      if (this.searchTimeout) clearTimeout(this.searchTimeout);
      const term = this.query.trim();
      if (term.length < 2) {
        this.results = [];
        this.error = '';
        this.loading = false;
        if (this.abortController) {
          this.abortController.abort();
          this.abortController = null;
        }
        return;
      }
      this.searchTimeout = setTimeout(() => this.fetchResults(term), 220);
    },

    async fetchResults(term) {
      if (this.abortController) this.abortController.abort();
      this.abortController = new AbortController();
      this.loading = true;
      this.error = '';
      try {
        const url = `${window.location.origin}/wp-json/wp/v2/search?search=${encodeURIComponent(term)}&per_page=8&type=post&subtype=post,page`;
        const res = await fetch(url, {
          signal: this.abortController.signal,
          headers: { 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error(`Search failed (${res.status})`);
        const data = await res.json();
        this.results = Array.isArray(data) ? data : [];
      } catch (err) {
        if (err.name !== 'AbortError') {
          this.error = 'Could not load search results.';
          this.results = [];
        }
      } finally {
        this.loading = false;
      }
    },

    submitSearch() {
      const term = this.query.trim();
      if (!term) return;
      window.location.href = `${window.location.origin}/?s=${encodeURIComponent(term)}`;
    },
  }));
});
</script>

<section
  id="site-nav"
  x-data="navbarSearch()"
  x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 1200) { $store.nav.open = false } })"
  x-effect="$store.nav.open ? document.body.style.overflow='hidden' : document.body.style.overflow=''"
  class="bg-white"
  role="banner"
>
  <?php get_template_part('template-parts/header/topbar'); ?>

  <!-- WHITE BAR -->
  <nav
  class="box-border flex relative justify-between items-center p-6 mx-auto w-full bg-white shadow-x font-primary max-md:p-5 max-sm:p-4 max-w-container"
  role="navigation"
  aria-label="Main navigation"
>
  <!-- Logo -->
  <div class="flex items-center">
    <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> - Go to homepage">
      <?php if ($logo_url) : ?>
        <img
          src="<?php echo esc_url($logo_url); ?>"
          alt="<?php echo esc_attr($logo_alt); ?>"
          class="h-[46px] w-[180px] object-contain max-md:w-40 max-md:h-[41px] max-sm:h-9 max-sm:w-[140px]"
        />
      <?php else : ?>
        <span class="text-xl font-bold text-slate-800"><?php echo esc_html(get_bloginfo('name')); ?></span>
      <?php endif; ?>
    </a>
  </div>

  <!-- Desktop Navigation -->
  <?php if ($primary_navigation->isNotEmpty()) : ?>
    <ul id="primary-menu" class="hidden gap-4 items-center lg:flex" role="menubar">
      <?php foreach ($primary_navigation->toArray() as $index => $item) : ?>
        <li class="relative group <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>" role="none">
          <div class="flex gap-1 items-center">
            <a
              href="<?php echo esc_url($item->url); ?>"
              class="flex gap-1 items-center text-sm font-semibold leading-5 rounded text-teal-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary"
              role="menuitem"
              aria-haspopup="<?php echo $item->children ? 'true' : 'false'; ?>"
              aria-expanded="false"
            >
              <span><?php echo esc_html($item->label); ?></span>

              <?php if ($item->children) : ?>
                <!-- Chevron icon (style-only change) -->
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                  <path d="M2 4L6 8L10 4" stroke="black" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              <?php endif; ?>
            </a>
          </div>

          <?php if ($item->children) : ?>
            <?php get_template_part('template-parts/header/navbar/dropdown', null, [
              'item'   => $item,
              'index'  => $index,
              'images' => $dropdown_image_map
            ]); ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <!-- Right Side: Search + Buttons + Mobile trigger -->
  <div class="flex gap-4 items-center">
    <!-- Search -->
    <?php if ($enable_search) : ?>
      <button
        type="button"
        class="flex items-center justify-center w-[31px] h-[31px] p-1.5 rounded-[15.5px] hover:bg-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary"
        @click.prevent="openSearch()"
        :aria-expanded="searchOpen ? 'true' : 'false'"
        aria-controls="navbar-search-modal"
        aria-label="Open search"
      >
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="#001F33" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M20.9999 21L16.6499 16.65" stroke="#001F33" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    <?php endif; ?>

    <!-- Looking for help -->
    <?php if (!empty($help_btn['url']) && !empty($help_btn['title'])) : ?>
      <a
        href="<?php echo esc_url($help_btn['url']); ?>"
        target="<?php echo esc_attr($help_btn['target'] ?: '_self'); ?>"
        class="hidden btn gap-2 items-center px-3 h-9 bg-sky-900 text-white rounded-md transition-colors sm:flex focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary"
        aria-label="<?php echo esc_attr($help_btn['title']); ?>"
      >
        <span class="text-sm font-medium leading-6 text-current">
          <?php echo esc_html($help_btn['title']); ?>
        </span>
      </a>
    <?php endif; ?>

    <!-- Make a referral -->
    <?php if (!empty($referral_btn['url']) && !empty($referral_btn['title'])) : ?>
      <a
        href="<?php echo esc_url($referral_btn['url']); ?>"
        target="<?php echo esc_attr($referral_btn['target'] ?: '_self'); ?>"
        class="flex btn items-center px-3 h-9 rounded-md border border-sky-900 text-teal-950 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary"
        role="button"
        aria-label="<?php echo esc_attr($referral_btn['title']); ?>"
      >
        <span class="text-sm font-medium leading-6 text-current">
          <?php echo esc_html($referral_btn['title']); ?>
        </span>
      </a>
    <?php endif; ?>

    <!-- Mobile / off-canvas (unchanged placement) -->
    <?php get_template_part('template-parts/header/navbar/mobile'); ?>
  </div>
</nav>

<?php if ($enable_search) : ?>
  <div
    id="navbar-search-modal"
    x-show="searchOpen"
    x-cloak
    x-transition.opacity
    @click.self="closeSearch()"
    @keydown.escape.window="closeSearch()"
    class="fixed inset-0 z-[120] flex justify-center items-start p-4 pt-20 bg-black/40"
    role="dialog"
    aria-modal="true"
    aria-labelledby="navbar-search-title"
  >
    <div class="overflow-hidden w-full bg-white rounded-md shadow-xl max-w-[536px]">
      <h2 id="navbar-search-title" class="sr-only">Search and FAQ</h2>
      <div class="flex justify-end px-3 pt-3 bg-white">
        <button
          type="button"
          class="flex justify-center items-center w-8 h-8 rounded hover:bg-gray-100 focus-visible:bg-gray-100 focus:outline-none"
          aria-label="Close search"
          @click="closeSearch()"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M18 6L6 18M6 6L18 18" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <form role="search" class="relative border-b border-sky-100" @submit.prevent="submitSearch()">
        <label for="navbar-search-input" class="sr-only">Search by keyword, symptom, or page</label>
        <input
          id="navbar-search-input"
          x-ref="searchInput"
          x-model="query"
          @input="handleQueryChange()"
          type="text"
          placeholder="Search by keyword, symptom, or page"
          class="pr-28 pl-5 w-full h-14 text-base text-gray-500 bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        />

        <button
          type="submit"
          class="flex absolute right-3 top-1/2 gap-2 items-center px-6 py-2 text-sm font-medium text-white rounded-md -translate-y-1/2 bg-sky-950 hover:bg-sky-900 focus-visible:bg-sky-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary whitespace-nowrap"
          aria-label="Search"
        >
          <span class="sr-only">Search</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 21L16.65 16.65" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Search</span>
        </button>

        <button
          x-show="query.length > 0"
          type="button"
          class="flex absolute top-1/2 justify-center items-center w-6 h-6 -translate-y-1/2 right-[116px] rounded hover:bg-gray-100 focus-visible:bg-gray-100 focus:outline-none"
          aria-label="Clear search"
          @click="clearSearch(true)"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M18 6L6 18M6 6L18 18" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </form>

      <section class="bg-slate-50" aria-label="Search results and FAQ links">
        <template x-if="query.trim().length >= 2">
          <div>
            <div x-show="loading" class="px-5 py-4 text-sm text-slate-600">Searching...</div>
            <div x-show="!loading && error" class="px-5 py-4 text-sm text-red-600" x-text="error"></div>
            <ul x-show="!loading && !error && results.length" class="list-none m-0 p-0">
              <template x-for="item in results" :key="item.id + '-' + item.subtype">
                <li>
                  <a
                    :href="item.url"
                    class="flex justify-between items-center px-5 py-4 w-full border-b border-slate-200 no-underline transition-colors hover:bg-slate-100 focus-visible:bg-slate-100 focus-visible:outline-none"
                  >
                    <span class="text-base font-medium text-slate-900" x-text="item.title"></span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6L15 12L9 18" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </a>
                </li>
              </template>
            </ul>
            <div x-show="!loading && !error && !results.length" class="px-5 py-4 text-sm text-slate-600">
              No results found. Try a different keyword.
            </div>
          </div>
        </template>

        <template x-if="query.trim().length < 2">
          <nav aria-label="FAQ categories">
            <ul class="list-none m-0 p-0">
              <template x-for="faq in faqLinks" :key="faq.title">
                <li>
                  <a
                    :href="faq.url"
                    class="flex justify-between items-center px-5 py-4 w-full border-b border-slate-200 no-underline transition-colors hover:bg-slate-100 focus-visible:bg-slate-100 focus-visible:outline-none"
                  >
                    <span class="text-base font-medium text-slate-900" x-text="faq.title"></span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6L15 12L9 18" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </a>
                </li>
              </template>
            </ul>
          </nav>
        </template>
      </section>
    </div>
  </div>
<?php endif; ?>
</section>
