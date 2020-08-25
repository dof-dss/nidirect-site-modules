/**
 * @file
 * Defines Javascript behaviors for landing pages.
 */

(function ($, Drupal) {
  Drupal.behaviors.lbconfigteasers = {
    attach: (context) => {
      // Check when the user clicks on the 'manual control' checkbox.
      $(context).find('[data-drupal-selector="edit-settings-block-form-field-manually-control-listing-value"]').on("change", function(){
        alert('here we go');
        console.log('fired');
      });
    }
  };
}(jQuery, Drupal));
