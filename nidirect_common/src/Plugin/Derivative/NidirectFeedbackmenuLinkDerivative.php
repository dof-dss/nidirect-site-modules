<?php

/**
 * @file
 * Contains \Drupal\nidirect_common\Plugin\Derivative\NidirectFeedbackmenuLinkDerivative.php.
 */
namespace Drupal\nidirect_common\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block plugin definitions.
 *
 * @see \Drupal\nidirect_common\Plugin\Block\NidirectFeedbackmenuLinkDerivative
 */
class NidirectFeedbackmenuLinkDerivative extends DeriverBase implements ContainerDeriverInterface
{
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id)
  {
    return new static();
  }
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition)
  {
    $links = array();

    /*
     * title: 'Feedback'
  route_name: entity.webform.canonical
  route_parameters: { webform: 'site_feedback' }
  options:
    query:
      s: 'front'
  menu_name: main
  weight: 8
     */
    $links['nidirect_feedbackmenulink'] = [
        'title' => 'Feedback XX',
        'menu_name' => 'main',
        'route_name' => 'entity.webform.canonical',
        'route_parameters' => [
          'webform' => 'site_feedback',
        ],
        'weight' => 8
      ] + $base_plugin_definition;
    return $links;
  }

}
