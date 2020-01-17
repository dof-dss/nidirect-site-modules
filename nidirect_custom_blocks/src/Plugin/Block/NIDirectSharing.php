<?php

namespace Drupal\nidirect_custom_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'NIDirectSharing' block.
 *
 * @Block(
 *  id = "nidirect_sharing",
 *  admin_label = @Translation("Nidirect sharing"),
 * )
 */
class NIDirectSharing extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'nidirect_sharing';
    $build['nidirect_sharing']['#attached'] = [];
    $build['nidirect_sharing']['#attached']['library'] = ['nidirect_custom_blocks/nidirect_sharing'];
    $build['nidirect_sharing']['#markup'] = '<span class="social_sharing" style="line-height: 32px;">
      <a class="facebook_share_link" target="_blank" href="https://www.facebook.com/dialog/share?app_id=5303202981&display=popup&href=http%3A%2F%2Fnidirect8.local%3A8080%2Farticles%2Fbaking-with-your-child&redirect_uri=https%3A%2F%2Fstatic.addtoany.com%2Fmenu%2Fthanks.html%23url%3Dhttp%3A%2F%2Fnidirect8.local%3A8080%2Farticles%2Fbaking-with-your-child&quote=" rel="nofollow noopener">
        <span class="facebook_share" style="background-color: rgb(59, 89, 152);">
          <svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
            <path fill="#FFF" d="M17.78 27.5V17.008h3.522l.527-4.09h-4.05v-2.61c0-1.182.33-1.99 2.023-1.99h2.166V4.66c-.375-.05-1.66-.16-3.155-.16-3.123 0-5.26 1.905-5.26 5.405v3.016h-3.53v4.09h3.53V27.5h4.223z"></path>
          </svg>
        </span>
      </a>
      <a class="twitter-share-button"
         href="https://twitter.com/intent/tweet?text=Hello%20world"
         data-size="large">
        <span class="twitter_share" style="background-color: rgb(85, 172, 238);">
          <svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
            <path fill="#FFF" d="M28 8.557a9.913 9.913 0 0 1-2.828.775 4.93 4.93 0 0 0 2.166-2.725 9.738 9.738 0 0 1-3.13 1.194 4.92 4.92 0 0 0-3.593-1.55 4.924 4.924 0 0 0-4.794 6.049c-4.09-.21-7.72-2.17-10.15-5.15a4.942 4.942 0 0 0-.665 2.477c0 1.71.87 3.214 2.19 4.1a4.968 4.968 0 0 1-2.23-.616v.06c0 2.39 1.7 4.38 3.952 4.83-.414.115-.85.174-1.297.174-.318 0-.626-.03-.928-.086a4.935 4.935 0 0 0 4.6 3.42 9.893 9.893 0 0 1-6.114 2.107c-.398 0-.79-.023-1.175-.068a13.953 13.953 0 0 0 7.55 2.213c9.056 0 14.01-7.507 14.01-14.013 0-.213-.005-.426-.015-.637.96-.695 1.795-1.56 2.455-2.55z"></path>
          </svg>
        </span>
      </a>
      <a class="email_share_link" target="_blank" href="/#email" rel="nofollow noopener">
        <span class="email_share" style="background-color: rgb(1, 102, 255);">
          <svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
            <path fill="#FFF" d="M26 21.25v-9s-9.1 6.35-9.984 6.68C15.144 18.616 6 12.25 6 12.25v9c0 1.25.266 1.5 1.5 1.5h17c1.266 0 1.5-.22 1.5-1.5zm-.015-10.765c0-.91-.265-1.235-1.485-1.235h-17c-1.255 0-1.5.39-1.5 1.3l.015.14s9.035 6.22 10 6.56c1.02-.395 9.985-6.7 9.985-6.7l-.015-.065z"></path>
          </svg>
        </span>
      </a>
  </span>';

    return $build;
  }

}
