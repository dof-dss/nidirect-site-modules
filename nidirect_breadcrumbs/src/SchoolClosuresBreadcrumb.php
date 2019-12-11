<?php

namespace Drupal\nidirect_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for school closure page(s)
 */

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Breadcrumb builder class to assemble breadcrumb trail
 * for paths relating to school closures.
 *
 * @package Drupal\nidirect_breadcrumbs
 */
class SchoolClosuresBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * Route matches from the service container parameters.
   *
   * @var array
   */
  protected $routeMatches;

  /**
   * Path alias manager service.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Class constructor.
   */
  public function __construct(array $route_matches, AliasManagerInterface $path_alias_manager, RequestStack $request_stack) {
    $this->routeMatches = $route_matches;
    $this->aliasManager = $path_alias_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('breadcrumb.schoolclosures.matches'),
      $container->get('path.alias_manager'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    if (in_array($this->requestStack->getCurrentRequest()->getPathInfo(), $this->routeMatches)) {
      $match = TRUE;
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $breadcrumb = new Breadcrumb();
    $links[] = Link::createFromRoute(t('Home'), '<front>');
    // TODO: replace fixed text/paths with routes to actual nodes or taxonomy term pages.
    $links[] = Link::fromTextAndUrl('Education', Url::fromUserInput('/information-and-services/education'));
    $links[] = Link::fromTextAndUrl('Schools, learning and development', Url::fromUserInput('/information-and-services/education/schools-learning-and-development'));
    $links[] = Link::fromTextAndUrl('School life', Url::fromUserInput('/information-and-services/schools-learning-and-development/school-life'));
    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }

}
