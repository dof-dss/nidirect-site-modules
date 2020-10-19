<?php

namespace Drupal\nidirect_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Block for presenting featured news; wrapper around FCL node tagged with 'News'.
 *
 * @Block(
 *  id = "featured_news_block",
 *  admin_label = @Translation("Featured news"),
 * )
 */
class FeaturedNewsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content['featured_news'] = \Drupal::service('nidirect_news.news')->getNewsEmbed('featured_news');
    return $content;
  }

}
