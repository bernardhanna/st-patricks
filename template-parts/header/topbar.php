<?php
/**
 * Top Bar (Theme Options)
 * - Left: menu links (ACF Link arrays)
 * - Right: phone link (ACF Link array)
 * - Background color (Color Picker)
 * - No Vue attributes, no Flexi loop
 */

$topbar_bg_color = get_field('topbar_bg_color', 'option');
$topbar_links    = get_field('topbar_links', 'option'); // repeater of link arrays
$topbar_phone    = get_field('topbar_phone_link', 'option'); // single link array

// Unique section ID (random)
$section_id = 'topbar-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Only render if we have something to show
if ( ($topbar_links && is_array($topbar_links) && count($topbar_links)) || $topbar_phone ):
?>
<section
  id="<?php echo esc_attr($section_id); ?>"
  class="md:flex flex-col items-start w-full h-[52px] max-md:hidden"
  role="banner"
  aria-label="Top bar navigation"
  style="background-color: <?php echo esc_attr( $topbar_bg_color ?: '#0f3c3c' ); ?>;"
>
  <div
    class="box-border flex justify-between items-center px-5 py-0   h-[52px] mx-auto w-full max-w-container"
  >

    <!-- Left: Menu Links -->
    <?php if ($topbar_links && is_array($topbar_links)) : ?>
      <nav
        class="flex gap-6 items-center max-md:gap-4 max-sm:hidden"
        aria-label="Top navigation links"
        role="navigation"
      >
        <?php foreach ($topbar_links as $row) :
          $link = isset($row['link']) ? $row['link'] : null;
          if (!$link || empty($link['url']) || empty($link['title'])) { continue; }
        ?>
          <a
            href="<?php echo esc_url($link['url']); ?>"
            class="text-sm font-medium leading-5 text-white transition-opacity cursor-pointer hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white max-md:text-sm"
            target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
            aria-label="<?php echo esc_attr($link['title']); ?>"
          >
            <div class="text-sm font-medium text-white">
              <?php echo esc_html($link['title']); ?>
            </div>
          </a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <!-- Right: Phone -->
    <?php if ($topbar_phone && !empty($topbar_phone['url']) && !empty($topbar_phone['title'])) : ?>
      <div class="flex gap-2.5 justify-center items-center px-0 py-2.5 max-sm:gap-2">
        <div>
          <div>
         <a
          href="<?php echo esc_url($topbar_phone['url']); ?>"
          class="text-sm font-medium leading-5 text-white transition-opacity hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white max-sm:text-sm max-md:hidden"
          target="<?php echo esc_attr($topbar_phone['target'] ?: '_self'); ?>"
          aria-label="Call <?php echo esc_attr($topbar_phone['title']); ?>"
        >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              class="phone-icon"
              style="display: flex; width: 24px; height: 24px; padding: 2px 1.95px 2.072px 2.112px; justify-content: center; align-items: center;"
              aria-hidden="true"
            >
              <path
                d="M21.9999 16.92V19.92C22.0011 20.1985 21.944 20.4742 21.8324 20.7294C21.7209 20.9845 21.5572 21.2136 21.352 21.4019C21.1468 21.5901 20.9045 21.7335 20.6407 21.8227C20.3769 21.9119 20.0973 21.9451 19.8199 21.92C16.7428 21.5856 13.7869 20.5342 11.1899 18.85C8.77376 17.3147 6.72527 15.2662 5.18993 12.85C3.49991 10.2412 2.44818 7.271 2.11993 4.18001C2.09494 3.90347 2.12781 3.62477 2.21643 3.36163C2.30506 3.09849 2.4475 2.85669 2.6347 2.65163C2.82189 2.44656 3.04974 2.28271 3.30372 2.17053C3.55771 2.05834 3.83227 2.00027 4.10993 2.00001H7.10993C7.59524 1.99523 8.06572 2.16708 8.43369 2.48354C8.80166 2.79999 9.04201 3.23945 9.10993 3.72001C9.23656 4.68007 9.47138 5.62273 9.80993 6.53001C9.94448 6.88793 9.9736 7.27692 9.89384 7.65089C9.81408 8.02485 9.6288 8.36812 9.35993 8.64001L8.08993 9.91001C9.51349 12.4136 11.5864 14.4865 14.0899 15.91L15.3599 14.64C15.6318 14.3711 15.9751 14.1859 16.3491 14.1061C16.723 14.0263 17.112 14.0555 17.4699 14.19C18.3772 14.5286 19.3199 14.7634 20.2799 14.89C20.7657 14.9585 21.2093 15.2032 21.5265 15.5775C21.8436 15.9518 22.0121 16.4296 21.9999 16.92Z"
                stroke="#FFB273"
                stroke-width="1.25"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M14.0498 2C16.0881 2.21477 17.992 3.1188 19.4467 4.56258C20.9014 6.00636 21.8197 7.90341 22.0498 9.94"
                stroke="#FFB273"
                stroke-width="1.25"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M14.0498 6C15.0333 6.19394 15.9358 6.67903 16.6402 7.39231C17.3446 8.10559 17.8183 9.01413 17.9998 10"
                stroke="#FFB273"
                stroke-width="1.25"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>
          <div class="text-sm font-medium text-white max-sm:text-sm">
            <?php echo esc_html($topbar_phone['title']); ?>
          </div>
        </a>
      </div>
    <?php endif; ?>

  </div>
</section>
<?php endif; ?>