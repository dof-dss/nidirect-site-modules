<?php

namespace Drupal\nidirect_campaign_utilities\Controller;

use DOMDocument;
use DOMXPath;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\layout_builder\Section;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for NIDirect Campaign Utilities routes.
 */
class NidirectCampaignCreatorController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Builds the response.
   */
  public function build($nid) {

    // Load existing D7 content
    $conn_drupal7 = Database::getConnection('default', 'migrate');


    // Retrieve all landing pages from D7.
    $query = $conn_drupal7->query(
      "select body_value from {field_data_body} where entity_id = " . $nid);
    $d7_landing_pages = $query->fetchCol(0);

    // Create new landing page
    $node = Node::create([
      'type' => 'landing_page',
      'title' => 'Careers',
    ]);

    // Parse the body content
    $dom = new DOMDocument();
    $dom->strictErrorChecking = FALSE;
    $dom->preserveWhiteSpace = FALSE;
    $dom->loadHTML($d7_landing_pages[0]);
    $xpath = new DOMXPath($dom);

    // Iterate each section and create a layout builder section
    foreach ($xpath->query('/html/body/div') as $domnode) {

      if ($domnode->hasAttribute('class')) {
        $section_class = $domnode->getAttribute('class');

        switch ($section_class) {
          case 'three-cols';
            $section = new Section('teasers_x3');
            foreach ($xpath->query('div[contains(@class,\'col\')]/div[contains(@class,\'col-content\')]', $domnode) as $child) {
              $block_content['title'] = $xpath->query('h2', $child)->item(0)->nodeValue;
              $block_content['link'] = $xpath->query('h2/a', $child)->item(0)->getAttribute('href');
              $block_content['body'] = $xpath->query('h2/following-sibling::p', $child)->item(0)->nodeValue;
              $block = $this->createBlock('card_standard', $block_content);
            }
            break;
          case 'two-cols';
            $section = new Section('teasers_x2');
            foreach ($xpath->query('div[contains(@class,\'col\')]/div[contains(@class,\'col-content\')]', $domnode) as $child) {
            $block_content['title'] = $xpath->query('h2', $child)->item(0)->nodeValue;
            $block_content['link'] = $xpath->query('h2/a', $child)->getAttribute('href');
            $block_content['body'] = $xpath->query('h2/following-sibling::p', $child)->item(0)->nodeValue;
            $block = $this->createBlock('card_standard', $block_content);
          }
            break;
          default;
            break;
        }
      }
    }

    // Iterate each column and create a new block

    // save the landing page.

    // Redirect to new page

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('NID ') . $nid,
    ];

    return $build;
  }

  protected function createBlock($type, $content) {

    $block = BlockContent::create([
      'info' => $content['title'],
      'type' => $type,
      'langcode' => 'en',
      'field_body' => $content['body'],
      'field_teaser' => $content['teaser'],
      'field_link' => $content['link'],
      'title' => $content['title'],
    ]);
    $block->save();

    return $block;
  }

}
