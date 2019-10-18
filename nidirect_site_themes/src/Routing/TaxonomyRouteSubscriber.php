<?php

namespace Drupal\nidirect_site_themes\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class TaxonomyRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    // Alter the generic, high level permissions to check whether
    // the current user can something basic with this vocabulary: eg: edit/add terms.
    // We make use of taxonony_access_fix's dynamic permissions for this as D8 core's
    // permissions are too coarse to allow us to limit control to one specific vocabulary.
    $vocab_routes_to_alter = [
      'taxonomy_manager.admin_vocabulary.add',
      'taxonomy_manager.admin_vocabulary.delete',
      'taxonomy_manager.admin_vocabulary.move',
    ];

    $vocab_routes_for_view_only = [
      'taxonomy_manager.admin_vocabulary',
      'taxonomy_manager.subTree',
      'taxonomy_manager.admin_vocabulary.search',
      'taxonomy_manager.admin_vocabulary.searchautocomplete',
    ];

    // Handle top-level admin route by piggybacking on core taxonomy term permission.
    $route = $collection->get('taxonomy_manager.admin');
    $route->setRequirement('_permission', 'access taxonomy overview');

    // Per-vocab routes need to be sympathetic to vocab id and operation.
    foreach ($vocab_routes_to_alter as $route_id) {
      $route = $collection->get($route_id);
      $route->setRequirements([
        '_custom_access' => '\Drupal\nidirect_site_themes\TaxonomyVocabAccess::handleAccess',
      ]);

      $route_op = explode('.', $route_id)[2];
      if (!empty($route_op)) {
        $route->setOption('op', $route_op);
      }
    }

    // These only need 'view' access.
    foreach ($vocab_routes_for_view_only as $route_id) {
      $route = $collection->get($route_id);
      $route->setRequirements([
        '_custom_access' => '\Drupal\nidirect_site_themes\TaxonomyVocabAccess::handleAccess',
      ]);
      $route->setOption('op', 'view');
    }

    // Set the permission so that to see the core Taxonomy vocabs overview you need the
    // administer taxonomy permission, rather than that or the access taxonomy overview
    // permission which we're extending to cover Taxonomy Manager.
    if ($route = $collection->get('entity.taxonomy_vocabulary.collection')) {
      $route->setRequirements([
        '_permission' => 'administer taxonomy',
      ]);
      $route->setOption('_access_checks', 'access_check.permission');
    }
  }

}
