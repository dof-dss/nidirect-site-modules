<?php

/**
 * @file
 * Views hooks for nidirect_backlinks module.
 */

/**
 * Implements hook_views_data_alter().
 */
function nidirect_backlinks_views_data_alter(array &$data) {
  // Works to provide reference/basic join.
//  $data['node_field_data']['reference_id'] = [
//    'title' => t('Backlink: Referenced content'),
//    'help' => t('Backlink: Referenced content'),
//    'relationship' => [
//      'id' => 'standard',
//      'label' => t('Backlink: Referenced content'),
//      'base' => 'nidirect_backlinks',
//      'base field' => 'reference_id',
//      'field' => 'nid',
//    ],
//  ];

  $data['nidirect_backlinks']['id'] = [
    'title' => t('Content related to node ID'),
    'help' => t('Related content from a specific node ID.'),
    'relationship' => [
      'id' => 'standard',
      'field' => 'id',
      'base' => 'node',
      'base field' => 'nid',
      'label' => t('node'),
      'skip base' => 'node',
    ],
  ];
}
