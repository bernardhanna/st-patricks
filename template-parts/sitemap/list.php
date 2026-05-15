<?php

/**
 * @deprecated Use matrix_render_sitemap_list() instead.
 */

echo matrix_render_sitemap_list(
    is_array($items ?? null) ? $items : [],
    max(0, (int) ($depth ?? 0))
);
