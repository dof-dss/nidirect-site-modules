<?php

namespace Drupal\Tests\nidirect_workflow\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests workflow menus for roles.
 *
 * @group nidirect_common
 */
class WorkflowAuditTest extends BrowserTestBase {

  /**
   * Use install profile so that we have all content types, modules etc.
   *
   * @var installprofile
   */
  protected $profile = 'test_profile';

  protected $strictConfigSchema = FALSE;

  /**
   * Test when logging on as author.
   */
  public function testAuthenticatedLogon() {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);

    $assert = $this->assertSession();

    // Is 'needs audit' view available ?
    $this->drupalGet('admin/workflow/needs-audit');
    // Access should be denied.
    $assert->statusCodeEquals('403');

    // Is 'needs review' view available ?
    $this->drupalGet('admin/workflow/needs-review');
    // Access should be denied.
    $assert->statusCodeEquals('403');
  }

  /**
   * Test when logging on as author.
   */
  public function testAuthorLogon() {
    $account = $this->drupalCreateUser(['access content']);
    $account->addRole('author_user');
    $account->save;
    $this->drupalLogin($account);

    $assert = $this->assertSession();

    // Is 'needs audit' view available ?
    $this->drupalGet('admin/workflow/needs-audit');
    // Access should be denied.
    $assert->statusCodeEquals('403');
  }

  /**
   * Test when logging on as user with audit permission.
   */
  public function testAuditPermission() {
    $account = $this->drupalCreateUser(['audit content']);
    $this->drupalLogin($account);

    $assert = $this->assertSession();

    // Is 'needs audit' view available ?
    $this->drupalGet('admin/workflow/needs-audit');
    // Should be available.
    $assert->statusCodeEquals('200');

    // Is 'needs review' view available ?
    $this->drupalGet('admin/workflow/needs-review');
    // Access should be denied.
    $assert->statusCodeEquals('403');
  }

}
