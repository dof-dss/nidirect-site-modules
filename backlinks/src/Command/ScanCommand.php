<?php

namespace Drupal\backlinks\Command;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\backlinks\LinkManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\Command;

/**
 * Class ScanCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="backlinks",
 *     extensionType="module"
 * )
 */
class ScanCommand extends Command {

  /**
   * Entity type manager service.
   *
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;


  protected $linkManager;

  /**
   * Constructs a new DefaultCommand object.
   */
  public function __construct(EntityTypeManager $entity_type_manager, Database $database, LinkManagerInterface $link_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->linkManager = $link_manager;

    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('backlinks:scan')
      ->setDescription($this->trans('commands.backlinks.scan.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->getIo()->info('Executing content scan for backlinks...');

    $db_conn = $this->database::getConnection('default', 'default');

    // Verify Drupal 8 backlinks table exists.
    if (!$db_conn->schema()->tableExists('backlinks')) {
      return 3;
    }

    $query = $db_conn->query('select nid from {node}');
    $results = $query->fetchAll();

    // Potentially long running task here; may need batching in future.
    $storage = $this->entityTypeManager->getStorage('node');

    foreach ($results as $row) {
      $node = $storage->load($row->nid);

      if ($node instanceof NodeInterface) {
        $this->linkManager->processEntity($node);
      }
    }

    $this->getIo()->info($this->trans('commands.backlinks.scan.messages.success'));
  }

}
