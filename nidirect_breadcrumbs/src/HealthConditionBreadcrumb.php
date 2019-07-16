<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for content including:
 * - Health condition
 *
 * In the format:
 * > Home
 * > Health and well-being
 * > Illnesses and conditions
 * > A to Z
 *
 * > <front>
 * > information-and-services/health-and-wellbeing
 * > information-and-services/health-and-wellbeing/illnesses-and-conditions
 * > services/health-conditions-a-z
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class HealthConditionBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    if ($node = $route_match->getParameter('node')) {
      $match = $node->bundle() == 'health_condition';
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $breadcrumb = new Breadcrumb();
    $links[] = Link::createFromRoute(t('Home'), '<front>');
    $links[] = Link::fromTextandUrl(t('Health and wellbeing'), Url::fromUri('entity:taxonomy_term/22'));
    $links[] = Link::fromTextandUrl(t('Illnesses and conditions'), Url::fromUri('entity:node/7387'));
    $links[] = Link::fromTextandUrl(t('A to Z'), Url::fromUserInput('/services/health-conditions-a-z'));

    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    // Add cache tags so that if any entities above change, we can regenerate the breadcrumb too.
    $breadcrumb->addCacheTags([
      'taxonomy_term:22',
      'node:7387',
    ]);

    return $breadcrumb;
  }

}
