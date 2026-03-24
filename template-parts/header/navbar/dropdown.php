<?php
$item      = $args['item']   ?? null;
$imagesMap = $args['images'] ?? [];
// ------------- guard -------------
if (!$item) {
    return;
}

/**
 * Get the WP menu-item ID that was used in the repeater.
 * Navi exposes both ->id and ->ID depending on version,
 * so ask for whichever exists first.
 */
$item_id = $item->ID ?? $item->id ?? null;

/** @var array|null $img  Full image array or null */
$img = $imagesMap[ $item_id ] ?? null;

/** Use safe helpers so null never throws notices */
$img_url   = is_array($img) && !empty($img['url'])   ? $img['url']   : '';
$img_alt   = is_array($img) && !empty($img['alt'])   ? $img['alt']   : ( $item->label . ' image' );
$img_title = is_array($img) && !empty($img['title']) ? $img['title'] : $img_alt;
?>
<div class="hidden overflow-hidden fixed left-0 z-50 pt-8 w-screen bg-transparent group-hover:flex">
  <div class="w-full bg-gray-50">
    <div class="flex flex-row gap-10 justify-between w-full max-w-[1440px] mx-auto text-base font-medium text-slate-700">
      <?php if ($img_url) : ?>
        <img
          src="<?php echo esc_url($img_url); ?>"
          alt="<?php echo esc_attr($img_alt); ?>"
          title="<?php echo esc_attr($img_title); ?>"
          class="object-cover h-fit max-h-[500px] w-full max-w-[50%] max-md:max-w-full"
        />
      <?php endif; ?>
      <nav class="flex flex-col justify-start items-start px-5 w-full max-w-[50%]">
        <span class="pt-8 pb-2 text-3xl font-bold leading-tight text-slate-700">
          <?php echo esc_html($item->label); ?>
        </span>
          <?php
          $children   = $item->children ?? [];
          $is_grid    = count($children) > 6;           // 🔑 switch at 7+
          $ul_classes = $is_grid
              ? 'grid grid-cols-2 gap-x-6 gap-y-2 mt-2' // two‑column grid
              : 'flex flex-col gap-2 mt-2';             // single column list
          ?>
          <ul class="<?= esc_attr( $ul_classes ); ?>">
            <?php foreach ( $children as $child ) : ?>
              <li class="pt-1 whitespace-nowrap">
                <a href="<?= esc_url( $child->url ); ?>" class="hover:text-secondary">
                  <?= esc_html( $child->label ); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
      </nav>
    </div>
  </div>
</div>
