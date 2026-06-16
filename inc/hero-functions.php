<?php
// File: inc/hero-functions.php

/**
 * Get Available Hero Layouts
 * 
 * Returns an array of available hero layout names based on template files
 */
function get_available_hero_layouts()
{
  $hero_path = get_template_directory() . '/template-parts/hero/';
  $files = glob($hero_path . '*.php');

  return array_map(function ($file) {
    return basename($file, '.php');
  }, $files);
}

/**
 * Validate Hero Layout
 * 
 * Ensures that ACF field definitions have corresponding template files
 */
function validate_hero_layout($layout_name)
{
  $available_layouts = get_available_hero_layouts();
  if (!in_array($layout_name, $available_layouts)) {
    error_log("Warning: ACF hero layout '{$layout_name}' has no corresponding template file");
    return false;
  }
  return true;
}

/**
 * Load Hero Templates
 * 
 * Automatically loads hero templates based on available files in the hero directory
 */
function load_hero_templates($post_id = null)
{
  // If no post_id is provided, use the current page's ID
  if (!$post_id) {
    $post_id = is_home() ? get_option('page_for_posts') : get_the_ID();
  }

  // Debugging: Log which page ID is being used
  error_log("Loading Hero Templates for Post ID: " . $post_id);

  if ($post_id && have_rows('hero_content_blocks', $post_id)) {
    while (have_rows('hero_content_blocks', $post_id)) : the_row();
      $layout = get_row_layout();

      $template_path = get_template_directory() . '/template-parts/hero/' . $layout . '.php';

      if (! file_exists($template_path)) {
        $template_path = get_template_directory() . '/template-parts/flexi/' . $layout . '.php';
      }

      if (file_exists($template_path)) {
        if (str_contains($template_path, '/template-parts/flexi/')) {
          require $template_path;
        } else {
          get_template_part('template-parts/hero/' . $layout);
        }
      } else {
        error_log("Missing hero template file: {$layout}.php");
      }
    endwhile;
  } else {
    error_log("No ACF Hero Blocks found for Post ID: " . $post_id);
  }
}

/**
 * Normalize hero breadcrumb data for manual or auto sources.
 *
 * @param string $source
 * @param array  $manual_items
 * @param string $current_label
 * @param array  $auto_data
 * @return array{items: array<int, array<string, string>>, current_label: string}
 */
function matrix_resolve_hero_breadcrumbs($source, $manual_items = array(), $current_label = '', $auto_data = array())
{
  $default_data = array(
    'items' => array(),
    'current_label' => '',
  );

  $auto_data = is_array($auto_data) ? array_merge($default_data, $auto_data) : $default_data;

  if ($source === 'manual') {
    return array(
      'items' => is_array($manual_items) ? array_values($manual_items) : array(),
      'current_label' => (string) $current_label,
    );
  }

  return array(
    'items' => is_array($auto_data['items']) ? array_values($auto_data['items']) : array(),
    'current_label' => (string) $auto_data['current_label'],
  );
}

/**
 * Build automatic hero breadcrumbs from the current page hierarchy.
 *
 * @param int $post_id
 * @return array{items: array<int, array<string, string>>, current_label: string}
 */
/**
 * Resolve the hero_with_breadcrumbs layout style.
 *
 * @param mixed $value
 * @return string
 */
function matrix_resolve_hero_with_breadcrumbs_layout_style($value)
{
  $value = is_string($value) ? trim($value) : '';

  if ($value === 'title_accent') {
    return 'title_accent';
  }

  if ($value === 'register_intro') {
    return 'register_intro';
  }

  return 'image_split';
}

function matrix_get_hero_external_link_icon_svg()
{
  return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" class="shrink-0"><path d="M6.5 2.5H12.5V8.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.5 2.5L3.5 11.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_get_hero_with_breadcrumbs_gradient_vars($background_color)
{
  $gradient_solid = (string) $background_color;
  $gradient_soft = 'rgba(198, 236, 244, 0.9)';
  $gradient_clear = 'rgba(198, 236, 244, 0)';

  if (preg_match('/^#([A-Fa-f0-9]{6})$/', $gradient_solid, $matches)) {
    $hex = $matches[1];
    $red = hexdec(substr($hex, 0, 2));
    $green = hexdec(substr($hex, 2, 2));
    $blue = hexdec(substr($hex, 4, 2));
    $gradient_soft = "rgba({$red}, {$green}, {$blue}, 0.9)";
    $gradient_clear = "rgba({$red}, {$green}, {$blue}, 0)";
  }

  return [
    'gradient_solid' => $gradient_solid,
    'gradient_soft' => $gradient_soft,
    'gradient_clear' => $gradient_clear,
  ];
}

/**
 * Mobile hero fade stops when the image sits above copy (Figma 2780:6668).
 */
function matrix_get_hero_image_split_mobile_bottom_fade_stops(): array
{
  return [
    'clear_end' => 62,
    'soft' => 80,
    'solid' => 100,
  ];
}

/**
 * Mobile hero fade stops when the image sits below copy (wide stacked layout).
 */
function matrix_get_hero_image_split_stacked_image_fade_stops(): array
{
  return [
    'solid_end' => 18,
    'soft' => 36,
    'clear' => 52,
  ];
}

function matrix_build_hero_image_split_mobile_bottom_fade_gradient(
  string $gradient_clear,
  string $gradient_soft,
  string $gradient_solid
): string {
  $stops = matrix_get_hero_image_split_mobile_bottom_fade_stops();

  return sprintf(
    'linear-gradient(to bottom, %s 0%%, %s %d%%, %s %d%%, %s %d%%)',
    $gradient_clear,
    $gradient_clear,
    $stops['clear_end'],
    $gradient_soft,
    $stops['soft'],
    $gradient_solid,
    $stops['solid']
  );
}

function matrix_build_hero_image_split_stacked_image_gradient(
  string $gradient_clear,
  string $gradient_soft,
  string $gradient_solid
): string {
  $stops = matrix_get_hero_image_split_stacked_image_fade_stops();

  return sprintf(
    'linear-gradient(to bottom, %s 0%%, %s %d%%, %s %d%%, %s %d%%, %s 100%%)',
    $gradient_solid,
    $gradient_solid,
    $stops['solid_end'],
    $gradient_soft,
    $stops['soft'],
    $gradient_clear,
    $stops['clear'],
    $gradient_clear
  );
}

function matrix_get_hero_with_breadcrumbs_image_split_grid_class_names($text_max_width = 'default')
{
  if (matrix_resolve_hero_with_breadcrumbs_text_max_width($text_max_width) === 'wide') {
    return matrix_get_hero_with_breadcrumbs_image_split_wide_container_class_names();
  }

  return 'flex w-full flex-col max-xl:px-0 lg:grid lg:min-h-[320px] lg:grid-cols-[minmax(0,1fr)_581px] lg:items-center';
}

function matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names($text_max_width = 'default')
{
  if (matrix_resolve_hero_with_breadcrumbs_text_max_width($text_max_width) === 'wide') {
    return 'relative order-2 mt-8 h-[240px] w-full overflow-hidden lg:mt-10 lg:h-[320px]';
  }

  return 'relative order-1 h-[240px] w-full overflow-hidden lg:order-2 lg:h-[320px] lg:border-l-2';
}

function matrix_resolve_hero_with_breadcrumbs_text_max_width($value)
{
  return (string) $value === 'wide' ? 'wide' : 'default';
}

function matrix_get_hero_with_breadcrumbs_text_max_width_class($text_max_width = 'default')
{
  return matrix_resolve_hero_with_breadcrumbs_text_max_width($text_max_width) === 'wide'
    ? 'max-w-[50rem]'
    : 'max-w-[599px]';
}

function matrix_get_hero_with_breadcrumbs_image_split_wide_container_class_names()
{
  return 'mx-auto flex w-full max-w-[1160px] flex-col py-16 max-xl:px-0';
}

function matrix_get_hero_with_breadcrumbs_image_split_heading_class_names($text_max_width = 'default')
{
  return matrix_get_hero_with_breadcrumbs_text_max_width_class($text_max_width) . ' font-primary text-[28px] font-bold leading-[28px] tracking-[-0.336px] text-[#08284B] lg:text-[48px] lg:leading-[48px] lg:tracking-[-0.576px]';
}

function matrix_get_hero_with_breadcrumbs_image_split_content_class_names($text_max_width = 'default')
{
  return implode(' ', [
    matrix_get_hero_with_breadcrumbs_text_max_width_class($text_max_width),
    'font-primary',
    'text-[18px]',
    'font-normal',
    'leading-[22.75px]',
    'tracking-[-0.09px]',
    'text-[#08284B]',
    'wp_editor',
    'lg:text-[18px]',
    'lg:leading-[28px]',
    'lg:tracking-normal',
    '[&_p:last-child]:mb-0',
    '[&_p:has(.btn)]:mt-5',
    'lg:[&_p:has(.btn)]:mt-6',
    '[&_a.btn]:inline-flex',
  ]);
}

function matrix_get_hero_with_breadcrumbs_image_split_column_class_names($text_max_width = 'default')
{
  if (matrix_resolve_hero_with_breadcrumbs_text_max_width($text_max_width) === 'wide') {
    return 'order-1 flex w-full flex-col gap-5 px-4 lg:gap-6 lg:px-0';
  }

  return 'order-2 flex w-full flex-col gap-5 px-4 py-4 lg:order-1 lg:gap-6 lg:pl-[52px] lg:pr-8 lg:py-0';
}

function matrix_get_hero_with_breadcrumbs_image_split_gradient_layout($text_max_width = 'default')
{
  return matrix_resolve_hero_with_breadcrumbs_text_max_width($text_max_width) === 'wide'
    ? 'stacked'
    : 'split';
}

function matrix_get_hero_with_breadcrumbs_primary_button_class_names()
{
  return 'btn inline-flex h-10 w-fit items-center justify-center gap-2 rounded-[6px] bg-[#024B79] px-3 text-[14px] font-medium leading-6 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

function matrix_get_hero_with_breadcrumbs_image_split_text_group_class_names()
{
  return 'flex w-full flex-col gap-3 lg:gap-[17px]';
}

/**
 * Normalize manual breadcrumb rows from ACF or flat item arrays.
 *
 * @param mixed $rows
 * @return array<int, array<string, string>>
 */
function matrix_prepare_hero_with_breadcrumbs_manual_items($rows)
{
  if (! is_array($rows)) {
    return [];
  }

  $items = [];

  foreach ($rows as $row) {
    if (! is_array($row)) {
      continue;
    }

    $link = isset($row['breadcrumb_link']) && is_array($row['breadcrumb_link']) ? $row['breadcrumb_link'] : $row;
    $title = trim((string) ($link['title'] ?? ''));
    $url = trim((string) ($link['url'] ?? ''));

    if ($title === '' || $url === '') {
      continue;
    }

    $items[] = [
      'title' => $title,
      'url' => $url,
      'target' => function_exists('matrix_normalize_link_target')
        ? matrix_normalize_link_target($url, (string) ($link['target'] ?? ''))
        : ((string) ($link['target'] ?? '') ?: '_self'),
    ];
  }

  return $items;
}

/**
 * Build a reusable hero config for utility and policy pages.
 *
 * @param string               $heading
 * @param string               $intro
 * @param array<string, mixed> $overrides
 * @return array<string, mixed>
 */
function matrix_get_utility_page_hero_config($heading, $intro = '', array $overrides = [])
{
  $heading = trim($heading);
  $intro = trim($intro);

  return array_merge([
    'layout_style' => 'image_split',
    'text_max_width' => 'wide',
    'show_breadcrumbs' => true,
    'breadcrumb_source' => 'manual',
    'manual_breadcrumb_items' => [
      [
        'title' => 'Home',
        'url' => home_url('/'),
        'target' => '',
      ],
    ],
    'current_crumb_label' => $heading,
    'heading_tag' => 'h1',
    'heading' => $heading,
    'content' => $intro !== '' ? '<p>' . esc_html($intro) . '</p>' : '',
    'primary_button' => null,
    'hero_image' => '',
    'background_color' => '#C6ECF4',
    'breadcrumb_background_color' => '#F1F8F9',
    'heading_color' => '#08284B',
    'text_color' => '#08284B',
    'accent_color' => '#6FC9C0',
    'aside_heading' => '',
  ], $overrides);
}

/**
 * Prepare the view model used by the hero with breadcrumbs partial.
 *
 * @param array<string, mixed> $config
 * @param int                  $post_id
 * @return array<string, mixed>
 */
function matrix_prepare_hero_with_breadcrumbs_view_model(array $config, $post_id = 0)
{
  $post_id = $post_id > 0 ? $post_id : (function_exists('get_the_ID') ? (int) get_the_ID() : 0);
  $layout_style = matrix_resolve_hero_with_breadcrumbs_layout_style($config['layout_style'] ?? 'image_split');
  $heading = trim((string) ($config['heading'] ?? ''));
  $heading_tag = trim((string) ($config['heading_tag'] ?? 'h1'));
  $content = $config['content'] ?? '';
  $hero_image = (int) ($config['hero_image'] ?? 0);
  $show_breadcrumbs = (bool) ($config['show_breadcrumbs'] ?? true);
  $breadcrumb_source = (string) ($config['breadcrumb_source'] ?? 'auto');
  $current_crumb_label = (string) ($config['current_crumb_label'] ?? '');
  $background_color = (string) ($config['background_color'] ?? '');
  $breadcrumb_background_color = (string) ($config['breadcrumb_background_color'] ?? '');
  $heading_color = (string) ($config['heading_color'] ?? '');
  $text_color = (string) ($config['text_color'] ?? '');
  $accent_color = (string) ($config['accent_color'] ?? '');
  $aside_heading = trim((string) ($config['aside_heading'] ?? ''));
  $text_max_width = matrix_resolve_hero_with_breadcrumbs_text_max_width($config['text_max_width'] ?? 'default');

  if ($heading === '') {
    if ($layout_style === 'title_accent') {
      $heading = 'Press Releases';
    } elseif ($layout_style === 'register_intro') {
      $heading = 'Register for Your Portal | Online Form';
    } else {
      $heading = 'About Us landing page title';
    }
  }

  if ($background_color === '') {
    if ($layout_style === 'title_accent') {
      $background_color = '#FBF8F3';
    } elseif ($layout_style === 'register_intro') {
      $background_color = '#FFFFFF';
    } else {
      $background_color = '#C6ECF4';
    }
  }

  if ($aside_heading === '' && $layout_style === 'register_intro') {
    $aside_heading = 'Already registered?';
  }

  if ($breadcrumb_background_color === '') {
    $breadcrumb_background_color = '#F1F8F9';
  }

  if ($heading_color === '') {
    $heading_color = $layout_style === 'title_accent' ? '#1E244B' : '#08284B';
  }

  if ($text_color === '') {
    $text_color = '#08284B';
  }

  if ($accent_color === '') {
    $accent_color = '#6FC9C0';
  }

  $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
  if (! in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h1';
  }

  $primary_button = function_exists('matrix_normalize_content_link')
    ? matrix_normalize_content_link($config['primary_button'] ?? null)
    : null;

  $manual_breadcrumb_items = matrix_prepare_hero_with_breadcrumbs_manual_items(
    $config['manual_breadcrumb_items'] ?? $config['manual_breadcrumbs'] ?? []
  );
  $auto_breadcrumb_data = matrix_get_auto_hero_breadcrumb_data($post_id);
  $breadcrumb_data = matrix_resolve_hero_breadcrumbs(
    $breadcrumb_source,
    $manual_breadcrumb_items,
    $current_crumb_label,
    $auto_breadcrumb_data
  );

  $section_id = trim((string) ($config['section_id'] ?? ''));
  if ($section_id === '') {
    $section_id = 'hero-with-breadcrumbs-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
  }

  $hero_heading_id = $section_id . '-heading';
  $hero_image_alt = '';
  $hero_image_title = '';

  if ($hero_image) {
    $hero_image_alt = (string) get_post_meta($hero_image, '_wp_attachment_image_alt', true);
    $hero_image_title = (string) get_the_title($hero_image);
  }

  if ($hero_image_alt === '') {
    $hero_image_alt = $hero_image_title !== '' ? $hero_image_title : $heading;
  }

  $gradient_vars = matrix_get_hero_with_breadcrumbs_gradient_vars($background_color);

  return [
    'section_id' => $section_id,
    'data_matrix_block' => trim((string) ($config['data_matrix_block'] ?? '')),
    'layout_style' => $layout_style,
    'heading' => $heading,
    'heading_tag' => $heading_tag,
    'content' => $content,
    'primary_button' => $primary_button,
    'hero_image' => $hero_image,
    'hero_image_alt' => $hero_image_alt,
    'hero_image_title' => $hero_image_title,
    'show_breadcrumbs' => $show_breadcrumbs,
    'breadcrumb_items' => is_array($breadcrumb_data['items']) ? $breadcrumb_data['items'] : [],
    'breadcrumb_current_label' => (string) ($breadcrumb_data['current_label'] ?? ''),
    'background_color' => $background_color,
    'breadcrumb_background_color' => $breadcrumb_background_color,
    'heading_color' => $heading_color,
    'text_color' => $text_color,
    'accent_color' => $accent_color,
    'aside_heading' => $aside_heading,
    'text_max_width' => $text_max_width,
    'text_max_width_class' => matrix_get_hero_with_breadcrumbs_text_max_width_class($text_max_width),    'hero_heading_id' => $hero_heading_id,
    'gradient_solid' => $gradient_vars['gradient_solid'],
    'gradient_soft' => $gradient_vars['gradient_soft'],
    'gradient_clear' => $gradient_vars['gradient_clear'],
  ];
}

/**
 * Render the hero with breadcrumbs section from a config array.
 *
 * @param array<string, mixed> $config
 * @param int                  $post_id
 * @return void
 */
function matrix_render_hero_with_breadcrumbs(array $config, $post_id = 0)
{
  get_template_part(
    'template-parts/partials/hero-with-breadcrumbs-section',
    null,
    [
      'hero' => matrix_prepare_hero_with_breadcrumbs_view_model($config, $post_id),
    ]
  );
}

function matrix_get_auto_hero_breadcrumb_data($post_id = 0)
{
  $post_id = $post_id ? (int) $post_id : (function_exists('get_the_ID') ? (int) get_the_ID() : 0);
  $items = array(
    array(
      'title' => 'Home',
      'url' => home_url('/'),
      'target' => '',
    ),
  );

  if (!$post_id) {
    return array(
      'items' => $items,
      'current_label' => '',
    );
  }

  $ancestor_ids = array_reverse(get_post_ancestors($post_id));

  foreach ($ancestor_ids as $ancestor_id) {
    $items[] = array(
      'title' => get_the_title($ancestor_id),
      'url' => get_permalink($ancestor_id),
      'target' => '',
    );
  }

  return array(
    'items' => $items,
    'current_label' => get_the_title($post_id),
  );
}
