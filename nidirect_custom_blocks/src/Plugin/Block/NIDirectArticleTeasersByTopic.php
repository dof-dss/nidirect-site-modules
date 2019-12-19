<?php

namespace Drupal\nidirect_custom_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'NIDirectArticleTeasersByTopic' block.
 *
 * @Block(
 *  id = "nidirect_article_teasers_by_topic",
 *  admin_label = @Translation("NIDirect Article Teasers by Topic"),
 * )
 */
class NIDirectArticleTeasersByTopic extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $node = \Drupal::routeMatch()->getParameter('node');
    $results = $this->renderArticleTeasersByTerm($node->field_subtheme->target_id, $node->id());
    $results += $this->renderArticleTeasersByTopic($node->field_subtheme->target_id);
    // Sort entries alphabetically (regardless of type).
    ksort($results);
    $build['nidirect_article_teasers_by_topic'] = $results;
    return $build;
  }

  /**
   * Utility function to render 'articles by term' view.
   */
  private function renderArticleTeasersByTerm($tid, $current_nid) {
    // Render the 'articles by term' view and process the results.
    $results = [];
    $articles_view = views_embed_view('articles_by_term', 'article_teasers_by_term_embed', $tid, $tid);
    \Drupal::service('renderer')->renderRoot($articles_view);
    foreach ($articles_view['view_build']['#view']->result as $row) {
      $thisresult = [];
      // Exclude the current page from the list.
      if ($row->nid == $current_nid)
        continue;
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
      $results[strtolower($row->_entity->getTitle())] = $thisresult;
      // Add cache tag for each article.
      $cache_tags[] = 'node:' . $row->nid;
    }
    return $results;
  }

  /**
   * Utility function to render 'site subtopics' view.
   */
  private function renderArticleTeasersByTopic($tid) {
    // Render the 'articles by term' view and process the results.
    $results = [];
    $articles_view = views_embed_view('site_subtopics', 'subtopic_teasers_by_topic_embed', $tid, $tid);
    \Drupal::service('renderer')->renderRoot($articles_view);
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
      $results[strtolower($row->_entity->getName())] = $thisresult;
      // Add cache tag for each listed term.
      $cache_tags[] = 'taxonomy_term:' . $row->tid;
    }
    return $results;
  }

}
