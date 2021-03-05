<?php

namespace Drupal\nidirect_gp;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A subscriber that overwrites Google Maps api keys on config import.
 */
class GeocoderApiKeyUpdate implements EventSubscriberInterface {

  /**
   * Overwrite Google Maps API key when it changes.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The Event to process.
   */
  public function onProviderChange(ConfigImporterEvent $event) {
    // We need to set up a Geocoder provider in config but we can't store a Google Maps API key
    // in config so we just put a placeholder in. When the geocoder config is imported or
    // updated code has been added here to overwrite the API key with the one stored in
    // the environment variable.
    $change_list = $event->getChangelist();
    if (!empty($change_list)) {
      if ((isset($change_list['update']) && ($change_list['update'][0] == 'geocoder.geocoder_provider.googlemaps')) ||
        (isset($change_list['create']) && ($change_list['create'][0] == 'geocoder.geocoder_provider.googlemaps'))) {
        $config_factory = \Drupal::configFactory();

        // Retrieve the geocoder provider settings.
        $googlemap_config = $config_factory->getEditable('geocoder.geocoder_provider.googlemaps')->get('configuration');

        if (!empty($googlemap_config) && isset($googlemap_config['apiKey'])) {
          // Overwrite the google map api key.
          $googlemap_config['apiKey'] = getenv('GOOGLE_MAP_API_SERVER_KEY');
          $config_factory->getEditable('geocoder.geocoder_provider.googlemaps')->set('configuration', $googlemap_config)->save();
        } else {
          $message = "Googlemap Geocoder provider not found at /admin/config/system/geocoder/geocoder-provider";
          \Drupal::logger('nidirect_gp')->error(t($message));
        }
      }
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::IMPORT][] = ['onProviderChange', 40];
    return $events;
  }

}
