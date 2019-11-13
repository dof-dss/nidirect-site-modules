<?php

namespace Drupal\whatlinkshere\Command;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\whatlinkshere\LinkManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\Command;

/**
 * Class ScanCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="whatlinkshere",
 *     extensionType="module"
 * )
 */
class ScanCommand extends Command {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Link manager service.
   *
   * @var \Drupal\whatlinkshere\LinkManagerInterface
   */
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
      ->setName('whatlinkshere:scan')
      ->setDescription($this->trans('commands.whatlinkshere.scan.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->getIo()->info('Executing content scan for whatlinkshere...');

    $db_conn = $this->database::getConnection('default', 'default');

    // Verify Drupal 8 whatlinkshere table exists.
    if (!$db_conn->schema()->tableExists('whatlinkshere')) {
      return 3;
    }

    $query = $db_conn->query('SELECT nid FROM {node}');
    $results = $query->fetchAll();

    // Potentially long running task here; may need batching in future.
    $storage = $this->entityTypeManager->getStorage('node');

    foreach ($results as $row) {
      $node = $storage->load($row->nid);

      if ($node instanceof NodeInterface) {
        $this->linkManager->processEntity($node);
      }
    }

    $this->getIo()->info($this->trans('commands.whatlinkshere.scan.messages.success'));
  }

}
