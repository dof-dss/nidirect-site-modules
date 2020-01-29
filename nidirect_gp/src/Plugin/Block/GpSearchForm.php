<?php

namespace Drupal\nidirect_gp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a GP search block for the gp_practices view,
 * as well as (via the GpSearchController) the gp_practices_proximity view.
 *
 * Exposed blocks tend to use AJAX and aren't available to embed displays
 * so we wrap the form in a custom Block so we don't have to write a full
 * custom form + handler from scratch and re-use as much of the exposed
 * views filters as possible.
 *
 * @Block(
 *  id = "gp_search_form",
 *  admin_label = @Translation("GP search form"),
 * )
 */
class GpSearchForm extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * Form builder object.
   *
   * @var Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * @param array $configuration
   *   Site configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param Drupal\Core\Form\FormBuilderInterface $form_builder
   *   Form builder object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view_id = 'gp_practices';
    $display_id = 'find_a_gp';
    $view = Views::getView($view_id);

    // See https://blog.werk21.de/en/2017/03/08/programmatically-render-exposed-filter-form.
    if ($view->access($display_id)) {
      $view->setDisplay($display_id);
      $view->initHandlers();

      $form_state = (new FormState())
        ->setStorage([
          'view' => $view,
          'display' => &$view->display_handler->display,
          'rerender' => TRUE,
        ])
        ->setMethod('get')
        ->setAlwaysProcess()
        ->disableRedirect();

      $form_state->set('rerender', NULL);
      $form = $this->formBuilder->buildForm('\Drupal\views\Form\ViewsExposedForm', $form_state);
      // Set a specific cache context because we want to vary the render output depending on whether
      // someone has set a different query parameter. Without this, the value entered in the input
      // box will persist for later requests making it look broken.
      $form['#cache']['contexts'][] = 'url.query_args:search_api_views_fulltext';
    }

    return $form;
  }

}
