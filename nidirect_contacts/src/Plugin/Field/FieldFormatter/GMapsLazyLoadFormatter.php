<?php

namespace Drupal\nidirect_contacts\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'gmaps_lazy_load_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "gmaps_lazy_load_formatter",
 *   label = @Translation("Google Maps: Lazy Loader"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 */
class GMapsLazyLoadFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'zoom' => '10',
        'map_type' => 'roadmap',
        'placeholder' => 'empty',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $form['map_type'] = [
      '#title' => $this->t('Map type'),
      '#type' => 'select',
      '#options' => [
        'roadmap' => t('Road map'),
        'satellite' => t('Satellite'),
        'hybrid' => t('Hybrid'),
        'terrain' => t('Terrain'),
      ],
      '#default_value' => $this->getSetting('map_type'),
    ];

    $form['zoom'] = [
      '#title' => $this->t('Zoom'),
      '#type' => 'number',
      '#min' => 1,
      '#max' => 22,
      '#default_value' => $this->getSetting('zoom'),
    ];

    $form['placeholder'] = [
      '#title' => $this->t('Placeholder'),
      '#type' => 'select',
      '#options' => [
        'empty' => t('Empty'),
        'link' => t('Link to Google map'),
        'static_map' => t('Static map'),
      ],
      '#default_value' => $this->getSetting('placeholder'),
    ];

    return $form + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {$summary = [];
    $summary[] = $this->t(
      'Map type: @maptype <br> Zoom: @zoom <br> Placeholder: @placeholder', [
        '@maptype' => $this->getSetting('map_type'),
        '@zoom' => $this->getSetting('zoom'),
        '@placeholder' => $this->getSetting('placeholder'),
      ]
    );

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $formatter_settings = $this->getSettings();
    $google_provider = \Drupal::service('plugin.manager.geolocation.mapprovider')->getMapProvider('google_maps');
    $google_settings = \Drupal::config('geolocation_google_maps.settings');

    foreach ($items as $delta => $item) {

      $map_settings = [
        'lat' => $item->get('lat')->getString(),
        'lng' => $item->get('lng')->getString(),
        'center' => $item->get('lat')->getString() . ',' . $item->get('lng')->getString(),
        'map_type' => $formatter_settings['map_type'],
        'zoom' => $formatter_settings['zoom'],
        'api_key' => $google_settings->get('google_map_api_key'),
      ];

      $elements[$delta] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['gmap', 'gmap-lazy-load'],
          'id' => Html::getUniqueId('gmap-lazy-load'),
          'data-lat' =>  $map_settings['lat'],
          'data-lng' =>  $map_settings['lng'],
          'data-maptype' => $map_settings['map_type'],
          'data-zoom' => $map_settings['zoom'],
        ],
      ];

      switch ($formatter_settings['placeholder']) {
        case 'static_map':

          $static_url = Url::fromUri($google_provider::$googleMapsApiUrlBase . '/maps/api/staticmap',[
            'query' => [
              'center' => $map_settings['center'],
              'zoom' => $map_settings['zoom'],
              'maptype' => $map_settings['map_type'],
              'size' => '800x300',
              'key' => $map_settings['api_key'],
            ],
          ]);

          $elements[$delta]['static_map'] = [
            '#type' => 'html_tag',
            '#tag' => 'img',
            '#attributes' => [
              'src' => $static_url->toString(),
            ],
          ];
          break;
        case 'link':
          $elements[$delta]['link'] = [
            '#markup' => 'link'
          ];
          break;
        default:
          break;
      }

    }

    $elements['#attached']['library'][] = 'nidirect_contacts/gmaps_lazy_load';
    $elements['#attached']['library'][] = 'geolocation_google_maps/google';

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return nl2br(Html::escape($item->value));
  }

}
