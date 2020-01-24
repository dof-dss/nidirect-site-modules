<?php

namespace Drupal\nidirect_gp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a GP search block.
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
   * @param Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   Route match object.
   * @param Drupal\Core\Form\FormBuilderInterface $form_builder
   *   Form builder object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $route_match, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
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
      $container->get('current_route_match'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Have we got a proximity search active?
    $is_proximity_search = FALSE; // TODO: swap out for service that can detect, say, a postcode as the trigger for this.

    // TODO: do we need the swap in/out as exposed filter is supposed to look the same across both views displays??
    $view_id = $is_proximity_search ? 'gp_practices_proximity' : 'gp_practices';
    $display_id = $is_proximity_search ? 'gps_by_proximity' : 'find_a_gp';
    $view = Views::getView($view_id);

    // See https://blog.werk21.de/en/2017/03/08/programmatically-render-exposed-filter-form.
    if ($view) {
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
    }

    return $form;
  }

}
