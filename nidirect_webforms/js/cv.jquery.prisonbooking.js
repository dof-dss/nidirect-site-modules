/**
 * @file
 * JQuery to look for pages that contain the feedback webform and other wedforms.
 * If other webforms are found then the ids of the submit buttons are updated
 * to make sure that they are unique.
 */

(function($, Drupal, once) {

  Drupal.behaviors.prisonVisit = {
    attach: function (context, settings) {

      const prisonVisitForm = once('prisonVisitForm', 'form.webform-submission-prison-visit-online-booking-form', context);

      let $prisonVisitOrderNumber = $(prisonVisitForm).find('input[name="visitor_order_number"]');
      let visitPrisons = drupalSettings.prisonVisitBooking.prisons;
      let visitTypes = drupalSettings.prisonVisitBooking.visit_type;
      let visitAdvanceNotice = drupalSettings.prisonVisitBooking.visit_advance_notice;
      let visitBookingRefValidityPeriodDays = drupalSettings.prisonVisitBooking.booking_reference_validity_period_days;
      let visitSlotsAvailable = drupalSettings.prisonVisitBooking.visit_slots;
      let visitSequenceAffiliations = drupalSettings.prisonVisitBooking.visit_order_number_categories;

      $prisonVisitOrderNumber.rules( "add", {
        validPrisonVisitBookingRef: [
          true,
          visitPrisons,
          visitTypes,
          visitAdvanceNotice,
          visitBookingRefValidityPeriodDays,
          visitSlotsAvailable,
          visitSequenceAffiliations
        ],
        expiredVisitBookingRef: [
          true,
          visitPrisons,
          visitTypes,
          visitAdvanceNotice,
          visitBookingRefValidityPeriodDays,
          visitSlotsAvailable,
          visitSequenceAffiliations
        ]
      });

    }
  };
})(jQuery, Drupal, once);
