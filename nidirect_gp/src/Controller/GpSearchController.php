<?php

namespace Drupal\nidirect_gp\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\geocoder\GeocoderInterface;
use Drupal\geocoder\ProviderPluginManager;
use Drupal\nidirect_gp\PostcodeExtractor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class GpSearchController.
 */
class GpSearchController extends ControllerBase {

  /**
   * Maximum distance (miles) for any proximity search to use.
   *
   * @var int
   */
  protected $proximityMaxDistance;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\Core\Block\BlockManagerInterface definition.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $pluginManagerBlock;

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
   * Geocoder provider service.
   *
   * @var Drupal\geocoder\ProviderPluginManager
   */
  protected $geocoderProvider;

  /**
   * GpSearchController constructor.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Block\BlockManagerInterface $plugin_manager_block
   *   Plugin manager interface.
   * @param \Drupal\nidirect_gp\PostcodeExtractor $postcode_extractor
   *   Postcode extractor service.
   * @param int $proximity_max_distance
   *   Service parameter, in miles. Units set on views argument handler config.
   * @param \Drupal\geocoder\GeocoderInterface $geocoder
   *   Geocoding service.
   * @param \Drupal\geocoder\ProviderPluginManager $geocoder_provider
   *   Geocoding provider manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request_stack, BlockManagerInterface $plugin_manager_block, PostcodeExtractor $postcode_extractor, int $proximity_max_distance, GeocoderInterface $geocoder, ProviderPluginManager $geocoder_provider) {
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
    $this->pluginManagerBlock = $plugin_manager_block;
    $this->postcodeExtractor = $postcode_extractor;
    $this->proximityMaxDistance = $proximity_max_distance;
    $this->geocoder = $geocoder;
    $this->geocoderProvider = $geocoder_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('plugin.manager.block'),
      $container->get('nidirect.postcode_extractor'),
      $container->getParameter('nidirect_gp.proximity_max_distance'),
      $container->get('geocoder'),
      $container->get('plugin.manager.geocoder.provider')
    );
  }

  /**
   * Handles the request for GP practice content.
   *
   * @return array
   *   Render array of items for Drupal to convert to a HTML response.
   */
  public function handleSearchRequest() {
    // Query term is validated by the routing definition, see nidirect_gp.routing.yml.
    $query_term = $this->requestStack->getCurrentRequest()->get('search_api_views_fulltext');

    $is_proximity_search = FALSE;

    if (!empty($query_term)) {
      $postcode = $this->postcodeExtractor->getPostCode($query_term);
      $is_proximity_search = !empty($postcode);
    }

    $view_id = $is_proximity_search ? 'gp_practices_proximity' : 'gp_practices';
    $display_id = $is_proximity_search ? 'gps_by_proximity' : 'find_a_gp';

    // Default to GP search by text.
    $build['gp_search'] = [
      '#type' => 'view',
      '#name' => $view_id,
      '#display_id' => $display_id,
    ];

    if ($view_id == 'gp_practices_proximity' && $is_proximity_search) {
      // Geocode the first postcode (only accepting single values in our search).
      $geocode_coordinates = $this->geocoder->geocode($postcode[0], ['googlemaps'])->first()->getCoordinates();

      // Pass the values into a single, pre-formatted string as per the argument handlers requirements:
      // Ie: LAT,LON[OPERATOR]MAX_DISTANCE.
      // Units of distance are pre-set on the views argument handler config.
      $build['gp_search']['#arguments'] = [sprintf('%s,%s<=%d',
        $geocode_coordinates->getLatitude(),
        $geocode_coordinates->getLongitude(),
        $this->proximityMaxDistance)
      ];
    }

    return $build;
  }

}
