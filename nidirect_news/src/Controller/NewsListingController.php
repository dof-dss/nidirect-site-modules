<?php

namespace Drupal\nidirect_news\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class NewsListingController.
 */
class NewsListingController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Block\BlockManagerInterface definition.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $pluginManagerBlock;
  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new NewsListingController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              BlockManagerInterface $plugin_manager_block,
                              RequestStack $request_stack) {

    $this->entityTypeManager = $entity_type_manager;
    $this->pluginManagerBlock = $plugin_manager_block;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.block'),
      $container->get('request_stack')
    );
  }

  /**
   * Default page listing. Show:
   *
   * - Latest news: teaser view per item
   * - Older news: title + date per item, with full pager.
   *
   * @return array
   *   Render array for the page for Drupal to convert to HTML.
   */
  public function default() {
    $content = [];

    if (empty($this->requestStack->getCurrentRequest()->query->get('page'))) {
      // Latest news embed display.
      $content['latest_news'] = [
        '#type' => 'view',
        '#name' => 'news',
        '#display_id' => 'latest_news',
      ];

      // View title: see views_embed_view() which the render array relies on for details of why this is missing.
      $content['older_news_title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#attributes' => [
          'class' => 'hr-above',
        ],
        '#value' => t('Older news items'),
      ];
    }

    // Older news.
    $content['older_news'] = [
      '#type' => 'view',
      '#name' => 'news',
      '#display_id' => 'older_news',
    ];

    return $content;
  }

}
