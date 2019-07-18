<?php

namespace Drupal\nidirect_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Class ModerationStateController.
 */
class ModerationStateController extends ControllerBase {

  /**
   * Change_state.
   *
   */
  public function change_state() {
    // Redirect user to page given in the 'destination' URL argument.
    $destination = Url::fromUserInput(\Drupal::destination()->get());
    if ($destination->isRouted()) {
      // Valid internal path.
      return $this->redirect($destination->getRouteName());
    }
  }

}
