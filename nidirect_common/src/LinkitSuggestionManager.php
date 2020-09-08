<?php

namespace Drupal\nidirect_common;

/**
 * @file
 * Contains \Drupal\nidirect_common\LinkitSuggestionManager.
 */

use Drupal\Core\Url;
use Drupal\linkit\ProfileInterface;
use Drupal\linkit\Suggestion\DescriptionSuggestion;
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
      $suggestion = new DescriptionSuggestion();
      $suggestion->setLabek($this->t('No content results'));
      $suggestion->setDescription($this->t('Please enter search terms'));

      $suggestions->addSuggestion($suggestion);
    }

    // Special for link to front page.
    if (strpos($search_string, 'front') !== FALSE) {
      $suggestion = new DescriptionSuggestion();
      $suggestion->setGroup($this->t('System'));
      $suggestion->setLabel($this->t('Front page'));
      $suggestion->setDescription($this->t('The front page for this site'));
      $suggestion->setPath(Url::fromRoute('<front>')->toString());

      $suggestions->addSuggestion($suggestion);
    }

    return $suggestions;
  }

}
