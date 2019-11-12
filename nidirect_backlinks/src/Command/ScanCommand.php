<?php

namespace Drupal\nidirect_backlinks\Command;

use Drupal\Core\Database\Database;
use Drupal\node\NodeInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\Command;

/**
 * Class ScanCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="nidirect_backlinks",
 *     extensionType="module"
 * )
 */
class ScanCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('nidirect_backlinks:scan')
      ->setDescription($this->trans('commands.nidirect_backlinks.scan.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->getIo()->info('Executing content scan for backlinks...');

    $db_conn = Database::getConnection('default', 'default');

    // Verify Drupal 8 backlinks table exists.
    if (!$db_conn->schema()->tableExists('nidirect_backlinks')) {
      return 3;
    }

    $query = $db_conn->query('select nid from {node}');
    $results = $query->fetchAll();

    // Potentially long running task here; may need batching.
    foreach ($results as $row) {
      $node = \Drupal::service('entity_type.manager')->getStorage('node')->load($row->nid);

      if ($node instanceof NodeInterface) {
        \Drupal::service('nidirect_backlinks.linkmanager')->processEntity($node);
        // $this->getIo()->info('Processed node ID: ' . $row->nid);
      }
    }

    $this->getIo()->info($this->trans('commands.nidirect_backlinks.scan.messages.success'));
  }

}
