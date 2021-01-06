<?php

namespace Drupal\nidirect_related_content;

/**
 * Provides methods for managing related content display.
 *
 * @package Drupal\nidirect_related_content
 */
class RelatedContentManager {

  public const CONTENT_ALL = 'all';
  public const CONTENT_THEMES = 'themes';
  public const CONTENT_NODES = 'nodes';

  /**
   * Theme content.
   *
   * @var array
   *   Array of theme content.
   */
  protected $content = [];

  /**
   * Fetches content for a theme.
   *
   * @param string|int $term_id
   *   Taxonomy term id.
   * @param string $content
   *   Return themes, nodes or both.
   *
   * @return $this
   */
  public function getThemeContent($term_id, $content = self::CONTENT_ALL): RelatedContentManager {
    return $this;
  }

  /**
   * Returns the current theme content as an array.
   *
   * @return array
   *   An array of theme content.
   */
  public function asArray(): array {
    return $this->content;
  }

  /**
   * Returns the current theme content as an render array.
   *
   * @return array
   *   A Drupal render array of theme content.
   */
  public function asRenderArray(): array {
    return [];
  }

}
