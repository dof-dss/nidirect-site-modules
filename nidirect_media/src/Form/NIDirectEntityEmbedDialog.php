<?php

namespace Drupal\nidirect_media\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\EditorInterface;
use Drupal\embed\EmbedButtonInterface;
use Drupal\entity_browser\Events\RegisterJSCallbacks;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;
use Drupal\entity_embed\Form\EntityEmbedDialog;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NIDirectEntityEmbedDialog extends EntityEmbedDialog {

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityEmbedDisplayManager $entity_embed_display_manager, FormBuilderInterface $form_builder, EntityTypeManagerInterface $entity_type_manager, EventDispatcherInterface $event_dispatcher, EntityFieldManagerInterface $entity_field_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($entity_embed_display_manager, $form_builder, $entity_type_manager, $event_dispatcher, $entity_field_manager, $module_handler);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nidirect_entity_embed_dialog';
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
    if ($embed_button->id() == 'location') {
      $form_state->set('step', 'select');

      // Pass in our pre-set form_state into the origin entity embed form builder
      // so we can get the correct form output in the state we need it to be in
      // for location embeds (either new map or replace existing map).
      $form += parent::buildForm($form, $form_state, $editor, $embed_button);
    }
    else {
      $form = parent::buildForm($form, $form_state, $editor, $embed_button);
    }

    return $form;
  }

//  public function buildEmbedStep(array $form, FormStateInterface $form_state) {
//    parent::buildEmbedStep($form, $form_state);
//  }
//
//  public function buildReviewStep(array $form, FormStateInterface $form_state) {
//    parent::buildReviewStep($form, $form_state);
//  }

//  public function buildSelectStep(array $form, FormStateInterface $form_state) {
//    parent::buildSelectStep($form, $form_state);
//  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitAndShowEmbed(array $form, FormStateInterface $form_state) {
    parent::submitAndShowEmbed($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitEmbedStep(array &$form, FormStateInterface $form_state) {
    parent::submitEmbedStep($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitAndShowReview(array &$form, FormStateInterface $form_state) {
    parent::submitAndShowReview($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitAndShowSelect(array &$form, FormStateInterface $form_state) {
    parent::submitAndShowSelect($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitSelectStep(array &$form, FormStateInterface $form_state) {
    parent::submitSelectStep($form, $form);
  }

  public function validateSelectStep(array $form, FormStateInterface $form_state) {
    parent::validateEmbedStep($form, $form_state);
  }

  public function validateEmbedStep(array $form, FormStateInterface $form_state) {
    parent::validateEmbedStep($form, $form_state);
  }

  public function registerJSCallback(RegisterJSCallbacks $event) {
    parent::registerJSCallback($event);
  }
}
