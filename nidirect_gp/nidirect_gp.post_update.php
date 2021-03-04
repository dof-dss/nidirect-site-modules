<?php

/**
 * @file
 * Post update functions for GPs.
 */

/**
 * Set up Google Map Key.
 */
function nidirect_gp_post_update_set_up_google_map_key() {
  // Overwrite the google map api key that appears in config
  // as 'placeholder' with the one that is stored in the
  // GOOGLE_MAP_API_SERVER_KEY environment variable.

  $config_factory = \Drupal::configFactory();

  // Retrieve the geocoder provider settings.
  $googlemap_config = $config_factory->getEditable('geocoder.geocoder_provider.googlemaps')->get('configuration');

  if (!empty($googlemap_config) && isset($googlemap_config['apiKey'])) {
    // Overwrite the google map api key.
    \Drupal::logger('nidirect_gp')->notice(t("Successfully overwrote Googlemap api key"));
    $googlemap_config['apiKey'] = getenv('GOOGLE_MAP_API_SERVER_KEY');
    $config_factory->getEditable('geocoder.geocoder_provider.googlemaps')->set('configuration', $googlemap_config)->save();
  } else {
    $message = "Googlemap Geocoder provider not found at /admin/config/system/geocoder/geocoder-provider";
    \Drupal::logger('nidirect_gp')->error(t($message));
  }
}
