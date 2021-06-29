<?php

namespace Drupal\nidirect_gp\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a GP has a unique cypher.
 */
class GpUniqueCypherConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity) ||  !$cypher = $entity->getCypher()) {
      return;
    }

    $is_unique = \Drupal::service('nidirect_gp.unique_cypher')->isCypherUnique($cypher, [$entity->id()]);

    if ($is_unique === FALSE) {
      $this->context->addViolation($constraint->message, ['@cypher' => $cypher]);
    }
  }

}
