<?php

namespace Drupal\nidirect_site_themes\Controller;

use Drupal\Core\Controller\ControllerBase;
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
      '#markup' => $output
    ];
  }

  protected function printVocab($vid) {
    $output = "<h1>Site Themes</h1><div class='item_list'><ul>";
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, 0, 1);
    foreach ($terms as $term) {
      $output .= $this->printOneLevel($vid, $term->tid);
    }
    $output .= "</ul></div>";
    return $output;
  }

  protected function printOneLevel($vid, $parent_tid) {
    $output = "<div class='item_list'><ul>";
    $term = Term::load($parent_tid);
    if (!empty($term)) {
      $output .= "<li>" . $term->getName() . "</li>";
      $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $parent_tid, 1);
      foreach ($terms as $thisterm) {
        $output .= $this->printOneLevel($vid, $thisterm->tid);
      }
    }
    $output .= "</ul></div>";
    return $output;
  }

}
