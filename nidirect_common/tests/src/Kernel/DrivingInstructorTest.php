<?php

namespace Drupal\Tests\nidirect_common\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests Driving Instructor title generation.
 *
 * @group nidirect_common
 */
class DrivingInstructorTest extends EntityKernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['user', 'node', 'nidirect_common'];

  /**
   * Test setup function.
   */
  public function setUp() {
    parent::setUp();

    $this->setInstallProfile('test_profile');
  }

  /**
   * Tests the behavior when creating the node.
   */
  public function testNodeCreate() {
    // Create a node to view.
    $content_admin_user = $this->createUser(['uid' => 2], ['administer nodes']);

    // Create a node type for testing.
    NodeType::create(['type' => 'driving_instructor',
      'label' => 'driving_instructor'])->save();

    // Add fields.
    FieldStorageConfig::create([
      'field_name' => 'field_di_firstname',
      'type' => 'string',
      'entity_type' => 'node',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_di_firstname',
      'label' => 'field_di_firstname',
      'entity_type' => 'node',
      'bundle' => 'driving_instructor',
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'field_di_lastname',
      'type' => 'string',
      'entity_type' => 'node',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_di_lastname',
      'label' => 'field_di_lastname',
      'entity_type' => 'node',
      'bundle' => 'driving_instructor',
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'field_di_adi_no',
      'type' => 'string',
      'entity_type' => 'node',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_di_adi_no',
      'label' => 'field_di_adi_no',
      'entity_type' => 'node',
      'bundle' => 'driving_instructor',
    ])->save();

    $ctype = NodeType::load('driving_instructor');
    $node = Node::create([
      'type' => 'driving_instructor',
      'field_di_firstname' => [['value' => 'Firstname']],
      'field_di_lastname' => [['value' => 'Lastname']],
      'field_di_adi_no' => [['value' => '222']],
      'uid' => $content_admin_user->id()
    ]);
    $node->save();
    $nid = $node->id();
    $new_node = Node::load($nid);
    $title = $new_node->getTitle();
    $comp = "Firstname Lastname (ADI No. 222)";
    $this->assertEquals('Firstname Lastname (ADI No. 222)', $title);

  }

}
