<?php

namespace Drupal\nidirect_site_themes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class SiteThemeController.
 */
class SiteThemeController extends ControllerBase {

  /**
   * Display.
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
      $links[] = $this->printOneLevel($vid, $term->tid)[0];
    }
    return $links;
  }

  /**
   * Display one level of tree.
   */
  protected function printOneLevel($vid, $parent_tid) {
    $links = [];
    $term = $this->entityTypeManager()->getStorage('taxonomy_term')->load($parent_tid);
    if (!empty($term)) {
      // This is a view link for the taxonomy term, the alias
      // will be picked up.
      $link = Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $parent_tid]);
      $this_link = [
        '#type' => 'link',
        '#prefix' => t(
          "@term (Topic ID: @tid) ", [
            '@term' => $link->toString(),
            '@tid' => $parent_tid,
          ]
        ),
      ];
      // If current user has 'edit terms in site_themes'
      // permission then add an 'edit' link.
      $account = $this->entityTypeManager()->getStorage('user')->load($this->currentUser()->id());
      if ($account->hasPermission('edit terms in site_themes')) {
        $this_link['#url'] = Url::fromRoute('entity.taxonomy_term.edit_form', ['taxonomy_term' => $parent_tid]);
        $this_link['#title'] = t('edit');
      }
      // Look for terms below this one.
      $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $parent_tid, 1);
      foreach ($terms as $thisterm) {
        // Call this function recursively.
        $this_link[] = $this->printOneLevel($vid, $thisterm->tid);
      }
      $links[] = $this_link;
    }
    return $links;
  }

}
