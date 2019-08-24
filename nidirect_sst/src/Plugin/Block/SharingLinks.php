<?php

namespace Drupal\nidirect_sst\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'SharingLinks' block.
 *
 * @Block(
 *  id = "sharing_links",
 *  admin_label = @Translation("Sharing links"),
 * )
 */
class SharingLinks extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Links for: Twitter, Facebook, Youtube and RSS.
    $build['twitter'] = Link::fromTextAndUrl($this->t('Twitter'),
      Url::fromUri('https://twitter.com/nidirect', [
        'attributes' => ['title' => $this->t('Twitter')]
      ]))->toRenderable();
    $build['fb'] = Link::fromTextAndUrl($this->t('Facebook'),
      Url::fromUri('https://www.facebook.com/nidirect', [
        'attributes' => ['title' => $this->t('Facebook')]
      ]))->toRenderable();
    $build['youtube'] = Link::fromTextAndUrl($this->t('YouTube'),
      Url::fromUri('https://www.youtube.com/user/nidirect', [
        'attributes' => ['title' => $this->t('YouTube')]
      ]))->toRenderable();
    // TODO: covert to route name once feed exists.
    $build['rss'] = Link::fromTextAndUrl($this->t('RSS'),
      Url::fromUri('internal:/news-rss.xml', [
        'attributes' => ['title' => $this->t('RSS')]
      ]))->toRenderable();

    return $build;
  }

}
