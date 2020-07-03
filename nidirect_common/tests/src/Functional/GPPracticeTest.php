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

    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $new_node = Node::load($node->id());
    // Node title should have been automatically set to include
    // both fields.
    $this->assertEquals('Surgery - Practice', $new_node->getTitle());
  }

  /**
   * Tests the behavior when creating the node with one field.
   */
  public function testOneFieldNodeCreate() {
    // Create a node with just one field filled in.
    $node = $this->drupalCreateNode([
      'type' => 'gp_practice',
      'field_gp_practice_name' => [['value' => 'Practice']],
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $new_node = Node::load($node->id());
    // Node title should have been automatically set to include
    // the practice name.
    $this->assertEquals('Practice', $new_node->getTitle());

    // Create a node with the other field filled in.
    $node = $this->drupalCreateNode([
      'type' => 'gp_practice',
      'field_gp_surgery_name' => [['value' => 'Surgery']],
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $new_node = Node::load($node->id());
    // Node title should have been automatically set to include
    // the practice name.
    $this->assertEquals('Surgery', $new_node->getTitle());
  }

}
