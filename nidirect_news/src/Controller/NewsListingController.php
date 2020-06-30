<?php

namespace Drupal\nidirect_news\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;
use Drupal\views\ResultRow;
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

    // Empty page parameter means we're on the landing page for news.
    if (empty($this->requestStack->getCurrentRequest()->query->get('page'))) {
      // Featured news comes from the first featured content list node tagged with 'News'.
      $featured_news_ids = [];

      $news_tag = $this->entityTypeManager
        ->getStorage('taxonomy_term')
        ->loadByProperties(['name' => 'News']);
      $news_tag = reset($news_tag);

      if ($news_tag instanceof TermInterface && $news_tag->bundle() == 'tags') {
        // If we've got a valid news tag from the tags vocab, go ahead and try to load
        // the featured content list node tagged with it.
        $featured_news_list = $this->entityTypeManager
          ->getStorage('node')
          ->loadByProperties([
            'type' => 'featured_content_list',
            'field_tags' => $news_tag->id(),
          ]);

        if (!empty($featured_news_list)) {
          $featured_news_list = reset($featured_news_list);
        }

        if (!empty($featured_news_list->field_featured_content)) {
          $featured_content_values = $featured_news_list->field_featured_content->getValue();

          if (!empty($featured_content_values)) {
            $featured_news_ids = array_column($featured_content_values, 'target_id');
          }
        }

        // Load/execute the view to extract the node ids; we add these as exclusions
        // to the 'older news items' contextual args to avoid duplication between
        // the two blocks and retain the 'sticky' flag ability to pin important
        // items to the list of top four items.
        $latest_news_view = $this->entityTypeManager()->getStorage('view')->load('news')->getExecutable();
        $latest_news_view->setDisplay('latest_news');
        $latest_news_view->initHandlers();
        $latest_news_view->setArguments([implode(',', $featured_news_ids ?? [])]);
        $latest_news_view->preExecute();
        $latest_news_view->execute();
        $latest_news_view->buildRenderable('latest_news');

        // Views' SQL query will naturally order the rows by node id, ascending. It's not easy to introduce
        // the original weightings into the SQL query from the featured content list field values without
        // some kind of pseudo-table + join + order by clause. That's quite a lot of effort for such a
        // small amount of data so you might as well re-order the result rows sequence right here.
        $sorted_results = [];

        // Nested loops could be more efficient with careful use of array_filter
        // and anonymous/lamba functions within, but we should choose clarity over brevity.
        foreach ($featured_news_ids as $id) {
          foreach ($latest_news_view->result as $row) {
            if ($row->nid == $id) {
              $sorted_results[] = $row;
              break;
            }
          }
        }

        // Replace the view results array with the sorted array.
        $latest_news_view->result = $sorted_results;

        // Create a render array that our controller can return/render.
        $content['latest_news'] = $latest_news_view->render();
      }

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

    if (!empty($featured_news_ids)) {
      $content['older_news']['#arguments'] = [implode(',', $featured_news_ids)];
    }

    return $content;
  }

}
