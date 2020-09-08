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
   * @return array
   *   An array of matches.
   */
  public function getSuggestions(ProfileInterface $linkitProfile, string $search_string) {
    $matches = [];

    if (empty(trim($search_string))) {
      return [
        [
          'title' => t('No content results'),
          'description' => t('Please enter search terms')
        ]
      ];
    }

    // Special for link to front page.
    if (strpos($search_string, 'front') !== FALSE) {
      $matches[] = [
        'title' => t('Front page'),
        'description' => 'The front page for this site.',
        'path' => Url::fromRoute('<front>')->toString(),
        'group' => t('System'),
      ];
    }

    foreach ($linkitProfile->getMatchers() as $plugin) {
      $matches = array_merge($matches, $plugin->getMatches($search_string));
    }

    // Check for an e-mail address then return an e-mail match and create a
    // mail-to link if appropriate.
    if (filter_var($search_string, FILTER_VALIDATE_EMAIL)) {
      $matches[] = [
        'title' => t('E-mail @email', ['@email' => $search_string]),
        'description' => t('Creates a mailto link for e-mail @email', ['@email' => $search_string]),
        'path' => 'mailto:' . Html::escape($search_string),
        'group' => t('E-mail'),
      ];
    }

    // If there is still no matches, return a "no results" array.
    if (empty($matches)) {
      return [
        [
          'title' => t('No content results'),
          'description' => t('There were no content results. Using the link provided as-is.'),
        ]
      ];
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
