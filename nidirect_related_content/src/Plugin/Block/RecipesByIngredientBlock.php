<?php

namespace Drupal\nidirect_related_content\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\facets\Utility\FacetsUrlGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * A block to show links to pre-filtered searches by ingredient.
 *
 * @Block(
 *  id = "recipes_by_ingredient",
 *  admin_label = @Translation("Recipes by ingredient"),
 *  category = "NIDirect related content",
 * )
 */
class RecipesByIngredientBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\facets\Utility\FacetsUrlGenerator
   */
  protected $facetsUrlGenerator;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * RecipesByIngredientBlock constructor.
   *
   * @param array $configuration
   *   Drupal configuration object.
   * @param string $plugin_id
   *   Block plugin id.
   * @param array $plugin_definition
   *   Block plugin definition details.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager for loading entities.
   * @param \Drupal\facets\Utility\FacetsUrlGenerator $facets_url_generator
   *   Facets URL generator service to easily generate facet URLs.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logging service.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, FacetsUrlGenerator $facets_url_generator, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->facetsUrlGenerator = $facets_url_generator;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Dependency injection container.
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('facets.utility.url_generator'),
      $container->get('logger.factory')->get('nidirect_related_content')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Load the current node and look for the main ingredient field value.
    $node = $this->routeMatch->getParameter('node');

    if (!empty($node)) {
      $ingredients = $node->get('field_recipe_main_ingredient')->getValue();

      if (empty($ingredients)) {
        return;
      }

      // For each entry, create a new Link object and add to the render array.
      $build['facet_link_ingredients'] = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
      ];
      foreach ((array) $ingredients as $value) {
        $term_id = $value['target_id'];
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);

        // Skip if null.
        if (empty($term)) {
          continue;
        }

        $facet_url = $this->facetsUrlGenerator->getUrl(['main_ingredient' => [$term_id]]);
        $build['facet_link_ingredients']['#items'][] = Link::fromTextAndUrl($term->label(), $facet_url)->toRenderable();
      }

      // Set cache context to vary by node path to avoid duplication across different pages.
      $build['facet_link_ingredients']['#cache']['contexts'] = ['url.path'];
      $build['facet_link_ingredients']['#cache']['tags'] = $node->getCacheTags();
    }

    return $build;
  }

}
