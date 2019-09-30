<?php

namespace Drupal\nidirect_site_themes\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
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
      'taxonomy_manager.admin_vocabulary',
      'taxonomy_manager.admin_vocabulary.add',
      'taxonomy_manager.admin_vocabulary.move',
      'taxonomy_manager.admin_vocabulary.search',
      'taxonomy_manager.admin_vocabulary.searchautocomplete',
    ];

    foreach ($vocab_routes_to_alter as $route_id) {
      $route = $collection->get($route_id);
      $route->setRequirements([
        '_custom_access' => '\Drupal\nidirect_site_themes\TaxonomyVocabAccess::handleAccess',
      ]);
    }

    // Amend overview and subtree route access to be a little more relaxed.
    $general_routes = [
      'taxonomy_manager.admin',
      'taxonomy_manager.subTree',
    ];

    foreach ($general_routes as $route_id) {
      $route = $collection->get($route_id);
      $route->setRequirement('_permission', 'access taxonomy overview');
    }
  }

}
