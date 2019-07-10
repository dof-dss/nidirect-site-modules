<?php

namespace Drupal\nidirect_cold_weather_payments\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Field formatter "nidirect_cold_weather_period".
 *
 * @FieldFormatter(
 *   id = "nidirect_cold_weather_period",
 *   label = @Translation("Cold Weather Period Default"),
 *   field_types = {
 *     "nidirect_cold_weather_period",
 *   }
 * )
 */
class ColdWeatherPeriodDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $output = [];
    $build = [];

    foreach ($items as $delta => $item) {
      $build['name'] = [
        '#type' => 'container',
        'label' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['field__label'],
          ],
          '#markup' => t('%start - %end', ['%start' => $item->date_start, '%end' => $item->date_end]),
        ],
        'value' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['field__item'],
          ],
          'stations' => [
            '#markup' => str_replace(',', ', ', $item->stations),
          ],
        ],
      ];
      $output[$delta] = $build;
    }

    return $output;
  }

}
