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
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RecipeBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Node object, or null if on a non-node page.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

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

    $route_name = $route_match->getRouteName();

    if ($route_name == 'entity.node.canonical' && !empty($route_match->getParameter('node'))) {
      $this->node = $route_match->getParameter('node');

      if ($this->node instanceof NodeInterface == FALSE) {
        $this->node = $this->entityTypeManager->getStorage('node')->load($this->node);
      }

      if (!empty($this->node)) {
        $match = $this->node->bundle() == 'recipe';
      }
    }

    if ($route_name == 'view.recipes.search_page') {
      // Also match on recipe search page.
      $match = TRUE;
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

    if ($this->node) {
      $links[] = Link::fromTextandUrl(t('Recipes'), Url::fromRoute('view.recipes.search_page'));
    }

    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    // Add cache tags so that if any entities above change, we can regenerate the breadcrumb too.
    $breadcrumb->addCacheTags([
      'taxonomy_term:22',
      'taxonomy_term:382',
    ]);

    return $breadcrumb;
  }

}
