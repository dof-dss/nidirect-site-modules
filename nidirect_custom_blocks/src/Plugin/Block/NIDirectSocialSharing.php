<?php

namespace Drupal\nidirect_custom_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'NIDirectSocialSharing' block.
 *
 * @Block(
 *  id = "nidirect_social_sharing",
 *  admin_label = @Translation("NIDirect Social Sharing"),
 * )
 */
class NIDirectSocialSharing extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $cache_tags = [];
    // Get the current node.
    $node = $this->routeMatch->getParameter('node');
    if (is_object($node) && !empty($node->field_subtheme->target_id)) {
      // Add custom cache tag for taxonomy term listing.
      $cache_tags[] = 'taxonomy_term_list:' . $node->field_subtheme->target_id;
      // Get a list of article teasers by term.
      if (!empty($node->id())) {
        $results = $this->renderArticleTeasersByTerm($node->field_subtheme->target_id, $node->id(), $cache_tags);
      }
      // Get a list of article teasers by topic.
      $results += $this->renderArticleTeasersByTopic($node->field_subtheme->target_id, $cache_tags);
      // Sort entries alphabetically (regardless of type).
      ksort($results);
      // Will be processed by block--nidirect-article-teasers-by-topic.html.twig.
      $build['nidirect_article_teasers_by_topic'] = $results;
      $build['nidirect_article_teasers_by_topic']['#cache'] = [
        'tags' => $cache_tags,
      ];
    }
    return $build;
  }

}
