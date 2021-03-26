/**
 * @file
 * Defines Javascript behaviors for Google Maps Lazy Load Formatter.
 */

(function ($, Drupal) {
  Drupal.behaviors.gmapslazyLoad = {
    attach: (context) => {

      // Define observer options.
      let options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.66
      };

      // Callback for observer targets.
      let callback = (entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {

              entry.target.classList.add('gmap-loaded');

              // Define map latitude and longitude coordinates.
              let mapLatLng = {
                lat: parseFloat(entry.target.dataset.lat),
                lng: parseFloat(entry.target.dataset.lng)
              };

              // Google map settings.
              let mapSettings = {
                mapTypeId: entry.target.dataset.maptype,
                zoom: parseInt(entry.target.dataset.zoom),
                center: mapLatLng,
              };

              // If EU Cookie Compliance module is installed then check the
              // user has given consent to load Google maps, if they haven't
              // prevent lazy loading of the map and replace with a text link.
              if (Drupal.eu_cookie_compliance != undefined && Drupal.eu_cookie_compliance.hasAgreed() == false) {
                  let url = 'https://www.google.com/maps/search/?api=1&query=' + mapLatLng.lat + ',' + mapLatLng.lng;
                  let map = $('.' + entry.target.id);
                  // Replace map div contents with a text link to Google maps.
                  map.html('<a href="' + url + '" target="_blank" rel="noopener noreferrer">View this location on Google Maps' +
                    '<span class="visually-hidden">(external link opens in a new window / tab)</span>' +
                    '<svg aria-hidden="true" class="ico ico-elink"><title>external link opens in a new window / tab</title>' +
                    '<use xlink:href="#elink"></use></svg></a>');
                  // Override the gmap class which sets the height of the map div to 400px.
                  map.css('height', 'auto');q

                  // Unsubscribe this target from the observer.
                  observer.unobserve(entry.target);

                  return;
              }

              // Create a new google map targeting the observed target element.
              let map = new google.maps.Map(document.getElementById(entry.target.id), mapSettings);

              // Add a marker to the map using the existing coordinates.
              let marker = new google.maps.Marker({
                position: mapLatLng,
                map: map,
              });

              // Unsubscribe this target from the observer.
              observer.unobserve(entry.target);
            }
          });
        };

      // Subscribe gmap elements to the observer.
      $(context).find('.gmap-lazy-load').each(function () {
        let observer = new IntersectionObserver(callback, options);
        let target = $(this)[0];
        observer.observe(target);
      });
    }
  }
}(jQuery, Drupal));
