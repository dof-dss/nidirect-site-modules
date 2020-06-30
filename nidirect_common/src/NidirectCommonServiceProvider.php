<?php

namespace Drupal\nidirect_common;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

// @note: You only need Reference, if you want to change service arguments.
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modifies the linkit result manager service.
 */
class NidirectCommonServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides linkit.result_manager class as return
    // output cannot be preprocessed.
    $definition = $container->getDefinition('linkit.result_manager');
    $definition->setClass('Drupal\nidirect_common\LinkitResultManager');
  }

}
