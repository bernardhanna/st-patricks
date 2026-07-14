<?php
$text_content = get_sub_field('text_content');
$policy_sections = matrix_wysiwyg_should_use_policy_section_layout($text_content)
    ? matrix_prepare_policy_wysiwyg_sections($text_content)
    : [];


?>

<?php if ($policy_sections !== []) { ?>
  <?php foreach ($policy_sections as $section_index => $policy_section) { ?>
    <?php
    $section_id = 'wysiwyg-policy-section-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
    $background_color = $section_index % 2 === 0 ? '#FFFFFF' : '#FBFAF7';
    ?>
    <section
      id="<?php echo esc_attr($section_id); ?>"
      class="flex overflow-hidden relative"
      data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-policy-' . get_row_index() . '-' . ($section_index + 1)); ?>"
      data-content-scheme="default"
      style="<?php echo esc_attr('background-color: ' . $background_color . ';'); ?>"
      aria-labelledby="<?php echo esc_attr($section_id . '-heading'); ?>"
    >
      <div class="<?php echo esc_attr(matrix_get_content_wrapper_class_names()); ?>">
        <div class="<?php echo esc_attr(matrix_get_content_grid_class_names('match_text', 'one_column')); ?>">
          <article class="order-1 flex w-full flex-col gap-8">
            <header class="flex flex-col gap-8 w-full">
              <h2
                id="<?php echo esc_attr($section_id . '-heading'); ?>"
                class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px] text-[#1E244B]"
              >
                <?php echo esc_html($policy_section['heading']); ?>
              </h2>

              <div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>
            </header>

            <div class="<?php echo esc_attr(matrix_get_content_rich_text_wrapper_class_names('medium', 'max-w-[720px]', 'default')); ?>">
              <?php echo matrix_kses_rich_text($policy_section['content']); ?>
            </div>
          </article>
        </div>
      </div>
    </section>
  <?php } ?>
<?php } else { ?>
<section class="flex overflow-hidden relative" data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>">
  <div class="py-12 lg:py-[100px] w-full mx-auto max-w-[1095px] flex flex-col md:flex-row-reverse items-center  max-lg:px-5  max-xxl:px-5">
      <div class="relative font-primary text-[16px] font-medium leading-[28px] text-[#08284B] [&_p:last-child]:mb-0 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-2 [&_a]:font-medium [&_a]:text-[#024B79] [&_a]:underline [&_a:hover]:no-underline">
        <?php if ($text_content): ?>
          <?= matrix_kses_rich_text($text_content); ?>
        <?php endif; ?>
      </div>

  </div>
</section>
<?php } ?>

