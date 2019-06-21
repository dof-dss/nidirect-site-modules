<?php

namespace Drupal\Tests\cnnic_common\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

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
   */
  protected $profile = 'test';

  /**
   * Tests the behavior when creating the node.
   */
  public function testNodeCreate() {
    // Create a node to view.
    $node = $this->drupalCreateNode(['type' => 'gp_practice',
      'field_gp_practice_name' => [['value' => 'Practice']],
      'field_gp_surgery_name' => [['value' => 'Surgery']]]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $this->drupalGet('/node/' . $node->id() . '/view');
    // Node title should have been automatically set to include
    // all three fields.
    $this->assertSession()->pageTextContains('Surgery - Practice');
  }

}