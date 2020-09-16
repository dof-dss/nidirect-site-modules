<?php

namespace Drupal\nidirect_campaign_importer\Controller;

use Drupal\block_content\BlockContentInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
   * Array of counters.
   *
   * @var array
   */
  protected $counters;

  /**
   * The UUID service.
   *
   * @var string
   */
  protected $uuidService;

  /**
   * Controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param Symfony\Component\HttpFoundation\RequestStack $request
   *   The current request stack.
   * @param \Drupal\Core\Database\Connection $connection
   *   The default database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request, Connection $connection, UuidInterface $uuid) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dbConnD7 = Database::getConnection('default', 'migrate');
    $this->request = $request->getCurrentRequest();
    $this->dbConnD8 = $connection;
    $this->uuidService = $uuid;
    $this->counters = ['sections' => 0, 'blocks' => 0];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('database'),
      $container->get('uuid')
    );
  }

  /**
   * Builds the response.
   */
  public function build($nid) {

    // Defines if a node was created or updated.
    $update_existing = FALSE;

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

    if ($drupal8_page_exists && $this->request->query->get('op') == 'update') {
      $update_existing = TRUE;
      $this->node = $this->entityTypeManager->getStorage('node')->load($nid);
    }
    else {
      // Create new landing page.
      $node_config = [
        'type' => 'landing_page',
        'title' => $d7_landing_pages['title'],
      ];

      $this->node = $this->entityTypeManager->getStorage('node')->create($node_config);

      $this->node->layout_builder__layout->setValue(new Section('layout_onecol'));
      $this->node->save();
    }

    // Apply the image banner if available.
    $query = $this->dbConnD7->query(
      "SELECT field_banner_image_fid FROM field_data_field_banner_image WHERE entity_id = " . $nid);
    $d7_banner_fid = $query->fetchCol();

    if (isset($d7_banner_fid[0])) {
      $layout = $this->node->layout_builder__layout->first();

      $layout_contents = $layout->getProperties();
      if (count($layout_contents) > 0) {
        $first_section = current($layout_contents)->getValue();

        if ($first_section->getLayoutId() == 'layout_onecol') {
          $settings = $first_section->getLayoutSettings();
          $settings['label'] = 'Page banner';
          $first_section->setLayoutSettings($settings);


          $block_config = [
            'info' => 'Page banner',
            'type' => 'banner_deep',
            'langcode' => 'en',
            'field_banner_image' => $d7_banner_fid[0],
            'reusable' => 0,
          ];

          $block = $this->entityTypeManager->getStorage('block_content')->create($block_config);

          $plugin_config = [
            'id' => 'inline_block:banner_deep',
            'provider' => 'layout_builder',
            'label' => $block->label(),
            'block_serialized' => serialize($block),
          ];

          $first_section->appendComponent(new SectionComponent($this->uuidService->generate(), 'content', $plugin_config));

          $this->node->layout_builder__layout->setValue($first_section);
          $this->node->save();

        }
      }
    }

    // Parse the body content.
    $dom = new \DOMDocument();
    $dom->strictErrorChecking = FALSE;
    $dom->preserveWhiteSpace = FALSE;
    $dom->loadHTML($d7_landing_pages['body_value']);
    $xpath = new \DOMXPath($dom);

    $sections[] = $this->node->layout_builder__layout->getValue()[0]['section'];

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

    // Update the stats counter.
    $this->counters['sections'] = count($sections);

    $this->node->layout_builder__layout->setValue($sections);
    $this->node->save();

    $build['landing_page_link'] = [
      '#title' => $this->node->getTitle(),
      '#type' => 'link',
      '#prefix' => $this->t('@action landing page:', ['@action' => ($update_existing) ? 'Updated' : 'Created new']),
      '#url' => Url::fromRoute('entity.node.canonical', ['node' => $this->node->id()]),
    ];

    $build['import_stats'] = [
      '#theme' => 'item_list',
      '#title' => $this->t('Import statistics'),
      '#list_type' => 'ul',
      '#items' => [
        $this->t('@count section(s)', ['@count' => $this->counters['sections']]),
        $this->t('@count blocks(s)', ['@count' => $this->counters['blocks']]),
      ],
    ];

    $build['dashboard_link'] = [
      '#title' => $this->t('Return to the campaign dashboard'),
      '#type' => 'link',
      '#url' => Url::fromRoute('nidirect_campaign_importer.dashboard'),
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
  protected function extractDomNodeData(\DOMNode $dom_node, \DOMXPath $xpath) {
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

    try {
      $image_data = json_decode($image_embed_value, FALSE, 512, JSON_THROW_ON_ERROR);
    }
    catch (\JsonException $e) {
      $this->messenger()->addWarning('Unable to decode image data.');
    }

    if (isset($image_data[0][0])) {
      $content['image'] = ['target_id' => $image_data[0][0]->fid];
    }

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

    // Block plugin configuration.
    // Reusable set to false to prevent creation of custom block library entry.
    $block_config = [
      'info' => $content['title'],
      'type' => $type,
      'langcode' => 'en',
      'field_body' => $content['body'],
      'field_image' => $content['image'],
      'field_teaser' => $content['teaser'],
      'field_link' => $content['link'],
      'title' => $content['title'],
      'reusable' => 0,
    ];

    $block = $this->entityTypeManager->getStorage('block_content')->create($block_config);

    // Increment the stats counter.
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

    // Component block plugin configuration containing
    // content block configuration.
    $plugin_config = [
      'id' => 'inline_block:' . $block->bundle(),
      'provider' => 'layout_builder',
      'label' => $block->label(),
      'label_display' => 'visible',
      'block_serialized' => serialize($block),
    ];

    return new SectionComponent($this->uuidService->generate(), $region, $plugin_config);
  }

}
