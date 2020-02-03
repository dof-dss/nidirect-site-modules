<?php

namespace Drupal\nidirect_custom_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'NIDirectArticleTeasersByTopic' block.
 *
 * @Block(
 *  id = "nidirect_article_teasers_by_topic",
 *  admin_label = @Translation("NIDirect Article Teasers by Topic"),
 * )
 */
class NIDirectArticleTeasersByTopic extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs an AggregatorFeedBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Current route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer = NULL, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $cache_tags = [];
    // Get the current node.
    $node = $this->routeMatch->getParameter('node');
    if (is_object($node)) {
      if ($node->field_manually_control_listing->value) {
        // content editor has taken charge of the contents, so don't
        // do any more work in here.
        return $build;
      }
      if (!empty($node->field_subtheme->target_id)) {
        // Add custom cache tag for taxonomy term listing.
        $cache_tags[] = 'taxonomy_term_list:' . $node->field_subtheme->target_id;
        // Get list of articles.
        $results = $this->retrieveArticlesBySubTheme($node->field_subtheme->target_id, $node->id(), $cache_tags);
        // Will be processed by block--nidirect-article-teasers-by-topic.html.twig.
        $build['nidirect_article_teasers_by_topic'] = $results;
        $build['nidirect_article_teasers_by_topic']['#cache'] = [
          'tags' => $cache_tags,
        ];
      }
    }
    return $build;
  }

  public function retrieveArticlesBySubTheme($tid, $nid = NULL, array &$cache_tags = []) {
    // Get a list of article teasers by term.
    $results = $this->renderArticleTeasersByTerm($tid, $nid, $cache_tags);
    // Get a list of article teasers by topic.
    $results += $this->renderArticleTeasersByTopic($tid, $cache_tags);
    // Sort entries alphabetically (regardless of type).
    ksort($results);
    return $results;
  }

  /**
   * Utility function to render 'articles by term' view.
   */
  private function renderArticleTeasersByTerm(int $tid, int $current_nid, array &$cache_tags) {
    // Render the 'articles by term' view and process the results.
    $results = [];
    $articles_view = views_embed_view('articles_by_term', 'article_teasers_by_term_embed', $tid, $tid);
    $this->renderer->renderRoot($articles_view);
    foreach ($articles_view['view_build']['#view']->result as $row) {
      $thisresult = [];
      // Exclude the current page from the list.
      if ($row->nid == $current_nid) {
        continue;
      }
      // This will be a link to an article.
      $thisresult['title_link'] = [
        '#type' => 'link',
        '#title' => $row->_entity->getTitle(),
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => $row->nid]),
      ];
      $thisresult['more_link'] = [
        '#type' => 'link',
        '#title' => '... ' . t('more'),
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => $row->nid]),
      ];
      $thisresult['summary_text'] = ['#markup' => $row->_entity->field_summary->value];
      // Place in an array keyed by lower case title (for sorting).
      $results[strtolower($row->_entity->getTitle())] = $thisresult;
      // Add cache tag for each article.
      $cache_tags[] = 'node:' . $row->nid;
    }
    return $results;
  }

  /**
   * Utility function to render 'site subtopics' view.
   */
  private function renderArticleTeasersByTopic(int $tid, array &$cache_tags) {
    // Render the 'site subtopics' view and process the results.
    $results = [];
    $articles_view = views_embed_view('site_subtopics', 'subtopic_teasers_by_topic_embed', $tid, $tid);
    $this->renderer->renderRoot($articles_view);
    foreach ($articles_view['view_build']['#view']->result as $row) {
      $thisresult = [];
      // This will be a link to a taxonomy term.
      $thisresult['title_link'] = [
        '#type' => 'link',
        '#title' => $row->_entity->getName(),
        '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $row->tid]),
      ];
      $thisresult['more_link'] = [
        '#type' => 'link',
        '#title' => '... ' . t('more'),
        '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $row->tid]),
      ];
      $thisresult['summary_text'] = ['#markup' => $row->_entity->field_teaser->value];
      // Place in an array keyed by lower case title (for sorting).
      $results[strtolower($row->_entity->getName())] = $thisresult;
      // Add cache tag for each listed term.
      $cache_tags[] = 'taxonomy_term:' . $row->tid;
    }
    return $results;
  }

}
