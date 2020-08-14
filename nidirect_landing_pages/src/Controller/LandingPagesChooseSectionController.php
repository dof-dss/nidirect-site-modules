<?php

namespace Drupal\layout_builder\Controller;

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

    return $build;
  }

}
