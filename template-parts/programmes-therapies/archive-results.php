<?php

$archive = is_array($args['programmes_therapies_archive'] ?? null) ? $args['programmes_therapies_archive'] : [];

if ($archive === []) {
    return;
}

$empty_state_message = (string) ($archive['empty_state_message'] ?? 'No programmes or therapies matched your filters.');
$state = is_array($archive['state'] ?? null) ? $archive['state'] : [];
$pagination = is_array($archive['pagination'] ?? null) ? $archive['pagination'] : [];
$query = $archive['query'] ?? null;
$base_url = (string) ($archive['base_url'] ?? home_url('/'));

$state = array_merge([
    'type' => 'all',
    'care' => 'all',
    'delivery' => 'all',
    'paged' => 1,
], $state);

$current_page = max(1, (int) ($pagination['current'] ?? $state['paged']));
$total_pages = max(1, (int) ($pagination['total'] ?? (($query instanceof WP_Query) ? $query->max_num_pages : 1)));
$has_posts = $query instanceof WP_Query && $query->have_posts();
?>

<?php if ($has_posts) { ?>
    <div class="flex flex-col gap-8">
        <?php while ($query->have_posts()) { ?>
            <?php
            $query->the_post();
            $card = matrix_map_programmes_therapies_post_card(get_the_ID());
            ?>
            <article class="rounded-[8px] bg-[#FBFAF7] p-6 shadow-[0px_1px_1px_rgba(0,0,0,0.05)]">
                <?php if ($card['tags'] !== []) { ?>
                    <ul class="flex flex-wrap gap-4 mb-4" role="list">
                        <?php foreach ($card['tags'] as $tag) { ?>
                            <li>
                                <span class="inline-flex h-[30px] items-center justify-center rounded-full bg-[#FADBD8] px-4 font-primary text-[14px] font-medium leading-[24px] text-[#08284B]">
                                    <?php echo esc_html((string) ($tag['label'] ?? '')); ?>
                                </span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>

                <h3 class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
                    <a
                        href="<?php echo esc_url($card['permalink']); ?>"
                        class="inline-flex items-center gap-2 text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                    >
                        <span><?php echo esc_html($card['title']); ?></span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </h3>

                <?php if ($card['summary'] !== '') { ?>
                    <p class="mt-4 font-primary text-[14px] font-normal leading-[24px] text-[#08284B]">
                        <?php echo esc_html($card['summary']); ?>
                    </p>
                <?php } ?>
            </article>
        <?php } ?>
        <?php wp_reset_postdata(); ?>
    </div>
<?php } else { ?>
    <p class="font-primary text-[16px] leading-[28px] text-[#1E244B]">
        <?php echo esc_html($empty_state_message); ?>
    </p>
<?php } ?>

<?php
get_template_part('template-parts/partials/archive-pagination', null, [
    'archive_pagination' => [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'aria_label' => 'Programmes and therapies pagination',
        'variant' => 'pill',
        'build_page_url' => static function (int $page) use ($base_url, $state): string {
            return matrix_build_programmes_therapies_archive_page_url($base_url, $state, $page);
        },
        'link_attributes_callback' => static function (int $page): array {
            return [
                'data-pt-page' => (string) $page,
            ];
        },
    ],
]);
?>
