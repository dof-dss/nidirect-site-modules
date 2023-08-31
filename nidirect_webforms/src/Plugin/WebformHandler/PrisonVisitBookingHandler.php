<?php

namespace Drupal\nidirect_webforms\Plugin\WebformHandler;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;


/**
 * Prison Visit Booking Webform Handler.
 *
 * @WebformHandler(
 *   id = "prison_visit_booking",
 *   label = @Translation("Prison Visit Booking"),
 *   category = @Translation("NIDirect"),
 *   description = @Translation("Does stuff with Prison Visit Booking."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class PrisonVisitBookingHandler extends WebformHandlerBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->validateVisitOrderNumber($form_state);
  }

  /**
   * Validate visit booking reference.
   */
  private function validateVisitOrderNumber(FormStateInterface $formState) {
    $order_number = !empty($formState->getValue('visitor_order_number')) ? Html::escape($formState->getValue('visitor_order_number')) : NULL;
    $order_number_week = (int) substr($order_number, 3, 2);
    $order_number_year = (int) substr($order_number, 5, 2);

    $today = DrupalDateTime::createFromTimestamp(time());
    $today_week = $today->format('W');
    $today_year = $today->format('Y');
    $today_year_two_digit = $today->format('y');

    $order_week_date = date_isodate_set($today->getPhpDateTime(), $today_year, $order_number_week, 1);

    if ($order_number_year < $today_year_two_digit || $order_number_week < $today_week) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number is not valid'));
    }
    else {
      $formState->setValue('visitor_order_number', $order_number);
      $formState->setValue('visit_week_commencing_date', $order_week_date->format('l, d M Y'));
    }
  }
}
