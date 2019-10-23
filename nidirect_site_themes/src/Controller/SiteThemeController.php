<?php

namespace Drupal\nidirect_site_themes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;

/**
 * Class SiteThemeController.
 */
class SiteThemeController extends ControllerBase {

  /**
   * Display.
   *
   * @return site themes tree.
   */
  public function display() {
    $links = $this->printVocab('site_themes');
    return [
      '#theme' => 'item_list',
      '#items' => $links,
    ];
  }

  /**
   * Display entire tree for one vocabulary.
   */
  protected function printVocab($vid) {
    $links = [];
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, 0, 1);
    foreach ($terms as $term) {
      $links[] = $this->printOneLevel($vid, $term->tid);
    }
    return $links;
  }

  /**
   * Display one level of tree.
   */
  protected function printOneLevel($vid, $parent_tid) {
    $links = [];
    $term = Term::load($parent_tid);
    if (!empty($term)) {
      $account = $this->entityTypeManager()->getStorage('user')->load($this->currentUser()->id());
      $edit_link = '';
      // If current user has 'edit terms in site_themes'
      // permission then add an 'edit' link.
      if ($account->hasPermission('edit terms in site_themes')) {
        $edit_url = Url::fromRoute('entity.taxonomy_term.edit_form', ['taxonomy_term' => $parent_tid]);
        //$edit_link = $link_object->toString();
      }

      $default_options = [
        '#type' => 'link',
        '#options' => [
          'absolute' => TRUE,
          'base_url' => $GLOBALS['base_url'],
        ],
      ];

      $prefix = t(
        "@term (Topic ID: @tid) ", [
          '@term' => $term->getName(),
          '@tid' => $parent_tid
        ]
      );

      $links[] = $default_options + [
        '#url' => Url::fromRoute('entity.taxonomy_term.edit_form', ['taxonomy_term' => $parent_tid]),
        '#title' => t('Edit'),
        '#prefix' => $prefix
      ];

      $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $parent_tid, 1);
      foreach ($terms as $thisterm) {
        // Call this function recursively.
         $links[] = $this->printOneLevel($vid, $thisterm->tid);
      }
    }
    return $links;
  }

}
