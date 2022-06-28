<?php

namespace Drupal\nidirect_gp\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting GP entities.
 *
 * @ingroup nidirect_gp
 */
class GpDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove the @entity-type %label?', [
      '@entity-type' => $this->getEntity()->getEntityType()->getSingularLabel(),
      '%label' => $this->getEntity()->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.gp.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $gpid =$this->getEntity()->id();

    // Fetch all GP Practices so we can check for references to this GP.
    $gp_practices = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(
      ['type' => 'gp_practice'
      ]);

    foreach ($gp_practices as $gp_practice) {
      // Remove if lead GP for a practice.
      if ($gp_practice->get('field_gp_practice_lead')->getString() == $gpid) {
        $gp_practice->get('field_gp_practice_lead')->removeItem(0);
        $gp_practice->save();
      }

      // Remove GP from practice members list.
      $members = $gp_practice->get('field_gp_practice_member')->referencedEntities();

      foreach ($members as $index => $member) {
        if ($member->id() == $gpid) {
          $gp_practice->get('field_gp_practice_member')->removeItem($index);
          $gp_practice->save();
        }
      }
    }

    $this->entity->delete();

    $this->messenger()->addMessage(
      $this->t('Deleted GP @label', [
        '@label' => $this->entity->label(),
      ])
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
