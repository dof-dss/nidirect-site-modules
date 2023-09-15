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
   * @throws \Exception
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    $form['#attached']['drupalSettings']['prisonVisitBooking'] = $this->configuration;

    $visit_type = $form_state->getValue('prison_visit_type') ?? NULL;
    $visit_type_key = $form_state->getValue('prison_visit_type_key') ?? NULL;
    $visit_prison = $form_state->getValue('prison_visit_prison_name') ?? NULL;
    $visit_prison_key = $form_state->getValue('prison_visit_prison_name_key') ?? NULL;
    $visit_sequence = (int) $form_state->getValue('prison_visit_sequence') ?? NULL;
    $visit_prisoner_category = $form_state->getValue('prison_visit_prisoner_category') ?? NULL;
    $visit_prisoner_subcategory = (int) $form_state->getValue('prison_visit_prisoner_subcategory') ?? NULL;
    $visit_order_valid_from = $form_state->getValue('prison_visit_order_valid_from') ?? NULL;
    $visit_order_valid_to = $form_state->getValue('prison_visit_order_valid_to') ?? NULL;
    $visit_week_date_start = $form_state->getValue('prison_visit_week_date_start') ?? NULL;

    // Work out visit order validity period in weeks. We need this to determine
    // how many weeks worth of time slots the form should show.

    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    $visit_advance_notice = $this->configuration['visit_advance_notice'][$visit_type_key];
    $visit_booking_earliest = clone $now;
    $visit_booking_earliest->modify('+' . $visit_advance_notice);

    $visit_booking_ref_valid_from = new \DateTime($visit_order_valid_from);
    $visit_booking_ref_valid_to = new \DateTime($visit_order_valid_to);
    $visit_booking_week_start = new \DateTime($visit_week_date_start);
    $visit_booking_week = (int) $visit_booking_week_start->format('W');
    $visit_booking_week_parity = ($visit_booking_week % 2 === 0) ? 'even' : 'odd';

    if ($visit_booking_ref_valid_from < $visit_booking_earliest) {
      $visit_booking_ref_valid_from = $visit_booking_earliest;
    }

    if ($visit_booking_ref_valid_from < $visit_booking_week_start) {
      $visit_booking_ref_valid_from = $visit_booking_week_start;
    }

    $visit_validity_period_weeks = (int) ceil($visit_booking_ref_valid_from->diff($visit_booking_ref_valid_to)->days / 7);

    // Retrieve configured visit slots for a given prison and visit type.
    // For example, slots for Maghaberry face-to-face visits).
    $config_visit_slots = $this->configuration['visit_slots'][$visit_prison][$visit_type];

    if (!empty($config_visit_slots)) {

      // Loop through four weeks worth of slots and disable or remove slots
      // that fall outside the validity period of the booking reference, or
      // fall within the notice period required to book, etc.

      for ($i = 4; $i > 0; $i--) {

        // Slots for each week are grouped together in some kind of webform
        // grouping element (e.g. container, section or details). The keys
        // must be slots_week_1, slots_week_2, etc.

        $form_slots_week = &$form['elements']['visit_preferred_day_and_time']['slots_week_' . $i];

        // By default, disable access. Enable access if there are days
        // and times to show.

        $form_slots_week['#access'] = FALSE;

        // Add week commencing date to container titles for each week.
        $form_slots_week_date = date('d F Y', strtotime($visit_week_date_start . '+' . ($i - 1) . 'weeks'));
        $form_slots_week['#title'] = str_replace('[DATE]', $form_slots_week_date, $form_slots_week['#title']);

        // Loop through each day of config slots.
        foreach ($config_visit_slots as $day => $config_slots) {

          $form_slots_day = &$form_slots_week[strtolower($day) . '_week_' . $i];

          // By default, disable access.
          $form_slots_day['#access'] = FALSE;

          // Get the configured time slots.
          if (!empty($config_slots[$visit_prisoner_category])) {
            $config_time_slots = $config_slots[$visit_prisoner_category];
          }
          else {
            $config_time_slots = $config_slots;
          }

          if (!empty($config_slots)) {
            // Work out date to prefix option keys with.
            $key_date = clone $visit_booking_ref_valid_from;
            $key_date->modify('+'. ($i - 1) . ' weeks');
            $key_date->modify($day . ' this week');

            // Loop through time slots for this day.
            $options = &$form_slots_day['#options'];
            foreach ($options as $key => $value) {
              $key_time = (date_parse($key));
              $key_date->setTime($key_time['hour'], $key_time['minute'], $key_time['second']);

              // If the option time is in config and the option key date falls
              // with visit booking dates ...
              if (array_key_exists($key, $config_time_slots) && $key_date >= $visit_booking_earliest && $key_date <= $visit_booking_ref_valid_to ) {
                // Make a new option with key containing full datetime.
                $new_key = $key_date->format('d/m/Y H:i');
                $options[$new_key] = $value;
              }

              // Unset old options.
              unset($options[$key]);
            }

            // If we have any options left to show to user, enable access to
            // parent webform elements.
            if (!empty($options)) {
              $form_slots_day['#access'] = TRUE;
              $form_slots_week['#access'] = TRUE;
            }
          }
        }
      }

    }
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
      $form_state->setValue('prison_visit_prison_name_key', $booking_ref_prison_identifier);
      $form_state->setValue('prison_visit_prison_name', $this->configuration['prisons'][$booking_ref_prison_identifier]);
    }

    // Validate visit type.
    if (array_key_exists($booking_ref_visit_type, $this->configuration['visit_type']) !== TRUE) {
      $booking_ref_is_valid = FALSE;
    }
    else {
      $form_state->setValue('prison_visit_type_key', $booking_ref_visit_type);
      $form_state->setValue('prison_visit_type', $this->configuration['visit_type'][$booking_ref_visit_type]);
    }

    // Validate week and year parts.
    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    $now_week_commence = DrupalDateTime::createFromDateTime($now);
    $now_week_commence->setISODate($now->format('Y'), $now->format('W'), 1);
    $now_week_commence->setTime(0, 0, 0);
    $now_week_commence_formatted = $now_week_commence->format('d F Y');

    $visit_advance_notice = $this->configuration['visit_advance_notice'][$booking_ref_visit_type];
    $visit_booking_earliest = clone $now;
    $visit_booking_earliest->modify('+' . $visit_advance_notice);

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

      // Determine prisoner category from the sequence number.
      $prisoner_categories = $this->configuration['visit_order_number_categories'];
      foreach ($prisoner_categories as $category_key => $category_value) {
        foreach ($category_value as $subcategory_key => $subcategory_value) {
          if ($booking_ref_sequence >= $subcategory_value[0] && $booking_ref_sequence <= $subcategory_value[1]) {
            $form_state->setValue('prison_visit_prisoner_category', $category_key);
            $form_state->setValue('prison_visit_prisoner_subcategory', $subcategory_key);
          }
        }
      }
    }

    if ($booking_ref_is_valid !== TRUE) {
      $form_state->setErrorByName('visitor_order_number', $this->t('Visit reference number does not look correct or has expired.'));
    }
    else {
      $form_state->setValue('visitor_order_number', $booking_ref);
    }

  }

}
