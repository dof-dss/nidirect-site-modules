<?php

namespace Drupal\nidirect_cold_weather_payments\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides cold weather payments information.
 */
class ColdWeatherPaymentsService {

  /**
   * Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cold Weather Payments Service constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }


  public function PaymentsForPostcode($postcode = NULL) {
    return NULL;
  }

}
