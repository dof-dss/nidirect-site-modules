<?php

namespace Drupal\Tests\nidirect_cold_weather_payments\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Cold Weather Payments tests
 *
 * @group nidirect_cold_weather_payments
 * @group nidirect
 */
class ColdWeatherPaymentsTest extends KernelTestBase {

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

    $this->installConfig(['nidirect_cold_weather_payments']);
    $this->installEntitySchema('cold_weather_payment');
  }

}
