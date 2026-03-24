<?php
/*
Template Name: Sitemap Page
*/

get_header();
?>
<main class="overflow-hidden w-full mt-[5rem] lg:mt-[9rem] site-main">
    <?php load_hero_templates(); ?>
        <section class="flex overflow-hidden relative">
            <div class="flex flex-col items-center w-full py-5 mx-auto max-w-[1085px] max-lg:px-5">
                <div class="flex flex-col gap-10 mt-10 w-full md:flex-row">
                    <div class="w-full">
                        <h2 class="text-2xl font-semibold text-black">Pages</h2>
                        <div class="mt-1.5 w-8 h-[4px] max-w-[32px] bg-primary" aria-hidden="true"></div>
                        <ul class="mt-4 space-y-2">
                            <?php
                            $pages = get_pages();
                            foreach ($pages as $page) {
                                echo '<li><a href="' . esc_url(get_permalink($page->ID)) . '" class="text-accent hover:underline">' . esc_html($page->post_title) . '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="w-full">
                        <h2 class="text-2xl font-semibold text-black">Posts</h2>
                        <div class="mt-1.5 w-8 h-[4px] bg-primary max-w-[32px]" aria-hidden="true"></div>
                        <ul class="mt-4 space-y-2">
                            <?php
                            $posts = get_posts(['numberposts' => -1, 'post_status' => 'publish']);
                            foreach ($posts as $post) {
                                echo '<li><a href="' . esc_url(get_permalink($post->ID)) . '" class="text-accent hover:underline">' . esc_html($post->post_title) . '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    <?php load_flexible_content_templates(); ?>
</main>
<?php get_footer(); ?>
