/**
 * @file
 * Defines Javascript behaviors for Google Maps Lazy Load Formatter.
 */

(function ($, Drupal) {
  Drupal.behaviors.gmapslazyLoad = {
    attach: (context) => {
      $(context).find('.gmap-lazy-load').each(function () {
        console.log($(this));
      });
    }
  }
}(jQuery, Drupal));
