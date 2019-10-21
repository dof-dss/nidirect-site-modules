<?php

namespace Drupal\nidirect_site_themes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\taxonomy\Entity\Term;

/**
 * Class SiteThemeController.
 */
class SiteThemeController extends ControllerBase {

  /**
   * Disp.
   *
   * @return string
   *   Return Hello string.
   */
  public function disp() {
    $output = $this->printVocab('site_themes');
    return [
      '#type' => 'markup',
      '#markup' => $output,
    ];
  }

  /**
   *
   */
  protected function printVocab($vid) {
    $output = "<div class='item_list'><ul>";
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, 0, 1);
    foreach ($terms as $term) {
      $output .= $this->printOneLevel($vid, $term->tid);
    }
    $output .= "</ul></div>";
    return $output;
  }

  /**
   *
   */
  protected function printOneLevel($vid, $parent_tid) {
    $output = "<div class='theme_item_list'><ul>";
    $term = Term::load($parent_tid);
    if (!empty($term)) {
      $output .= "<li>";
      $account = $this->entityTypeManager()->getStorage('user')->load($this->currentUser()->id());
      $edit_link = '';
      // If current user has 'edit terms in site_themes'
      // permission then add an 'edit' link.
      if ($account->hasPermission('edit terms in site_themes')) {
        $link_object = Link::createFromRoute(t('edit'), 'entity.taxonomy_term.edit_form', ['taxonomy_term' => $parent_tid]);
        $edit_link = $link_object->toString();
      }
      $output .= t(
            "@term (Topic ID: @tid) @edit_link", [
              '@term' => $term->getName(),
              '@tid' => $parent_tid,
              '@edit_link' => $edit_link,
            ]
        );
      $output .= "</li>";
      $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $parent_tid, 1);
      foreach ($terms as $thisterm) {
        $output .= $this->printOneLevel($vid, $thisterm->tid);
      }
    }
    $output .= "</ul></div>";
    return $output;
  }

}
