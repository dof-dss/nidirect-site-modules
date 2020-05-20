/**
 * @file
 * Defines Javascript behaviors for Google Maps Lazy Load Formatter.
 */

(function ($, Drupal) {
  Drupal.behaviors.gpLocationSearch = {
    attach: (context) => {

      $(context).find('#views-exposed-form-gp-practices-find-a-gp').once('gp-location-search').each(function () {
        // Detect if the the browser supports the Geolocation API
        if('geolocation' in navigator) {
          $(this).prepend('<div>' +
            '<label for="use_location">Search for a GP practice near you</label>' +
            '<input type="button" id="use_location" name="use_location" value="Use my location" />' +
            '</div>');
        }
      });
    }
  }
}(jQuery, Drupal));
