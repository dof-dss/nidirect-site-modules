<?php

namespace Drupal\nidirect_campaign_utilities;

use Drupal\Core\Database\Connection;

class LayoutBuilderBlockManager {

  protected $connection;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection)
  {
    $this->connection = $connection;
  }


  public function add($node, $block) {
    $result = $this->connection->insert('nidirect_layout_builder_blocks')
      ->fields([
        'nid' => $node->id(),
        'uuid' => $block->uuid(),
      ])
      ->execute();

    return (boolean) $result;
  }


}
