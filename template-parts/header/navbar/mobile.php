<?php
use Log1x\Navi\Navi;
use Illuminate\Support\Collection;

$primary_navigation = $primary_navigation ?? Navi::make()->build('primary');

$enable_hamburger = get_field('enable_hamburger', 'option');
$hamburger_style  = get_field('hamburger_style', 'option');

$effect    = get_field('mobile_menu_effect', 'option') ?: 'slide_left';
$widthPct  = get_field('mobile_menu_width',  'option') ?: 80;
$bgColour  = get_field('mobile_menu_background', 'option') ?: '#FFF';

$startTransform = match ($effect) {
  'slide_right' => 'translate-x-full',
  'slide_up'    => 'translate-y-full',
  'fullscreen'  => 'translate-y-full',
  default       => '-translate-x-full',
};
$panelStyle = $effect === 'fullscreen' ? 'width:100%;' : "width: {$widthPct}%; left:0;";

$valid_styles = ['hamburger--spin','hamburger--squeeze','hamburger--elastic','hamburger--collapse','hamburger--vortex','hamburger--arrow','hamburger--emphatic','hamburger--slider'];
if (! in_array($hamburger_style, $valid_styles, true)) $hamburger_style = 'hamburger--spin';

function val($src, array $keys, $default = null) {
  foreach ($keys as $k) {
    if (is_array($src) && array_key_exists($k, $src)) return $src[$k];
    if (is_object($src) && isset($src->{$k}))          return $src->{$k};
  }
  return $default;
}
function to_seq_array($maybe) {
  if ($maybe instanceof Collection) return $maybe->values()->all();
  if (is_array($maybe))             return array_values($maybe);
  if (is_object($maybe) && $maybe instanceof Traversable) return array_values(iterator_to_array($maybe));
  return [];
}
function normalize_items($items): array {
  $items = to_seq_array($items);
  usort($items, function($a, $b) {
    $ao = (int) val($a, ['menu_order','order','position'], 0);
    $bo = (int) val($b, ['menu_order','order','position'], 0);
    return $ao <=> $bo;
  });
  $out = [];
  foreach ($items as $it) {
    $label  = val($it, ['label','title','name','post_title'], '');
    $url    = val($it, ['url','link','permalink','guid'], '');
    $active = (bool) val($it, ['active','current','is_current'], false);
    $kids   = val($it, ['children','items','submenu','child_items'], []);
    $out[] = ['label'=>(string)$label,'url'=>(string)$url,'active'=>$active,'children'=>normalize_items($kids)];
  }
  return $out;
}

if ($primary_navigation instanceof Collection) {
  $navArray = $primary_navigation->toArray();
} elseif (method_exists($primary_navigation, 'toArray')) {
  $navArray = $primary_navigation->toArray();
} else {
  $navArray = $primary_navigation;
}
$topItems  = is_array($navArray) && array_key_exists('items', $navArray) ? $navArray['items'] : $navArray;
$menu_data = normalize_items($topItems);
$appointment_button = get_field('topbar_appointment_button', 'option');
?>

<?php if ($enable_hamburger && !empty($menu_data)) : ?>
<div x-data="navSlide(<?php echo esc_attr(json_encode($menu_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>)" x-cloak class="flex gap-3 items-center lg:hidden">
  <!-- Cart (mobile) -->
  <?php get_template_part('template-parts/header/navbar/cart'); ?>

  <!-- HAMBURGER -->
  <button class="hamburger <?php echo esc_attr($hamburger_style); ?>"
          :class="{ 'is-active': open }"
          @click="toggle"
          aria-label="Menu">
    <span class="hamburger-box"><span class="hamburger-inner"></span></span>
  </button>

  <!-- PANEL -->
  <div x-show="open"
       @click.outside="close"
       x-transition:enter="transition transform ease-out duration-500"
       x-transition:enter-start="<?php echo $startTransform; ?> opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition transform ease-in duration-400"
       x-transition:leave-end="<?php echo $startTransform; ?> opacity-0"
       class="flex overflow-hidden fixed top-0 left-0 z-50 flex-col h-screen"
       style="background:<?php echo esc_attr($bgColour); ?>; <?php echo $panelStyle; ?> will-change: transform;"
       x-cloak
  >

    <!-- BACK BAR -->
    <div class="flex gap-2 items-center px-6 py-4 border-b"
         x-show="depth>0"
         x-transition.opacity
         x-cloak>
      <button @click="back" aria-label="Back">
        <svg class="w-5 h-5" viewBox="0 0 24 24" stroke="currentColor" fill="none" aria-hidden="true">
          <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <span class="text-sm font-medium">Back</span>
    </div>

    <!-- SLIDING TRACK -->
    <div class="overflow-hidden relative flex-1">
      <template x-for="(level, idx) in stack" :key="idx">
        <ul class="overflow-y-auto absolute inset-0 px-6 py-8 space-y-6 transition-transform duration-500 ease-in-out"
            :style="slideStyle(idx)">
          <template x-for="(item, i) in level" :key="i">
            <li class="pb-4 border-b border-[#CCDEE2]">
              <template x-if="item.children.length">
                <button type="button"
                        @click.prevent="forward(item.children)"
                        class="flex justify-between items-center w-full max-sm:text-[20px] text-lg text-secondary-800">
                  <span class="text-left uppercase" x-text="item.label"></span>
                  <i class="ml-2 fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
              </template>
              <template x-if="!item.children.length">
                <a :href="item.url"
                   class="block text-lg text-secondary-800"
                   :class="item.active ? 'font-semibold' : ''"
                   x-text="item.label"></a>
              </template>
            </li>
          </template>

          <div class="relative pt-5 w-full">
              <a href="tel:+0494334486"
                class="inline-flex gap-2.5 justify-center items-center px-10 py-4 text-base font-medium text-white uppercase whitespace-nowrap border-2 transition-colors duration-200 border-primary btn w-fit bg-primary hover:bg-white hover:text-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 max-md:px-5 max-sm:w-full"
                aria-label="Call us at (049) 433 4486">
                Call Us
              </a>
            </div>

          <?php if ($appointment_button): ?>
            <div class="flex gap-2.5 items-start max-sm:w-full">
              <a href="<?php echo esc_url($appointment_button['url']); ?>"
                 class="inline-flex gap-2.5 justify-center items-center px-10 py-4 text-base font-medium text-white uppercase whitespace-nowrap border-2 transition-colors duration-200 border-primary btn w-fit bg-primary hover:bg-white hover:text-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 max-md:px-5 max-sm:w-full"
                 target="<?php echo esc_attr($appointment_button['target'] ?: '_self'); ?>"
                 aria-label="<?php echo esc_attr($appointment_button['title']); ?>">
                <span><?php echo esc_html($appointment_button['title']); ?></span>
              </a>
            </div>
          <?php endif; ?>
        <div class="flex flex-col justify-between items-center w-full">
            <!-- Search -->
            <div class="relative mt-12 w-full">
              <?php echo do_shortcode('[fibosearch]'); ?>
            </div>
          </div>
        </ul>
      </template>
    </div>
  </div>
</div>

<!-- Alpine controller uses a shared store so the black bar can fade -->
<script>
function navSlide(root) {
  return {
    get open() { return Alpine.store('nav').open },
    set open(v){ Alpine.store('nav').open = v },

    depth: 0,
    stack: [root],

    slideStyle(idx){ return `transform:translateX(${(idx - this.depth) * 100}%);`; },
    toggle(){ this.open = !this.open },
    close(){ this.open = false; this.reset() },
    forward(kids){ this.stack.push(kids); this.depth++ },
    back(){ if (this.depth) { this.stack.pop(); this.depth-- } },
    reset(){ this.stack = [root]; this.depth = 0 },
  }
}
</script>
<?php endif; ?>
