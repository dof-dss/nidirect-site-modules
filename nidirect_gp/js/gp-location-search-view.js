/**
 * @file
 * Defines Javascript behaviors for Google Maps Lazy Load Formatter.
 */

(function ($, Drupal) {
  Drupal.behaviors.gpLocationSearchView = {
    attach: (context) => {

      $(context).find('#views-exposed-form-gp-practices-proximity-gp-location-search').once('gp-location-search-view').each(function() {
        // Hide the submit button
        $(this).find('#edit-submit-gp-practices-proximity').hide();

        // Trigger form submission on proximity change.
        $(this).find('#edit-proximity').on('change', function () {
          $('#views-exposed-form-gp-practices-proximity-gp-location-search').submit();
        });
      });
    }
  };

}(jQuery, Drupal));
