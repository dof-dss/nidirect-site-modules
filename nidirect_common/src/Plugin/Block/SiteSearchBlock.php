<?php

namespace Drupal\nidirect_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SiteSearchBlock' block.
 *
 * @Block(
 *  id = "site_search_block",
 *  admin_label = @Translation("Site search block"),
 * )
 */
class SiteSearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['site_search_block']['site_search'] = [
      '#type' => 'view',
      '#name' => 'search',
      '#display_id' => 'site_search',
    ];

    return $build;
  }

}
