<?php

/**
 * @file
 * Contains weather_station.page.inc.
 *
 * Page callback for Weather station entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Weather station templates.
 *
 * Default template: weather_station.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_weather_station(array &$variables) {
  // Fetch WeatherStationEntity Entity Object.
  $weather_station = $variables['elements']['#weather_station'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
