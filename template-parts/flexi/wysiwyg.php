<?php
$text_content = get_sub_field('text_content');

$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen = get_sub_field('screen_size');
    $pt = get_sub_field('padding_top');
    $pb = get_sub_field('padding_bottom');
    $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
    $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
  }
}
?>

<section class="flex overflow-hidden relative" data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>">
  <div class="py-12 lg:py-[100px] w-full mx-auto max-w-[1095px] flex flex-col md:flex-row-reverse items-center  max-lg:px-5  max-xxl:px-5">
      <div class="relative font-primary text-[16px] font-medium leading-[28px] text-[#08284B] [&_p:last-child]:mb-0 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-2 [&_a]:font-medium [&_a]:text-[#024B79] [&_a]:underline [&_a:hover]:no-underline">
        <?php if ($text_content): ?>
          <?= matrix_kses_rich_text($text_content); ?>
        <?php endif; ?>
      </div>

  </div>
</section>

