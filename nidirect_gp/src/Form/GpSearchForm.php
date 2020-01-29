<?php

namespace Drupal\nidirect_gp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class GpSearchForm extends FormBase {

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'gp_search_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search_api_views_fulltext'] = [
      '#type' => 'textfield',
      '#title' => '',
      '#placeholder' => t('Keywords'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Needed to satisfy the base class inheritance.
  }

}
