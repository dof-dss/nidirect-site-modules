/**
 * @file
 * Defines Javascript behaviors for Media Entity Browser.
 */
(function ($) {

  "use strict";

  /**
   * Attaches the behavior of the media entity browser view.
   */
  Drupal.behaviors.viewMediaEntityBrowserView = {
    attach: function (context, settings) {
      // Initially set the Select button state to disabled as the user will
      // not have selected any media element.
      var $select_button = $('.is-entity-browser-submit', context);
      $select_button.prop('disabled', true);

      $('.views-row', context).click(function () {
        var $row = $(this);
        var $view = $row.parents('.view-media-entity-browser-view');
        var $select_button = $view.parent().find('.is-entity-browser-submit');

        // Because we can select multiple media elements, we need to check if
        // there are any rows with the checked class to determine the
        // enabled/disabled state of the select button.
        if ($view.find('.views-row.checked').length > 0) {
          $select_button.prop('disabled',false);
        } else {
          $select_button.prop('disabled', true);
        }
      });
    }
  };

}(jQuery, Drupal));
