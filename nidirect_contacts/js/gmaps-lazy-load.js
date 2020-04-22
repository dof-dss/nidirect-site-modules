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

              let mapLatLng = {
                lat: parseFloat(entry.target.dataset.lat),
                lng: parseFloat(entry.target.dataset.lng)
              };

              let mapSettings = {
                mapTypeId: entry.target.dataset.maptype,
                zoom: parseInt(entry.target.dataset.zoom),
                center: mapLatLng,
              };

              let map = new google.maps.Map(document.getElementById(entry.target.id), mapSettings);

              let marker = new google.maps.Marker({
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
