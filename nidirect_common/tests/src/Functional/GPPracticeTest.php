<?php

namespace Drupal\Tests\nidirect_common\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;

/**
 * Tests Driving Instructor title generation.
 *
 * @group nidirect_common
 */
class GPPracticeTest extends BrowserTestBase {

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
   * Tests the behavior when creating the node with two fields.
   */
  public function testVanillaNodeCreate() {
    // Create a node to view.
    $node = $this->drupalCreateNode([
      'type' => 'gp_practice',
      'field_gp_practice_name' => [['value' => 'Practice']],
      'field_gp_surgery_name' => [['value' => 'Surgery']],
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $this->drupalGet('/node/' . $node->id() . '/view');
    // Node title should have been automatically set to include
    // both fields.
    $this->assertSession()->pageTextContains('Surgery - Practice');
  }

  /**
   * Tests the behavior when creating the node with one field.
   */
  public function testOneFieldNodeCreate() {
    // Create a node with just one field filled in.
    $node = $this->drupalCreateNode([
      'type' => 'gp_practice',
      'field_gp_practice_name' => [['value' => 'Practice']]
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $this->drupalGet('/node/' . $node->id() . '/view');
    // Node title should have been automatically set to include
    // the practice name.
    $this->assertSession()->pageTextContains('Practice');
    // There should be no hyphen.
    $this->assertSession()->pageTextNotContains('- Practice');

    // Create a node with the other field filled in.
    $node = $this->drupalCreateNode([
      'type' => 'gp_practice',
      'field_gp_surgery_name' => [['value' => 'Surgery']]
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $this->drupalGet('/node/' . $node->id() . '/view');
    // Node title should have been automatically set to include
    // the practice name.
    $this->assertSession()->pageTextContains('Surgery');
    // There should be no hyphen.
    $this->assertSession()->pageTextNotContains('Surgery -');
  }

}
