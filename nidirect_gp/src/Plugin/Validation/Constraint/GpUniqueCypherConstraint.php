<?php

namespace Drupal\nidirect_gp\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Ensure a GP has a unique cypher.
 *
 * @Constraint(
 *   id = "GpUniqueCypher",
 *   label = @Translation("Ensure a GP has a unique cypher", context = "Validation"),
 *   type = "entity"
 * )
 */
class GpUniqueCypherConstraint extends Constraint {

  /**
   * Message to display when a GP does not have a unique cypher.
   *
   * @var string
   */
  public $message = 'A GP must have a unique cypher.';

}
