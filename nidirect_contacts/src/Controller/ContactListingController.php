<?php

namespace Drupal\nidirect_contacts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContactListingController.
 */
class ContactListingController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Block\BlockManagerInterface definition.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Constructs a new ContactListingController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, BlockManagerInterface $block_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Default presentation of the contacts listing page.
   *
   * - Title (Contacts A to Z)
   * - Search by contact name
   *    <input 'Search contacts' placeholder> <submit>
   * - Find contacts beginning with...
   *    <Big A-Z, 0-9 grid of links>
   *    Link is: /contacts/letter/a
   *
   * @return string
   *   Return Hello string.
   */
  public function default() {
    $content = [];

    // Views static service class used in absence of container service to inject via constructor.
    $view = Views::getView('contacts');
    $content['contact_form'] = [
      '#type' => 'view',
      '#name' => 'contacts',
      '#display_id' => 'search_form',
    ];

    $az_block = $this->blockManager->createInstance('contact_az_block', []);
    $content['a-z'] = $az_block->build();

    return $content;
  }

}
