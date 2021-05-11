<?php

namespace Drupal\Tests\nidirect_related_content\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\nidirect_related_content\RelatedContentManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * @coversDefaultClass Drupal\nidirect_related_content\RelatedContentManager
 *
 * @group nidirect
 */
class RelatedContentTest extends KernelTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * Set Strict config Schema status.
   *
   * @var bool
   */
  protected $strictConfigSchema;

  /**
   * Related content service.
   *
   * @var \Drupal\nidirect_related_content\RelatedContentManager
   */
  protected $relatedContentManager;

  /**
   * Module machine name.
   *
   * @var array
   */
  public static $modules = [
    'nidirect_related_content',
    'taxonomy',
    'node',
    'user',
    'views',
    'text',
    'field',
    'system',
  ];

  /**
   * Test setup function.
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig(['nidirect_related_content']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_term');

    $this->createContentType(['type' => 'article']);

    $this->relatedContentManager = \Drupal::service('nidirect_related_content.manager');
  }

  /**
   * Test related content manager service.
   */
  public function testSubThemeRelated() {



  }



}
