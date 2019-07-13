<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for content including:
 * - Article
 * - Application
 * - Publications
 *
 * In the format:
 * > Themes /themes
 * > [node:field_subtheme] /themes/[entity:taxonomy_term/tid]
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class ThemesBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    $applies_to_types = [
      'article',
      'application',
      'publication',
    ];

    if ($node = $route_match->getParameter('node')) {
      $match = in_array($node->bundle(), $applies_to_types);
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $links[] = Link::createFromRoute(t('Home'), '<front>');

    $node = $route_match->getParameter('node');

    if ($node->hasField('field_subtheme')) {
      // Subtheme is a mandatory field so should always be present.
      $theme_tid = $node->field_subtheme->target_id;

      // Find parent terms, if any and begin to build up link chain.
      $ancestors = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($theme_tid);
      // Flip so we have oldest > youngest ancestors.
      $ancestors = array_reverse($ancestors, TRUE);

      foreach ($ancestors as $term) {
        $links[] = Link::fromTextandUrl($term->label(), Url::fromUri('entity:taxonomy_term/' . $term->id()));
      }
    }

    // Assemble a new breadcrumb object, add the links and set
    // a URL path cache context so it varies as you move from one
    // set of content to another.
    $breadcrumb = new Breadcrumb();
    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }

}
