<?php

namespace Drupal\nidirect_search;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Solr Elevated ID entities.
 */
class SolrElevatedIdEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Search term');
    $header['index'] = $this->t('Solr index');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->index();
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

  /**
   * @inheritDoc
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);

    if (!empty($operations['edit'])) {
      $edit = $operations['edit']['url'];
      $edit->setRouteParameters(['solr_elevated_id' => $entity->id(),]);
    }

    return $operations;
  }

}
