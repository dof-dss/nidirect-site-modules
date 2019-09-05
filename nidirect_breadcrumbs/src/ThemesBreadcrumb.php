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
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ThemesBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

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
      if (is_object($node) == FALSE) {
        $node = $this->entityTypeManager->getStorage('node')->load($node);
      }
      $bundle = $node->bundle();

      $match = in_array($bundle, $applies_to_types);
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $cache_tags = [];

    $links[] = Link::createFromRoute(t('Home'), '<front>');
    $links[] = Link::fromTextandUrl(t('Themes'), Url::fromUserInput('/themes'));

    $node = $route_match->getParameter('node');

    if ($node->hasField('field_subtheme')) {
      $theme_tid = $node->field_subtheme->target_id;

      // Find parent terms, if any and begin to build up link chain.
      $ancestors = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($theme_tid);
      // Flip so we have oldest > youngest ancestors.
      $ancestors = array_reverse($ancestors, TRUE);

      foreach ($ancestors as $term) {
        $links[] = Link::fromTextandUrl($term->label(), Url::fromUri('entity:taxonomy_term/' . $term->id()));
        $cache_tags[] = 'taxonomy_term:' . $term->id();
      }
    }

    // Assemble a new breadcrumb object, add the links and set
    // a URL path cache context so it varies as you move from one
    // set of content to another.
    $breadcrumb = new Breadcrumb();
    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    if (!empty($cache_tags)) {
      $breadcrumb->addCacheTags($cache_tags);
    }

    return $breadcrumb;
  }

}
