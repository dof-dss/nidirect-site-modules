<?php

namespace Drupal\nidirect_landing_pages\Controller;

use Drupal\layout_builder\Controller\ChooseSectionController;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Class LandingPagesChooseSectionController.
 */
class LandingPagesChooseSectionController extends ChooseSectionController {

  /**
   * Choose a layout plugin to add as a section.
   *
   * Improves upon the core layout builder display by adding additional
   * styling for layouts and the back link.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   *
   * @return array
   *   The render array.
   */
  public function build(SectionStorageInterface $section_storage, $delta) {
    $build = parent::build($section_storage, $delta);

    foreach ($build['layouts']['#items'] as &$item) {
      $item['#attributes']['class'][] = 'nidirect-landing-pages--add-section';
    }

    $build['#attached']['library'][] = 'nidirect_landing_pages/landing_page_admin';

    $build['layouts']['#attributes']['class'][] = 'nidirect-landing-pages';

    return $build;
  }

}
