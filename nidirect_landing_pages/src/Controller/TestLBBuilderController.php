<?php

namespace Drupal\nidirect_landing_pages\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for NIDirect Landing Pages routes.
 */
class TestLBBuilderController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Layout Builder node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * The UUID of the component.
   *
   * @var string
   */
  protected $uuid;


  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, UuidInterface $uuid) {
    $this->entityTypeManager = $entity_type_manager;
    $this->uuidGenerator = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('uuid')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {

    $node_config = [
      'type' => 'landing_page',
      'title' => 'TEST LANDING PAGE',
    ];

    $this->node = $this->entityTypeManager->getStorage('node')->create($node_config);

    $section = new Section('teasers_x2');

    // Block plugin configuration.
    // Prepend the title with the nid to make it easier to track node blocks.
    $block_config = [
      'info' => 'My Custom Block content',
      'type' => 'card_standard',
      'langcode' => 'en',
      'field_body' => 'This is the block body',
      'field_teaser' => 'This is the block teaser',
      'title' => 'My Custom title',
      'reusable' => 0,
    ];

    $block = $this->entityTypeManager->getStorage('block_content')->create($block_config);

    $plugin_config = [
      'id' => 'inline_block:card_standard',
      'label' => 'Test card standard',
      'label_display' => 'visible',
      'block_serialized' => serialize($block),
      'context_mapping' => []
    ];

    // Create and return a new Layout Builder Section Component using the
    // content block and plugin configuration.
    $sectionComponent = new SectionComponent($this->uuidGenerator->generate(), 'one', $plugin_config);
    $section->appendComponent($sectionComponent);
    $sections[] = $section;
    $this->node->layout_builder__layout->setValue($sections);
    $this->node->save();


    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
