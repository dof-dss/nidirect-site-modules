<?php

namespace Drupal\nidirect_campaign_importer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for NIDirect Campaign Utilities routes.
 */
class CampaignImporterDashboardController extends ControllerBase {

  /**
   * The legacy database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnD7;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnD8;

  /**
   * Controller constructor.
   */
  public function __construct($d8_connection) {
    $this->dbConnD8 = $d8_connection;

    $this->dbConnD7 = Database::getConnection('default', 'migrate');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      Database::getConnection('default', 'drupal7db'),
    );
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
      // Fetch the Drupal 8 node ID matching the Drupal 7 node title.
      $d8nid = $this->drupal8LandingPageUrl($landing_page->title);

      $item = [
        'nid' => $landing_page->nid,
        'title' => $landing_page->title,
        'published' => [
          'data' => [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#value' => $landing_page->status ? 'Yes' : 'No',
            '#attributes' => [
              'style' => [
                'font-weight: bold;', $landing_page->status ? 'color: green' : 'color: red',
              ],
            ],
          ],
        ],
        'drupal7' => Link::fromTextAndUrl('View', Url::fromUri('https://www.nidirect.gov.uk/node/' . $landing_page->nid)),
        'drupal8' => empty($d8nid) ? '' : Link::fromTextAndUrl('View', Url::fromUri($host . '/node/' . $d8nid)),
        'update' => '',
      ];

      if (!empty($d8nid)) {
        $item['update'] = Link::createFromRoute('Update', 'nidirect_campaign_importer.creator',
          ['nid' => $landing_page->nid],
          [
            'query' => [$this->getDestinationArray(), 'op' => 'update'],
            'attributes' => [
              'class' => 'button',
              'title' => 'This will overwrite any existing content',
            ],
          ]
        );
      }

      $item['create'] = Link::createFromRoute('Create', 'nidirect_campaign_importer.creator',
        ['nid' => $landing_page->nid],
        [
          'query' => $this->getDestinationArray(),
          'attributes' => ['class' => 'button'],
        ]
      );

      $items[] = $item;

    }

    $build['content'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('NID'),
        $this->t('Title'),
        $this->t('Published (D7)'),
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

  /**
   * Fetch the Drupal 8 node ID based on the node title.
   *
   * @param string $title
   *   Title of the landing page node to search for.
   *
   * @return mixed
   *   Node ID of the matching node or null for no matches.
   */
  protected function drupal8LandingPageUrl(string $title) {
    $query = $this->dbConnD8->query("SELECT n.nid FROM {node} n INNER JOIN {node_field_data} d ON d.nid = n.nid WHERE n.type = 'landing_page' AND d.title = '" . $title . "'");
    $nid = $query->fetchCol(0);

    return $nid[0];
  }

}
