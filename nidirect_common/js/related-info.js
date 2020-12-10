/**
 * @file
 * Defines Javascript behaviors for Related Info / More Useful links fields.
 */
(function ($) {

  "use strict";

  /**
   * Extracts and copies the title of internal nodes from the URL input to the Link text input.
   */
  Drupal.behaviors.relatedInfo = {
    attach: function (context, settings) {
      // Match nid which is the last occurrence of open bracket, number, close bracket.
      const regex = /\(\d+\)$/gm;

      $('.field--name-field-related-info input.form-autocomplete').autocomplete({
        close: function(event, ui) {
          // Only process the title if we are dealing with internal urls.
          if (this.value.startsWith('http://') === false || this.value.startsWith('https://') === false) {
            let title = this.value.replace(regex, '');
            $(this).parent().next().children('input').val(title);
          }
        }
      });
    }
  };

}(jQuery, Drupal));
