<?php

namespace Drupal\nidirect_webforms\Plugin\WebformHandler;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
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
    return $this->configFactory->get('nidirect_webforms.prison_visit_booking.settings')->getRawData() ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    $form['#attached']['drupalSettings']['prisonVisitBooking'] = $this->configuration;

    //\Kint::$depth_limit = 3;
    //kint($form_state->getValue('prison_visit_prison_name'));

    $visit_type = $form_state->getValue('prison_visit_type') ?? NULL;
    $visit_prison = $form_state->getValue('prison_visit_prison_name') ?? NULL;
    $visit_sequence = (int) $form_state->getValue('prison_visit_sequence') ?? NULL;
    $visit_week_date_start = $form_state->getValue('prison_visit_week_date_start') ?? NULL;

    if ($visit_sequence > 0 && $visit_sequence < 9000) {
      $visit_sequence_category = 'integrated';
    }
    elseif ($visit_sequence >= 9000) {
      $visit_sequence_category = 'separates';
    }

    if (!empty($visit_type) && $visit_type !== 'enhanced') {
      $form['elements']['visit_preferred_day_and_time']['slots_week_2']['#access'] = FALSE;
      $form['elements']['visit_preferred_day_and_time']['slots_week_3']['#access'] = FALSE;
      $form['elements']['visit_preferred_day_and_time']['slots_week_4']['#access'] = FALSE;
    }

    if (!empty($visit_prison) && !empty($visit_type) && !empty($visit_sequence_category)) {

      // Retrieve configured visit slots for a given prison and visit type.
      // For example, slots for Maghaberry face-to-face visits).
      $config_visit_slots = $this->configuration['visit_slots'][$visit_prison][$visit_type];

      // Loop through four weeks worth of slots.
      for ($i = 1; $i <= 4; $i++) {
        // Loop through each day of time slots ...
        foreach ($config_visit_slots as $day => $config_visit_slots_by_day) {
          // If there are no time slots, remove corresponding form elements.
          if (empty($config_visit_slots_by_day)) {
            $form['elements']['visit_preferred_day_and_time']['slots_week_' . $i][strtolower($day) . '_week_' . $i]['#access'] = FALSE;
          }
          else {
            if ($visit_type == 'virtual') {
              $config_time_slots_by_category = $config_visit_slots_by_day;
            }
            else {
              $config_time_slots_by_category = $config_visit_slots_by_day[$visit_sequence_category];
            }
            // Get the corresponding options in the form.
            $options = &$form['elements']['visit_preferred_day_and_time']['slots_week_' . $i][strtolower($day) . '_week_' . $i]['#options'];
            // Remove options if they don't exist in the config.
            foreach ($options as $key => $value) {
              if (empty($config_time_slots_by_category[$key])) {
                unset($options[$key]);
              }
            }
          }
        }

        // Add week commencing date to container titles for each week's slots
        $slots_title = &$form['elements']['visit_preferred_day_and_time']['slots_week_' . $i]['#title'];
        $slots_title = str_replace('[DATE]', date('d F Y', strtotime($visit_week_date_start . '+' . ($i - 1) . 'weeks')), $slots_title);
      }

    }

  }


  public function configureVisitSlots(array &$form, FormStateInterface $form_state) {

    /** @var \Drupal\webform\WebformSubmissionForm $form_object */
    $form_object = $form_state->getFormObject();

    /** @var \Drupal\webform\Ajax\WebformSubmissionAjaxResponse $response */
    $response = $form_object->submitAjaxForm($form, $form_state);
    $response->addCommand(new InvokeCommand(NULL, 'configureVisitSlots'));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function alterElements(array &$elements, WebformInterface $webform) {
    // TODO do something ...
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->validateVisitBookingReference($form, $form_state);
  }

  /**
   * Validate visit booking reference.
   */
  private function validateVisitBookingReference(array &$form, FormStateInterface $form_state) {

    $booking_ref = !empty($form_state->getValue('visitor_order_number')) ? $form_state->getValue('visitor_order_number') : NULL;

    // Basic validation with early return.
    if (empty($booking_ref)) {
      $form_state->setErrorByName('visitor_order_number', $this->t('Visit reference number is required'));
      return;
    }
    elseif (strlen($booking_ref) !== $this->configuration['visit_order_number_length']) {
      $form_state->setErrorByName('visitor_order_number', $this->t('Visit reference number must contain 12 characters'));
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
      $form['elements']['prison_visit_prison_name']['#default_value'] = $this->configuration['prisons'][$booking_ref_prison_identifier];
      $form_state->setValue('prison_visit_prison_name', $this->configuration['prisons'][$booking_ref_prison_identifier]);
    }

    // Validate visit type.
    if (array_key_exists($booking_ref_visit_type, $this->configuration['visit_type']) !== TRUE) {
      $booking_ref_is_valid = FALSE;
    }
    else {
      $form['elements']['prison_visit_type']['#default_value'] = $this->configuration['visit_type'][$booking_ref_visit_type];
      $form_state->setValue('prison_visit_type', $this->configuration['visit_type'][$booking_ref_visit_type]);
    }

    // Validate week and year parts.
    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    $now_week_commence = DrupalDateTime::createFromDateTime($now);
    $now_week_commence->setISODate($now->format('Y'), $now->format('W'), 1);
    $now_week_commence->setTime(0, 0, 0);
    $now_week_commence_formatted = $now_week_commence->format('d F Y');

    $booking_ref_valid_from = DrupalDateTime::createFromDateTime($now);
    $booking_ref_valid_from->setISODate($booking_ref_year_full, $booking_ref_week, 1);
    $booking_ref_valid_from->setTime(0, 0, 0);
    $booking_ref_valid_from_formatted = $booking_ref_valid_from->format('l, d F Y');

    $booking_ref_valid_to = DrupalDateTime::createFromDateTime($booking_ref_valid_from->getPhpDateTime());
    $booking_ref_valid_to->modify('+' . $booking_ref_validity_period_days . ' days');
    $booking_ref_valid_to_formatted = $booking_ref_valid_to->format('l, d F Y');

    if ($now->getTimestamp() > $booking_ref_valid_to->getTimestamp()) {
      // The booking reference has expired.
      $booking_ref_is_valid = FALSE;
    }
    else {
      // Set some date related form elements.
      $form_state->setValue('prison_visit_order_valid_from', $booking_ref_valid_from_formatted);
      $form_state->setValue('prison_visit_order_valid_to', $booking_ref_valid_to_formatted);

      // Determine whether week date for the booking is for a future week or
      // current week.
      if ($booking_ref_valid_from->getTimestamp() > $now_week_commence->getTimestamp()) {
        $form_state->setValue('prison_visit_week_date_start', $booking_ref_valid_from_formatted);
      }
      else {
        $form_state->setValue('prison_visit_week_date_start', $now_week_commence_formatted);
      }
    }

    // Validate visit sequence number.
    if ($booking_ref_sequence < 1 || $booking_ref_sequence > 9999) {
      $booking_ref_is_valid = FALSE;
    }
    else {
      $form_state->setValue('prison_visit_sequence', $booking_ref_sequence);
    }

    if ($booking_ref_is_valid !== TRUE) {
      $form_state->setErrorByName('visitor_order_number', $this->t('Visit reference number does not look correct or has expired.'));
    }
    else {
      $form_state->setValue('visitor_order_number', $booking_ref);
    }

  }

}
