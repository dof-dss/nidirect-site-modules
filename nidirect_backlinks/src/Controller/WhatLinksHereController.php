<?php

namespace Drupal\nidirect_backlinks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\Translator\TranslatorInterface;
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
      $container->get('nidirect_backlinks.linkmanager'),
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
    $content = [];

    $entity = $this->entityTypeManager->getStorage('node')->load($node);
    $related_content = $this->linkManager->getReferenceContent($entity);

    $rows = [];
    foreach ($related_content as $item) {
      $rows[] = [
        Link::createFromRoute($item['title'], 'entity.node.canonical', ['node' => $item['nid']]),
        $item['type'],
        $item['reference_fields'],
        Link::createFromRoute($this->t->translate('Edit'), 'entity.node.edit_form', ['node' => $item['nid']]),
      ];
    }

    $content['links_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t->translate('Content title'),
        $this->t->translate('Type'),
        $this->t->translate('From field(s)'),
        $this->t->translate('Tasks'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t->translate('Nothing content links to this.'),
    ];
    $content['pager'] = [
      '#type' => 'pager',
    ];

    return $content;
  }

}
