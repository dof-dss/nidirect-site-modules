<?php

namespace Drupal\nidirect_campaign_utilities;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use mysql_xdevapi\Executable;

class LayoutBuilderBlockManager {

  protected $connection;

  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, $connection)
  {
    $this->entityTypeManager = $entity_type_manager;
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

    $query = $this->connection->select('nidirect_layout_builder_blocks', 'b')
              ->fields('b', ['bid'])->condition('nid', $node->id());
    $bids = $query->execute()->fetchCol();

    $blocks = $this->entityTypeManager->getStorage('block_content')->loadMultiple($bids);

    foreach ($blocks as $block) {
      $block->delete();
    }

    $result = $this->connection->delete('nidirect_layout_builder_blocks')
      ->condition('nid', $node->id())
      ->execute();

    return (boolean) $result;
  }


}
