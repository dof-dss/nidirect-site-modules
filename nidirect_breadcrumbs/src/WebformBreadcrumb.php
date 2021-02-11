<?php

namespace Drupal\nidirect_breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Generates the breadcrumb trail for webform pages.
 *
 * @package Drupal\nidirect_breadcrumbs
 */
class WebformBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    $route_name = $route_match->getRouteName();

    // Better to match webforms here than have to put all urls
    // in the null breadcrumb matches.
    if (($route_name == 'entity.webform.confirmation')
      || ($route_name == 'entity.node.webform.confirmation')
      || ($route_name == 'entity.webform.canonical')) {
      $match = TRUE;
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    // Return an empty breadcrumb.
    $breadcrumb = new Breadcrumb();
    $breadcrumb->setLinks([]);
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }

}
