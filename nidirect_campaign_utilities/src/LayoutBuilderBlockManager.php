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
        'bid' => $block->id(),
      ])
      ->execute();

    return (boolean) $result;
  }

  public function remove($block) {

    $result = $this->connection->delete('nidirect_layout_builder_blocks')
      ->condition('id', $block->id())
      ->execute();

    $block->delete();

    return (boolean) $result;
  }

  public function purge($node) {

    $result = $this->connection->delete('nidirect_layout_builder_blocks')
      ->condition('nid', $node->id())
      ->execute();

    return (boolean) $result;
  }


}
