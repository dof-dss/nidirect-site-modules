<?php

namespace Drupal\nidirect_contacts\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ContactAzBlock' block.
 *
 * @Block(
 *  id = "contact_az_block",
 *  admin_label = @Translation("Contacts A to Z"),
 * )
 */
class ContactAzBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param Drupal\Core\Routing\CurrentRouteMatch $route_match
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

    $title = $this->t('Find contacts beginning with') . '...';

    if ($this->routeMatch->getRouteName() == 'nidirect_contacts.letter') {
      $letter = $this->routeMatch->getParameter('letter');
      $title = $this->t('Showing entries for') . ' ' . strtoupper($letter);
    }

    $build['title'] = [
      '#markup' => '<p>' . $title . '</p>',
    ];

    foreach (array_merge(range('a', 'z'), range('0', '9')) as $item) {
      $links[] = Link::createFromRoute(strtoupper($item), 'nidirect_contacts.letter', ['letter' => $item], [
        'attributes' => [
          'title' => $this->t('View entries under') . ' ' . strtoupper($item)
        ]
      ])->toRenderable();
    }

    $build['contact_az_block'] = [
      '#theme' => 'item_list',
      '#items' => $links,
    ];

    return $build;
  }

}
