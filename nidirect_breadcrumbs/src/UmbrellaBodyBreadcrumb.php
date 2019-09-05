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
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UmbrellaBodyBreadcrumb implements BreadcrumbBuilderInterface {

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

    if ($node = $route_match->getParameter('node')) {
      if (is_object($node) == FALSE) {
        $node = $this->entityTypeManager->getStorage('node')->load($node);
      }
      $bundle = $node->bundle();
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
