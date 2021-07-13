<?php

namespace Drupal\nidirect_common\Commands;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\path_alias\AliasManager;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Path alias manager.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $pathAliasManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(AliasManager $alias_manager) {
    $this->dbConn = Database::getConnection('default', 'default');
    $this->pathAliasManager = $alias_manager;
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
    $tables = ['node__', 'node_revision__'];

    if ($options['revisions'] !== TRUE) {
      array_pop($tables);
    }

    foreach ($tables as $table) {
      $updated_count = 0;

      $query = $this->dbConn->select($table . $field, 'f');
      $query->fields('f', ['entity_id', $field . '_value']);
      $query->where($field . "_value REGEXP '\/node\/\[0-9]*'");

      if ($node_type !== 'all') {
        $query->condition('f.bundle', $node_type, '=');
      }
      $results = $query->execute()->fetchAllAssoc('entity_id');

      foreach ($results as $result) {
        $updated_value = preg_replace_callback(
          '/\/node\/\d+/m',
          'self::nid_to_alias',
          $result->body_value);

        $updated_count += $this->dbConn->update($table . $field)
          ->fields([$field . '_value' => $updated_value])
          ->condition('entity_id', $result->entity_id, '=')
          ->execute();
      }

      $this->output()->writeln('Updated content aliases for ' . str_replace('_', ' ', $table) . ': ' . $updated_count);
    }

  }

  /**
   * Match node ID to path alias.
   *
   * @param $matches
   */
  public function nid_to_alias($matches) {
    foreach ($matches as $match) {
      $alias = $this->pathAliasManager->getAliasByPath($match);

      if ($alias !== $match) {
        return $alias;
      }
    }
  }

}
