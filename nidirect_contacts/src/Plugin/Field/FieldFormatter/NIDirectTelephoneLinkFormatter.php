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
    // Array of characters to be removed from the number formatting to allow for
    // value comparisons.
    $formatting_chars = [' ', '+'];

    foreach ($items as $item) {
      $unformatted_field_number = str_replace($formatting_chars, '', $item->getValue('telephone_number')['telephone_number']);
      // Match international and textphone numbers to replace the default
      // formatting provided by the libphonenumber library.
      if (strpos($unformatted_field_number, '00800') !== FALSE || strpos($unformatted_field_number, '18001') !== FALSE) {
        foreach ($elements as &$element) {
          if (strpos($unformatted_field_number, str_replace($formatting_chars, '', $element['number']['#value'])) !== FALSE) {
            $element['number']['#value'] = $item->getValue('telephone_number')['telephone_number'];
          }
        }
      }
    }

    return $elements;
  }

}
