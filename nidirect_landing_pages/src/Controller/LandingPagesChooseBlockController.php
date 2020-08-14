<?php

namespace Drupal\nidirect_landing_pages\Controller;

use Drupal\layout_builder\Controller\ChooseBlockController;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LandingPagesChooseBlockController.
 */
class LandingPagesChooseBlockController extends ChooseBlockController {
  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->moduleHandler = $container->get('module_handler');
    $instance->fileSystem = $container->get('file_system');
    return $instance;
  }

  /**
   * Provides the UI for choosing a new inline block-icons.
   *
   * Improves upon the core layout builder display by adding block-icons type
   * icons and additional styling for the back link.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block-icons is going in.
   *
   * @return array
   *   A render array.
   */
  public function inlineBlockList(SectionStorageInterface $section_storage, $delta, $region) {
    $build = parent::inlineBlockList($section_storage, $delta, $region);
    $module_path_rel = drupal_get_path('module', 'nidirect_landing_pages');
    $module_path_abs = $this->fileSystem->realpath($module_path_rel);

    // Support for Layout Builder Restrictions.
    if ($this->moduleHandler->moduleExists('layout_builder_restrictions')) {
      $layout_builder_restrictions_manager = \Drupal::service('plugin.manager.layout_builder_restriction');
      $restriction_plugins = $layout_builder_restrictions_manager->getSortedPlugins();
      foreach (array_keys($restriction_plugins) as $id) {
        $plugin = $layout_builder_restrictions_manager->createInstance($id);
        $allowed_inline_blocks = $plugin->inlineBlocksAllowedinContext($section_storage, $delta, $region);

        foreach ($build['links']['#links'] as $key => $link) {
          $route_parameters = $link['url']->getRouteParameters();
          if (!in_array($route_parameters['plugin_id'], $allowed_inline_blocks)) {
            unset($build['links']['#links'][$key]);
          }
        }
      }
    }

    foreach ($build['links']['#links'] as &$link) {
      $id = $link['url']->getRouteParameters()['plugin_id'];
      $img_name = substr($id, (strpos($id, ':') + 1));
      $title = $link['title'];

      // Check for an image matching this block otherwise use the default.
      if (file_exists($module_path_abs . '/img/block-icons/' . $img_name . '.png')) {
        $img_path = $module_path_rel . '/img/block-icons/' . $img_name . '.png';
      }
      else {
        $img_path = $module_path_rel . '/img/block-icons/default.png';
      }

      $icon = [
        '#theme' => 'image',
        '#uri' => $img_path,
        '#width' => 150,
        '#height' => 150,
        '#alt' => $title,
      ];

      // Convert the title to a nested set of HTML elements.
      $link['title'] =
        [
          $icon,
          [
            '#type' => 'container',
            '#children' => $title,
          ],
        ];
      $link['attributes']['class'][] = 'nidirect-landing-pages-add-block-icon';
    }

    $build['links']['#attributes']['class'][] = 'nidirect-landing-page--add-custom-block-icons';

    // Additional styling for the back link.
    $build['back_button']['#attributes']['class'][] = 'nidirect-landing-page--button-back';

    // Update the title.
    $build['#title'] = $this->t('Select a custom block type');

    $build['#attached']['library'][] = 'nidirect_landing_pages/landing_page_admin';

    return $build;
  }

}
