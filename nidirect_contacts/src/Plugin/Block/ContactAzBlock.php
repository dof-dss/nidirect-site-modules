<?php

namespace Drupal\nidirect_contacts\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ContactAzBlock' block.
 *
 * @Block(
 *  id = "contact_az_block",
 *  admin_label = @Translation("Contacts A to Z"),
 * )
 */
class ContactAzBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['contact_az_block']['#markup'] = 'Implement ContactAzBlock.';

    return $build;
  }

}
