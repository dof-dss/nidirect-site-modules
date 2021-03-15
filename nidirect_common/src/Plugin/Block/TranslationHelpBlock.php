<?php

namespace Drupal\nidirect_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Access\AccessResult;
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

    $config = $this->getConfiguration();
    $translation_help_link_title = isset($config['title']) ? $config['title'] : '';
    $transalation_help_link_url = isset($config['url']) ? $config['url'] : '';

    return [
      '#title' => $this->t($translation_help_link_title),
      '#type' => 'link',
      '#url' => Url::fromUri($transalation_help_link_url),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    $form['url'] = [
      '#type' => 'url',
      '#title' => 'Link',
      '#description' => 'The URL for translation help link.',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('url', $form_state->getValue('url'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
   
  }

}

