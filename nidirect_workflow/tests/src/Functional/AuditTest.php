<?php

namespace Drupal\Tests\nidirect_workflow\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;

/**
 * Tests Driving Instructor title generation.
 *
 * @group nidirect_common
 */
class AuditTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['nidirect_workflow', 'node'];

  /**
   * Use install profile so that we have all content types, modules etc.
   *
   * @var installprofile
   */
  protected $profile = 'test_profile';

  /**
   * Tests the behavior when creating an article.
   */
  public function testArticleNodeCreate() {
    // Create a new article node.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'audit testing article',
      'moderation_state' => 'published'
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $new_node = Node::load($node->id());
    // 'Next audit due' date should have been set automatically
    // to six months in the future.
    $sixm = date('Y-m-d', strtotime("+6 months"));
    $this->assertEquals($sixm, $new_node->get('field_next_audit_due')->value);
  }

  /**
   * Tests the behavior when creating a contact.
   */
  public function testContactNodeCreate() {
    // Create a new article node.
    $node = $this->drupalCreateNode([
      'type' => 'contact',
      'title' => 'audit testing contact',
      'moderation_state' => 'published'
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $new_node = Node::load($node->id());
    // 'Next audit due' date should have been set automatically
    // to six months in the future.
    $sixm = date('Y-m-d', strtotime("+6 months"));
    $this->assertEquals($sixm, $new_node->get('field_next_audit_due')->value);
  }

  /**
   * Tests the behavior when creating a page.
   */
  public function testPageNodeCreate() {
    // Create a new article node.
    $node = $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'audit testing page',
      'moderation_state' => 'published'
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $new_node = Node::load($node->id());
    // 'Next audit due' date should have been set automatically
    // to six months in the future.
    $sixm = date('Y-m-d', strtotime("+6 months"));
    $this->assertEquals($sixm, $new_node->get('field_next_audit_due')->value);
  }

}
