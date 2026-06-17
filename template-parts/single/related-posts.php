<?php

if (! in_array(get_post_type(), ['post', 'webinars'], true)) {
    return;
}

$defaults = matrix_get_blog_single_defaults();
$related_count = (int) ($defaults['related_count'] ?? 3);

if (get_post_type() === 'webinars') {
    $related_query = matrix_get_webinar_related_posts(null, $related_count);
    $map_related_card = static function (int $post_id): array {
        return matrix_map_webinar_related_post_card($post_id);
    };
} else {
    $related_query = matrix_get_blog_related_posts(null, $related_count);
    $map_related_card = static function (int $post_id): array {
        return matrix_map_blog_related_post_card($post_id);
    };
}

if (! $related_query->have_posts()) {
    return;
}

$heading = (string) ($defaults['related_heading'] ?? 'Related Links');
?>

<aside class="w-full lg:w-[328px] lg:shrink-0" aria-label="<?php echo esc_attr($heading); ?>">
    <div class="flex flex-col gap-8">
        <header class="flex flex-col gap-8">
            <h2 class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]">
                <?php echo esc_html($heading); ?>
            </h2>
            <div class="h-[4px] w-10 bg-[#6FC9C0]"></div>
        </header>

        <div class="flex flex-col gap-8">
            <?php while ($related_query->have_posts()) { ?>
                <?php
                $related_query->the_post();
                $card = $map_related_card(get_the_ID());
                ?>
                <article class="flex flex-col gap-6 rounded-[8px] bg-[#FBFAF7] p-6 shadow-[0px_1px_1px_rgba(0,0,0,0.05)]">
                    <?php
                    get_template_part('template-parts/single/partials/event-style-related-image', null, [
                        'card' => $card,
                    ]);
                    ?>

                    <div class="flex flex-col gap-4">
                        <?php if ($card['category_name'] !== '') { ?>
                            <?php
                            $badge_background = (string) ($card['badge_background'] ?? '#F9E5F2');
                            ?>
                            <span
                                class="inline-flex h-[30px] w-fit items-center justify-center rounded-full px-4 font-primary text-[14px] font-medium leading-[24px] text-[#08284B]"
                                style="background-color: <?php echo esc_attr($badge_background); ?>;"
                            >
                                <?php echo esc_html($card['category_name']); ?>
                            </span>
                        <?php } ?>

                        <h3 class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
                            <a
                                href="<?php echo esc_url($card['permalink']); ?>"
                                class="focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                            >
                                <?php echo esc_html($card['title']); ?>
                            </a>
                        </h3>

                        <p class="font-primary text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-[#1E244B]">
                            <?php echo esc_html($card['date_label']); ?>
                        </p>
                    </div>
                </article>
            <?php } ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
</aside>
