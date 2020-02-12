<?php

namespace Drupal\Tests\nidirect_common\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests Driving Instructor title generation.
 *
 * @group nidirect_common
 */
class GPPracticeTest extends EntityKernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['nidirect_common', 'node'];

  /**
   * Test setup function.
   */
  public function setUp() {
    parent::setUp();

    // Create a content type for testing.
    NodeType::create([
      'type' => 'gp_practice',
      'label' => 'gp_practice',
    ])->save();

    // Add required fields.
    $fields = ['field_gp_practice_name', 'field_gp_surgery_name'];
    foreach ($fields as $field) {
      FieldStorageConfig::create([
        'field_name' => $field,
        'type' => 'string',
        'entity_type' => 'node',
        'cardinality' => 1,
      ])->save();
      FieldConfig::create([
        'field_name' => $field,
        'label' => $field,
        'entity_type' => 'node',
        'bundle' => 'gp_practice',
      ])->save();
    }
  }

  /**
   * Tests the behavior when creating the node with two fields.
   */
  public function testVanillaNodeCreate() {
    // Create a node to view.
    $node = Node::create([
      'type' => 'gp_practice',
      'field_gp_practice_name' => [['value' => 'Practice']],
      'field_gp_surgery_name' => [['value' => 'Surgery']],
    ]);
    $node->save();
    $this->assertEquals('Surgery - Practice', $node->getTitle());
  }

  /**
   * Tests the behavior when creating the node with one field.
   */
  public function testOneFieldNodeCreate() {
    // Create a node with just one field filled in.
    $node = Node::create([
      'type' => 'gp_practice',
      'field_gp_practice_name' => [['value' => 'Practice']],
    ]);
    $node->save();
    $this->assertEquals('Practice', $node->getTitle());

    // Create a node with the other field filled in.
    $node = Node::create([
      'type' => 'gp_practice',
      'field_gp_practice_name' => [['value' => 'Surgery']],
    ]);
    $node->save();
    $this->assertEquals('Surgery', $node->getTitle());
  }

}
