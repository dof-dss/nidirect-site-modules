<?php

namespace Drupal\nidirect_temporary\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class GovDeptController.
 */
class GovDeptController extends ControllerBase {

  /**
   * Disp.
   *
   * @return string
   *   Return Hello string.
   */
  public function disp() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: disp')
    ];
  }

}
