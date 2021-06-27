<?php

namespace Drupal\nidirect_gp\Controller;

use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\nidirect_gp\GpAutocompleteMatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\system\Controller\EntityAutocompleteController;

class GpAutocompleteController extends EntityAutocompleteController {

  /**
   * Autocomplete matcher for GP entities.
   */
  protected $matcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(GpAutocompleteMatcher $matcher, KeyValueStoreInterface $key_value) {
    $this->matcher = $matcher;
    $this->keyValue = $key_value;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nidirect_gp.gp_autocomplete_matcher'),
      $container->get('keyvalue')->get('entity_autocomplete')
    );
  }

}
