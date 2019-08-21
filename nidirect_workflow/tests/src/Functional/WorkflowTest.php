<?php

namespace Drupal\Tests\nidirect_workflow\Functional;

use Drupal\nidirect_workflow\Controller\AuditController;
use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;

/**
 * Tests workflow menus for roles
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
  public function testAuthLogon() {
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
    //$role = Role::load('author_user');
    $role = Role::create([
      'id' => 'author_user',
      'label' => 'Author User',
    ]);
    $role->save();
    $account->addRole($role->id());
    $account->save;
    //print_r($account->getRoles());
    $this->drupalLogin($account);

    $assert = $this->assertSession();

    $this->drupalGet('admin/workflow/needs-review');
    $assert->statusCodeEquals('200');
    //$assert->pageTextContains('Needs Review');

    $this->drupalGet('admin/workflow/needs-audit');
    $assert->statusCodeEquals('200');
    //$assert->pageTextContains('You are not authorized to access this page');
  }

  /**
   * Test menus available when logging on as author.
   */
  public function testAuditPermission() {
    $account = $this->drupalCreateUser(['audit content']);
    $this->drupalLogin($account);

    //$this->drupalLogin($this->rootUser);

    $assert = $this->assertSession();

    //$this->drupalGet('admin/workflow/needs-review');
    //$assert->statusCodeEquals('200');
    //$assert->pageTextContains('Needs Review');

    $this->drupalGet('admin/workflow/needs-audit');
    $assert->statusCodeEquals('200');

    $this->drupalGet('admin/workflow/needs-review');
    $assert->statusCodeEquals('403');
    //$assert->pageTextContains('You are not authorized to access this page');
  }

}
