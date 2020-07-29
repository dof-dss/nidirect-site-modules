<?php

namespace Drupal\nidirect_taxonomy_navigator;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;

/**
 * Provides additional permissions for entities provided by Taxonomy module.
 */
class TaxonomyNavigatorAccess implements AccessInterface {

  public static function canViewTerms($vocabulary) {
    return self::taxonomyAccess('view', $vocabulary);
  }

  public static function canEditTerms($vocabulary) {
    return self::taxonomyAccess('edit', $vocabulary);
  }

  public static function canDeleteTerms($vocabulary) {
    return self::taxonomyAccess('delete', $vocabulary);
  }

  public static function canReorderTerms($vocabulary) {
    return self::taxonomyAccess('reorder', $vocabulary);
  }

  public static function taxonomyAccess($type, $vocabulary) {
    $moduleHandler = \Drupal::service('module_handler');
    $taf_enabled = $moduleHandler->moduleExists('taxonomy_access_fix');

    switch ($type) {
      case 'view':
        $permission_query = ($taf_enabled) ? 'view terms in ' . $vocabulary : 'Access the taxonomy vocabulary overview page';
        break;
      case 'edit':
        $permission_query = ($taf_enabled) ? 'edit terms in ' . $vocabulary : $vocabulary . ': Edit terms';
        break;
      case 'delete':
        $permission_query = ($taf_enabled) ? 'delete terms in ' . $vocabulary : $vocabulary . ': Delete terms';
        break;
      case 'reorder':
        $permission_query = ($taf_enabled) ? 'reorder terms in ' . $vocabulary : $vocabulary . ': Edit terms';
        break;
    }

    if (!empty($permission_query)) {
      $result = \Drupal::currentUser()->hasPermission($permission_query);
      return $result ? AccessResult::allowed() : AccessResult::forbidden();
    }

    return AccessResult::forbidden();
  }
}
