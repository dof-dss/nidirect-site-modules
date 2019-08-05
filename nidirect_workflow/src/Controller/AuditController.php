<?php

namespace Drupal\nidirect_workflow\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AuditController.
 */
class AuditController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Creates a new ModerationStateConstraintValidator instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.factory')->get('nidirect_workflow')
    );
  }

  /**

  /**
   * Content Audit.
   *
   * @return string
   *   Return confirmation string.
   */
  public function contentAudit($nid) {
    $msg = 'Invalid node id';
    // Don't forget translations.
    if (!empty($nid)) {
      //$node = Node::load($nid);
      $node = $this->entityTypeManager()->getStorage('node')->load($nid);
      if ($node) {
        $msg = "Click this button to indicate that you have audited this published content ";
        $msg .= "and are happy that it is still accurate and relevant.";
        $msg .= "<div><a href='/nidirect_workflow/confirm_audit/$nid'>Audit this published content</a></div>";
      }
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t($msg),
    ];
  }

  /**
   * Confirm Audit.
   *
   * @return string
   *   Return confirmation string.
   */
  public function confirmAudit($nid) {
    $msg = 'Invalid node id';
    // Don't display anything, just do the update and
    // follow the 'destination' param.
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Successfully audited'),
    ];
  }

}
