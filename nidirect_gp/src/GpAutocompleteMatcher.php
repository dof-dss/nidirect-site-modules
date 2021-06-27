<?php

namespace Drupal\nidirect_gp;

use Drupal\Component\Utility\Html;

/**
 * Autocomplete matcher for GP's.
 */
class GpAutocompleteMatcher extends \Drupal\Core\Entity\EntityAutocompleteMatcher {

  /**
   * Matches GP's to the autocomplete search string.
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {

    if ($target_type !== 'gp') {
      return;
    }

    $matches = [];

    $options = [
      'target_type'      => $target_type,
      'handler'          => $selection_handler,
      'handler_settings' => $selection_settings,
    ];

    $handler = $this->selectionManager->getInstance($options);

    if (isset($string)) {
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, 30);

      foreach ($entity_labels as $values) {
        foreach ($values as $id => $label) {
          $entity = \Drupal::entityTypeManager()->getStorage($target_type)->load($id);

          $key = $label . ' (' . $id . ')';
          $label .= ' [GP cypher: ' . $entity->getCypher() . '] (' . $id . ')';
          $key = Html::decodeEntities($key);
          $matches[] = ['value' => $key, 'label' => $label];
        }
      }
    }

    return $matches;
  }

}
