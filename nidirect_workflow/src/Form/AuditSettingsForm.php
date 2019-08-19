<?php

namespace Drupal\nidirect_workflow\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements admin form to allow setting of audit text.
 */
class AuditSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nidirect_workflow.auditsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'audit_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nidirect_workflow.auditsettings');

    $form['audit_button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Audit button text'),
      '#description' => $this->t('Text to be displayed on the button that the editor presses to audit the content.'),
      '#default_value' => $config->get('audit_button_text'),
    ];

    $form['audit_button_hover_text'] = [
      '#type' => 'textfield',
      '#size' => 130,
      '#title' => $this->t('Audit button hover text'),
      '#description' => $this->t('Text to be displayed when the editor hovers their mouse over the audit button.'),
      '#default_value' => $config->get('audit_button_hover_text'),
    ];

    $form['audit_confirmation_text'] = [
      '#type' => 'textfield',
      '#size' => 130,
      '#title' => $this->t('Audit confirmation text'),
      '#description' => $this->t('Ask the editor to confirm that they have audited the content.'),
      '#default_value' => $config->get('audit_confirmation_text'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('nidirect_workflow.auditsettings')
      ->set('audit_button_text', $form_state->getValue('audit_button_text'))
      ->set('audit_button_hover_text', $form_state->getValue('audit_button_hover_text'))
      ->set('audit_confirmation_text', $form_state->getValue('audit_confirmation_text'))
      ->save();
  }

}
