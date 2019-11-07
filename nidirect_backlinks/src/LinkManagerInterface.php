<?php

namespace Drupal\nidirect_backlinks;

/**
 * @file
 * Link Manager interface.
 */

use Drupal\Core\Entity\EntityInterface;

interface LinkManagerInterface {

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to examine for links to other content.
   *
   * @return null
   *   Nothing.
   */
  public function processEntity(EntityInterface $entity);

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to remove reference data for.
   *
   * @return null
   *   Nothing.
   */
  public function deleteEntity(EntityInterface $entity);

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to use as a base for identifying related content for.
   *
   * @return array
   *   Array of entity IDs as [bundle][entity_id]
   */
  public function getReferenceContent(EntityInterface $entity);

}
