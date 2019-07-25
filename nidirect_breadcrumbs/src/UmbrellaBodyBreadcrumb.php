<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for content including:
 * - Umbrella body
 *
 * In the format:
 * > Home
 * > Crime, justice and the law
 * > AccessNI criminal record checks
 * > Find an AccessNI umbrella body
 *
 * > <front>
 * > information-and-services/crime-justice-and-law
 * > information-and-services/crime-justice-and-law/accessni-criminal-record-checks
 * > accessni/find-an-umbrella-body
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class UmbrellaBodyBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    if ($node = $route_match->getParameter('node')) {
      $match = $node->bundle() == 'umbrella_body';
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $breadcrumb = new Breadcrumb();
    $links[] = Link::createFromRoute(t('Home'), '<front>');
    $links[] = Link::fromTextandUrl(t('Crime, justice and the law'), Url::fromUri('entity:taxonomy_term/24'));
    $links[] = Link::fromTextandUrl(t('AccessNI criminal record checks'), Url::fromUri('entity:node/4007'));
    $links[] = Link::fromTextandUrl(t('Find an AccessNI umbrella body'), Url::fromUri('entity:node/7712'));

    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    // Add cache tags so that if any entities above change, we can regenerate the breadcrumb too.
    $breadcrumb->addCacheTags([
      'taxonomy_term:24',
      'node:4007',
      'node:7712',
    ]);

    return $breadcrumb;
  }

}
