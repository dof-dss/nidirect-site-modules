<?php

namespace Drupal\nidirect_campaign_utilities\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a NIDirect Campaign Utilities form.
 */
class CreatorConfirmationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nidirect_campaign_utilities_creator_confirmation';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $nid = $this->getRequest()->get('nid');

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid;
    ]

    $form['options'] = array(
      '#type' => 'radios',
      '#title' => $this->t('A landing page exists for this content, please select to:'),
      '#options' => [
        'update' => $this->t('update the existing content.'),
        'create' => $this->t('create a new landing page.'),
        'cancel' => $this->t('cancel and return to list.'),
      ],
    );

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('options'))) {
      $form_state->setErrorByName('options', $this->t('Please select an option.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $option = $form_state->getValue('options');

    if ($option == 'cancel') {
      $form_state->setRedirect('nidirect_campaign_utilities.list');
    }
    else {
      $form_state->setRedirect('nidirect_campaign_utilities.creator', ['nid' => $nid], ['option' => $option]);
    }
  }
}
