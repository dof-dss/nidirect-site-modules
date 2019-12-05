<?php

namespace Drupal\nidirect_common;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class InvalidateTaxonomyListCacheTags.
 */
class InvalidateTaxonomyListCacheTags {

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new \Drupal\Core\Menu\MenuTreeStorage.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(CacheTagsInvalidatorInterface $cache_tags_invalidator,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Invalidate custom cache tags for this entity.
   */
  public function invalidateForEntity(EntityInterface $entity) {
    // If a node references any point in the 'site themes'
    // vocabulary, make sure that the appropriate taxonomy
    // cache tags are invalidated.
    $field_list = ['field_subtheme', 'field_site_themes'];
    $taxonomy_tags = [];
    foreach ($field_list as $thisfield) {
      if ($entity->hasField($thisfield)) {
        $tid = $entity->get($thisfield)->target_id;
        // If landing page, get parent.
        if (!empty($tid)) {
          $tid = $this->checkLandingPageParent($entity, $tid);
          $taxonomy_tags[] = 'taxonomy_term_list:' . $tid;
        }
        // If this is an update, invalidate cache tag for
        // original taxonomy term as well.
        if (isset($entity->original)) {
          $tid = $entity->original->get($thisfield)->target_id;
          if (!empty($tid)) {
            $tid = $this->checkLandingPageParent($entity, $tid);
            $taxonomy_tags[] = 'taxonomy_term_list:' . $tid;
          }
        }
      }
    }
    if (count($taxonomy_tags) > 0) {
      $this->cacheTagsInvalidator->invalidateTags($taxonomy_tags);
    }
  }

  /**
   * Get parent taxonomy term for landing pages.
   */
  private function checkLandingPageParent(EntityInterface $entity, int $tid) {
    // Landing page is a special case.
    if ($entity->type->target_id == 'landing_page') {
      // In this case we want to invalidate the cache tag for
      // the parent of the current term.
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
      if (isset($term->parent->target_id)) {
        $tid = $term->parent->target_id;
      }
    }
    return $tid;
  }

}
