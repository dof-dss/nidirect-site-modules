<?php

namespace Drupal\nidirect_taxonomy_navigator\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Link;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TaxonomyNavigatorController.
 */
class TaxonomyNavigatorController extends ControllerBase {

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
      $links[] = Link::createFromRoute($vocabulary->label(), 'nidirect_taxonomy_navigator.taxonomy_navigator_form', [
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

  /**
   * Provides title callback for vocabulary navigator .
   *
   * @param Vocabulary $vocabulary
   *   The Vocabulary entity.
   * @return string|null
   *   The title for the entity view page, if an entity was found.
   */
  public function navigatorVocabularyTitle(Vocabulary $vocabulary) {
    return  $this->t('Taxonomy manager: @vocabulary ', ['@vocabulary' => $vocabulary->label()]);
  }

  public function searchAutocomplete(Request $request) {

    $input = Xss::filter($request->query->get('q'));
    $vocabulary = Xss::filter($request->query->get('vocabulary'));

    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    $query = $termStorage->getQuery()
      ->condition('vid', $vocabulary)
      ->condition('name', $input, 'CONTAINS')
      ->sort('name', 'DESC')
      ->range(0, 25);

    $ids = $query->execute();
    $terms = $ids ? $termStorage->loadMultiple($ids) : [];

    foreach ($terms as $term) {

      $results[] = [
        'value' => EntityAutocomplete::getEntityLabels([$term]),
        'label' => $term->label(),
      ];
    }

    return new JsonResponse($results);
  }
}
