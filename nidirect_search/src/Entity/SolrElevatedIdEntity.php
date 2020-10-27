<?php

namespace Drupal\nidirect_search\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\nidirect_search\SolrElevatedIdEntityInterface;

/**
 * Defines Solr Elevated ID entity.
 *
 * @ConfigEntityType(
 *   id = "solr_elevated_id",
 *   label = @Translation("Solr Elevated ID"),
 *   handlers = {
 *     "list_builder" = "Drupal\nidirect_search\SolrElevatedIdEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\nidirect_search\Form\SolrElevatedIdEntityForm"
 *     }
 *   },
 *   config_prefix = "solr_elevated_id",
 *   admin_permission = "administer solr elevated ids",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/config/search/solr-elevated-id/add",
 *     "collection" = "/admin/config/search/solr-elevated-id/"
 *   }
 * )
 */
class SolrElevatedIdEntity extends ConfigEntityBase implements SolrElevatedIdEntityInterface {

  /**
   * The Solr Elevated ID identifier.
   *
   * @var string
   */
  protected $id;

  /**
   * The Solr Elevated ID search term.
   *
   * @var string
   */
  protected $label;

  /**
   * The Solr index to elevate against.
   *
   * @var string
   */
  protected $index;

  /**
   * The nodes IDs to elevate for this term.
   *
   * @var string
   */
  protected $nodes;

  /**
   * The Solr Elevated ID status.
   *
   * @var bool
   */
  protected $status;

  /**
   * {@inheritdoc}
   */
  public function index() {
    return $this->index;
  }

  /**
   * {@inheritdoc}
   */
  public function nodes() {
    return $this->nodes;
  }

}
