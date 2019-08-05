<?php

namespace Drupal\nidirect_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AuditController.
 */
class AuditController extends ControllerBase {

  /**
   * Content Audit.
   *
   * @return string
   *   Return cofirmation string.
   */
  public function contentAudit() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Do you want to audit ?')
    ];
  }

}
