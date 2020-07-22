<?php

namespace Drupal\nidirect_taxoman\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    $build['article'] = [
      '#type' => 'textfield',
      '#title' => $this->t('My Autocomplete'),
      '#autocomplete_route_name' => 'my_module.autocomplete.articles',
    ];

    $vocabularies = $this->entityTypeManager->getStorage('taxonomy_vocabulary')->loadMultiple();

    foreach ($vocabularies as $vocabulary) {
      $links[] = Link::createFromRoute($vocabulary->label(), 'nidirect_taxoman.taxonomy_navigator_form', [
        'vocabulary' => $vocabulary->id(),
      ]);
    }

    $build['navigator_vocabularies'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#title' => $this->t('Taxonomy navigator'),
      '#items' => $links,
    ];

    return $build;
  }

  public function searchAutocomplete(Request $request) {

    $results[] = [
      'value' => '320:Road safety',
      'label' => 'Road safety',
    ];

    return new JsonResponse($results);
  }

}
