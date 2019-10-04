<?php

namespace Drupal\nidirect_health_conditions\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'HealthConditionsAzBlock' block.
 *
 * @Block(
 *  id = "healthconditions_az_block",
 *  admin_label = @Translation("Health Conditions A to Z"),
 * )
 */
class HealthConditionsAzBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * @param array $configuration
   *   Site configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   Route match object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $links = [];

    $title = $this->t('Or find conditions beginning with') . '...';

    if ($this->routeMatch->getRouteName() == 'nidirect_health_conditions.letter') {
      $letter = $this->routeMatch->getParameter('letter');
      $title = $this->t('Showing entries for :letter', [':letter' => strtoupper($letter)]);
    }

    $build['title'] = [
      '#markup' => '<p>' . $title . '</p>',
    ];

    foreach (range('a', 'z') as $item) {
      $links[] = Link::createFromRoute(strtoupper($item), 'nidirect_health_conditions.letter', ['letter' => $item], [
        'attributes' => [
          'title' => $this->t('View entries under :item', [':item' => strtoupper($item)]),
        ]
      ])->toRenderable();
    }

    $build['healthconditions_az_block'] = [
      '#theme' => 'item_list',
      '#items' => $links,
      '#attributes' => [
        'class' => 'az-facet-list',
        'id' => 'health-conditions-az',
      ],
    ];

    return $build;
  }

}
