/**
 * @file
 * Defines Javascript behaviors for Google Maps Lazy Load Formatter.
 */

(function ($, Drupal) {
  Drupal.behaviors.gmapslazyLoad = {
    attach: (context) => {

      let options = {
        root: null,
        rootMargin: '0px',
        threshold: 1.0
      };

      let callback = (entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              // Call google map creation.
              entry.target.classList.add('gmap-loaded');

              var mapLatLng = {
                lat: parseFloat(entry.target.dataset.lat),
                lng: parseFloat(entry.target.dataset.lng)
              };

              var map = new google.maps.Map(document.getElementById(entry.target.id), {
                center: mapLatLng,
                zoom: 12
              });

              var marker = new google.maps.Marker({
                position: mapLatLng,
                map: map,
              });

              observer.unobserve(entry.target);
            }
          });
        };

      $(context).find('.gmap-lazy-load').each(function () {
        let observer = new IntersectionObserver(callback, options);
        let target = $(this)[0];
        observer.observe(target);
      });
    }
  }
}(jQuery, Drupal));
