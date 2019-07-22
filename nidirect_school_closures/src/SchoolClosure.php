<?php

namespace Drupal\nidirect_school_closures;

use DateTime;

/**
 * SchoolClosure class.
 */
class SchoolClosure {

  protected $name;
  protected $altName;
  protected $location;
  protected $date;
  protected $reason;
  /**
   * Array of friendly reason texts.
   */
  protected const REASONS = [
    "adverse weather conditions/exceptionally heavy snowfall"                     => "due to adverse weather.",
    "no water supply"                                                             => "due to no water supply.",
    "lack of heating"                                                             => "due to no heating.",
    "road closures"                                                               => "due to road closures.",
    "electricity failure"                                                         => "due to no electricity.",
    "other - please contact the school for further information"                   => ". Contact the school for more information.",
    "used as a polling station for parliamentary/local government elections"      => "due to use as a polling station for an election.",
    "death of a member of staff, pupil or another person working at the school"   => "due to a death in the school family.",
    "flooding or burst pipes"                                                     => "due to flooding or a burst pipe.",
  ];

  /**
   * SchoolClosure constructor.
   *
   * @param string $name
   *   Name of the school.
   * @param string $location
   *   School location.
   * @param \DateTime $date
   *   Date the closure takes place.
   * @param string $reason
   *   Reason for the school closure.
   */
  public function __construct(string $name, string $location, DateTime $date, string $reason) {
    $this->name = $name;
    $this->location = $location;
    $this->date = $date;
    $this->reason = $reason;

    // Call all processors.
    $this->processAltName();
    $this->processLocation();
    $this->processReason();
  }

  /**
   * Get the school closure data.
   *
   * @return array
   *   Associative array of closure data.
   */
  public function getData() {
    return [
      'name' => $this->name,
      'altname' => $this->altName,
      'location' => $this->location,
      'date' => $this->date,
      'reason' => $this->reason,
    ];
  }

  /**
   * Return if the closure date has expired.
   */
  public function isExpired() {
    $today = new \DateTime('now', new \DateTimeZone('Europe/London'));
    // Reset the clock to avoid issues with time comparisons.
    $today->setTime(0, 0, 0);

    return ($this->date < $today) ? TRUE : FALSE;
  }

  /**
   * Process alternative school names.
   */
  protected function processAltname() {
    // Add alternative names for Irish name schools.
    $pattern = '/[ÁÉÍÓÚáéíóú]/';
    if (preg_match($pattern, $this->name)) {
      $transliteration = \Drupal::service('transliteration');
      $this->altName = $transliteration->removeDiacritics($this->name);
    }
  }

  /**
   * Process the school location.
   */
  protected function processLocation() {
    /* If the first word of the title is also the first word of the
     * location, remove it from the location. So if we have
     *
     * Portadown Primary School, Portadown, County Armagh
     *
     * it should be changed to
     *
     * Portadown Primary School, County Armagh
     */
    $title_arr = explode(' ', $this->name);
    $first_word = $title_arr[0] . ', ';
    if (substr($this->location, 0, strlen($first_word)) == $first_word) {
      $this->location = substr($this->location, strlen($first_word));
    }
  }

  /**
   * Process the reason for the closure.
   */
  protected function processReason() {
    if (isset(static::REASONS[strtolower($this->reason)])) {
      // Do the comparison in lowercase because $reason_map array keys are
      // lowercase (to allow for variations in capitalisation)
      $this->reason = static::REASONS[strtolower($this->reason)];
    }
    else {
      // Try to make the existing string consistent with the Nidirect versions.
      $this->reason = '. ' . ucfirst($this->reason);
      if (!preg_match('/.*\.$/', $this->reason)) {
        $this->reason = $this->reason . '.';
      }
    }
  }

}
