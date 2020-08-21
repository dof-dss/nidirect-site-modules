<?php

namespace Drupal\nidirect_campaign_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

/**
 * Returns responses for NIDirect Campaign Utilities routes.
 */
class NidirectCampaignListController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $conn_drupal7 = Database::getConnection('default', 'migrate');

    $query = $conn_drupal7->query(
      "SELECT nid, title, status FROM {node} WHERE type = 'landing_page'");
    $d7_landing_pages = $query->fetchAll();

    foreach ($d7_landing_pages as $landing_page) {
      $items[] = $landing_page->nid . ' | ' . $landing_page->title . ' | ' . $landing_page->status;
    }


    $build['content'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    return $build;
  }

}
