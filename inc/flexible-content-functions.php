<?php
// File: inc/flexible-content-functions.php

if (! function_exists('matrix_normalize_attachment_id')) {
  /**
   * @param mixed $value
   */
  function matrix_normalize_attachment_id($value): int
  {
    if (is_numeric($value)) {
      return max(0, (int) $value);
    }

    if (is_array($value)) {
      return max(0, (int) ($value['ID'] ?? $value['id'] ?? 0));
    }

    return 0;
  }
}

if (! function_exists('matrix_extract_page_hero_image_id_from_rows')) {
  /**
   * @param mixed $rows
   */
  function matrix_extract_page_hero_image_id_from_rows($rows): int
  {
    if (! is_array($rows)) {
      return 0;
    }

    foreach ($rows as $row) {
      if (! is_array($row)) {
        continue;
      }

      if (($row['acf_fc_layout'] ?? '') !== 'hero_with_breadcrumbs') {
        continue;
      }

      $hero_id = matrix_normalize_attachment_id($row['hero_image'] ?? 0);

      if ($hero_id > 0) {
        return $hero_id;
      }
    }

    return 0;
  }
}

if (! function_exists('matrix_get_page_hero_image_id')) {
  /**
   * Hero image for the current page's flexi hero block (cached per request).
   */
  function matrix_get_page_hero_image_id($post_id = null): int
  {
    static $cache = [];

    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1) {
      return 0;
    }

    if (array_key_exists($post_id, $cache)) {
      return $cache[$post_id];
    }

    if (
      isset($GLOBALS['matrix_page_hero_image_ids'])
      && is_array($GLOBALS['matrix_page_hero_image_ids'])
      && array_key_exists($post_id, $GLOBALS['matrix_page_hero_image_ids'])
    ) {
      return $cache[$post_id] = (int) $GLOBALS['matrix_page_hero_image_ids'][$post_id];
    }

    if (! function_exists('get_field')) {
      return $cache[$post_id] = 0;
    }

    $rows = get_field('flexible_content_blocks', $post_id);

    return $cache[$post_id] = matrix_extract_page_hero_image_id_from_rows($rows);
  }
}

if (! function_exists('matrix_exclude_page_hero_image')) {
  /**
   * Clear an image field value when it matches the page hero image.
   *
   * @param mixed $image
   * @return mixed
   */
  function matrix_exclude_page_hero_image($image, $post_id = null)
  {
    $image_id = matrix_normalize_attachment_id($image);

    if ($image_id < 1) {
      return $image;
    }

    $hero_id = matrix_get_page_hero_image_id($post_id);

    if ($hero_id < 1 || $image_id !== $hero_id) {
      return $image;
    }

    if (is_array($image)) {
      return null;
    }

    if (is_numeric($image)) {
      return 0;
    }

    return null;
  }
}

if (! function_exists('matrix_exclude_page_hero_from_related_cards')) {
  /**
   * @param array<int, array<string, mixed>> $cards
   * @return array<int, array<string, mixed>>
   */
  function matrix_exclude_page_hero_from_related_cards(array $cards, $post_id = null): array
  {
    $hero_id = matrix_get_page_hero_image_id($post_id);

    if ($hero_id < 1) {
      return $cards;
    }

    foreach ($cards as $index => $card) {
      if (! is_array($card)) {
        continue;
      }

      if ((int) ($card['image_id'] ?? 0) === $hero_id) {
        $cards[$index]['image_id'] = 0;
      }
    }

    return $cards;
  }
}

if (! function_exists('matrix_strip_duplicate_hero_images_from_flexi_rows')) {
  /**
   * Persistently clear non-hero flexi images that reuse the page hero.
   *
   * @param mixed $rows
   * @return array{rows: array<int, array<string, mixed>>, changed: bool}
   */
  function matrix_strip_duplicate_hero_images_from_flexi_rows($rows): array
  {
    if (! is_array($rows)) {
      return ['rows' => [], 'changed' => false];
    }

    $hero_id = matrix_extract_page_hero_image_id_from_rows($rows);
    $changed = false;

    if ($hero_id < 1) {
      return ['rows' => $rows, 'changed' => false];
    }

    foreach ($rows as $index => $row) {
      if (! is_array($row)) {
        continue;
      }

      $layout = (string) ($row['acf_fc_layout'] ?? '');

      if ($layout === 'hero_with_breadcrumbs') {
        continue;
      }

      foreach (['image', 'background_image', 'hero_image'] as $field) {
        if (! array_key_exists($field, $row)) {
          continue;
        }

        if (matrix_normalize_attachment_id($row[$field]) !== $hero_id) {
          continue;
        }

        $rows[$index][$field] = is_array($row[$field]) ? null : '';
        $changed = true;
      }

      if ($layout === 'related_cards' && ! empty($row['cards']) && is_array($row['cards'])) {
        foreach ($row['cards'] as $card_index => $card) {
          if (! is_array($card) || ! array_key_exists('image', $card)) {
            continue;
          }

          if (matrix_normalize_attachment_id($card['image']) !== $hero_id) {
            continue;
          }

          $rows[$index]['cards'][$card_index]['image'] = is_array($card['image']) ? null : '';
          $changed = true;
        }
      }

      if ($layout === 'story_slider' && ! empty($row['slides']) && is_array($row['slides'])) {
        foreach ($row['slides'] as $slide_index => $slide) {
          if (! is_array($slide) || ! array_key_exists('image', $slide)) {
            continue;
          }

          if (matrix_normalize_attachment_id($slide['image']) !== $hero_id) {
            continue;
          }

          $rows[$index]['slides'][$slide_index]['image'] = is_array($slide['image']) ? null : '';
          $changed = true;
        }
      }
    }

    return ['rows' => $rows, 'changed' => $changed];
  }
}

/**
 * Load Flexible Content Templates
 * 
 * Automatically loads flexible content templates based on the layout name
 */
function load_flexible_content_templates($post_id = null)
{
  // If no post_id is provided, use the current page's ID
  if (!$post_id) {
    $post_id = is_home() ? get_option('page_for_posts') : get_the_ID();
  }

  $post_id = (int) $post_id;

  // Debugging: Log which page ID is being used
  error_log("Loading Flexible Content for Post ID: " . $post_id);

  if ($post_id && have_rows('flexible_content_blocks', $post_id)) {
    if (! isset($GLOBALS['matrix_page_hero_image_ids']) || ! is_array($GLOBALS['matrix_page_hero_image_ids'])) {
      $GLOBALS['matrix_page_hero_image_ids'] = [];
    }

    // Resolve once before the ACF row loop so templates can suppress hero reuse
    // without calling get_field() mid-loop.
    $flexi_rows = function_exists('get_field') ? get_field('flexible_content_blocks', $post_id) : null;
    $GLOBALS['matrix_page_hero_image_ids'][$post_id] = matrix_extract_page_hero_image_id_from_rows($flexi_rows);

    $row_index = 0;
    $flex_field = class_exists('Matrix_Export') ? Matrix_Export::FLEX_FIELD : 'flexible_content_blocks';
    while (have_rows('flexible_content_blocks', $post_id)) : the_row();
      // Honour content-gathering "Disable this block" until Publish removes the row.
      if (
        function_exists('matrix_export_is_block_disabled')
        && matrix_export_is_block_disabled((int) $post_id, $flex_field, $row_index)
      ) {
        $row_index++;
        continue;
      }

      $layout = get_row_layout();
      $template_path = get_template_directory() . '/template-parts/flexi/' . $layout . '.php';

      if (file_exists($template_path)) {
        get_template_part('template-parts/flexi/' . $layout);
      } else {
        error_log("Missing flexible content template file: {$layout}.php");
      }
      $row_index++;
    endwhile;
  } else {
    error_log("No ACF Flexible Content Blocks found for Post ID: " . $post_id);
  }
}
/**
 * Get Available Flexible Content Layouts
 * 
 * Returns an array of available layout names based on template files
 */
function get_available_flexi_layouts()
{
  $flexi_path = get_template_directory() . '/template-parts/flexi/';
  $files = glob($flexi_path . '*.php');

  return array_map(function ($file) {
    return basename($file, '.php');
  }, $files);
}

/**
 * Validate Flexible Content Layout
 * 
 * Ensures that ACF field definitions have corresponding template files
 */
function validate_flexi_layout($layout_name)
{
  $available_layouts = get_available_flexi_layouts();
  if (!in_array($layout_name, $available_layouts)) {
    error_log("Warning: ACF flexible content layout '{$layout_name}' has no corresponding template file");
    return false;
  }
  return true;
}

function force_hero_as_first_block($value, $post_id, $field)
{
  if ($field['name'] === 'flexible_content_layout') {
    $hero_block = [];
    $other_blocks = [];

    foreach ($value as $block) {
      if ($block['acf_fc_layout'] === 'hero_001') {
        $hero_block = $block;
      } else {
        $other_blocks[] = $block;
      }
    }

    // Always place hero first
    if (!empty($hero_block)) {
      array_unshift($other_blocks, $hero_block);
    }

    return $other_blocks;
  }
  return $value;
}

function apply_acf_to_blog_page($query)
{
  if (!is_admin() && $query->is_home() && $query->is_main_query()) {
    $query->set('page_id', get_option('page_for_posts'));
  }
}

if (function_exists('add_filter')) {
  add_filter('acf/update_value/name=flexible_content_layout', 'force_hero_as_first_block', 10, 3);
}

if (function_exists('add_action')) {
  add_action('pre_get_posts', 'apply_acf_to_blog_page');
}
