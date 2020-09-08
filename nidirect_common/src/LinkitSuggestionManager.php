<?php

namespace Drupal\nidirect_common;

/**
 * @file
 * Contains \Drupal\linkit\ResultManager.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\linkit\ProfileInterface;
use Drupal\linkit\Suggestion\DescriptionSuggestion;
use Drupal\linkit\Suggestion\SimpleSuggestion;
use Drupal\linkit\Suggestion\SuggestionCollection;

/**
 * Result service to handle autocomplete matcher results.
 */
class LinkitSuggestionManager {

  /**
   * Gets the results.
   *
   * @param \Drupal\linkit\ProfileInterface $linkitProfile
   *   The linkit profile.
   * @param string $search_string
   *   The string ro use in the matchers.
   *
   * @return \Drupal\linkit\Suggestion\SuggestionCollection
   *   A suggestion collection.
   */
  public function getSuggestions(ProfileInterface $linkitProfile, string $search_string) {

    $suggestions = new SuggestionCollection();

    // Display for empty searches.
    if (empty(trim($search_string))) {
      $suggestion = new SimpleSuggestion();
      $suggestion->setGroup('No content results');
      $suggestion->setLabel('Please enter search terms');

      $suggestions->addSuggestion($suggestion);
    }

    return $suggestions;
  }

  public function addUnscathedSuggestion(SuggestionCollection $suggestionCollection, $search_string) {
    $suggestion = new DescriptionSuggestion();
    $suggestion->setLabel(Html::escape($search_string))
      ->setGroup($this->t('No results'))
      ->setDescription($this->t('Linkit could not find any suggestions. This URL will be used as is.'))
      ->setPath($search_string);
    $suggestionCollection->addSuggestion($suggestion);
    return $suggestionCollection;
  }

}
