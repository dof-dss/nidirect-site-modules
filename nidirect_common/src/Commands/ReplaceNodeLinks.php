<?php

namespace Drupal\migrate_common\Commands;

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
   * {@inheritdoc}
   */
  public function __construct() {
    $this->dbConn = Database::getConnection('default', 'default');
  }

  /**
   * Update links
   *
   * @command node-links-to-aliases
   *
   * @param string $node_type
   *   Node bundle to perform the operation on. Defaults to 'all'.
   * @param string $field
   *   Node field to search and replace links. Must be the full machine name
   *   (e.g. field_summary) defaults to 'body'.
   *
   * @option Process revision links.
   */
  public function updatePublishStatus($node_type = 'all', $field = 'body', $options = ['revisions' => TRUE]) {


  }

}
