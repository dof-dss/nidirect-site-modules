<?php

namespace Drupal\nidirect_homepage\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to show a single featured content list.
 *
 * TODO: This class will need refining to be more selective over which
 * featured_content_list node it renders. At present, only the homepage will
 * use an instance of this node type but if the same are needed across other
 * site locations, a taxonomy driven approach will likely be required to organise
 * and select them by.
 *
 * @Block(
 *  id = "featured_content",
 *  admin_label = @Translation("Featured content"),
 * )
 */
class FeaturedContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityManager = $container->get('entity.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Load the first featured content list node.
    $fcl_nodes = $this->entityManager->getStorage('node')->loadByProperties([
      'type' => 'featured_content_list'
    ]);

    if (!empty($fcl_nodes)) {
      $node_render = $this->entityManager->getViewBuilder('node')->view(reset($fcl_nodes));

      $build['#theme'] = 'featured_content';
      $build['featured_content'] = $node_render;
    }

    return $build;
  }

}
