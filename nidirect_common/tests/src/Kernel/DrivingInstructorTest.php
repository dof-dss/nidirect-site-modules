<?php

namespace Drupal\Tests\nidirect_common\Kernel;

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

    $ctype = NodeType::load('driving_instructor');
    var_dump($ctype->id());
    $node = Node::create([
      'type' => 'driving_instructor',
      'field_di_firstname' => [['value' => 'Firstname']],
      'field_di_lastname' => [['value' => 'Lastname']],
      'field_di_adi_no' => [['value' => '222']],
      'uid' => $content_admin_user->id()
    ]);
    //$node->save();
    var_dump($node->getTitle());
    $this->assertEquals('Firstname Lastname (ADI No. 222)', $node->getTitle());

  }

}
