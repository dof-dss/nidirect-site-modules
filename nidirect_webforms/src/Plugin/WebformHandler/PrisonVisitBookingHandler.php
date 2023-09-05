<?php

namespace Drupal\nidirect_webforms\Plugin\WebformHandler;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->configFactory = $container->get('config.factory');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'visit_type' => [
        'V' => 'virtual',
        'F' => 'face-to-face',
        'E' => 'enhanced',
      ],
      'visit_prison' => [
        'MY' => 'Maghaberry',
        'HK' => 'Hydebank',
        'MN' => 'Magilligan',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $form['#attached']['drupalSettings']['prisonVisitBooking'] = $this->configuration;
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

    if (empty($order_number)) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number is required'));
      return;
    }

    // Validate order number.
    $order_number_is_valid = TRUE;

    $order_number_prison_identifier = substr($order_number, 0, 2);
    $order_number_visit_type = substr($order_number, 2, 1);
    $order_number_week = (int) substr($order_number, 3, 2);
    $order_number_year = (int) substr($order_number, 5, 2);
    $order_number_sequence = (int) substr($order_number, 8);

    // Validate prison identifier.
    if (array_key_exists($order_number_prison_identifier, $this->configuration['visit_prison']) !== TRUE) {
      $order_number_is_valid = FALSE;
    }
    else {
      $formState->setValue('prison_visit_prison_name', $this->configuration['visit_prison'][$order_number_prison_identifier]);
    }

    // Validate visit type.
    if (array_key_exists($order_number_visit_type, $this->configuration['visit_type']) !== TRUE) {
      $order_number_is_valid = FALSE;
    }
    else {
      $formState->setValue('prison_visit_type', $this->configuration['visit_type'][$order_number_visit_type]);
    }

    // Validate order number week and year.
    $today = DrupalDateTime::createFromTimestamp(time());
    $today_week = $today->format('W');
    $today_year = $today->format('Y');
    $today_year_two_digit = $today->format('y');

    $order_week_date = date_isodate_set($today->getPhpDateTime(), $today_year, $order_number_week, 1);

    if ($order_number_year < $today_year_two_digit || $order_number_week < $today_week) {
      $order_number_is_valid = FALSE;
    }
    else {
      $formState->setValue('prison_visit_week_date', $order_week_date->format('l, d M Y'));
    }

    // Validate visit sequence number.
    if ($order_number_sequence < 1 || $order_number_sequence > 9999) {
      $order_number_is_valid = FALSE;
    }
    else {
      $formState->setValue('prison_visit_sequence', $order_number_sequence);
    }

    if ($order_number_is_valid !== TRUE) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number is not valid'));
    }
    else {
      $formState->setValue('visitor_order_number', $order_number);
    }
  }

}
