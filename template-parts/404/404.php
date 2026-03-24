<?php
// Fetch the entire 404 settings group first
$not_found_settings = get_field('not_found_settings', 'option');

// Fallbacks, in case no data is set in ACF:
$title = $not_found_settings['hero_title'] ?? 'Sorry, We Can’t Find That Page.';
$text = $not_found_settings['hero_text'] ?? 'Here are some helpful links to get you back on track:';
$links = $not_found_settings['links'] ?? []; // The repeater array
$bg_color = $not_found_settings['background_color'] ?? '#f8f9fa';
$text_color = $not_found_settings['text_color'] ?? '#333';
$padding_top = $not_found_settings['padding_top'] ?? 'py-10';
$padding_bottom = $not_found_settings['padding_bottom'] ?? 'pb-10';
?>

<main class="flex overflow-hidden justify-center items-center w-full site-main"
  style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">

      <div
        class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5"
      >
   
          <figure
            class="overflow-hidden my-auto rounded-none bg-blend-normal w-[896px] max-md:max-w-full"
          >
            <div
              class="flex relative flex-col w-full min-h-[700px] max-md:max-w-full max-sm:min-h-[246px]"
            >
              <img
                src="https://cdn.builder.io/api/v1/image/assets/f35586c581c84ecf82b6de32c55ed39e/5e8dc16bd588cdcca27f68b3c078f7c6c4367427?placeholderIfAbsent=true"
                alt="404 error background image"
                class="object-cover absolute inset-0 size-full"
                aria-hidden="true"
              />
            </div>
          </figure>
          <article
            class="flex flex-col flex-1 my-auto basis-0 max-md:max-w-full max-sm:px-6"
          >
            <h1
              class="text-6xl font-bold tracking-tighter text-primary leading-[72px] max-md:max-w-full max-md:text-4xl max-md:leading-[53px]"
            >
             <?php echo esc_html($title); ?>
            </h1>
            <div
              class="mt-4 text-xl leading-snug text-slate-700 max-md:max-w-full"
            >
             <?php echo wp_kses_post($text); ?>
            </div>
            <div
              class="pt-8 mt-4 text-sm font-semibold leading-none text-black max-sm:pt-4"
            >
              <a
                href="/"
                class="flex gap-2 justify-center items-center px-7 py-4 whitespace-nowrap rounded bg-primary min-h-14 max-md:px-5 w-fit hover:bg-hover hover:text-hover"
                role="button"
              >
                <span class="my-auto">Return Home</span>
                <img
                  src="https://cdn.builder.io/api/v1/image/assets/f35586c581c84ecf82b6de32c55ed39e/5387a1043b71bf88580bd78119a5783cdfc8284f?placeholderIfAbsent=true"
                  alt="Arrow icon"
                  class="object-contain my-auto w-6 shrink-0 aspect-square"
                  aria-hidden="true"
                />
              </a>
            </div>
          </article>
      </div>
</main>