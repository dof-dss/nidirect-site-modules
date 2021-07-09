<?php

namespace Drupal\nidirect_common\Commands;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drush\Commands\DrushCommands;

/**
 * Replaces references to node/xxx within content with the path alias.
 *
 */
class ReplaceNodeLinks extends DrushCommands {

  /**
   * Drupal database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConn;

  /**
   * Core EntityTypeManager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->dbConn = Database::getConnection('default', 'default');
    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * Replace links containing /node/XXX to the path alias for that NID.
   *
   * @command nidirect:node-to-alias
   *
   * @param string $node_type
   *   Node bundle to perform the operation on.
   * @param string $field
   *   Node field to search and replace links. Must be the full machine name
   *   (e.g. field_summary).
   */
  public function updateNodeFieldLinks($node_type = 'all', $field = 'body', $options = ['revisions' => TRUE]) {

    if ($node_type === 'all') {
      $bundles = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    }

    $query = $this->dbConn->select('node__' . $field, 'f');
    $query->fields('f', ['entity_id', $field . '_value']);
    $query->where("body_value REGEXP '\/node\/\[0-9]*'");

    if ($node_type !== 'all') {
      $query->condition('f.bundle', $node_type, '=');
    }
    $results = $query->execute()->fetchAllAssoc('entity_id');

  }

}
