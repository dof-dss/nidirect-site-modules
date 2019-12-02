<?php

namespace Drupal\nidirect_common;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

/**
 * Class InvalidateTaxonomyListCacheTags.
 */
class InvalidateTaxonomyListCacheTags {

  /**
   * Constructs a new \Drupal\Core\Menu\MenuTreeStorage.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   */
  public function __construct(CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * Invalidate custom cache tags for this entity.
   */
  public function invalidateForEntity($entity) {
    // If a node references any point in the 'site themes'
    // vocabulary, make sure that the appropriate taxonomy
    // cache tags are invalidated.
    $field_list = ['field_subtheme', 'field_site_themes'];
    $taxonomy_tags = [];
    foreach ($field_list as $thisfield) {
      if ($entity->hasField($thisfield)) {
        $tid = $entity->get($thisfield)->target_id;
        if (isset($tid)) {
          $taxonomy_tags[] = 'taxonomy_term_list:' . $tid;
        }
        // If this is an update, invalidate cache tag for
        // original taxonomy term as well.
        if (isset($entity->original)) {
          $tid = $entity->original->get($thisfield)->target_id;
          if (isset($tid)) {
            $taxonomy_tags[] = 'taxonomy_term_list:' . $tid;
          }
        }
      }
    }
    if (count($taxonomy_tags) > 0) {
      $this->cacheTagsInvalidator->invalidateTags($taxonomy_tags);
    }
  }

}
