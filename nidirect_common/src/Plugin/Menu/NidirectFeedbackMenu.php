<?php

/**
 * @file
 * Contains \Drupal\nidirect_common\Menu\NidirectFeedbackMenu.
 */

namespace Drupal\nidirect_common\Plugin\Menu;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Menu\MenuLinkDefault;

/**
 * Provides a default implementation for menu link plugins.
 */
class NidirectFeedbackMenu extends MenuLinkDefault {
  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $options = parent::getOptions();
    // Append the current path as 's' parameter.
    $page = \Drupal::request()->getRequestUri();
    if ($page == '/') {
      $page = '/front';
    }
    $options['query']['s'] = $page;
    return $options;
  }

  /**
   * {@inheritdoc}
   *
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
