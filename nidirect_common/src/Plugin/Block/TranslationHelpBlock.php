<?php

namespace Drupal\nidirect_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;

/**
 * Provides the Translation Help Block.
 *
 * @Block(
 *   id = "translation_help_block",
 *   admin_label = @Translation("Translation help link"),
 *   category = @Translation("Translation help link"),
 * )
 */
class TranslationHelpBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $uri = \Drupal::request()->getRequestUri();
    $query = [
      'uri' => $uri,
    ];

    return [
      '#attributes' => [
        'class' => ['section-translation-help'],
      ],
      'translation-help-link' => [
        '#type' => 'link',
        '#title' => $this->t('How to translate this page'),
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => 13488, $query]),
        '#attributes' => [
          'class' => ['section-translation-help__link'],
        ]
      ],
    ];
  }

  public function getCacheMaxAge() {
    // The output for this block differs on every page - so don't cache it.
    return 0;
  }

}
