<?php

namespace Drupal\nidirect_taxoman\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
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

    $vocabulary = $route_params->get('vocabulary');
    // TODO: Lookup parent tid for given vocabulary.
    $tid = $route_params->get('term') ?? 0;

    // For performance reasons we won't load the term entities.
    $terms = $this->entityTypeManager->getStorage("taxonomy_term")->loadTree($vocabulary, $tid, 1, FALSE);
    $group_class = 'group-order-weight';

    $form['items'] = [
      '#type' => 'table',
      // Todo: Display current tree as a breadcrumb trail in caption.
      '#caption' => $vocabulary,
      '#header' => [
        $this->t('Name'),
        $this->t('Weight'),
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
      $form['items'][$key]['#attributes']['class'][] = 'draggable';
      $form['items'][$key]['#weight'] = $term->weight;

      $form['items'][$key]['name'] = [
        '#title' => $term->name,
        '#type' => 'link',
        '#url' => Url::fromRoute('nidirect_taxoman.taxonomy_navigator_form', [
          'vocabulary' => $vocabulary,
          'term' => $term->tid,
        ],
        [
          'query' => \Drupal::destination()->getAsArray(),
        ]),
      ];

      $form['items'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $term->name]),
        '#title_display' => 'invisible',
        '#default_value' => $term->weight,
        '#attributes' => ['class' => [$group_class]],
      ];

      $form['items'][$key]['operations'] = [
        '#type' => 'operations',
        '#links' => [],
      ];

      $form['items'][$key]['operations']['#links']['view'] = [
        'title' => t('View'),
        'url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid]),
      ];

      $form['items'][$key]['operations']['#links']['edit'] = [
        'title' => t('Edit'),
        'url' => Url::fromRoute('entity.taxonomy_term.edit_form', ['taxonomy_term' => $term->tid], ['query' => \Drupal::destination()->getAsArray()]),
      ];

      $form['items'][$key]['operations']['#links']['delete'] = [
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
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      ksm($form_state->getValues());
    }
  }

}
