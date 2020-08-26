<?php

namespace Drupal\nidirect_campaign_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for NIDirect Campaign Utilities routes.
 */
class NidirectCampaignListController extends ControllerBase {

  protected $dbConnD7;

  protected $dbConnD8;

  public function __construct()
  {
    $this->dbConnD7 = Database::getConnection('default', 'migrate');
    $this->dbConnD8 = Database::getConnection('default', 'default');
  }

  /**
   * Builds the response.
   */
  public function build() {

    $query = $this->dbConnD7->query("SELECT nid, title, status FROM {node} WHERE type = 'landing_page' ORDER BY title");
    $d7_landing_pages = $query->fetchAll();

    $items = [];

    $host = \Drupal::request()->getSchemeAndHttpHost();

    foreach ($d7_landing_pages as $landing_page) {

      $d8nid = $this->drupal8LandingPageURL($landing_page->title);

      $item = [
        'nid' => $landing_page->nid,
        'title' => $landing_page->title,
        'published' => $landing_page->status ? 'yes' : 'no',
        'drupal7' => Link::fromTextAndUrl('View', Url::fromUri('https://www.nidirect.gov.uk/node/' . $landing_page->nid)),
        'drupal8' => empty($d8nid) ? '' : Link::fromTextAndUrl('View', Url::fromUri($host . '/node/' . $d8nid) ),
        'update' => '',
      ];

      if (!empty($d8nid)) {
        $item['update'] = Link::createFromRoute('Update', 'nidirect_campaign_utilities.creator',
          ['nid' => $landing_page->nid],
          ['query' => [$this->getDestinationArray(), 'op' => 'update'],
          'attributes' => ['class' => 'button']]);
      }

      $item['create'] = Link::createFromRoute('Create', 'nidirect_campaign_utilities.creator',
        ['nid' => $landing_page->nid],
        ['query' => $this->getDestinationArray(),
        'attributes' => ['class' => 'button']]);

      $items[] = $item;

    }

    $build['content'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('NID'),
        $this->t('Title'),
        $this->t('Published'),
        $this->t('Drupal 7'),
        $this->t('Drupal 8'),
        [
          'data' => $this->t('Operations'),
          'colspan' => 2,
        ],
      ],
      '#empty' => $this->t('No campaign content found.'),
    ];

    $build['content']['#rows'] = $items;

    return $build;
  }

  protected function drupal8LandingPageURL(string $title) {
    $query = $this->dbConnD8->query("SELECT n.nid FROM {node} n INNER JOIN {node_field_data} d ON d.nid = n.nid WHERE n.type = 'landing_page' AND d.title = '" . $title . "'");
    $nid = $query->fetchCol(0);

    return $nid[0];
  }

}
