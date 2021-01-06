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

    if ($content === 'themes') {
      $this->getThemeThemes($term_id);
    } elseif ($content === 'nodes') {

    } else {

    }

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

  protected function getThemeThemes($term_id) {

  }

  /**
   * Returns an array of term id's with a campaign page.
   *
   * @return array
   *   Term ID indexed array of node objects.
   */
  protected function getTermsWithCampaignPages() {
    // Array of terms with campaign pages.
    $terms = [];

    // Fetch every published campaign page.
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'landing_page')
      ->condition('status', 1);
    $nids = $query->execute();

    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

    // Construct the terms array assigning the campaign node to each
    // overridden tid.
    foreach ($nodes as $node) {
      if (isset($node->get('field_subtheme')->target_id)) {
        $terms[$node->get('field_subtheme')->getString()] = $node;
      }
    }

    return $terms;
  }

}
