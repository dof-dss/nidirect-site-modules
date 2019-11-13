<?php

namespace Drupal\whatlinkshere\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\Translator\TranslatorInterface;
use Drupal\whatlinkshere\LinkManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class WhatLinksHereController.
 *
 * Handles requests for routes defined in whatlinkshere.routing.yml.
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
   * Drupal\whatlinkshere\LinkManagerInterface definition.
   *
   * @var \Drupal\whatlinkshere\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * Drupal\Core\StringTranslation\Translator\TranslatorInterface definition.
   *
   * @var \Drupal\Core\StringTranslation\Translator\TranslatorInterface
   */
  protected $t;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request, LinkManagerInterface $link_manager, TranslatorInterface $translator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request;
    $this->linkManager = $link_manager;
    $this->t = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('whatlinkshere.linkmanager'),
      $container->get('string_translation')
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
    $build = [];

    // Table header/sort options.
    $header = [
      'title' => [
        'data' => $this->t->translate('Title'),
        'field' => 'title',
        'sort' => 'asc'
      ],
      'type' => $this->t->translate('Type'),
      'fields' => $this->t->translate('From field(s)'),
      'tasks' => $this->t->translate('Tasks'),
    ];

    // Pager init.
    $page = pager_find_page();
    $num_per_page = 25;
    $offset = $num_per_page * $page;

    // Fetch data about what content links to this node.
    $entity = $this->entityTypeManager->getStorage('node')->load($node);
    $related_content = $this->linkManager->getReferenceContent($entity, $num_per_page, $offset, $header);

    // Now that we have the total number of results, initialize the pager.
    pager_default_initialize($related_content['total'], $num_per_page);

    $rows = [];
    foreach ($related_content['rows'] as $item) {
      $rows[] = [
        Link::createFromRoute($item['title'], 'entity.node.canonical', ['node' => $item['nid']]),
        $item['type'],
        $item['reference_fields'],
        Link::createFromRoute($this->t->translate('Edit'), 'entity.node.edit_form', ['node' => $item['nid']]),
      ];
    }

    $build['links_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t->translate('No content links here.'),
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

}
