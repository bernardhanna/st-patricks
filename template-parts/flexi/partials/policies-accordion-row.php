<?php

$row = is_array($args['row'] ?? null) ? $args['row'] : [];
$content_classes = (string) ($args['content_classes'] ?? '');
$row_type = (string) ($row['type'] ?? 'text');

if ($row_type === 'text') {
    ?>
    <div class="<?php echo esc_attr($content_classes); ?>">
        <?php echo wp_kses_post((string) ($row['content'] ?? '')); ?>
    </div>
    <?php
    return;
}

if ($row_type === 'pdf_grid') {
    $documents = is_array($row['documents'] ?? null) ? $row['documents'] : [];
    ?>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <?php foreach ($documents as $document) { ?>
            <?php
            $document_link = is_array($document['link'] ?? null) ? $document['link'] : null;
            if ($document_link === null) {
                continue;
            }
            $document_target = ($document_link['target'] ?? '') !== '' ? $document_link['target'] : '_blank';
            ?>
            <article class="flex gap-3 rounded-[8px] bg-white p-6 shadow-[0px_1px_1px_rgba(0,0,0,0.05)]">
                <?php echo matrix_get_policies_pdf_icon_svg(); ?>
                <div class="flex min-w-0 flex-1 flex-col gap-4">
                    <h3 class="font-primary text-[20px] font-semibold leading-8 tracking-[-0.12px] text-[#1E244B] lg:text-[24px]">
                        <?php echo esc_html((string) ($document['title'] ?? '')); ?>
                    </h3>
                    <a
                        href="<?php echo esc_url($document_link['url']); ?>"
                        target="<?php echo esc_attr($document_target); ?>"
                        class="btn inline-flex h-10 w-fit items-center gap-2 rounded-[6px] border border-[#E2E8F0] bg-white px-3 text-[14px] font-medium leading-6 text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                        <?php if ($document_target === '_blank') { ?>
                            rel="noopener noreferrer"
                        <?php } ?>
                    >
                        <?php echo matrix_get_policies_external_link_icon_svg(); ?>
                        <span>PDF opens in a new tab</span>
                    </a>
                </div>
            </article>
        <?php } ?>
    </div>
    <?php
    return;
}

if ($row_type === 'link_cards') {
    $cards = is_array($row['cards'] ?? null) ? $row['cards'] : [];
    ?>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <?php foreach ($cards as $card) { ?>
            <?php
            $card_link = is_array($card['link'] ?? null) ? $card['link'] : null;
            if ($card_link === null) {
                continue;
            }
            $card_target = ($card_link['target'] ?? '') !== '' ? $card_link['target'] : '_blank';
            ?>
            <article class="flex flex-col gap-4 rounded-[8px] bg-white p-6 shadow-[0px_1px_1px_rgba(0,0,0,0.05)]">
                <h3 class="font-primary text-[20px] font-semibold leading-8 tracking-[-0.12px] text-[#1E244B] lg:text-[24px]">
                    <?php echo esc_html((string) ($card['title'] ?? '')); ?>
                </h3>
                <a
                    href="<?php echo esc_url($card_link['url']); ?>"
                    target="<?php echo esc_attr($card_target); ?>"
                    class="<?php echo esc_attr(matrix_get_content_button_class_names('filled')); ?> h-10 gap-2"
                    <?php if ($card_target === '_blank') { ?>
                        rel="noopener noreferrer"
                    <?php } ?>
                >
                    <?php echo matrix_get_policies_external_link_icon_svg(); ?>
                    <span><?php echo esc_html($card_link['title']); ?></span>
                </a>
            </article>
        <?php } ?>
    </div>
    <?php
    return;
}

if ($row_type === 'external_links') {
    $links = is_array($row['links'] ?? null) ? $row['links'] : [];
    ?>
    <div class="flex flex-col gap-4">
        <?php foreach ($links as $external_link) { ?>
            <?php
            $link = is_array($external_link['link'] ?? null) ? $external_link['link'] : null;
            if ($link === null) {
                continue;
            }
            $link_target = ($link['target'] ?? '') !== '' ? $link['target'] : '_blank';
            ?>
            <a
                href="<?php echo esc_url($link['url']); ?>"
                target="<?php echo esc_attr($link_target); ?>"
                class="flex items-center gap-3 rounded-[8px] bg-white p-6 shadow-[0px_1px_1px_rgba(0,0,0,0.05)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                <?php if ($link_target === '_blank') { ?>
                    rel="noopener noreferrer"
                <?php } ?>
            >
                <?php echo matrix_get_policies_pdf_icon_svg(); ?>
                <span class="font-primary text-[20px] font-semibold leading-8 tracking-[-0.12px] text-[#1E244B] lg:text-[24px]">
                    <?php echo esc_html((string) ($external_link['title'] ?? '')); ?>
                </span>
                <span class="ml-auto" aria-hidden="true">→</span>
            </a>
        <?php } ?>
    </div>
    <?php
}
