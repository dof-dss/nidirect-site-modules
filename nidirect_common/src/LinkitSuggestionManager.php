<?php

namespace Drupal\nidirect_common;

/**
 * @file
 * Contains \Drupal\nidirect_common\LinkitSuggestionManager.
 */

use Drupal\Core\Url;
use Drupal\linkit\ProfileInterface;
use Drupal\linkit\Suggestion\SimpleSuggestion;
use Drupal\linkit\Suggestion\SuggestionCollection;
use Drupal\linkit\SuggestionManager;

/**
 * Replacement suggestion service to handle Linkit autocomplete results.
 */
class LinkitSuggestionManager extends SuggestionManager {

  /**
   * {@inheritdoc}
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

    // Special for link to front page.
    if (strpos($search_string, 'front') !== FALSE) {
      $suggestion = new SimpleSuggestion();
      $suggestion->setGroup('System');
      $suggestion->setLabel('Front page: The front page for this site.');
      $suggestion->setPath(Url::fromRoute('<front>')->toString());

      $suggestions->addSuggestion($suggestion);
    }

    return $suggestions;
  }

}
