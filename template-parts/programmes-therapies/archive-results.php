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

<?php if ($total_pages > 1) { ?>
    <nav class="flex flex-wrap gap-2 justify-center items-center mt-10" aria-label="Programmes and therapies pagination">
        <?php if ($current_page > 1) { ?>
            <a
                href="<?php echo esc_url(matrix_build_programmes_therapies_archive_page_url($base_url, $state, $current_page - 1)); ?>"
                data-pt-page="<?php echo esc_attr((string) ($current_page - 1)); ?>"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[#08284B] transition-colors duration-200 hover:border-[#024B79] hover:bg-[#F1F8F9] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                aria-label="Go to previous page"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M8.75 3.5L5.25 7L8.75 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        <?php } ?>

        <?php for ($page = 1; $page <= $total_pages; $page++) { ?>
            <?php if ($page === $current_page) { ?>
                <span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#08284B] font-primary text-[14px] font-semibold text-white"
                    aria-current="page"
                >
                    <?php echo esc_html((string) $page); ?>
                </span>
            <?php } else { ?>
                <a
                    href="<?php echo esc_url(matrix_build_programmes_therapies_archive_page_url($base_url, $state, $page)); ?>"
                    data-pt-page="<?php echo esc_attr((string) $page); ?>"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white font-primary text-[14px] font-semibold text-[#08284B] transition-colors duration-200 hover:border-[#024B79] hover:bg-[#F1F8F9] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                    aria-label="Go to page <?php echo esc_attr((string) $page); ?>"
                >
                    <?php echo esc_html((string) $page); ?>
                </a>
            <?php } ?>
        <?php } ?>

        <?php if ($current_page < $total_pages) { ?>
            <a
                href="<?php echo esc_url(matrix_build_programmes_therapies_archive_page_url($base_url, $state, $current_page + 1)); ?>"
                data-pt-page="<?php echo esc_attr((string) ($current_page + 1)); ?>"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[#08284B] transition-colors duration-200 hover:border-[#024B79] hover:bg-[#F1F8F9] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                aria-label="Go to next page"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M5.25 3.5L8.75 7L5.25 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        <?php } ?>
    </nav>
<?php } ?>
