<?php

namespace Drupal\nidirect_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ModerationStateController.
 */
class ModerationStateController extends ControllerBase {

  /**
   * Change_state.
   *
   */
  public function change_state() {
    // Redirect user
    $response = new RedirectResponse('/admin/content');
    $response->send();
  }

}
