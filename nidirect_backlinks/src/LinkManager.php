<?php

/**
 * @file
 * Link Manager class instance for handling references between content entities.
 */

namespace Drupal\nidirect_backlinks;

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
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * @param EntityInterface $entity
   *   The entity to examine for links to other content.
   *
   * @return null
   */
  public function processEntity($entity) {
    $reference_values = [];

    // Get a list of all fields attached to this entity instance.
//    foreach ($entity->getFieldDefinitions() as $field_name => $field_definition) {
//      var_dump($field_name);
//      kint($field_definition->getType());
//    }
//    die();

    // Check all entity reference fields.

    // Check all link fields.

    // Check all text fields.

    // Store the values in the table; multi-insert as per https://www.drupal.org/docs/8/api/database-api/insert-queries#multi-insert-form.
    $query = $this->database->insert('nidirect_backlinks')->fields(['id', 'reference_id', 'reference_field']);
    foreach ($reference_values as $record) {
      $query->values($record);
    }
    $query->execute();
  }

  /**
   * @param EntityInterface $entity
   *   The entity to remove reference data for.
   *
   * @return null
   */
  public function deleteEntity($entity) {
    $this->database->delete('nidirect_backlinks')->condition('id', $entity->id());
  }
}
