/**
 * @file
 * Defines Javascript behaviors for Google Maps Lazy Load Formatter.
 */

(function ($, Drupal) {
  Drupal.behaviors.gpLocationSearch = {
    attach: (context) => {

      let locationOptions = {
        enableHighAccuracy: false,
        timeout: 5000,
        maximumAge: 0
      };

      // Callback function to initiate the GP location search.
      function performLocationSearch(location) {
        let lat = location.coords.latitude;
        let lng = location.coords.longitude;
        const distance = drupalSettings.nidirect.gpSearch.maxDistance ?? 10;

        let url = '/services/gp-practices?lat=' + lat +'&lng=' + lng + '&proximity=' + distance;
        window.location.href = url;
      }

      // Callback function for geolocation errors.
      function locationError(err) {

        console.warn(`ERROR(${err.code}): ${err.message}`);
        $('#find-by-location-status').html('');

        let errmsg = '' +
          '<p>There was a problem finding your location.</p>' +
          '<p>Try searching by entering a GP name, practice, town or postcode</p>';

        const confirmationDialog = Drupal.dialog('<div>' + errmsg + '.</div>', {
          title: Drupal.t('Sorry'),
          dialogClass: 'editor-change-text-format-modal',
          resizable: false,
          closeOnEscape: true,
          buttons: [{
            text: Drupal.t('OK'),
            class: 'button button--primary',
            click() {
              confirmationDialog.close();
              $('#edit-search-api-views-fulltext').focus();
            },
          }]
        });

        confirmationDialog.showModal();
      }

      $(context).find('#views-exposed-form-gp-practices-find-a-gp').once('gp-location-search').each(function () {
        // Detect if the the browser supports the Geolocation API
        if('geolocation' in navigator) {
          $(this).prepend('<div class="find-by-location">' +
            '<label for="use_location">Search for a GP practice near you</label>' +
            '<input type="button" id="use_location" name="use_location" value="Use my location" />' +
            '<div id="find-by-location-status" role="alert" aria-live="assertive"></div>' +
            '</div>');

          $(this)
            .find('#use_location').on('click', function() {
              // Update status with a ajax progress indicator (even though it's not an ajax call).
              $('#find-by-location-status').html(Drupal.theme.ajaxProgressIndicatorFullscreen());

              // Do the location search
              navigator.geolocation.getCurrentPosition(performLocationSearch, locationError, locationOptions);
            })
            .find('label[for="edit-search-api-views-fulltext"]')
            .text('Or enter a GP name, practice, town or postcode');
        }
      });
    }
  };

}(jQuery, Drupal));
