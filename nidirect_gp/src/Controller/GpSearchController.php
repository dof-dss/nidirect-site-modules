<?php

namespace Drupal\nidirect_gp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormState;
use Drupal\geocoder\GeocoderInterface;
use Drupal\nidirect_gp\PostcodeExtractor;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class GpSearchController.
 *
 * Generates View results for fulltext and location based GP searches.
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
   * Machine key of the geocoding service.
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
   * Drupal Form Builder.
   *
   * @var \Drupal\core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * GpSearchController constructor.
   *
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
   * @param \Drupal\core\Form\FormBuilder $form_builder
   *   Form builder.
   */
  public function __construct(
    RequestStack $request_stack,
    PostcodeExtractor $postcode_extractor,
    GeocoderInterface $geocoder,
    int $proximity_max_distance,
    string $geocoding_service_id,
    FormBuilder $form_builder
  ) {

    $this->requestStack = $request_stack;
    $this->postcodeExtractor = $postcode_extractor;
    $this->geocoder = $geocoder;
    $this->proximityMaxDistance = $proximity_max_distance;
    $this->geocodingServiceId = $geocoding_service_id;
    $this->formBuilder = $form_builder;
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
      $container->getParameter('nidirect_gp.geocoding_service'),
      $container->get('form_builder')
    );
  }

  /**
   * Route title callback.
   *
   * @return string
   *   GP search page title.
   */
  public function getTitle() {

    $search_type = $this->searchType();

    switch ($search_type['type']) {
      case 'FULLTEXT':
        return $this->t('GP practice results for %querytext', ['%querytext' => '"' . $search_type['querytext'] . '"']);

      case 'POSTCODE':
        return $this->t('GP practices near %postcode', ['%postcode' => '"' . $search_type['postcode'][0] . '"']);

      case 'LOCATION':
        return $this->t('GP practices near your location');

      default:
        return $this->t('Find a GP practice');
    }
  }

  /**
   * Handles the request for GP practice content.
   *
   * We could include the form render array from GpSearchForm.php for
   * conciseness but by doing this we can't place the form in a block
   * in the sidebar region as per the present design.
   *
   * @return array
   *   Render array of items for Drupal to convert to a HTML response.
   */
  public function handleSearchRequest() {
    $search_type = $this->searchType();

    // Determine the View and View Display to use.
    if ($search_type['type'] === 'POSTCODE' || $search_type['type'] === 'LOCATION') {
      $view_id = 'gp_practices_proximity';
      $display_id = 'gps_by_proximity';
    }
    else {
      $view_id = 'gp_practices';
      $display_id = 'find_a_gp';
    }

    $view = $this->entityTypeManager()->getStorage('view')->load($view_id)->getExecutable();
    $view->setDisplay($display_id);
    $args = [];

    // If proximity search, add arguments to the View.
    if ($view_id == 'gp_practices_proximity') {

      // Set Postcode search arguments.
      if ($search_type['type'] === 'POSTCODE') {
        // Geocode the first postcode (only accept single values for search).
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

      // Pass the values into a single, pre-formatted string as per the
      // argument handlers requirements:
      // Ie: LAT,LON[OPERATOR]MAX_DISTANCE[mi|km].
      // Units of distance are pre-set on the views argument handler config.
      $args = [sprintf('%s,%s<=%dmi',
        $this->latitude,
        $this->longitude,
        $this->proximityMaxDistance),
      ];
    }

    // Generate the results View.
    $view->setArguments($args);
    $view->initHandlers();
    $view->preExecute();
    $view->execute();
    $view->buildRenderable($display_id, $args);

    // Set exposed form and cache contexts.
    $build['form'] = $this->viewForm($view);
    $build['form']['#cache']['contexts'][] = 'url.query_args:search_api_views_fulltext';
    $build['form']['#cache']['contexts'][] = 'url.query_args:lat';
    $build['form']['#cache']['contexts'][] = 'url.query_args:lng';

    $build['view'] = $view->render();
    $build['#attached']['drupalSettings']['nidirect']['gpSearch']['maxDistance'] = $this->proximityMaxDistance;

    return $build;

  }

  /**
   * Create the exposed filter form for GP searches.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   Current search View executable.
   *
   * @return array
   *   Form render array.
   */
  private function viewForm(ViewExecutable $view) {
    // To give a consistent UI across GP Search, for location based search the
    // View doesn't have an exposed form (it can't as it isn't fulltext search)
    // To work around this, we need to load the fulltext search based view and
    // render the exposed form before attaching to our render array.
    if ($view->id() === 'gp_practices' && $view->getDisplay() === 'find_a_gp') {
      $form_view = $view;
    }
    else {
      $form_view = $this->entityTypeManager()->getStorage('view')->load('gp_practices')->getExecutable();
      $form_view->setDisplay('find_a_gp');
    }

    $form_view->initHandlers();

    // Build the form state for the exposed View filters.
    $form_state = new FormState();
    $form_state->setFormState([
      'view' => $form_view,
      'display' => $form_view->display_handler->display,
      'exposed_form_plugin' => $form_view->display_handler->getPlugin('exposed_form'),
      'method' => 'get',
      'rerender' => TRUE,
      'no_redirect' => TRUE,
      'always_process' => TRUE,
    ]);
    $form_state->setMethod('get');

    return $this->formBuilder()->buildForm('Drupal\views\Form\ViewsExposedForm', $form_state);
  }

  /**
   * Determines the type of GP search performed.
   *
   * @return array
   *   Array with search type and additional data.
   */
  private function searchType() {

    $request = $this->requestStack->getCurrentRequest();

    // Text search.
    $query_term = $request->get('search_api_views_fulltext');

    if (!empty($query_term)) {

      $output = [
        'type' => 'FULLTEXT',
        'querytext' => $query_term,
      ];

      // Postcode search (if a postcode can be extracted).
      $postcode = $this->postcodeExtractor->getPostCode($query_term);

      if (!empty($postcode)) {
        $output = [
          'type' => 'POSTCODE',
          'postcode' => $postcode,
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
