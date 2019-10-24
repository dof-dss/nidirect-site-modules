<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for Publication pages/content.
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PublicationBreadcrumb implements BreadcrumbBuilderInterface {

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
    $this->node = $route_match->getParameter('node');

    if (!empty($this->node)) {
      if (is_object($this->node) == FALSE) {
        $this->node = $this->entityTypeManager->getStorage('node')->load($this->node);
      }

      $match = $this->node->bundle() == 'publication';
    }
    else {
      // Also match on recipe search page.
      $match = $route_match->getRouteName() == 'view.publications.search_page';
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }

}
