/**
 * @file
 * Defines Javascript behaviors for landing pages.
 */

(function ($, Drupal) {
  Drupal.behaviors.landinglisting = {
    attach: (context) => {
      // Look through all <select>s.
      $(context).find('select').each(function() {
        var $thisclass = $(this).attr('class');
        // Find any whose class starts with 'shs-'.
        if ($thisclass.indexOf('shs-') == 0) {
          $(this).on("change", function(){
            if ($(context).find('#edit-field-manually-control-listing-value').prop('checked') == true) {
              // This is an edge case, user has already ordered the listings but then
              // changes the subtheme. Just un-check the 'manual control' button and
              // hide the fields until after the node is saved for now.
              $(context).find('#edit-field-manually-control-listing-value')
                .trigger('click');
              $(context).find('#edit-field-manually-control-listing-wrapper')
                .hide();
              $(context).find('.field--type-entity-reference-revisions.field--name-field-listing.field--widget-paragraphs.js-form-wrapper.form-wrapper')
                .hide();
            }
          });
        }
      });
    }
  };
}(jQuery, Drupal));
