<?php
/**
 * Banner Image Block
 * - Fixed width: 1920px
 * - Fixed height: 240px
 * - Responsive, optimized with srcset
 */

if (!function_exists('esc_attr')) {
    exit; // Prevent direct access
}

// Collect ACF field
$banner_image_id = get_sub_field('banner_image_field');

// Alt text from media library, fallback to title or generic label
$banner_alt = $banner_image_id
    ? (get_post_meta($banner_image_id, '_wp_attachment_image_alt', true)
        ?: get_the_title($banner_image_id)
        ?: 'Banner Image')
    : '';

$random_token = function_exists('wp_generate_password') ? wp_generate_password(8, false, false) : uniqid();
$section_id   = 'banner-' . $random_token;
?>

<?php if (!empty($banner_image_id)) : ?>
    <section
        id="<?php echo esc_attr($section_id); ?>"
        class="flex overflow-hidden relative"
        role="img"
        aria-label="<?php echo esc_attr($banner_alt); ?>"
    >
        <div class="w-full max-w-[1920px] h-[240px] mx-auto md:mt-[8rem]">
            <?php
            echo wp_get_attachment_image(
                $banner_image_id,
                'full',
                false,
                [
                    'alt'     => esc_attr($banner_alt),
                    'class'   => 'object-cover w-full h-[240px]',
                    'loading' => 'eager', // banners load early
                ]
            );
            ?>
        </div>
    </section>
<?php endif; ?>
