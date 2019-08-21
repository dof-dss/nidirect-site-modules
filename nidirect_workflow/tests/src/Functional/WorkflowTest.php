<?php

namespace Drupal\Tests\nidirect_workflow\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests workflow menus for roles.
 *
 * @group nidirect_common
 */
class WorkflowTest extends BrowserTestBase {

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

  protected $strictConfigSchema = FALSE;

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
   * Test menus available when logging on as author.
   */
  public function testAuthenticatedLogon() {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);

    $assert = $this->assertSession();

    $this->drupalGet('admin/workflow/needs-audit');
    $assert->statusCodeEquals('403');

    $this->drupalGet('admin/workflow/needs-review');
    $assert->statusCodeEquals('403');
  }

  /**
   * Test menus available when logging on as author.
   */
  public function testAuthorLogon() {
    $account = $this->drupalCreateUser(['access content']);
    $account->addRole('author_user');
    $account->save;
    $this->drupalLogin($account);

    $assert = $this->assertSession();

    $this->drupalGet('admin/workflow/needs-audit');
    $assert->statusCodeEquals('403');
  }

  /**
   * Test menus available when logging on as user with audit permission.
   */
  public function testAuditPermission() {
    $account = $this->drupalCreateUser(['audit content']);
    $this->drupalLogin($account);

    $assert = $this->assertSession();
    $this->drupalGet('admin/workflow/needs-audit');
    $assert->statusCodeEquals('200');

    $this->drupalGet('admin/workflow/needs-review');
    $assert->statusCodeEquals('403');
  }

}
