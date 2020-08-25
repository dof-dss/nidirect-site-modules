/**
 * @file
 * Defines Javascript behaviors for landing pages.
 */

(function ($, Drupal) {
  Drupal.behaviors.lbconfigteasers = {
    attach: (context) => {
      // Check when the user clicks on the 'manual control' checkbox.
      $(context).find('[data-drupal-selector="edit-settings-block-form-field-manually-control-listing-value"]').on("change", function(){
        if ($(context).find('[data-drupal-selector="edit-settings-block-form-field-manually-control-listing-value"]').prop("checked") == true) {
          // Show teasers so that they can be manually controlled.
          $(context).find('[data-drupal-selector="edit-settings-block-form-field-article-teasers-wrapper"]').show();
        } else {
          // Hide teasers as auto populated.
          $(context).find('[data-drupal-selector="edit-settings-block-form-field-article-teasers-wrapper"]').hide();
        }
      });
    }
  };
}(jQuery, Drupal));
