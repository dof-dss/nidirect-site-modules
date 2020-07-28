<?php

namespace Drupal\nidirect_taxoman;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountProxy;

/**
 * Provides additional permissions for entities provided by Taxonomy module.
 */
class TaxonomyNavigatorAccess implements AccessInterface {

  public function canViewVocabularyTerms($vocabulary, $taxonomy_term, AccountProxy $account) {

    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('taxonomy_access_fix')){
      return AccessResult::allowedIf($account->hasPermission('view terms in ' . $vocabulary));
    }
    return AccessResult::allowed();
  }

}
