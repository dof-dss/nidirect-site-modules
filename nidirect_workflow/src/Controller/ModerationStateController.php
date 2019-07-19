<?php

namespace Drupal\nidirect_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ModerationStateController.
 */
class ModerationStateController extends ControllerBase {

  /**
   * Change_state of specified entity.
   */
  public function change_state($nid, $new_state) {
    // Load the entity.
    $entity = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    if ($entity) {
      // Request the state change.
      $entity->set('moderation_state', $new_state);
      $entity->save();
      // Log it.
      $message = t('State of') . ' (' . $nid . ') ' . t('changed to');
      $message .= ' ' . $new_state . ' ' . t('by') . ' ' . $this->currentUser()->getAccountName();
      \Drupal::logger('nidirect_workflow')->notice($message);
    }
    // Redirect user to current page (although the 'destination'
    // url argument will override this).
    return $this->redirect('view.workflow_moderation.page_1');
  }

}
