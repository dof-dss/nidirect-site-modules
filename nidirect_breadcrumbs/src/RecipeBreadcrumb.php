<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for content including:
 * - Recipe
 *
 * In the format:
 * > Home
 * > Health and well-being
 * > Eat well
 * > Recipes
 *
 * > <front>
 * > information-and-services/health-and-well-being
 * > information-and-services/health-and-well-being/eat-well
 * > information-and-services/eat-well/recipes
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class RecipeBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    if ($node = $route_match->getParameter('node')) {
      $match = $node->bundle() == 'recipe';
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
    $links[] = Link::fromTextandUrl(t('Eat well'), Url::fromUri('entity:taxonomy_term/382'));
    $links[] = Link::fromTextandUrl(t('Recipes'), Url::fromUserInput('/information-and-services/eat-well/recipes'));

    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    // Add cache tags so that if any entities above change, we can regenerate the breadcrumb too.
    $breadcrumb->addCacheTags([
      'taxonomy_term:22',
      'taxonomy_term:382',
      // 'view:view_id:display?'
    ]);

    return $breadcrumb;
  }

}
