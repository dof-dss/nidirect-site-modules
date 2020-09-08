<?php

namespace Drupal\nidirect_common\Controller;

/**
 * @file
 * Contains \Drupal\nidirect_common\Controller\LinkitAutocompleteController.
 *
 * Overrides the linkit equivalent so we can wire up our custom result manager
 * as a workaround for no preprocessing or service replacement options.
 */

use Drupal\Component\Utility\Unicode;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\nidirect_common\LinkitSuggestionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LinkitAutocompleteController implements ContainerInjectionInterface {

  /**
   * The linkit profile storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $linkitProfileStorage;

  /**
   * The result manager.
   *
   * @var \Drupal\nidirect_common\LinkitSuggestionManager
   */
  protected $resultManager;

  /**
   * The linkit profile.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * Constructs a EntityAutocompleteController object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $linkit_profile_storage
   *   The linkit profile storage service.
   * @param \Drupal\nidirect_common\LinkitSuggestionManager $resultManager
   *   The result service.
   */
  public function __construct(EntityStorageInterface $linkit_profile_storage, LinkitSuggestionManager $resultManager) {
    $this->linkitProfileStorage = $linkit_profile_storage;
    $this->resultManager = $resultManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('linkit_profile'),
      $container->get('linkit.result_manager')
    );
  }

  /**
   * Menu callback for linkit search autocompletion.
   *
   * Like other autocomplete functions, this function inspects the 'q' query
   * parameter for the string to use to search for suggestions.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $linkit_profile_id
   *   The linkit profile id.
   * @return  \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */
  public function autocomplete(Request $request, string $linkit_profile_id) {
    $this->linkitProfile = $this->linkitProfileStorage->load($linkit_profile_id);
    $string = mb_strtolower($request->query->get('q'));

    $matches = $this->resultManager->getResults($this->linkitProfile, $string);

    $json_object = new \stdClass();
    $json_object->matches = $matches;

    return new JsonResponse($json_object);
  }

}
