<?php

namespace Drupal\nidirect_media\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\EditorInterface;
use Drupal\embed\EmbedButtonInterface;
use Drupal\entity_embed\Form\EntityEmbedDialog;

/**
 * Extend the base entity embed dialog class to ensure
 * we can pre-set a step for location entity embedding.
 */
class NIDirectEntityEmbedDialog extends EntityEmbedDialog {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_embed_dialog';
  }

  /**
   * Form constructor.
   *
   * Pre-set the form state step value to 'select' but only for location embed button;
   * we don't have an entity to embed/review for maps.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\editor\EditorInterface $editor
   *   The editor to which this dialog corresponds.
   * @param \Drupal\embed\EmbedButtonInterface $embed_button
   *   The URL button to which this dialog corresponds.
   */
  public function buildForm(array $form, FormStateInterface $form_state, EditorInterface $editor = NULL, EmbedButtonInterface $embed_button = NULL) {
    // Pass in our pre-set form_state into the origin entity embed form builder
    // so we can get the correct form output in the state we need it to be in
    // for location embeds (either new map or replace existing map).
    if ($embed_button->id() == 'location') {
      $form_state->set('step', 'select');
    }

    return parent::buildForm($form, $form_state, $editor, $embed_button);
  }

}
