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
    return [
      'visit_order_number_length' => 12,
      'visit_type' => [
        'F' => 'face-to-face',
        'V' => 'virtual',
        'E' => 'enhanced',
      ],
      'visit_prison' => [
        'MY' => 'Maghaberry',
        'HK' => 'Hydebank',
        'MN' => 'Magilligan',
      ],
      'visit_booking_advance_notice' => [
        'F' => '24 hours', // Face to face must be booked 24 hours in advance.
        'V' => '48 hours', // Virtual must be booked 48 hours in advance.
        'E' => '24 hours', // Enhanced must be booked 24 hours in advance.
      ],
      'visit_booking_reference_validity_weeks' => [
        'F' => '1', // Face to face booking reference valid for 1 week.
        'V' => '1', // Virtual booking reference valid for 1 week.
        'E' => '4', // Enhanced booking reference valid for 4 weeks.
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

    // Basic validation with early return.
    if (empty($order_number)) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number is required'));
      return;
    }
    else if (strlen($order_number) !== $this->configuration['visit_order_number_length']) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number must contain 12 characters'));
      return;
    }

    // We have an order number with correct length. Now we can dissect it
    // individual parts.
    $order_number_is_valid = TRUE;

    $order_number_prison_identifier = substr($order_number, 0, 2);
    $order_number_visit_type = substr($order_number, 2, 1);
    $order_number_week = (int) substr($order_number, 3, 2);
    $order_number_week_validity = $this->configuration['visit_booking_reference_validity_weeks'][$order_number_visit_type];
    $order_number_year = (int) substr($order_number, 5, 2);
    $order_number_year_full = (int) DrupalDateTime::createFromFormat('y', $order_number_year)->format('Y');
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

    // Validate whether order number has expired.
    
    $now = new DrupalDateTime('now');
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    $now_week_commence = new DrupalDateTime();
    $now_week_commence->setISODate($now->format('Y'), $now->format('W'), 1);

    $order_number_valid_from = new DrupalDateTime();
    $order_number_valid_from->setISODate($order_number_year_full, $order_number_week, 1);

    $order_number_valid_to = new DrupalDateTime($order_number_valid_from->format('Y-m-d'));
    $order_number_valid_to->modify('+' . $order_number_week_validity . ' weeks');

    if ($now_week_commence->getTimestamp() > $order_number_valid_to->getTimestamp()) {
      $order_number_is_valid = FALSE;
    }
    else if ($order_number_valid_from->getTimestamp() > $now_week_commence->getTimestamp()) {
      $formState->setValue('prison_visit_week_date', $order_number_valid_from->format('l, d M Y'));
    }
    else {
      $formState->setValue('prison_visit_week_date', $now_week_commence->format('l, d M Y'));
    }

    // Validate visit sequence number.
    if ($order_number_sequence < 1 || $order_number_sequence > 9999) {
      $order_number_is_valid = FALSE;
    }
    else {
      $formState->setValue('prison_visit_sequence', $order_number_sequence);
    }

    if ($order_number_is_valid !== TRUE) {
      $formState->setErrorByName('visitor_order_number', $this->t('Visit reference number does not look correct or has expired.'));
    }
    else {
      $formState->setValue('visitor_order_number', $order_number);
    }
  }

}
