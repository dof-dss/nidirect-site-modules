/**
 * @file
 * Defines Javascript behaviors for Link handling.
 */
(function ($) {

  "use strict";

  /**
   * Changes any page links, within certain defined regions, from
   * <a> elements to <span> elements if the current path matches the
   * 'href' attribute.
   *
   * header.header
   * #block-mainnavigation
   * #main-content
   * #footer
   */
  Drupal.behaviors.rewriteSelfReferencingLinks = {
    attach: function (context, settings) {
      const pathname = $(location).attr('pathname');
      const elements = ['header.header', '#block-mainnavigation', '#main-content', '#footer'];

      $.each(elements, function(index, elementRef) {
        let classes = ['active', 'link__self'];

        if (elementRef === '#block-mainnavigation') {
          classes.push('nav-link');
        }

        $(elementRef + ' a[href*="' + pathname + '"]').each(function() {
          if ($(this).attr('href') === pathname) {
            $(this).replaceWith('<span class="' + classes.join(' ') + '">' + $(this).text() + '</span>');
          }
        });
      });
    }
  };

}(jQuery, Drupal));
