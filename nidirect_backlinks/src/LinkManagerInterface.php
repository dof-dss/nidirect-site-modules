<?php

/**
 * @file
 * Link Manager interface.
 */

namespace Drupal\nidirect_backlinks;

use Drupal\Core\Entity\EntityInterface;

interface LinkManagerInterface {

  /**
   * @param EntityInterface $entity
   *   The entity to examine for links to other content.
   *
   * @return null
   */
  public function processEntity($entity);

  /**
   * @param EntityInterface $entity
   *   The entity to remove reference data for.
   *
   * @return null
   */
  public function deleteEntity($entity);

}
