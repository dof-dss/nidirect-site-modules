<?php

namespace Drupal\nidirect_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;

/**
 * Block for presenting featured news; shared with news embed display
 * on the main news landing page. See NewsListingController.php::default().
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
    $view = \Drupal::service('nidirect_news.news')->getLatestNewsView();
    $content['featured_news'] = $view->buildRenderable('latest_news_block');

    return $content;
  }

}
