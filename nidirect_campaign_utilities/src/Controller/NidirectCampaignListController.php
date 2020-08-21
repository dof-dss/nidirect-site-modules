<?php

namespace Drupal\nidirect_campaign_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Returns responses for NIDirect Campaign Utilities routes.
 */
class NidirectCampaignListController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $conn_drupal7 = Database::getConnection('default', 'migrate');

    $query = $conn_drupal7->query("SELECT nid, title, status FROM {node} WHERE type = 'landing_page' ORDER BY title");
    $d7_landing_pages = $query->fetchAll();

    $items = [];

    foreach ($d7_landing_pages as $landing_page) {
      $items[] = ['nid' => $landing_page->nid, 'title' => $landing_page->title, 'Published' => $landing_page->status ? 'yes' : 'no'];
    }

    $build['content'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('NID'),
        $this->t('Title'),
        $this->t('Published'),
      ],
      '#empty' => $this->t('No campaign content found.'),
    ];

    $build['content']['#rows'] = $items;

    return $build;
  }

}
