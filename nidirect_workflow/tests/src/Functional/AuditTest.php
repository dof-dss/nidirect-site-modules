<?php

namespace Drupal\Tests\nidirect_workflow\Functional;

use Drupal\content_moderation\EntityTypeInfo;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\nidirect_workflow\Controller\AuditController;
use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests audit workflow.
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->logger = $this->container->get('logger.factory')->get('audit_test');
    $this->account = $this->container->get('current_user');
  }

  /**
   * Tests the behaviour when creating an article.
   */
  public function testArticleNodeCreate() {
    $this->newNodeCreateTest('article');
  }

  /**
   * Tests the behaviour when creating a contact.
   */
  public function testContactNodeCreate() {
    $this->newNodeCreateTest('contact');
  }

  /**
   * Tests the behavior when creating a page.
   */
  public function testPageNodeCreate() {
    $this->newNodeCreateTest('page');
  }

  /**
   * Test the specified content type.
   */
  public function newNodeCreateTest($type) {
    // Create a new node.
    $node = $this->drupalCreateNode([
      'type' => $type,
      'title' => 'audit testing ' . $type,
      'moderation_state' => 'published'
    ]);
    $this->assertTrue(Node::load($node->id()), 'Node created.');
    $nid = $node->id();
    $new_node = Node::load($nid);
    // 'Next audit due' date should have been set automatically
    // to six months in the future.
    $sixm = date('Y-m-d', strtotime("+6 months"));
    $this->assertEquals($sixm, $new_node->get('field_next_audit_due')->value);
    // Now reset the audit due date to today.
    $today = date('Y-m-d', \Drupal::time()->getCurrentTime());
    $new_node->set('field_next_audit_due', $today);
    $new_node->save();
    // Audit the node.
    $auditer = new AuditController($this->entityTypeManager, $this->logger, $this->account);
    $auditer->confirmAudit($nid);
    // 'Next audit due' date should now have bumped to 6 months time.
    $audited_node = Node::load($nid);
    $this->assertEquals($sixm, $audited_node->get('field_next_audit_due')->value);
  }

}
