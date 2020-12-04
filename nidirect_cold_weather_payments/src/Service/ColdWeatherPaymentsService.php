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
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /*
   * Returns a list of payments (or not) for a postcode during the most recent
   * payments period.
   */
  public function ForPostcode($postcode = NULL) {
    return NULL;
  }

}
