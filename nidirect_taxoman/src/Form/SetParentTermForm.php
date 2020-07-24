<?php

namespace Drupal\nidirect_taxoman\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SetParentTermForm.
 */
class SetParentTermForm extends FormBase {

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
    return 'set_parent_term_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $field_name = 'term_field';
    $request = $this->getRequest();

    $tid = $this->getRouteMatch()->getParameter('term');
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);

    if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
      $page_title = $route->getDefault('_title');
      $route->setDefault('_title', $page_title . ' for ' . $term->label());
    }

    $term_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($term->bundle());
    $terms = [];
    foreach ($term_tree as $tree_term) {
      $terms[$tree_term->tid] = $tree_term->name;
    }

    // Todo: Provide option to set parent as root term.

    $shs = [
      'settings' => [
        'required' => TRUE,
        'multiple' => FALSE,
        'anyLabel' => t('- Any -'),
        'anyValue' => 'All',
        'addNewLabel' => '',
        'force_deepest' => FALSE,
        'create_new_items' => FALSE,
        'create_new_levels' => FALSE,
        'display_node_count' => FALSE,
      ],
      'bundle' => $term->bundle(),
      'baseUrl' => 'shs-term-data',
      'cardinality' => 1,
      'parents' => [[['parent' => 0, 'defaultValue' => 'All']]],
      'defaultValue' => NULL,
    ];

    $form['vocabulary'] = [
      '#type' => 'hidden',
      '#value' => $term->bundle(),
    ];

    $form[$field_name] = [
      '#type' => 'container',
    ];

    $form[$field_name]['widget'] = [
      '#type' => 'select',
      '#title' => t('Select a parent term'),
      '#key_column' => 'tid',
      '#field_parents' => [],
      '#field_name' => $field_name,
      '#shs' => $shs,
      '#options' => $terms,
      '#attributes' => [
        'class' => ['shs-enabled'],
      ],
      '#attached' => [
        'library' => ['shs/shs.form'],
      ],
      '#element_validate' => [[
        '\Drupal\shs\Plugin\Field\FieldWidget\OptionsShsWidget',
        'validateElement',
      ]],
      '#after_build' => [[
        '\Drupal\shs\Plugin\Field\FieldWidget\OptionsShsWidget',
        'afterBuild',
      ]],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
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
    $tid = $this->getRouteMatch()->getParameter('term');
    $form_values = $form_state->getValues();

    $parent_tid = $form_values['widget'][0]['tid'];

    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    $term->set('parent', $parent_tid);
    $term->save();

    $this->getRequest()->query->remove('destination');
    $form_state->setRedirect('nidirect_taxoman.taxonomy_navigator_form', ['vocabulary' => $form_values['vocabulary'], 'term' => $parent_tid], ['query' => ['highlight' => $tid],'fragment' => $tid]);
  }

}
