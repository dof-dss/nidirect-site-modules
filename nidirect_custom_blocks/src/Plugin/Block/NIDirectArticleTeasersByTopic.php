<?php

namespace Drupal\nidirect_custom_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
    $build['nidirect_article_teasers_by_topic']['#markup'] = 'Implement NIDirectArticleTeasersByTopic.';

    return $build;
  }

}
