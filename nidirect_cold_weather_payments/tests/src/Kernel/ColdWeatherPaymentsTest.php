<?php

namespace Drupal\Tests\nidirect_cold_weather_payments\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Cold Weather Payments tests.
 *
 * @group nidirect_cold_weather_payments
 * @group nidirect
 */
class ColdWeatherPaymentsTest extends KernelTestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * List of Weather station entities.
   *
   * @var array
   */
  protected $stations;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'text',
    'datetime',
    'datetime_range',
    'user',
    'nidirect_cold_weather_payments',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('weather_station');
    $this->installConfig(['nidirect_cold_weather_payments']);

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->stations = $this->entityTypeManager->getStorage('weather_station')->loadMultiple();
  }

}
