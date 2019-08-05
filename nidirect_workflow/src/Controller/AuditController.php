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
   *   Return confirmation string.
   */
  public function contentAudit($nid) {
    $msg = 'Invalid node id';
    if (!empty($nid)) {
      $node = \Drupal\node\Entity\Node::load($nid);
      if ($node) {
        $msg = "Click this button to indicate that you have audited this published content ";
        $msg .= "and are happy that it is still accurate and relevant.";
        $msg .= "<div><a href='/nidirect_workflow/audit_confirm/$nid'>Audit this published content</a></div>";
      }
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t($msg)
    ];
  }

}
