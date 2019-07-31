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

    // Create a content type for testing.
    NodeType::create([
      'type' => 'driving_instructor',
      'label' => 'driving_instructor',
    ])->save();

    // Add required fields.
    $fields = ['field_di_firstname', 'field_di_lastname', 'field_di_adi_no'];
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
        'bundle' => 'driving_instructor',
      ])->save();
    }
  }

  /**
   * Tests the behavior when creating the node.
   */
  public function testNodeCreate() {
    // Create a driving instructor.
    $content_admin_user = $this->createUser(['uid' => 2], ['administer nodes']);
    $node = Node::create([
      'type' => 'driving_instructor',
      'field_di_firstname' => [['value' => 'Firstname']],
      'field_di_lastname' => [['value' => 'Lastname']],
      'field_di_adi_no' => [['value' => '222']],
      'uid' => $content_admin_user->id(),
    ]);
    $node->save();
    // Title should have been automatically set to a combination of fields.
    $this->assertEquals('Firstname Lastname (ADI No. 222)', $node->getTitle());
  }

}
