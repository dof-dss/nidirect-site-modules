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

    // Validate order number week and year.
    let today = new Date();
    console.log('Prison visit booking date is ' + today.toLocaleDateString('en-GB'));

    let today_week = today.getUTCWeekNumber();
    console.log('Week number is ' + today_week);

    let today_year = today.getUTCFullYear();
    console.log('Year is ' + today_year);

    let today_year_two_digit = today_year.toString().slice(2,4);
    console.log('Two digit year is ' + today_year_two_digit);

    /*if (pvbYear < today_year_two_digit || pvbWeek < today_week) {
      bookRefIsValid = false;
    }*/

    return bookRefIsValid;
  }, 'Visit reference number does not look correct or has expired.');

})(jQuery);
