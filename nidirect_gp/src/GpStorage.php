<?php

namespace Drupal\nidirect_gp;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\nidirect_gp\Entity\GpInterface;

/**
 * Defines the storage handler class for GP entities.
 *
 * This extends the base storage class, adding required special handling for
 * GP entities.
 *
 * @ingroup nidirect_gp
 */
class GpStorage extends SqlContentEntityStorage implements GpStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(GpInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {gp_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {gp_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(GpInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {gp_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('gp_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
