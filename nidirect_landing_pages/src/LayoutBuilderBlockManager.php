<?php

namespace Drupal\nidirect_landing_pages;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\block_content\BlockContentInterface;

/**
 * Manages custom block relationships to layout builder enabled nodes.
 */
class LayoutBuilderBlockManager {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $connection) {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
  }

  /**
   * Add a block to node relationship.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node containing the content block.
   * @param \Drupal\block_content\BlockContentInterface $block
   *   The content block created for the node.
   *
   * @return bool
   *   TRUE if the relationship was created.
   *
   * @throws \Exception
   */
  public function add(NodeInterface $node, BlockContentInterface $block) {
    $result = $this->connection->insert('nidirect_layout_builder_block_manager')
      ->fields([
        'nid' => $node->id(),
        'bid' => $block->id(),
      ])
      ->execute();

    return (boolean) $result;
  }

  /**
   * Removes a block from the node / block relationships.
   *
   * @param \Drupal\block_content\BlockContentInterface $block
   *   The content block to remove.
   *
   * @return bool
   *   TRUE if the block relationship was removed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function remove(BlockContentInterface $block) {
    $result = $this->connection->delete('nidirect_layout_builder_block_manager')
      ->condition('id', $block->id())
      ->execute();

    $block->delete();

    return (boolean) $result;
  }

  /**
   * Removes all content block relationships associated with a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to remove all content block relations.
   *
   * @return bool
   *   TRUE if the relationships were removed.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function purge(NodeInterface $node) {

    $query = $this->connection->select('nidirect_layout_builder_block_manager', 'b')
      ->fields('b', ['bid'])->condition('nid', $node->id());
    $bids = $query->execute()->fetchCol();

    $blocks = $this->entityTypeManager->getStorage('block_content')->loadMultiple($bids);

    foreach ($blocks as $block) {
      $block->delete();
    }

    $result = $this->connection->delete('nidirect_layout_builder_block_manager')
      ->condition('nid', $node->id())
      ->execute();

    return (boolean) $result;
  }

}
