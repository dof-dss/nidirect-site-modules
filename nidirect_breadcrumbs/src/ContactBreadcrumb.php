<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for content including:
 * - Contact
 *
 * In the format:
 * > Home
 * > Contacts
 *
 * > <front>
 * > contacts
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContactBreadcrumb implements BreadcrumbBuilderInterface {

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
   * Node object, or null if on a non-node page.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    $this->node = $route_match->getParameter('node');

    if (!empty($this->node)) {
      if (is_object($this->node) == FALSE) {
        $this->node = $this->entityTypeManager->getStorage('node')->load($node);
      }

      $match = $this->node->bundle() == 'contact';
    }
    else {
      // Also check for contacts listing/search pages.
      $match = in_array($route_match->getRouteName(), [
        'nidirect_contacts.default',
        'nidirect_contacts.letter',
      ]);
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();

    $links = [];

    if (!empty($this->node)) {
      // Only add to contact node pages.
      $links[] = Link::createFromRoute(t('Home'), '<front>');
      $links[] = Link::fromTextandUrl(t('Contacts'), Url::fromUserInput('/contacts'));
    }

    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }

}
