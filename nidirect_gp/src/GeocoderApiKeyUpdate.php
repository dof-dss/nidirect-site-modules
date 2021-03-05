<?php

namespace Drupal\nidirect_gp;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A subscriber invalidating cache tags when color config objects are saved.
 */
class GeocoderApiKeyUpdate implements EventSubscriberInterface {

  /**
   * Invalidate cache tags when a color theme config object changes.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The Event to process.
   */
  public function onChange(ConfigImporterEvent $event) {
    // Changing a theme's color settings causes the theme's asset library
    // containing the color CSS file to be altered to use a different file.
    \Drupal::logger('nidirect_gp')->notice(t("checking for geocoder"));
    if (($event->getChangelist()['update'][0] == 'geocoder.geocoder_provider.googlemaps') ||
      ($event->getChangelist()['create'][0] == 'geocoder.geocoder_provider.googlemaps')) {
      $config_factory = \Drupal::configFactory();

      // Retrieve the geocoder provider settings.
      $googlemap_config = $config_factory->getEditable('geocoder.geocoder_provider.googlemaps')->get('configuration');

      if (!empty($googlemap_config) && isset($googlemap_config['apiKey'])) {
        // Overwrite the google map api key.
        \Drupal::logger('nidirect_gp')->notice(t("xxSuccessfully overwrote Googlemap api key"));
        $googlemap_config['apiKey'] = getenv('GOOGLE_MAP_API_SERVER_KEY');
        $config_factory->getEditable('geocoder.geocoder_provider.googlemaps')->set('configuration', $googlemap_config)->save();
      } else {
        $message = "Googlemap Geocoder provider not found at /admin/config/system/geocoder/geocoder-provider";
        \Drupal::logger('nidirect_gp')->error(t($message));
      }
    }
  }

  /**
   * Creates a config snapshot.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The Event to process.
   */
  public function onConfigImporterImport(ConfigImporterEvent $event) {
    $this->configManager->createSnapshot($this->sourceStorage, $this->snapshotStorage);
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    \Drupal::logger('nidirect_gp')->notice(t("getsub"));
    $events[ConfigEvents::IMPORT][] = ['onChange', 40];
    return $events;
  }

}
