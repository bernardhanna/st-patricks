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

function matrix_get_auto_hero_breadcrumb_data($post_id = 0)
{
  $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
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
