<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for content including:
 * - News
 *
 * In the format:
 * > Home
 * > News
 *
 * > <front>
 * > /news
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class NewsBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    if ($node = $route_match->getParameter('node')) {
      $match = $node->bundle() == 'news';
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $breadcrumb = new Breadcrumb();
    $links[] = Link::createFromRoute(t('Home'), '<front>');
    $links[] = Link::fromTextandUrl(t('News'), Url::fromUserInput('/news'));

    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }

}
