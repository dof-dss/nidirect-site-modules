<?php

namespace Drupal\nidirect_site_themes;

use Drupal\Core\Access\AccessResult;

class TaxonomyVocabAccess {

  /**
   * Access callback for common CUSTOM taxonomy operations.
   */
  public static function handleAccess($taxonomy_vocabulary = NULL) {
    // Admin: always.
    if (\Drupal::currentUser()->hasPermission('administer taxonomy')) {
      return AccessResult::allowed();
    }
    else {
      // Check permissions defined by taxonomy_access_fix; defined per vocab.
      if (\Drupal::currentUser()->hasPermission('add terms in ' . $taxonomy_vocabulary)) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
