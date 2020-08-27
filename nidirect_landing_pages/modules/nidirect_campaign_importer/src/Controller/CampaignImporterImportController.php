<?php

namespace Drupal\nidirect_campaign_importer\Controller;

use DOMDocument;
use DOMXPath;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\nidirect_campaign_importer\LayoutBuilderBlockManager;

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

  protected $dbConnD7;

  protected $dbConnD8;

  protected $node;

  protected $request;

  protected $blockManager;

  protected $counters;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
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
      $container->get('nidirect_campaign_importer.layout_builder_block_manager')
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
    } else {
      // Create new landing page
      $node_config = [
        'type' => 'landing_page',
        'title' => $d7_landing_pages['title'],
      ];

      $this->node = $this->entityTypeManager->getStorage('node')->create($node_config);
      $this->node->save();
    }

    // Parse the body content
    $dom = new DOMDocument();
    $dom->strictErrorChecking = FALSE;
    $dom->preserveWhiteSpace = FALSE;
    $dom->loadHTML($d7_landing_pages['body_value']);
    $xpath = new DOMXPath($dom);

    $sections = [];

    // Iterate each section and create a layout builder section
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
                $block_content = $this->extractCKTemplateData($child, $xpath);
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
                $block_content = $this->extractCKTemplateData($child, $xpath);
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
                $block_content = $this->extractCKTemplateData($child, $xpath);
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
      $output = 'Updated landing page <a href="/node/' . $this->node->id() .'">' . $d7_landing_pages['title'] . '</a>';
    } else {
      $output = 'New landing page created for <a href="/node/' . $this->node->id() .'">' . $d7_landing_pages['title'] . '</a>';
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

  protected function extractCKTemplateData($node, $xpath) {
    $content = [];

    // Title.
    $content['title'] = $xpath->query('h2', $node)->item(0)->nodeValue;

    // Title link
    $link = $xpath->query('h2/a', $node);

    if ($link->length == 0) {
      $link = $xpath->query('div[contains(@class, \'img-placeholder\')]/a', $node->parentNode);
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

    // Image
    $image = $xpath->query('div[contains(@class, \'img-placeholder\')]', $node->parentNode);
    $image_embed_value = $image->item(0)->nodeValue;

    $image_data = json_decode($image_embed_value);
    $content['image'] = ['target_id' => $image_data[0][0]->fid];

    // Teaser
    $content['teaser'] = $xpath->query('h2/following-sibling::*', $node)->item(0)->nodeValue;

    return $content;
  }

  protected function createBlock($type, $content, $node) {

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

  protected function createSectionContent($block, $region) {

    $pluginConfiguration = [
      'id' => 'inline_block:' . $block->bundle(),
      'provider' => 'layout_builder',
      'label' => $block->label(),
      'label_display' => 'visible',
      'block_revision_id' => $block->id(),
    ];

    // Create a new section component using the node and plugin config.
    $component = new SectionComponent($block->uuid(), $region, $pluginConfiguration);

    return $component;
  }

}
