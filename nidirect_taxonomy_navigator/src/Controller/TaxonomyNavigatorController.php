<?php

namespace Drupal\nidirect_taxonomy_navigator\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\nidirect_taxonomy_navigator\TaxonomyNavigatorAccess;
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
    $administer_taxonomies = TaxonomyNavigatorAccess::canAdministerVocabularies();

    $build['vocabularies'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No vocabularies found.'),
      '#tableselect' => FALSE,
    ];


    foreach ($vocabularies as $vocabulary) {
      $build['vocabularies'][$vocabulary->id()]['name'] = [
        '#title' => $vocabulary->label(),
        '#type' => 'link',
        '#url' => Url::fromRoute('nidirect_taxonomy_navigator.taxonomy_navigator_form', [
          'vocabulary' => $vocabulary->id(),
        ]),
      ];

      if ($administer_taxonomies) {
        $build['vocabularies'][$vocabulary->id()]['operations'] = [
          '#type' => 'operations',
          '#links' => [],
        ];

        $build['vocabularies'][$vocabulary->id()]['operations']['#links']['overview'] = [
          'title' => t('Overview'),
          'url' => Url::fromRoute('entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => $vocabulary->id()], ['query' => \Drupal::destination()->getAsArray()]),
        ];

        $build['vocabularies'][$vocabulary->id()]['operations']['#links']['edit'] = [
          'title' => t('Edit'),
          'url' => Url::fromRoute('entity.taxonomy_vocabulary.edit_form', ['taxonomy_vocabulary' => $vocabulary->id()], ['query' => \Drupal::destination()->getAsArray()]),
        ];

        $build['vocabularies'][$vocabulary->id()]['operations']['#links']['delete'] = [
          'title' => t('Delete'),
          'url' => Url::fromRoute('entity.taxonomy_vocabulary.delete_form', ['taxonomy_vocabulary' => $vocabulary->id()], ['query' => \Drupal::destination()->getAsArray()]),
        ];
      }
    }

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
    return  $this->t('@vocabulary ', ['@vocabulary' => $vocabulary->label()]);
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
