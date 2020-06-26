<?php

namespace Drupal\nidirect_common\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Override from base class to set lighter
   * event priority than linkit module.
   *
   * See https://www.drupal.org/docs/8/creating-custom-modules/subscribe-to-and-dispatch-events#s-event-subscriber-priorities.
   *
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -100];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Swap Linkit\AutocompleteController for nidirect_common\LinkitAutocompleteController
    // so we can effectively manipulate the content of the responses due to lack of
    // preprocess and interfaces.
    if ($route = $collection->get('linkit.autocomplete')) {
      $route->setDefaults(['_controller' => '\Drupal\nidirect_common\Controller\LinkitAutocompleteController::autocomplete']);
    }
  }

}
