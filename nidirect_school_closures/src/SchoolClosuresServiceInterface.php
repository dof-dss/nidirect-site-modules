<?php

namespace Drupal\nidirect_school_closures;

/**
 * Interface DefaultServiceInterface.
 */
interface SchoolClosuresServiceInterface {

  /**
   * Returns school closures data.
   *
   * @return array
   *   school closure data.
   */
  public function getClosures();

  /**
   * Returns last updated date.
   *
   * @return DateTime
   *   updated date.
   */
  public function getUpdated();

}
