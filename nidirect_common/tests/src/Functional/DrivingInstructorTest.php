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
  public static $modules = ['nidirect_common', 'node', 'config', 'system'];

  //protected $profile = 'test';

  /**
   * Tests the behavior when creating the node.
   */
  public function testNodeCreate() {
    // Import the content of the sync directory.
    //$sync = \Drupal::service('config.storage.sync');
    //$this->copyConfig(\Drupal::service('config.storage'), $sync);
    //$steps = $this->configImporter()->initialize();
    //$this->configImporter()->import();

    //$this->setInstallProfile($this->profile);
    //$this->setUp();

    // Create a node to view.
    //$node = $this->drupalCreateNode(['type' => 'driving_instructor', 'field_di_firstname' => [['value' => 'Firstname']]]);
    //$this->drupalLogin($this->rootUser);
    $node = $this->drupalCreateNode(['type' => 'cold_weather_payment', 'title' => 'testing']);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    //$this->drupalGet('/node/' . $node->id() . '/view');
    //$this->assertResponse(200);
  }

}