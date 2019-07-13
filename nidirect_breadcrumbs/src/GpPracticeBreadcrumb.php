<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for content including:
 * - GP Practice
 *
 * In the format:
 * > Home
 * > Health and well-being
 * > Health services
 * > Doctors, dentists and other health services
 * > Find a GP practice
 *
 * > <front>
 * > information-and-services/health-and-well-being
 * > information-and-services/health-and-well-being/health-services
 * > information-and-services/health-services/doctors-dentists-and-other-health-services
 * > services/gp-practices
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class GpPracticeBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    if ($node = $route_match->getParameter('node')) {
      $match = $node->bundle() == 'gp_practice';
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $breadcrumb = new Breadcrumb();
    $links[] = Link::createFromRoute(t('Home'), '<front>');
    $links[] = Link::fromTextandUrl(t('Health and well-being'), Url::fromUri('entity:taxonomy_term/22'));
    $links[] = Link::fromTextandUrl(t('Health services'), Url::fromUri('entity:taxonomy_term/262'));
    $links[] = Link::fromTextandUrl(t('Doctors, dentists and other health services'), Url::fromUri('entity:taxonomy_term/263'));
    $links[] = Link::fromTextandUrl(t('Find a GP practice'), Url::fromUserInput('/services/gp-practices'));

    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    // Add cache tags so that if any entities above change, we can regenerate the breadcrumb too.
    $breadcrumb->addCacheTags([
      'taxonomy_term:22',
      'taxonomy_term:262',
      'taxonomy_term:262',
    ]);

    return $breadcrumb;
  }

}
