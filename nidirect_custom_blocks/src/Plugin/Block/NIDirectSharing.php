<?php

namespace Drupal\nidirect_custom_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'NIDirectSharing' block.
 *
 * @Block(
 *  id = "nidirect_sharing",
 *  admin_label = @Translation("Nidirect sharing"),
 * )
 */
class NIDirectSharing extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'nidirect_sharing';
    return $build;
  }

}
