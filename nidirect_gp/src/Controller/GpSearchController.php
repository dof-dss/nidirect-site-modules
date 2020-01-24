<?php

namespace Drupal\nidirect_gp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GpSearchController.
 */
class GpSearchController extends ControllerBase {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->requestStack = $container->get('request_stack');
    $instance->pluginManagerBlock = $container->get('plugin.manager.block');
    return $instance;
  }

  /**
   * Handles the request for GP practice content.
   *
   * @return array
   *   Render array of items for Drupal to convert to a HTML response.
   */
  public function handleSearchRequest() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: searchByKeyword')
    ];
  }

  /**
   * Delegate callback; can be used from main controller function
   * to trigger the use of a separate view to search by proximity
   * rather than keywords.
   *
   * @return array
   *   Render array of items for Drupal to convert to a HTML response.
   */
  public function searchByProximity() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: searchByProximity')
    ];
  }

}
