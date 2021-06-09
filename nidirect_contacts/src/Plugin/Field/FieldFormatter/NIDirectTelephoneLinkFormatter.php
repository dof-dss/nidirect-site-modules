<?php

namespace Drupal\nidirect_contacts\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\telephone_plus\Plugin\Field\FieldFormatter\TelephonePlusLinkFormatter;

/**
 * Plugin extending the 'telephone_plus_link' formatter.
 *
 * This plugin calls the parent viewElements method of the telephone_plus module
 * and checks for the existence of numbers that require reformatting of the
 * output from the libphonenumber-for-php library used in that module.
 *
 * @FieldFormatter(
 *   id = "nidirect_telephone_link",
 *   label = @Translation("NIDirect telephone link"),
 *   description = @Translation("Extends the TelephonePlus Link formatter with some custom output formatting."),
 *   field_types = {
 *     "telephone_plus_field"
 *   }
 * )
 */
class NIDirectTelephoneLinkFormatter extends TelephonePlusLinkFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    foreach ($items as $item) {
      $matches = [];

      // Check for international freephone numbers and reformat the output.
      if (preg_match('/^00\s?800\s?(.+)/m', $item->getValue('telephone_number')['telephone_number'], $matches)) {
        foreach ($elements as &$element) {
          if (strpos($element['number']['#value'], $matches[1]) !== FALSE) {
            $element['number']['#value'] = $item->getValue('telephone_number')['telephone_number'];
          }
        }
        continue;
      }

      // Check for textphone numbers and reformat the output.
      if (preg_match('/^18001\s?(.+)/m', $item->getValue('telephone_number')['telephone_number'], $matches)) {
        foreach ($elements as &$element) {
          if (str_replace(' ', '', $item->getValue('telephone_number')['telephone_number']) === $element['number']['#value']) {
            $element['number']['#value'] = $item->getValue('telephone_number')['telephone_number'];
          }
        }
      }
    }

    return $elements;
  }

}
