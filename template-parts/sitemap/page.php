<?php

$sitemap = is_array($args['sitemap'] ?? null) ? $args['sitemap'] : matrix_prepare_sitemap_page();
$heading = trim((string) ($sitemap['heading'] ?? 'Sitemap'));
$intro = trim((string) ($sitemap['intro'] ?? ''));
$breadcrumbs = is_array($sitemap['breadcrumbs'] ?? null) ? $sitemap['breadcrumbs'] : [];
$current_crumb_label = trim((string) ($sitemap['current_crumb_label'] ?? $heading));
$page_sections = is_array($sitemap['page_sections'] ?? null) ? $sitemap['page_sections'] : [];
$archive_sections = is_array($sitemap['archive_sections'] ?? null) ? $sitemap['archive_sections'] : [];
$section_id = 'sitemap-page-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading_id = $section_id . '-heading';
?>

<main id="<?php echo esc_attr($section_id); ?>" class="w-full site-main">
    <section class="w-full bg-[#FBF8F3]" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
        <div class="mx-auto w-full max-w-[1280px]">
            <nav class="w-full bg-[#F1F8F9] px-5 py-3 lg:px-[70px]" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-3" role="list">
                    <?php foreach ($breadcrumbs as $breadcrumb) :
                        $crumb_title = trim((string) ($breadcrumb['title'] ?? ''));
                        $crumb_url = trim((string) ($breadcrumb['url'] ?? ''));

                        if ($crumb_title === '' || $crumb_url === '') {
                            continue;
                        }
                        ?>
                        <li class="flex items-center gap-3">
                            <a
                                href="<?php echo esc_url($crumb_url); ?>"
                                class="font-primary text-[14px] font-semibold leading-[20px] text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                            >
                                <?php echo esc_html($crumb_title); ?>
                            </a>
                            <span aria-hidden="true" class="text-[#08284B]">/</span>
                        </li>
                    <?php endforeach; ?>

                    <?php if ($current_crumb_label !== '') { ?>
                        <li class="font-primary text-[14px] font-normal leading-[20px] text-[#08284B]" aria-current="page">
                            <?php echo esc_html($current_crumb_label); ?>
                        </li>
                    <?php } ?>
                </ol>
            </nav>

            <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-8 px-5 py-10 xl:px-0 xl:py-[100px]">
                <h1
                    id="<?php echo esc_attr($heading_id); ?>"
                    class="font-primary text-[30px] font-semibold leading-[36px] tracking-[-0.225px] text-[#1E244B] lg:text-[36px] lg:leading-[40px] lg:tracking-[-0.432px]"
                >
                    <?php echo esc_html($heading); ?>
                </h1>

                <div class="h-1 w-10 bg-[#6FC9C0]" aria-hidden="true"></div>

                <?php if ($intro !== '') { ?>
                    <p class="max-w-[1018px] font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                        <?php echo esc_html($intro); ?>
                    </p>
                <?php } ?>
            </div>
        </div>
    </section>

    <section class="bg-white" aria-label="Site map links">
        <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-16 px-5 py-12 xl:px-0 xl:py-[100px]">
            <?php if ($page_sections !== []) { ?>
                <div class="grid grid-cols-1 gap-12 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($page_sections as $section) :
                        $section_title = trim((string) ($section['title'] ?? ''));
                        $section_url = trim((string) ($section['url'] ?? ''));
                        $children = is_array($section['children'] ?? null) ? $section['children'] : [];

                        if ($section_title === '' || $section_url === '') {
                            continue;
                        }

                        $section_heading_id = $section_id . '-section-' . (int) ($section['id'] ?? 0);
                        ?>
                        <article class="flex flex-col" aria-labelledby="<?php echo esc_attr($section_heading_id); ?>">
                            <h2
                                id="<?php echo esc_attr($section_heading_id); ?>"
                                class="font-primary text-[20px] font-semibold leading-[32px] tracking-[-0.12px] text-[#1E244B]"
                            >
                                <a
                                    href="<?php echo esc_url($section_url); ?>"
                                    class="transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                                >
                                    <?php echo esc_html($section_title); ?>
                                </a>
                            </h2>

                            <div class="mt-4 h-1 w-10 bg-[#6FC9C0]" aria-hidden="true"></div>

                            <?php echo matrix_render_sitemap_list($children); ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php } ?>

            <?php if ($archive_sections !== []) { ?>
                <div class="flex flex-col gap-12 border-t border-[rgba(8,40,75,0.12)] pt-12">
                    <h2 class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]">
                        News, resources, and listings
                    </h2>

                    <div class="grid grid-cols-1 gap-12 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($archive_sections as $index => $archive_section) :
                            $archive_title = trim((string) ($archive_section['title'] ?? ''));
                            $archive_url = trim((string) ($archive_section['url'] ?? ''));
                            $archive_items = is_array($archive_section['items'] ?? null) ? $archive_section['items'] : [];

                            if ($archive_title === '') {
                                continue;
                            }

                            $archive_heading_id = $section_id . '-archive-' . $index;
                            ?>
                            <section class="flex flex-col" aria-labelledby="<?php echo esc_attr($archive_heading_id); ?>">
                                <h3
                                    id="<?php echo esc_attr($archive_heading_id); ?>"
                                    class="font-primary text-[20px] font-semibold leading-[32px] tracking-[-0.12px] text-[#1E244B]"
                                >
                                    <?php if ($archive_url !== '') { ?>
                                        <a
                                            href="<?php echo esc_url($archive_url); ?>"
                                            class="transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                                        >
                                            <?php echo esc_html($archive_title); ?>
                                        </a>
                                    <?php } else { ?>
                                        <?php echo esc_html($archive_title); ?>
                                    <?php } ?>
                                </h3>

                                <div class="mt-4 h-1 w-10 bg-[#6FC9C0]" aria-hidden="true"></div>

                                <?php if ($archive_items !== []) {
                                    echo matrix_render_sitemap_list(array_map(static function ($link) {
                                        return [
                                            'title' => (string) ($link['title'] ?? ''),
                                            'url' => (string) ($link['url'] ?? ''),
                                            'children' => [],
                                        ];
                                    }, $archive_items));
                                } ?>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
</main>
