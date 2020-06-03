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
      // Load/execute the view to extract the node ids; we add these as exclusions
      // to the 'older news items' contextual args to avoid duplication between
      // the two blocks and retain the 'sticky' flag ability to pin important
      // items to the list of top four items.
      $latest_news_view = $this->entityTypeManager()->getStorage('view')->load('news')->getExecutable();
      $latest_news_view->setDisplay('latest_news');

      $latest_news_view->initHandlers();
      $latest_news_view->preExecute();
      $latest_news_view->execute();
      $latest_news_view->buildRenderable('latest_news');

      $latest_news_nids = [];

      if (!empty($latest_news_view->result)) {
        foreach ($latest_news_view->result as $row) {
          $latest_news_nids[] = $row->nid;
        }
      }

      // Latest news embed display.
      $content['latest_news'] = $latest_news_view->render();

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
      '#arguments' => [implode(',', $latest_news_nids ?? [])],
    ];

    return $content;
  }

}
