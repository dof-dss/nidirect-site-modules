<?php

namespace Drupal\nidirect_search\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Solr Elevated Id entity form.
 *
 * @property \Drupal\nidirect_search\SolrElevatedIdEntityInterface $entity
 */
class SolrElevatedIdEntityForm extends EntityForm {

  /**
   * Solr server entity.
   *
   * @var \Drupal\search_api\Entity\Server
   */
  protected $solr_server;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->solr_server = $entity_type_manager->getStorage('search_api_server')->load('solr_default');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search term'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('Search term to define elevations for.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\nidirect_search\Entity\SolrElevatedIdEntity::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    foreach ($this->solr_server->getIndexes() as $id => $index) {
      $index_options[$id] = $index->label();
    }

    $form['index'] = [
      '#type' => 'select',
      '#title' => $this->t('Search index'),
      '#options' => $index_options,
      '#default_value' => $this->entity->index(),
      '#description' => $this->t('Solr search index to elevate against.'),
      '#required' => TRUE,
    ];

    $form['nodes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nodes'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->nodes(),
      '#description' => $this->t('Comma separated nodes ids to elevate'),
      '#placeholder' => 'e.g. 1, 1021, 67',
      '#required' => TRUE,
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('label', strtolower($form_state->getValue('label')));

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $message = $result === SAVED_NEW
      ? $this->t('Created new Solr elevated id: %label.', ['%label' => $this->entity->label()])
      : $this->t('Updated Solr elevated id: %label.', ['%label' => $this->entity->label()]);
    $this->messenger()->addStatus($message);

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
