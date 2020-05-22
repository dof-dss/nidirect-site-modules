<?php

namespace Drupal\nidirect_gp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\geocoder\GeocoderInterface;
use Drupal\nidirect_gp\PostcodeExtractor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class GpSearchController.
 *
 * GpSearchForm block class used to place exposed views filter into
 * sidebar region without requiring the use of AJAX for exposed filters.
 */
class GpSearchController extends ControllerBase {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Postcode extractor service.
   *
   * @var \Drupal\nidirect_gp\PostcodeExtractor
   */
  protected $postcodeExtractor;

  /**
   * Geocoder service.
   *
   * @var \Drupal\geocoder\GeocoderInterface
   */
  protected $geocoder;

  /**
   * Maximum distance (miles) for any proximity search to use.
   *
   * @var int
   */
  protected $proximityMaxDistance;

  /**
   * Machine key of the geocoding service we want to use (sourced from container).
   *
   * @var string
   */
  protected $geocodingServiceId;

  /**
   * Latitude coordinate.
   *
   * @var float
   */
  protected $latitude;

  /**
   * Longitude coordinate.
   *
   * @var float
   */
  protected $longitude;

  /**
   * GpSearchController constructor.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack object.
   * @param \Drupal\nidirect_gp\PostcodeExtractor $postcode_extractor
   *   Postcode extractor service.
   * @param \Drupal\geocoder\GeocoderInterface $geocoder
   *   Geocoder service.
   * @param int $proximity_max_distance
   *   Max distance in miles for geocoding radius.
   * @param string $geocoding_service_id
   *   Geocoding service ID.
   */
  public function __construct(
    RequestStack $request_stack,
    PostcodeExtractor $postcode_extractor,
    GeocoderInterface $geocoder,
    int $proximity_max_distance,
    string $geocoding_service_id
  ) {

    $this->requestStack = $request_stack;
    $this->postcodeExtractor = $postcode_extractor;
    $this->geocoder = $geocoder;
    $this->proximityMaxDistance = $proximity_max_distance;
    $this->geocodingServiceId = $geocoding_service_id;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('nidirect.postcode_extractor'),
      $container->get('geocoder'),
      $container->getParameter('nidirect_gp.proximity_max_distance'),
      $container->getParameter('nidirect_gp.geocoding_service')
    );
  }

  /**
   * Route title callback.
   *
   * @return string
   *   GP search page title.
   */
  public function getTitle() {

    $search_type = $this->_searchType();

    switch ($search_type['type']) {
      case 'POSTCODE':
        return $this->t('GP practices near %postcode', ['%postcode' => $search_type['postcode'][0]]);

      case 'LOCATION':
        return $this->t('GP practices near your location');

      default:
        return $this->t('GP practices');
    }
  }

  /**
   * Handles the request for GP practice content.
   *
   * We could include the form render array from GpSearchForm.php for conciseness but by
   * doing this we can't place the form in a block in the sidebar region as per the present design.
   *
   * @return array
   *   Render array of items for Drupal to convert to a HTML response.
   */
  public function handleSearchRequest() {
    $search_type = $this->_searchType();

    // Determine the View and View Display to use.
    if ($search_type['type'] === 'POSTCODE' || $search_type['type'] === 'LOCATION') {
      $view_id = 'gp_practices_proximity';
      $display_id = 'gps_by_proximity';
    } else {
      $view_id = 'gp_practices';
      $display_id = 'find_a_gp';
    }

    // Default to GP search by text.
    $build['gp_search'] = [
      '#type' => 'view',
      '#name' => $view_id,
      '#display_id' => $display_id,
    ];

    // If proximity search, add arguments to the View.
    if ($view_id == 'gp_practices_proximity') {

      // Set Postcode search arguments.
      if ($search_type['type'] === 'POSTCODE') {
        // Geocode the first postcode (only accepting single values in our search).
        $geocode_task_results = $this->geocoder->geocode($search_type['postcode'][0], [$this->geocodingServiceId]);

        if (!empty(($geocode_task_results))) {
          $geocode_coordinates = $geocode_task_results->first()->getCoordinates();
          $this->latitude = $geocode_coordinates->getLatitude();
          $this->longitude = $geocode_coordinates->getLongitude();
        }
      }

      // Set Location search arguments.
      if ($search_type['type'] === 'LOCATION') {
        $this->latitude = $search_type['lat'];
        $this->longitude = $search_type['lng'];
      }

      // Pass the values into a single, pre-formatted string as per the argument handlers requirements:
      // Ie: LAT,LON[OPERATOR]MAX_DISTANCE[mi|km].
      // Units of distance are pre-set on the views argument handler config.
      $build['gp_search']['#arguments'] = [sprintf('%s,%s<=%dmi',
        $this->latitude,
        $this->longitude,
        $this->proximityMaxDistance)
      ];

    }
    return $build;

  }

  /**
   * Determines the type of GP search performed.
   *
   * @return array
   *   Array with search type and additional data.
   */
  private function _searchType() {

    $request = $this->requestStack->getCurrentRequest();

    $output = [
      'type' => 'FULLTEXT'
    ];

    // Postcode search.
    $query_term = $request->get('search_api_views_fulltext');

    if (!empty($query_term)) {
      $postcode = $this->postcodeExtractor->getPostCode($query_term);

      if (!empty($postcode)) {
        $output = [
          'type' => 'POSTCODE',
          'postcode' => $postcode
        ];
      }
    }

    // Geolocation search.
    $lat = $request->get('lat');
    $lng = $request->get('lng');

    if (!empty($lat) && !empty($lng)) {
      $output = [
        'type' => 'LOCATION',
        'lat' => $lat,
        'lng' => $lng,
      ];
    }

    return $output;
  }
}
