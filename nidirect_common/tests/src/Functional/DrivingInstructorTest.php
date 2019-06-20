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
class DrivingInstructorTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['nidirect_common', 'node'];

  /**
   * Tests the behavior when creating the node.
   */
  public function testNodeCreate() {
    // Create a node to view.
    //$node = $this->drupalCreateNode(['type' => 'driving_instructor', 'field_di_firstname' => [['value' => 'Firstname']]]);
    $node = $this->drupalCreateNode(['type' => 'cold_weather_payment', 'title' => 'testing']);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
  }

}