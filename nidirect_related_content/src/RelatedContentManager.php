<?php

namespace Drupal\nidirect_related_content;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\node\NodeTypeInterface;

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
  protected $content;

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
  public function getThemeContent($content = self::CONTENT_ALL): RelatedContentManager {

    if ($content === 'themes') {
      $this->getThemeThemes();
    } elseif ($content === 'nodes') {
      $this->getThemeNodes();
    } else {
      $this->getThemeThemes();
      $this->getThemeNodes();
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

  protected function getThemeNodes() {
    // Render the 'articles by term' view and process the results.

    $articles_view = views_embed_view('articles_by_term', 'articles_by_term_embed');
    \Drupal::service('renderer')->renderRoot($articles_view);
    foreach ($articles_view['view_build']['#view']->result as $row) {

      // If we are dealing with a book entry and it's lower than the first page,
      // don't add to the list of articles for the taxonomy term.
      if (!empty($row->_entity->book) && $row->_entity->book['depth'] > 1) {
        continue;
      }

      // External link nodes' titles should be replaced with the link value they contain.
      if ($row->_entity->bundle() === 'external_link') {
        $title = $row->_entity->field_link->title;
        $url = Url::fromUri($row->_entity->field_link->uri);
      }
      else {
        $title = $row->_entity->getTitle();
        $url = Url::fromRoute('entity.node.canonical', ['node' => $row->nid]);
      }

      $this->content[] = [
        'entity' => $row->_entity,
        'title' => $title,
        'url' => $url,
      ];
    }

  }

  protected function getThemeThemes() {
    $campaign_terms = $this->getTermsWithCampaignPages();

    $subtopics_view = views_embed_view('site_subtopics', 'by_topic_simple_embed');
    \Drupal::service('renderer')->renderRoot($subtopics_view);

    foreach ($subtopics_view['view_build']['#view']->result as $row) {
      // Do we need to override?
      if (array_key_exists($row->tid, $campaign_terms)) {
        // This will be a link to a campaign (landing page).
        $this->content[] = [
          'entity' => $campaign_terms[$row->tid],
          'title' => $campaign_terms[$row->tid]->getTitle(),
          'url' => Url::fromRoute('entity.node.canonical', ['node' => $campaign_terms[$row->tid]->id()]),
        ];
        continue;
      }
      // This will be a link to another taxonomy page.
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($row->tid);
      $this->content[] = [
        'entity' => $term,
        'title' => $term->getName(),
        'url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $campaign_terms[$term->id()]]),
      ];
    }
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
