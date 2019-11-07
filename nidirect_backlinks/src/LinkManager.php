<?php

namespace Drupal\nidirect_backlinks;

/**
 * @file
 * Link Manager class instance for handling references between content entities.
 */

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LinkManager implements LinkManagerInterface {

  /**
   * Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processEntity(EntityInterface $entity) {
    // $reference_values = [].
    //
    // // Check all entity reference fields.
    //
    // // Check all link fields.
    //
    // // Check all text fields.
    //
    // // Store the values in the table; multi-insert as per https://www.drupal.org/docs/8/api/database-api/insert-queries#multi-insert-form.
    // $query = $this->database->insert('nidirect_backlinks')->fields(['id', 'reference_id', 'reference_field']);
    // foreach ($reference_values as $record) {
    // $query->values($record);
    // }
    // $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteEntity(EntityInterface $entity) {
    $this->database->delete('nidirect_backlinks')->condition('id', $entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceContent(EntityInterface $entity) {
    $query = $this->database->select('node_field_data', 'nfd');
    $query->fields('nfd', ['nid', 'title']);
    $query->innerJoin('nidirect_backlinks', 'b', 'nfd.nid = b.reference_id');
    $query->condition('b.id', $entity->id(), '=');

    $result = $query->execute();

    $related_content = [];

    foreach ($result as $record) {
      $related_content[$record->nid] = $record->title;
    }

    return $related_content;
  }

}
