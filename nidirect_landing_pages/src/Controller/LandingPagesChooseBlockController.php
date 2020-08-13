<?php

namespace Drupal\nidirect_landing_pages\Controller;

use Drupal\Component\Utility\Html;
use Drupal\layout_builder\Controller\ChooseBlockController;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LandingPagesChooseBlockController.
 */
class LandingPagesChooseBlockController extends ChooseBlockController
{
  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->moduleHandler = $container->get('module_handler');
    return $instance;
  }

  /**
   * Provides the UI for choosing a new inline block.
   *
   * Improves upon the core layout builder display by adding block type
   * icons and additional styling for the back link.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block is going in.
   *
   * @return array
   *   A render array.
   */
  public function inlineBlockList(SectionStorageInterface $section_storage, $delta, $region)
  {
    $build = parent::inlineBlockList($section_storage, $delta, $region);

    if ($this->moduleHandler->moduleExists('layout_builder_restrictions')){
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
      $link['attributes']['class'][] = 'nidirect-landing-pages-add-block-icon';
      $link['attributes']['class'][] = 'block-' . strtolower(HTML::cleanCssIdentifier($link['title']));
    }

    $build['links']['#attributes']['class'][] = 'nidirect-landing-page--add-custom-block';

    $build['back_button']['#attributes']['class'][] = 'nidirect-landing-page--button-back';

    $build['#title'] = $this->t('Select a custom block type');

    $build['#attached']['library'][] = 'nidirect_landing_pages/landing_page_admin';

    return $build;
  }

}


