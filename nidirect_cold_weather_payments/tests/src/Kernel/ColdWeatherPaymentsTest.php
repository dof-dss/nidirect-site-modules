<?php

namespace Drupal\Tests\nidirect_cold_weather_payments\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;

/**
 * Cold Weather Payments tests.
 *
 * @group nidirect_cold_weather_payments
 * @group nidirect
 */
class ColdWeatherPaymentsTest extends EntityKernelTestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cold Weather Payments service.
   *
   * @var \Drupal\nidirect_cold_weather_payments\Service\ColdWeatherPaymentsService
   */
  protected $paymentsService;

  /**
   * List of Weather station entities.
   *
   * @var array
   */
  protected $stations;

  /**
   * List of payment entities.
   *
   * @var array
   */
  protected $payment;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'system',
    'node',
    'field',
    'text',
    'datetime',
    'datetime_range',
    'filter',
    'entity_test',
    'nidirect_cold_weather_payments',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig('nidirect_cold_weather_payments');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->paymentsService = $this->container->get('nidirect_cold_weather_payments.payments');

    $this->stations = $this->entityTypeManager->getStorage('weather_station')->loadMultiple();

    Node::create([
      'title' => t('CWP test'),
      'type' => 'cold_weather_payment',
      'language' => 'en',
      'field_cwp_payments_period' => [
        'value' => "2020-12-03",
        'end_value' => "2020-12-25",
      ],
      'field_cwp_payments_triggered' => [
        [
          'date_start' => '2020-12-03',
          'date_end' => '2020-12-17',
          'stations' => 'aldergrove,glenanne,magilligan',
        ],
        [
          'date_start' => '2020-12-10',
          'date_end' => '2020-12-22',
          'stations' => 'katesbridge',
        ],
        [
          'date_start' => '2020-12-22',
          'date_end' => '2020-12-28',
          'stations' => 'aldergrove',
        ],
      ],
    ])->save();

    $this->payment = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'cold_weather_payment']);

  }

  /**
   * Tests that payment details are returned for qualifying postcode.
   */
  public function testPostcodeHasPayment() {

    // Postcode that matches the 'Katesbridge' station.
    $postcode = 'BT24 1AB';

    $result = $this->paymentsService->forPostcode($postcode);

    self::assertEquals([
      'date_start' => '2020-12-10',
      'date_end' => '2020-12-22',
      'stations' => 'katesbridge',
      'postcodes' => [24,25,26,30,31,32,33,34],
      'payment_granted' => true,

    ], $result['payments_triggered'][1]);
  }

}
