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
  // Class overrides here.
  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $options = parent::getOptions();
    // Append the current path as 's' parameter.
    $options['query']['s'] = Url::fromRoute('<current>')->toString();
    return $options;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make cacheable once https://www.drupal.org/node/2582797 lands.
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
