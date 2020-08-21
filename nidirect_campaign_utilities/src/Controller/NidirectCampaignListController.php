<?php

namespace Drupal\nidirect_campaign_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for NIDirect Campaign Utilities routes.
 */
class NidirectCampaignListController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
