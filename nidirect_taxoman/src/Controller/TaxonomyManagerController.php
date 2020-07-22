<?php

namespace Drupal\nidirect_taxoman\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaxonomyManagerController.
 */
class TaxonomyManagerController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * Index.
   */
  public function index() {

    $vocabularies = $this->entityTypeManager->getStorage('taxonomy_vocabulary')->loadMultiple();

    foreach ($vocabularies as $vocabulary) {
      $links[] = Link::createFromRoute($vocabulary->label(), 'nidirect_taxoman.taxonomy_navigator_form', [
        'vocabulary' => $vocabulary->id(),
      ]);
    }

    $build = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#title' => $this->t('Taxonomy navigator'),
      '#items' => $links,
    ];

    return $build;
  }


}
