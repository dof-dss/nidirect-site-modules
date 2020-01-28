<?php

namespace Drupal\nidirect_common\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block plugin definitions.
 *
 * @see \Drupal\nidirect_common\Plugin\Block\NidirectFeedbackmenuLinkDerivative
 */
class NidirectFeedbackmenuLinkDerivative extends DeriverBase implements ContainerDeriverInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    $links['nidirect_feedbackmenulink'] = [
      'title' => 'Feedback',
      'menu_name' => 'main',
      'route_name' => 'entity.webform.canonical',
      'route_parameters' => [
        'webform' => 'site_feedback',
      ],
      'weight' => 8,
    ] + $base_plugin_definition;
    return $links;
  }

}
