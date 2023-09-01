/**
 * @file
 * jquery-validation method for prison booking reference number.
 *
 */
(function ($) {

  $.validator.addMethod('validPrisonVisitBookingRef', function (value, element, params) {
    let bookRefIsValid = true;
    let pvbID = value.slice(0, 2),
        pvbType = value.slice(2, 3),
        pvbWeek = value.slice(3, 5),
        pvbYear = value.slice(5, 7),
        pvbSeq = value.slice(9, 12);

    if (params[1].includes(pvbID) !== true) {
      bookRefIsValid = false;
    }

    if (params[2].includes(pvbType) !== true) {
      bookRefIsValid = false;
    }

    return bookRefIsValid;
  }, 'Visit reference number does not look correct or has expired.');

})(jQuery);
