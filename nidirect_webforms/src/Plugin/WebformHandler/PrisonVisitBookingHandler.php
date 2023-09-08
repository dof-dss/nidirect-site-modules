<?php

namespace Drupal\nidirect_webforms\Plugin\WebformHandler;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
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
    return $this->configFactory->get('prison_visit_booking.settings')->getRawData() ?? [];
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
    $this->validateVisitBookingReference($form_state);
  }

  /**
   * Validate visit booking reference.
   */
  private function validateVisitBookingReference(FormStateInterface $formState) {

    $booking_ref = !empty($formState->getValue('visitor_order_number')) ? Html::escape($formState->getValue('visitor_order_number')) : NULL;

    // Basic validation with early return.
    if (empty($booking_ref)) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number is required'));
      return;
    }
    else if (strlen($booking_ref) !== $this->configuration['visit_order_number_length']) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number must contain 12 characters'));
      return;
    }

    // Abort further validation if there is no config.
    if (empty($this->configuration)) {
      return;
    }

    // We have an order number with correct length. Now dissect and process the
    // individual parts.
    $booking_ref_is_valid = TRUE;

    $booking_ref_prison_identifier = substr($booking_ref, 0, 2);
    $booking_ref_visit_type = substr($booking_ref, 2, 1);
    $booking_ref_week = (int) substr($booking_ref, 3, 2);
    $booking_ref_validity_period_days = $this->configuration['booking_reference_validity_period_days'][$booking_ref_visit_type];
    $booking_ref_year = (int) substr($booking_ref, 5, 2);
    $booking_ref_year_full = (int) DrupalDateTime::createFromFormat('y', $booking_ref_year)->format('Y');
    $booking_ref_sequence = (int) substr($booking_ref, 8);

    // Validate prison identifier.
    if (array_key_exists($booking_ref_prison_identifier, $this->configuration['prisons']) !== TRUE) {
      $booking_ref_is_valid = FALSE;
    }
    else {
      $formState->setValue('prison_visit_prison_name', $this->configuration['prisons'][$booking_ref_prison_identifier]);
    }

    // Validate visit type.
    if (array_key_exists($booking_ref_visit_type, $this->configuration['visit_type']) !== TRUE) {
      $booking_ref_is_valid = FALSE;
    }
    else {
      $formState->setValue('prison_visit_type', $this->configuration['visit_type'][$booking_ref_visit_type]);
    }

    // Validate week and year parts.
    $now = new DrupalDateTime('now');
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    $now_week_commence = new DrupalDateTime();
    $now_week_commence->setISODate($now->format('Y'), $now->format('W'), 1);

    $booking_ref_valid_from = new DrupalDateTime();
    $booking_ref_valid_from->setISODate($booking_ref_year_full, $booking_ref_week, 1);

    $booking_ref_valid_to = new DrupalDateTime($booking_ref_valid_from->format('Y-m-d'));
    $booking_ref_valid_to->modify('+' . $booking_ref_validity_period_days . ' days');

    if ($now_week_commence->getTimestamp() > $booking_ref_valid_to->getTimestamp()) {
      $booking_ref_is_valid = FALSE;
    }
    else if ($booking_ref_valid_from->getTimestamp() > $now_week_commence->getTimestamp()) {
      $formState->setValue('prison_visit_week_date', $booking_ref_valid_from->format('l, d M Y'));
    }
    else {
      $formState->setValue('prison_visit_week_date', $now_week_commence->format('l, d M Y'));
    }

    // Validate visit sequence number.
    if ($booking_ref_sequence < 1 || $booking_ref_sequence > 9999) {
      $booking_ref_is_valid = FALSE;
    }
    else {
      $formState->setValue('prison_visit_sequence', $booking_ref_sequence);
    }

    if ($booking_ref_is_valid !== TRUE) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number does not look correct or has expired.'));
    }
    else {
      $formState->setValue('visitor_order_number', $booking_ref);
    }
  }

}
