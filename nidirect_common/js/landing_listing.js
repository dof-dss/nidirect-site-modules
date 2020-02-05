/**
 * @file
 * Defines Javascript behaviors for the flag module.
 */

(function ($, Drupal) {
  Drupal.behaviors.landinglisting = {
    attach: (context) => {
      // Add submit handler to file upload form.
      $(context).find('.shs-select')
        .on('change', function (event) {
          var $form = $(this);
          /*$(context).find('#edit-field-manually-control-listing-value')
            .prop('checked', false);*/
          if ($(context).find('#edit-field-manually-control-listing-value').prop('checked') == true) {
            $(context).find('#edit-field-manually-control-listing-value')
              .trigger('click');
          }
          /*$(context).find('.field--type-entity-reference-revisions.field--name-field-listing.field--widget-paragraphs.js-form-wrapper.form-wrapper')
            .hide();*/
          alert('yeahhhh');
        });
    }
  };
}(jQuery, Drupal));
