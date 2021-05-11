<?php

namespace Drupal\Tests\nidirect_related_content\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\nidirect_related_content\RelatedContentManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * @coversDefaultClass Drupal\nidirect_related_content\RelatedContentManager
 *
 * @group nidirect
 */
class RelatedContentTest extends KernelTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * Set Strict config Schema status.
   *
   * @var bool
   */
  protected $strictConfigSchema;

  /**
   * Related content service.
   *
   * @var \Drupal\nidirect_related_content\RelatedContentManager
   */
  protected $relatedContentManager;

  /**
   * Module machine name.
   *
   * @var array
   */
  public static $modules = [
    'nidirect_related_content',
    'taxonomy',
    'node',
    'user',
    'views',
    'text',
    'field',
    'system',
  ];

  /**
   * Test setup function.
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig(['nidirect_related_content']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_term');

    $this->createContentType(['type' => 'article']);

    $this->relatedContentManager = \Drupal::service('nidirect_related_content.manager');
  }

  /**
   * Test related content manager service.
   */
  public function testSubThemeRelated() {
    $this->_createThemeVocab();
    $this->_createNodeType();

  }

  /**
   * Create a Theme vocabulary and terms.
   */
  private function _createThemeVocab() {
    $vocabulary = Vocabulary::create([
      'name' => 'Site themes',
      'vid' => 'site_themes',
    ]);
    $vocabulary->save();

    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $root = $term_storage->create([
      'name' => 'Motoring',
      'vid' => $vocabulary->id()
    ]);
    $root->save();

    $parent1 = $term_storage->create([
      'name' => 'MOT and vehicle testing',
      'vid' => $vocabulary->id(),
      'parent' => $root->id(),
    ]);
    $parent1->save();

    $parent2 = $term_storage->create([
      'name' => 'Road safety',
      'vid' => $vocabulary->id(),
      'parent' => $root->id(),
    ]);
    $parent2->save();

    /**
     * Motoring child terms.
     */
    $parent1_child1 = $term_storage->create([
      'name' => 'About the MOT scheme',
      'vid' => $vocabulary->id(),
      'parent' => $parent1->id(),
    ]);
    $parent1_child1->save();

    $parent1_child2 = $term_storage->create([
      'name' => 'Other tests DVA carries out',
      'vid' => $vocabulary->id(),
      'parent' => $parent1->id(),
    ]);
    $parent1_child2->save();

    $parent1_child3 = $term_storage->create([
      'name' => 'Types of vehicles which require a test',
      'vid' => $vocabulary->id(),
      'parent' => $parent1->id(),
    ]);
    $parent1_child3->save();

    $parent1_child4 = $term_storage->create([
      'name' => 'The test centre',
      'vid' => $vocabulary->id(),
      'parent' => $parent1->id(),
    ]);
    $parent1_child4->save();

    /**
     * Road safety child terms.
     */
    $parent2_child1 = $term_storage->create([
      'name' => 'Drink and drugs',
      'vid' => $vocabulary->id(),
      'parent' => $parent2->id(),
    ]);
    $parent2_child1->save();

    $parent2_child2 = $term_storage->create([
      'name' => 'Road safety education resources',
      'vid' => $vocabulary->id(),
      'parent' => $parent2->id(),
    ]);
    $parent2_child2->save();

    $parent3_child2_child1 = $term_storage->create([
      'name' => 'Road safety for post primary school children',
      'vid' => $vocabulary->id(),
      'parent' => $parent2_child2->id(),
    ]);
    $parent3_child2_child1->save();

    $parent3_child2_child2 = $term_storage->create([
      'name' => 'Road safety for primary school children',
      'vid' => $vocabulary->id(),
      'parent' => $parent2_child2->id(),
    ]);
    $parent3_child2_child2->save();

  }

  /**
   * Create Article bundle and site themes field.
   */
  private function _createNodeType() {
    NodeType::create([
      'type' => 'article',
    ])->save();

    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_subtheme',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'taxonomy_term',
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_subtheme',
      'entity_type' => 'node',
      'bundle' => 'article',
    ])->save();

  }



}
