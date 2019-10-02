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
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HealthConditionBreadcrumb implements BreadcrumbBuilderInterface {

  /**
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

      $match = $this->node->bundle() == 'health_condition';
    }
    else {
      // Also match on recipe search page.
      $match = $route_match->getRouteName() == 'view.health_conditions.search_page';
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

    if ($this->node) {
      $links[] = Link::fromTextandUrl(t('A to Z'), Url::fromRoute('view.health_conditions.search_page'));
    }

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
