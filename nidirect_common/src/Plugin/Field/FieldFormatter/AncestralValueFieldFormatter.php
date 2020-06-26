<?php

namespace Drupal\nidirect_common\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field formatter "AncestralValueFieldFormatter".
 *
 * @FieldFormatter(
 *   id = "ancestral_value_field_formatter",
 *   label = @Translation("Ancestral value formatter"),
 *   description = @Translation("Fetch value from the current field, parent or grandparent."),
 *   field_types = {
 *     "text",
 *     "text_long",
 *   }
 * )
 */
class AncestralValueFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // If this entity doesn't have a value for the field, try the
    // parent and grandparent entities which are identified from
    // the sub_theme field.
    if (!$items->count()) {
      $entity = $items->getEntity();

      if ($entity->hasField('field_subtheme') && !$entity->get('field_subtheme')->isEmpty()) {
        $term = $entity->get('field_subtheme')->entity;
        // todo: try and load the field value from the term ancestors.
      }
    } else {
      foreach ($items as $delta => $item) {
        $elements[$delta] = [
          '#type' => 'processed_text',
          '#text' => $item->value,
          '#format' => $item->format,
          '#langcode' => $item->getLangcode(),
        ];
      }
    }

    return $elements;
  }

}
