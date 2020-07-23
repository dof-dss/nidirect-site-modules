<?php

namespace Drupal\nidirect_taxoman\Form;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaxonomySearchForm.
 */
class TaxonomySearchForm extends FormBase {

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
    return 'taxonomy_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['term'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Taxonomy term'),
      '#autocomplete_route_name' => 'nidirect_taxoman.taxonomy_search_form.autocomplete',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('View'),
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
    $tid = EntityAutocomplete::extractEntityIdFromAutocompleteInput($form_values['term']);

    $ancestors = array_values($this->entityTypeManager->getStorage("taxonomy_term")->loadAllParents($tid));

    if (count($ancestors) > 1) {
      array_shift($ancestors);
      $parent = current($ancestors);
      $form_state->setRedirect('nidirect_taxoman.taxonomy_navigator_form', ['term' => $parent->id()]);
    }
    else {
      $form_state->setRedirect('nidirect_taxoman.taxonomy_navigator_form');
    }

  }
}
