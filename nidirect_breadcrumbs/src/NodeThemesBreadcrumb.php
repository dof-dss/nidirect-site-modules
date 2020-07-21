<?php

namespace Drupal\nidirect_breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates the breadcrumb trail for content types with subtheme fields.
 *
 * Applies to the following:
 * - Article
 * - Application
 * - Landing page
 * - Publications
 * - Webforms
 * content types.
 *
 * @package Drupal\nidirect_breadcrumbs
 */
class NodeThemesBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * Core EntityTypeManager instance.
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

    // Full node view.
    if ($route_name == 'entity.node.canonical') {
      $this->node = $route_match->getParameter('node');
    }

    // Editorial preview.
    if ($route_name == 'entity.node.preview') {
      $this->node = $route_match->getParameter('node_preview');
    }

    if (!empty($this->node)) {

      if ($this->node instanceof NodeInterface == FALSE) {
        // Node route but needs loaded entity to check bundle.
        $this->node = $this->entityTypeManager->getStorage('node')->load($this->node);
      }

      if (!empty($this->node)) {
        $applies_to_types = [
          'article',
          'landing_page',
          'application',
          'publication',
          'webform',
        ];

        $match = in_array($this->node->bundle(), $applies_to_types);
      }
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $links = [];
    $cache_tags = [];

    // Fetch the node or preview node object.
    $node = $route_match->getParameter('node') ?? $route_match->getParameter('node_preview');

    if ($node->hasField('field_subtheme') && !empty($node->field_subtheme->target_id)) {
      $links[] = Link::createFromRoute(t('Home'), '<front>');

      $theme_tid = $node->field_subtheme->target_id;

      // Find parent terms, if any and begin to build up link chain.
      $ancestors = $this->entityTypeManager->getStorage("taxonomy_term")->loadAllParents($theme_tid);
      // Flip so we have oldest > youngest ancestors.
      $ancestors = array_reverse($ancestors, TRUE);

      foreach ($ancestors as $term) {
        $links[] = Link::fromTextandUrl($term->label(), Url::fromUri('entity:taxonomy_term/' . $term->id()));
        $cache_tags[] = 'taxonomy_term:' . $term->id();
      }
    }

    // Invalidate the breadcrumb cache if the node is updated.
    $cache_tags[] = 'node:' . $node->id();

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
