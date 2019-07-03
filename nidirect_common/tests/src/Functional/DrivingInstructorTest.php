<?php

namespace Drupal\Tests\cnnic_common\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;

/**
 * Tests Driving Instructor title generation.
 *
 * @group nidirect_common
 */
class DrivingInstructorTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['nidirect_common', 'node'];

  /**
   * Use install profile so that we have all content types, modules etc.
   *
   * @var installprofile
   */
  protected $profile = 'test_profile';

  /**
   * Tests the behavior when creating the node.
   */
  public function testNodeCreate() {
    // Create a node to view.
    $node = $this->drupalCreateNode([
      'type' => 'driving_instructor',
      'field_di_firstname' => [['value' => 'Firstname']],
      'field_di_lastname' => [['value' => 'Lastname']],
      'field_di_adi_no' => [['value' => '222']],
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $this->drupalGet('/node/' . $node->id() . '/view');
    // Node title should have been automatically set to include
    // all three fields.
    $this->assertSession()->pageTextContains('Firstname Lastname (ADI No. 222)');
  }

}
