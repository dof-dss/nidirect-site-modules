<?php

namespace Drupal\nidirect_site_themes;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\Entity\Vocabulary;

class TaxonomyVocabAccess {

  use StringTranslationTrait;

  /**
   * Access callback for common CUSTOM taxonomy operations.
   */
  public static function handleAccess($taxonomy_vocabulary = NULL) {
    // Admin: always.
    if (\Drupal::currentUser()->hasPermission('administer taxonomy')) {
      return AccessResult::allowed();
    }
    else {
      // What option have we set? It's based on path.
      $op = \Drupal::routeMatch()->getRouteObject()->getOption('op');

      if (\Drupal::currentUser()->hasPermission("$op terms in $taxonomy_vocabulary")) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

  /**
   * Get permissions.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    $permissions = [];

    foreach (Vocabulary::loadMultiple() as $vocabulary) {
      $id = $vocabulary->id();
      $args = ['%vocabulary' => $vocabulary->label()];

      $permissions["view terms in $id"] = ['title' => $this->t('%vocabulary: View terms', $args)];
    }

    return $permissions;
  }

}
