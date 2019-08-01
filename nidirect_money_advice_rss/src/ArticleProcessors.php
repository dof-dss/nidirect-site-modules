<?php

namespace Drupal\nidirect_money_advice_rss;

/**
 * Callback functions for article migrations.
 */
class ArticleProcessors {

  /**
   * Extracts summary text from body value.
   */
  public static function summary($value) {
    $pattern = '/<p><strong>(.*?)<\/strong>/s';
    if (preg_match($pattern, $value, $matches)) {
      return $matches[1];
    }

    return $value;
  }

  /**
   * Extracts teaser text from body value.
   */
  public static function teaser($value) {
    $pattern = '/<p><strong>(.*?)<\/strong>/s';
    if (preg_match($pattern, $value, $matches)) {
      $summary = $matches[1];
      $teaser = substr($summary, 0, strrpos(substr($summary, 0, 120), ' ')) . '...';
    }
    else {
      $teaser = 'Advice on managing your money from the Money Advice Service';
    }
    return $teaser;
  }

}
