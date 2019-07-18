<?php

namespace Drupal\nidirect_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ModerationStateController.
 */
class ModerationStateController extends ControllerBase {

  /**
   * Change_state.
   *
   * @return string
   *   Return Hello string.
   */
  public function change_state() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: change_state')
    ];
  }

}
