<?php

$title = (string) ($args['title'] ?? '');
$show_portal_cta = (bool) ($args['show_portal_cta'] ?? false);
$portal_cta = $args['portal_cta'] ?? null;
?>

<div class="relative z-[1] flex w-[232px] shrink-0 flex-col gap-12">
    <div>
        <h2 class="font-primary text-[30px] font-semibold leading-9 tracking-[-0.225px] text-[#1E244B]">
            <?php echo esc_html($title); ?>
        </h2>
        <div class="mt-8 h-px w-10 bg-[#FF9E66]" aria-hidden="true"></div>
    </div>

    <?php if ($show_portal_cta && is_array($portal_cta) && ! empty($portal_cta['url']) && ! empty($portal_cta['title'])) : ?>
        <a
            href="<?php echo esc_url($portal_cta['url']); ?>"
            target="<?php echo esc_attr($portal_cta['target'] ?? '_self'); ?>"
            class="btn inline-flex h-11 w-fit items-center justify-center rounded-[6px] bg-[#80CCD9] px-8 text-sm font-medium leading-6 text-white transition-colors hover:bg-[#66c4d8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#80CCD9]"
        >
            <?php echo esc_html($portal_cta['title']); ?>
        </a>
    <?php endif; ?>
</div>
