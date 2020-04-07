/**
 * @file
 * Javascript behaviors for Telephone Plus field overrides.
 */
(function ($) {

  "use strict";

  /**
   * Attach behaviour to allow altering of Telephone Plus title field
   */
  Drupal.behaviors.telephonePlusPredefined = {
    attach: function (context, settings) {
      $('.telephone-predefined', context)
        .once('telephone-predefined-select')
        .change(function() {
          if ($(this).val() == 'other') {
            $(this).parent().next().find('.telephone-title').val('');
          }
        });
    },
  }

}(jQuery, Drupal));
