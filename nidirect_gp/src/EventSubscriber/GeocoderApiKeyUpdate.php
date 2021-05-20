<?php

namespace Drupal\nidirect_gp\EventSubscriber;

use Drupal\Core\Config\ConfigCollectionInfo;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Config\Importer\MissingContentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A subscriber that overwrites Google Maps api keys on config import.
 */
class GeocoderApiKeyUpdate implements EventSubscriberInterface {

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    return array(
      ConfigEvents::IMPORT => array('onProviderChange'),
      ConfigEvents::IMPORT_VALIDATE => array('onEmptyImport'),
      ConfigEvents::COLLECTION_INFO => array('addCollections'),
      ConfigEvents::IMPORT_MISSING_CONTENT => array('onMissingContent')
    );
    //$events[ConfigEvents::SAVE][] = ['onConfigSave', 40];
    //$events[ConfigEvents::IMPORT][] = ['onProviderChange', 40];
    //$events[ConfigEvents::IMPORT_VALIDATE][] = ['onEmptyImport', 40];
    \Drupal::logger('nidirect_gp')->notice(t("registering for config import event"));
    return $events;
  }

  /**
   * Handles the missing content event.
   *
   * @param \Drupal\Core\Config\Importer\MissingContentEvent $event
   *   The missing content event.
   */
  public function onMissingContent(MissingContentEvent $event) {
    \Drupal::logger('nidirect_gp')->notice(t("FIRING missing content event !"));
    $config_update_service = \Drupal::service('nidirect_common.update_config_from_environment');
    $config_update_service->updateApiKey('geocoder.geocoder_provider.googlemaps', 'apiKey', 'GOOGLE_MAP_API_SERVER_KEY');
  }

  /**
   * Reacts to the ConfigEvents::COLLECTION_INFO event.
   *
   * @param \Drupal\Core\Config\ConfigCollectionInfo $collection_info
   *   The configuration collection info event.
   */
  public function addCollections(ConfigCollectionInfo $collection_info) {
    \Drupal::logger('nidirect_gp')->notice(t("FIRING add collection import event !"));
    $config_update_service = \Drupal::service('nidirect_common.update_config_from_environment');
    $config_update_service->updateApiKey('geocoder.geocoder_provider.googlemaps', 'apiKey', 'GOOGLE_MAP_API_SERVER_KEY');
  }


  /**
   * Overwrite Google Maps API key before config import.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The Event to process.
   */
  public function onEmptyImport(ConfigImporterEvent $event) {
    // We need to set up a Geocoder provider in config but we can't store a
    // Google Maps API key in config so we just put a placeholder in.
    // When the geocoder config is imported or updated code has been added
    // here to overwrite the API key with the one stored in the environment
    // variable.
    \Drupal::logger('nidirect_gp')->notice(t("FIRING empty import event !"));
    $config_update_service = \Drupal::service('nidirect_common.update_config_from_environment');
    $config_update_service->updateApiKey('geocoder.geocoder_provider.googlemaps', 'apiKey', 'GOOGLE_MAP_API_SERVER_KEY');
  }

  /**
   * Overwrite Google Maps API key before config import.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onSaveImport(ConfigCrudEvent $event) {
    // We need to set up a Geocoder provider in config but we can't store a
    // Google Maps API key in config so we just put a placeholder in.
    // When the geocoder config is imported or updated code has been added
    // here to overwrite the API key with the one stored in the environment
    // variable.
    \Drupal::logger('nidirect_gp')->notice(t("FIRING Save import event !"));
    $config_update_service = \Drupal::service('nidirect_common.update_config_from_environment');
    $config_update_service->updateApiKey('geocoder.geocoder_provider.googlemaps', 'apiKey', 'GOOGLE_MAP_API_SERVER_KEY');
  }

  /**
   * Overwrite Google Maps API key when it changes.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The Event to process.
   */
  public function onProviderChange(ConfigImporterEvent $event) {
    // We need to set up a Geocoder provider in config but we can't store a
    // Google Maps API key in config so we just put a placeholder in.
    // When the geocoder config is imported or updated code has been added
    // here to overwrite the API key with the one stored in the environment
    // variable.
    \Drupal::logger('nidirect_gp')->notice(t("FIRING config import event !"));
    $change_list = $event->getChangelist();
    if (!empty($change_list)) {
      if ((isset($change_list['update']) && ($change_list['update'][0] == 'geocoder.geocoder_provider.googlemaps')) ||
        (isset($change_list['create']) && ($change_list['create'][0] == 'geocoder.geocoder_provider.googlemaps'))) {
        $config_update_service = \Drupal::service('nidirect_common.update_config_from_environment');
        $config_update_service->updateApiKey('geocoder.geocoder_provider.googlemaps', 'apiKey', 'GOOGLE_MAP_API_SERVER_KEY');
      }
    }
  }
}
