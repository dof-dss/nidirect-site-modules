<?php

namespace Drupal\Tests\nidirect_common\Functional;

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
   * Set to TRUE to strict check all configuration saved.
   * Need to set to FALSE here because some contrib modules have a schema in
   * config/schema that does not match the actual settings exported
   * (eu_cookie_compliance and google_analytics_counter, I'm looking at you).
   *
   * @see \Drupal\Core\Config\Development\ConfigSchemaChecker
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

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
    $new_node = Node::load($node->id());
    // Node title should have been automatically set to include
    // all three fields.
    $this->assertEquals('Firstname Lastname (ADI No. 222)', $new_node->getTitle());
  }

}
