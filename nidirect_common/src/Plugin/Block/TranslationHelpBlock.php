<?php

namespace Drupal\nidirect_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
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

    return [
      '#type' => 'link',
      '#title' => $this->t('How to translate this page'),
      '#url' => Url::fromRoute('entity.node.canonical', ['node' => 13488]),
      '#attributes' => [
        'class' => ['translation-help'],
      ],
    ];
  }

}

