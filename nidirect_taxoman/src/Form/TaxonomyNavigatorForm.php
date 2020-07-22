<?php

namespace Drupal\nidirect_taxoman\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;

/**
 * Class TaxonomyNavigatorForm.
 */
class TaxonomyNavigatorForm extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->dbConnection = Database::getConnection('default', 'default');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'taxonomy_navigator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $route_params = $this->getRouteMatch()->getParameters();

    $vocabulary_id = $route_params->get('vocabulary');
    $tid = $route_params->get('term') ?? 0;

    $vocabulary = $this->entityTypeManager->getStorage("taxonomy_vocabulary")->load($vocabulary_id);

    $breadcrumb = new Breadcrumb();

    $links[] = Link::createFromRoute($vocabulary->label(), 'nidirect_taxoman.taxonomy_navigator_form', ['vocabulary' => $vocabulary->id()]);

    if ($tid > 0) {
      // This issue https://www.drupal.org/node/2019905
      // prevents us from using ->loadParents() as we won't
      // retrieve the root term.
      $ancestors = array_reverse(array_values($this->entityTypeManager->getStorage("taxonomy_term")->loadAllParents($tid)));

      foreach ($ancestors as $ancestor) {
        $links[] = Link::createFromRoute($ancestor->label(), 'nidirect_taxoman.taxonomy_navigator_form', [
          'vocabulary' => $vocabulary->id(),
          'term' => $ancestor->id(),
          ]);
      }
    }

    $breadcrumb->setLinks($links);

    $form['breadcrumb'] = $breadcrumb->toRenderable();

    // For performance reasons we won't load the term entities.
    $terms = $this->entityTypeManager->getStorage("taxonomy_term")->loadTree($vocabulary->id(), $tid, 1, FALSE);
    $group_class = 'group-order-weight';

    $form['terms'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Weight'),
        '',
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No terms found.'),
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ],
      ],
    ];

    // Build rows.
    foreach ($terms as $key => $term) {
      $form['terms'][$key]['#attributes']['class'][] = 'draggable';
      $form['terms'][$key]['#weight'] = $term->weight;

      $form['terms'][$key]['name'] = [
        '#title' => $term->name,
        '#type' => 'link',
        '#url' => Url::fromRoute('nidirect_taxoman.taxonomy_navigator_form', [
          'vocabulary' => $vocabulary->id(),
          'term' => $term->tid,
        ]),
      ];

      $form['terms'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $term->name]),
        '#title_display' => 'invisible',
        '#default_value' => $term->weight,
        '#attributes' => ['class' => [$group_class]],
      ];

      $form['terms'][$key]['tid'] = [
        '#type' => 'hidden',
        '#value' => $term->tid,
      ];

      $form['terms'][$key]['operations'] = [
        '#type' => 'operations',
        '#links' => [],
      ];

      $form['terms'][$key]['operations']['#links']['view'] = [
        'title' => t('View'),
        'url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid]),
      ];

      $form['terms'][$key]['operations']['#links']['edit'] = [
        'title' => t('Edit'),
        'url' => Url::fromRoute('entity.taxonomy_term.edit_form', ['taxonomy_term' => $term->tid], ['query' => \Drupal::destination()->getAsArray()]),
      ];

      $form['terms'][$key]['operations']['#links']['delete'] = [
        'title' => t('Delete'),
        'url' => Url::fromRoute('entity.taxonomy_term.delete_form', ['taxonomy_term' => $term->tid], ['query' => \Drupal::destination()->getAsArray()]),
      ];

    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $terms = $form_values['terms'];
    foreach ($terms as $term) {
      // Todo: Improve the performance of updating weight.
      $this->dbConnection->update('taxonomy_term_field_data')
        ->fields(['weight' => $term['weight']])
        ->condition('tid', $term['tid'], '=')
        ->execute();
    }
  }

}
