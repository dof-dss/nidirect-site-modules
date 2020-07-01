<?php

namespace Drupal\nidirect_homepage\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to show a single featured content list.
 *
 * @Block(
 *  id = "featured_content",
 *  admin_label = @Translation("Featured content"),
 * )
 */
class FeaturedContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new featured content block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Our featured content list will be tagged with the "Homepage" term, so load that term object.
    $homepage_tag = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadByProperties(['name' => 'Homepage']);
    $homepage_tag = reset($homepage_tag);

    if ($homepage_tag instanceof Term) {
      // Load the first featured content list node.
      $fcl_nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
        'type' => 'featured_content_list',
        'field_tags' => $homepage_tag->id(),
      ]);

      if (!empty($fcl_nodes)) {
        $node_render = $this->entityTypeManager->getViewBuilder('node')->view(reset($fcl_nodes));

        $build['#theme'] = 'featured_content';
        $build['featured_content'] = $node_render;
      }
    }

    return $build;
  }

}
