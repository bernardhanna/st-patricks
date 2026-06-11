<?php

if (get_post_type() !== 'research_projects') {
    return;
}

$defaults = matrix_get_research_project_single_defaults();
$previous_label = (string) ($defaults['previous_label'] ?? 'Previous project');
$next_label = (string) ($defaults['next_label'] ?? 'Next project');
$author_name = matrix_get_research_project_author_name();
$share_links = matrix_get_research_project_share_links();
$previous_post = matrix_get_research_project_adjacent_post_link('previous');
$next_post = matrix_get_research_project_adjacent_post_link('next');
$copy_link_id = 'research-project-share-copy-' . get_the_ID();
?>

<div class="flex w-full flex-col gap-8 pt-12">
    <div class="flex flex-col gap-6 border-y border-[#80CCD9] py-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="font-primary text-[16px] leading-[28px] text-[#08284B]">
            <p class="font-medium"><?php echo esc_html((string) ($defaults['published_by_label'] ?? 'Published by')); ?></p>
            <p class="font-bold"><?php echo esc_html($author_name); ?></p>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <p class="font-primary text-[16px] font-bold leading-[24px] text-[#08284B]">
                <?php echo esc_html((string) ($defaults['share_label'] ?? 'Share on:')); ?>
            </p>

            <ul class="flex flex-wrap items-center gap-3" role="list">
                <?php foreach ($share_links as $share_link) { ?>
                    <?php
                    $is_copy = ! empty($share_link['is_copy']);
                    $label = (string) ($share_link['label'] ?? '');
                    ?>
                    <li>
                        <?php if ($is_copy) { ?>
                            <button
                                type="button"
                                class="btn inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#C6ECF4] text-[#024B79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                data-copy-url="<?php echo esc_attr((string) ($share_link['url'] ?? '')); ?>"
                                aria-describedby="<?php echo esc_attr($copy_link_id); ?>"
                                onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copyUrl)"
                            >
                                <span class="sr-only"><?php echo esc_html($label); ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <path d="M6.5 2.75H11.25C11.94 2.75 12.5 3.31 12.5 4V10.25H11.25V4H6.5V2.75ZM4.75 4.75H9.5C10.19 4.75 10.75 5.31 10.75 6V12.25C10.75 12.94 10.19 13.5 9.5 13.5H4.75C4.06 13.5 3.5 12.94 3.5 12.25V6C3.5 5.31 4.06 4.75 4.75 4.75ZM4.75 6V12.25H9.5V6H4.75Z" fill="currentColor"/>
                                </svg>
                            </button>
                        <?php } else { ?>
                            <a
                                href="<?php echo esc_url((string) ($share_link['url'] ?? '#')); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn inline-flex h-8 w-8 items-center justify-center rounded-full bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                aria-label="<?php echo esc_attr($label); ?>"
                            >
                                <?php if (($share_link['id'] ?? '') === 'facebook') { ?>
                                    <svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true"><path d="M19.723 17L20.1675 14.104H17.389V12.225C17.389 11.433 17.777 10.6605 19.0215 10.6605H20.2845V8.1955C20.2845 8.1955 19.1385 8 18.0425 8C15.7545 8 14.259 9.387 14.259 11.8975V14.1045H11.7155V17H14.259V24H17.389V17L19.723 17Z" fill="#024B79"/></svg>
                                <?php } elseif (($share_link['id'] ?? '') === 'twitter') { ?>
                                    <svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true"><path d="M20.1344 9.5H22.3406L17.5219 15.0062L23.1906 22.5H18.7531L15.275 17.9563L11.3 22.5H9.09063L14.2438 16.6094L8.80938 9.5H13.3594L16.5 13.6531L20.1344 9.5ZM19.3594 21.1812H20.5813L12.6938 10.75H11.3813L19.3594 21.1812Z" fill="#024B79"/></svg>
                                <?php } elseif (($share_link['id'] ?? '') === 'whatsapp') { ?>
                                    <svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true"><path d="M16.02 6C10.48 6 6 10.07 6 15.14C6 17.22 6.73 19.16 7.95 20.72L6.5 25.5L11.49 24.1C13.01 25.2 14.94 25.82 16.98 25.82C22.52 25.82 27 21.75 27 16.68C27 11.61 22.56 6 16.02 6ZM22.2 19.34C21.84 20.08 20.56 20.74 19.68 20.92C19.02 21.06 18.14 21.16 16.02 20.22C13.34 18.98 11.58 16.42 11.38 16.14C11.18 15.86 10.22 14.52 10.22 13.16C10.22 11.8 10.92 11.12 11.18 10.84C11.44 10.56 11.76 10.5 11.96 10.5C12.16 10.5 12.36 10.5 12.52 10.5C12.7 10.5 12.94 10.44 13.18 10.98C13.44 11.54 14.02 12.92 14.1 13.08C14.18 13.24 14.24 13.42 14.14 13.66C14.04 13.9 13.96 14.02 13.82 14.18C13.68 14.34 13.52 14.52 13.36 14.7C13.22 14.86 13.06 15.04 13.24 15.36C13.42 15.68 14.08 16.84 15.18 17.78C16.58 18.98 17.72 19.36 18.08 19.52C18.44 19.68 18.66 19.64 18.86 19.42C19.06 19.2 19.7 18.48 19.94 18.14C20.18 17.8 20.42 17.86 20.68 17.96C20.94 18.06 22.2 18.72 22.48 18.86C22.76 19 22.96 19.08 23.02 19.2C23.08 19.32 23.08 19.82 22.2 19.34Z" fill="#024B79"/></svg>
                                <?php } else { ?>
                                    <svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true"><path d="M8 11H24V13H8V11ZM8 15H24V17H8V15ZM8 19H18V21H8V19Z" fill="#024B79"/></svg>
                                <?php } ?>
                            </a>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
            <span id="<?php echo esc_attr($copy_link_id); ?>" class="sr-only" aria-live="polite"></span>
        </div>
    </div>

    <?php if (is_array($previous_post) || is_array($next_post)) { ?>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <?php if (is_array($previous_post)) { ?>
                <a
                    href="<?php echo esc_url($previous_post['permalink']); ?>"
                    class="btn inline-flex items-center gap-3 font-primary text-[16px] font-medium leading-[28px] text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                >
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M8.75 3.5L5.25 7L8.75 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span><?php echo esc_html($previous_label); ?></span>
                </a>
            <?php } else { ?>
                <span></span>
            <?php } ?>

            <?php if (is_array($next_post)) { ?>
                <a
                    href="<?php echo esc_url($next_post['permalink']); ?>"
                    class="btn inline-flex items-center gap-3 font-primary text-[16px] font-medium leading-[28px] text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                >
                    <span><?php echo esc_html($next_label); ?></span>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5.25 3.5L8.75 7L5.25 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</div>
