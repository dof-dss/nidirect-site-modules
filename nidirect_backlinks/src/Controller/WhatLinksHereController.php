<?php

namespace Drupal\nidirect_backlinks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\nidirect_backlinks\LinkManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class WhatLinksHereController.
 *
 * Handles requests for routes defined in nidirect_backlinks.routing.yml.
 */
class WhatLinksHereController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Drupal\nidirect_backlinks\LinkManagerInterface definition.
   *
   * @var \Drupal\nidirect_backlinks\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request, LinkManagerInterface $link_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request;
    $this->linkManager = $link_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('nidirect_backlinks.linkmanager')
    );
  }

  /**
   * Present a report of related content from the current node ID parameter.
   *
   * @param int $node
   *   Node ID of the node.
   * @return array
   *   Render array for Drupal to convert to HTML.
   */
  public function default($node) {
    $content = [];

    $entity = $this->entityTypeManager->getStorage('node')->load($node);
    $related_content = $this->linkManager->getReferenceContent($entity);
    kint($related_content);

    $content['links_table'] = [
      '#markup' => '<p>Hello</p>',
    ];

    return $content;
  }

}
