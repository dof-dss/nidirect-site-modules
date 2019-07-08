<?php

namespace Drupal\nidirect_cold_weather_payments\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Provides a resource to view cold weather payment details.
 *
 * @RestResource(
 *   id = "cold_weather_payment_resource",
 *   label = @Translation("Cold weather payment resource"),
 *   uri_paths = {
 *     "canonical" = "/api/cwp/{postcode}"
 *   }
 * )
 */
class ColdWeatherPaymentResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * EntityQuery.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Constructs a new ColdWeatherPaymentResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager instance.
   * @param Drupal\Core\Entity\Query\QueryFactory $entityQuery
   *   Entity Query instance.
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      array $serializer_formats,
      LoggerInterface $logger,
      AccountProxyInterface $current_user,
      EntityTypeManagerInterface $entityTypeManager,
      QueryFactory $entityQuery
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityQuery = $entityQuery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
    $configuration,
    $plugin_id,
    $plugin_definition,
    $container->getParameter('serializer.formats'),
    $container->get('logger.factory')->get('nidirect_cold_weather_payments'),
    $container->get('current_user'),
    $container->get('entity_type.manager'),
    $container->get('entity.query')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param string $postcode
   *   The postcode to query payment against.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($postcode = NULL) {
    preg_match_all('/^(BT)?(\d{1,2})\s?/mi', $postcode, $matches, PREG_SET_ORDER, 0);
    $response['postcode'] = $matches[0][2];

    // Fetch the latest Payment period node.
    $query = $this->entityQuery->get('node')
      ->condition('type', 'cold_weather_payment')
      ->sort('created', 'DESC')
      ->range(0, 1);

    $vid = array_pop(array_keys($query->execute()));
    $node = $this->entityTypeManager->getStorage('node')->loadRevision($vid);

    // Payment period covered.
    $period = $node->get('field_cwp_payments_period')->getValue();
    $response['payments_period']['date_start'] = $period[0]['value'];
    $response['payments_period']['date_end'] = $period[0]['end_value'];

    // Payment triggers for the period.
    $payment_triggers = $node->get('field_cwp_payments_triggered');
    foreach ($payment_triggers as $trigger) {
      $payment_granted = FALSE;
      // We need to load each station to extract the postcodes it covers.
      $station_ids = explode(',', $trigger->get('stations')->getValue());
      $stations = $this->entityTypeManager->getStorage('weather_station')->loadMultiple($station_ids);

      $postcodes = [];
      foreach ($stations as $station) {
        $postcodes = array_merge($postcodes, explode(',', $station->get('postcodes')));
      }

      // Postcodes for stations are stored as the first 2 digits, strip BT
      // from the query postcode.
      if (in_array(str_replace('BT', '', $postcode), $postcodes)) {
        $payment_granted = TRUE;
      }

      $response['payments_triggered'][] = [
        "date_start" => $trigger->get('date_start')->getValue(),
        "date_end" => $trigger->get('date_end')->getValue(),
        "stations" => $trigger->get('stations')->getValue(),
        "postcodes" => $postcodes,
        "payment_granted" => $payment_granted,
      ];
    }

    return new ResourceResponse($response, 200);
  }

}
