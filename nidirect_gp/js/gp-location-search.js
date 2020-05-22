/**
 * @file
 * Defines Javascript behaviors for Google Maps Lazy Load Formatter.
 */

(function ($, Drupal) {
  Drupal.behaviors.gpLocationSearch = {
    attach: (context) => {

      // Callback function to initiate the GP location search.
      function performLocationSearch(location) {
        let lat = location.coords.latitude;
        let lng = location.coords.longitude;

        let url = '/services/gp-practices?lat=' + lat +'&lng=' + lng + '&proximity=10';
        window.location.href = url;
      }

      // Callback function for geolocation errors.
      function locationError() {
        const confirmationDialog = Drupal.dialog('<div>Unable to determine your location.</div>', {
          title: Drupal.t('Sorry'),
          dialogClass: 'editor-change-text-format-modal',
          resizable: false,
          closeOnEscape: true,
          buttons: [{
            text: Drupal.t('OK'),
            class: 'button button--primary',
            click() {
              confirmationDialog.close();
            },
          }]
        });

        confirmationDialog.showModal();
      }

      $(context).find('#views-exposed-form-gp-practices-find-a-gp').once('gp-location-search').each(function () {
        // Detect if the the browser supports the Geolocation API
        if('geolocation' in navigator) {
          $(this).prepend('<div>' +
            '<label for="use_location">Search for a GP practice near you</label>' +
            '<input type="button" id="use_location" name="use_location" value="Use my location" />' +
            '</div>');

          $(this).find('#use_location').on('click', function() {
            navigator.geolocation.getCurrentPosition(performLocationSearch, locationError);
          })
        }
      });
    }
  };

}(jQuery, Drupal));
