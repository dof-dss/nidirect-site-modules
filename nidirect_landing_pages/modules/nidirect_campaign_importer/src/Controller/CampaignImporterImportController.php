<?php

namespace Drupal\nidirect_campaign_importer\Controller;

use DOMDocument;
use DOMNode;
use DOMXPath;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\nidirect_campaign_importer\LayoutBuilderBlockManager;
use Drupal\block_content\BlockContentInterface;
use Drupal\node\NodeInterface;

/**
 * Returns responses for NIDirect Campaign Utilities routes.
 */
class CampaignImporterImportController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * The Layout Builder node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * The current request stack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The Layout Builder Block Manager.
   *
   * @var \Drupal\nidirect_landing_pages\LayoutBuilderBlockManager
   */
  protected $blockManager;

  /**
   * Array of counters.
   *
   * @var array
   */
  protected $counters;

  /**
   * Controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param Symfony\Component\HttpFoundation\RequestStack $request
   *   The current request stack.
   * @param \Drupal\nidirect_landing_pages\LayoutBuilderBlockManager $block_manager
   *   The Layout Builder Block Manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request, LayoutBuilderBlockManager $block_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dbConnD7 = Database::getConnection('default', 'migrate');
    $this->dbConnD8 = Database::getConnection('default', 'default');
    $this->request = $request;
    $this->blockManager = $block_manager;
    $this->counters = ['sections' => 0, 'blocks' => 0];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('nidirect_landing_pages.layout_builder_block_manager')
    );
  }

  /**
   * Builds the response.
   */
  public function build($nid) {

    // Check if we have an existing landing page with that nid.
    $query = $this->dbConnD8->query("SELECT nid FROM {node} WHERE nid = " . $nid);
    $drupal8_page_exists = empty($query->fetchCol(0)) ? FALSE : TRUE;

    // Retrieve details of Drupal 7 landing page.
    $query = $this->dbConnD7->query(
      "SELECT title, body_value FROM {node} INNER JOIN {field_data_body} ON node.nid = field_data_body.entity_id WHERE entity_id = " . $nid);
    $d7_landing_pages = $query->fetchAssoc();

    if (empty($d7_landing_pages)) {
      $build['content'] = [
        '#markup' => $this->t('Unable to fetch landing page details from Drupal 7 database.'),
      ];
    }

    if ($drupal8_page_exists) {
      $this->node = $this->entityTypeManager->getStorage('node')->load($nid);
    }
    else {
      // Create new landing page.
      $node_config = [
        'type' => 'landing_page',
        'title' => $d7_landing_pages['title'],
      ];

      $this->node = $this->entityTypeManager->getStorage('node')->create($node_config);
      $this->node->save();
    }

    // Parse the body content.
    $dom = new DOMDocument();
    $dom->strictErrorChecking = FALSE;
    $dom->preserveWhiteSpace = FALSE;
    $dom->loadHTML($d7_landing_pages['body_value']);
    $xpath = new DOMXPath($dom);

    $sections = [];

    // Iterate each section and create a layout builder section.
    foreach ($xpath->query('/html/body/div') as $domnode) {

      if ($domnode->hasAttribute('class')) {
        $section_class = $domnode->getAttribute('class');

        switch ($section_class) {
          case 'three-cols';
            $section = new Section('teasers_x3');
            $region = ['one', 'two', 'three'];
            foreach ($xpath->query('div[contains(@class,\'col\')]/div[contains(@class,\'col-content\')]', $domnode) as $child) {
              $current_region = array_shift($region);

              if ($current_region) {
                $block_content = $this->extractDomNodeData($child, $xpath);
                $block = $this->createBlock('card_standard', $block_content, $this->node);
                $component = $this->createSectionContent($block, $current_region);
                $section->appendComponent($component);
              }
            }

            $sections[] = $section;

            break;

          case 'two-cols';
            $section = new Section('teasers_x2');
            $region = ['one', 'two'];
            foreach ($xpath->query('div[contains(@class,\'col\')]/div[contains(@class,\'col-content\')]', $domnode) as $child) {
              $current_region = array_shift($region);

              if ($current_region) {
                $block_content = $this->extractDomNodeData($child, $xpath);
                $block = $this->createBlock('card_standard', $block_content, $this->node);
                $this->createSectionContent($block, $current_region);
                $component = $this->createSectionContent($block, $current_region);
                $section->appendComponent($component);
              }
            }

            $sections[] = $section;

            break;

          case 'article-topic-teaser-wrap';
            $section = new Section('teasers_x2');
            $region = ['one', 'two'];
            foreach ($xpath->query('div[contains(@class,\'columnItem\')]', $domnode) as $child) {
              $current_region = array_pop($region);

              if ($current_region) {
                $block_content = $this->extractDomNodeData($child, $xpath);
                $block = $this->createBlock('card_standard', $block_content, $this->node);
                $this->createSectionContent($block, $current_region);
                $component = $this->createSectionContent($block, $current_region);
                $section->appendComponent($component);
              }
            }

            $sections[] = $section;

            break;

          default;
            break;

        }
      }
    }

    $this->counters['sections'] = count($sections);

    $this->node->layout_builder__layout->setValue($sections);
    $this->node->save();

    if ($drupal8_page_exists) {
      $output = 'Updated landing page <a href="/node/' . $this->node->id() . '">' . $d7_landing_pages['title'] . '</a>';
    }
    else {
      $output = 'New landing page created for <a href="/node/' . $this->node->id() . '">' . $d7_landing_pages['title'] . '</a>';
    }

    $build['link'] = [
      '#markup' => $output,
    ];

    $build['import_stats'] = [
      '#theme' => 'item_list',
      '#title' => $this->t('Import statistics'),
      '#list_type' => 'ul',
      '#items' => [
        $this->counters['sections'] . ' sections.',
        $this->counters['blocks'] . ' blocks.',
      ],
    ];

    return $build;
  }

  /**
   * Extracts content data from DOM node.
   *
   * @param \DOMNode $dom_node
   *   The DOM node to extract content from.
   * @param \DOMXPath $xpath
   *   XPath query object.
   *
   * @return array
   *   An array of extract content from the DOM node.
   */
  protected function extractDomNodeData(DOMNode $dom_node, DOMXPath $xpath) {
    $content = [];

    // Extract the title.
    $content['title'] = $xpath->query('h2', $dom_node)->item(0)->nodeValue;

    // Extract a link from the title, if present.
    $link = $xpath->query('h2/a', $dom_node);

    // Extract a link from the image link.
    if ($link->length == 0) {
      $link = $xpath->query('div[contains(@class, \'img-placeholder\')]/a', $dom_node->parentNode);
    }

    if ($link->length > 0) {
      $link = $link->item(0)->getAttribute('href');

      $links[] = [
        'uri' => (strpos($link, '/') === 0 ? 'internal:' . $link : $link),
        'title' => '',
        'options' => [
          'attributes' => [],
        ],
      ];

      $content['link'] = $links[0];
    }

    // Extract image data.
    $image = $xpath->query('div[contains(@class, \'img-placeholder\')]', $dom_node->parentNode);
    $image_embed_value = $image->item(0)->nodeValue;

    $image_data = json_decode($image_embed_value);
    $content['image'] = ['target_id' => $image_data[0][0]->fid];

    // Extract teaser content.
    $content['teaser'] = $xpath->query('h2/following-sibling::*', $dom_node)->item(0)->nodeValue;

    return $content;
  }

  /**
   * Create a new content block.
   *
   * @param string $type
   *   The machine name of the content block type.
   * @param array $content
   *   An array of content to populate the block.
   * @param \Drupal\node\NodeInterface $node
   *   The node this block will be added to.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The new content block.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createBlock(string $type, array $content, NodeInterface $node) {

    $block_config = [
      'info' => $node->id() . ' : ' . $content['title'],
      'type' => $type,
      'langcode' => 'en',
      'field_body' => $content['body'],
      'field_image' => $content['image'],
      'field_teaser' => $content['teaser'],
      'field_link' => $content['link'],
      'title' => $content['title'],
    ];

    $block = $this->entityTypeManager->getStorage('block_content')->create($block_config);
    $block->save();

    $this->blockManager->add($node, $block);

    $this->counters['blocks']++;

    return $block;
  }

  /**
   * Create a new Layout Builder Section Component containing a content block.
   *
   * @param \Drupal\block_content\BlockContentInterface $block
   *   The content block to add to the section.
   * @param string $region
   *   The region id of the section for the block to be inserted into.
   *
   * @return \Drupal\layout_builder\SectionComponent
   *   The new layout builder section component.
   */
  protected function createSectionContent(BlockContentInterface $block, string $region) {

    $plugin_config = [
      'id' => 'inline_block:' . $block->bundle(),
      'provider' => 'layout_builder',
      'label' => $block->label(),
      'label_display' => 'visible',
      'block_revision_id' => $block->id(),
    ];

    // Create and return a new Layout Builder Section Component using the
    // content block and plugin configuration.
    return new SectionComponent($block->uuid(), $region, $plugin_config);
  }

}
