<?php

namespace Drupal\nidirect_backlinks;

/**
 * @file
 * Link Manager class instance for handling references between content entities.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Url;
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
   * Drupal entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Path alias manager service.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface;
   */
  protected $pathAliasManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database, EntityFieldManagerInterface $entity_field_manager, AliasManagerInterface $path_alias_manager) {
      $this->entityTypeManager = $entity_type_manager;
      $this->database = $database;
      $this->entityFieldManager = $entity_field_manager;
      $this->pathAliasManager = $path_alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('path.alias_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processEntity(EntityInterface $entity) {
    $fields = $this->entityFieldManager->getFieldDefinitions('node', $entity->bundle());

    $types_of_interest = [
      'entity_reference',
      'text_with_summary',
      'link',
    ];

    // Array to store entity ids of things that this entity references.
    $reference_values = [];

    foreach ($fields as $field) {
      $type = $field->getType();
      $field_name = $field->getName();
      $field_value = NULL;

      // Skip over any fields we don't believe would contain a reference.
      if (in_array($type, $types_of_interest) == FALSE || preg_match('/^field_(.+)|body/', $field_name) == FALSE) {
        continue;
      }

      // Array to capture any extracted nids from any field type.
      $extracted_nids = [];

      // Text fields that may contain link markup.
      if ($type == 'text_with_summary' || $type == 'text_long') {
        $field_value = $entity->get($field->getName())->value;
        // Scan for link elements in this chunk of HTML.
        $dom = Html::load($field_value);
        $link_elements = $dom->getElementsByTagName('a');

        foreach ($link_elements as $link) {
          $href = $link->getAttribute('href');

          if (preg_match('/^http/', $href)) {
            // Skip over absolute or external links.
            continue;
          }
          else {
            // Lookup content by path alias.
            $matches = [];
            preg_match('/node\/(\d+)/', $this->pathAliasManager->getPathByAlias($href), $matches);

            if (!empty($matches[1])) {
              $ref_nid = $matches[1];

              $extracted_nids[] = $ref_nid;
            }
          }
        }

        // Dedupe the array and store in the 'field values' variable to use in the query later.
        $field_value = array_unique($extracted_nids);
      }

      // Any link fields.
      if ($type == 'link') {
        $link_field_values = $entity->get($field->getName())->getValue();

        foreach ($link_field_values as $link) {
          $url = Url::fromUri($link['uri']);

          if ($url->isExternal() == FALSE) {
            $uri_path = $url->getInternalPath();
            $matches = [];

            if (preg_match('/node\/(\d+)/', $uri_path, $matches)) {
              $extracted_nids[] = $matches[1];
            }
          }
        }

        $field_value = array_unique($extracted_nids);
      }

      // Entity reference fields (only interested in Node for PoC).
      if ($type == 'entity_reference' && $field->getSetting('target_type') == 'node') {
        $field_value = $entity->get($field->getName())->getValue();

        if (!empty($field_value)) {
          foreach ($field_value as $value) {
            $extracted_nids[] = $value['target_id'];
          }

          $field_value = array_unique($extracted_nids);
        }
      }

      // Bundle the gathered values into an array to pass into our DB layer.
      if (!empty($field_value)) {
        // Cast field value to array for ease of iterating.
        $field_value = (array) $field_value;

        for ($i = 0; $i < count($field_value); $i++) {
          $reference_values[] = [
            'id' => $entity->id(),
            'reference_id' => $field_value[$i],
            'reference_field' => $field_name,
            'delta' => $i,
          ];
        }
      }
    }

    // Delete existing values.
    $this->database->delete('nidirect_backlinks')
      ->condition('id', $entity->id())
      ->execute();

    // Insert new values.
    $query = $this->database->insert('nidirect_backlinks')->fields([
      'id',
      'reference_id',
      'reference_field',
      'delta',
    ]);
    foreach ($reference_values as $record) {
      $query->values($record);
    }

    $query->execute();
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
    $query->fields('nfd', ['nid', 'title', 'type']);
    $query->innerJoin('nidirect_backlinks', 'b', 'nfd.nid = b.id');
    $query->addExpression('GROUP_CONCAT(DISTINCT b.reference_field)', 'reference_fields');
    $query->condition('b.reference_id', $entity->id(), '=');
    $query->groupBy('nfd.nid, nfd.title, nfd.type');
    $query->orderBy('nfd.title');

    $result = $query->execute();

    $related_content = [];

    foreach ($result as $record) {
      $related_content[] = [
        'nid' => $record->nid,
        'type' => $this->entityTypeManager->getStorage('node_type')->load($record->type)->label(),
        'title' => $record->title,
        // Pad commas with trailing space to improve readability.
        'reference_fields' => str_replace(',', ', ', $record->reference_fields),
      ];
    }

    return $related_content;
  }

}
