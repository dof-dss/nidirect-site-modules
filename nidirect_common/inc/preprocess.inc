<?php

/**
 * @file
 * Contains hook_preprocess functions for nidirect_common.module.
 */

use Drupal\node\Entity\Node;

/**
 * Implements hook_preprocess_views_view().
 */
function nidirect_common_preprocess_views_view(&$variables) {
  if ($variables['view']->id() === 'media_entity_browser') {
    $variables['view_array']['#attached']['library'][] = 'nidirect_common/media_entity_browser';
  }
}

/**
 * Implements hook_preprocess_page().
 */
function nidirect_common_preprocess_page(&$variables) {
  if (\Drupal::service('router.admin_context')->isAdminRoute()) {
    return;
  }

  // Similar to how core adds the node object to $variables, we will add
  // the term if we are on a taxonomy page.
  if (\Drupal::routeMatch()->getRouteName() == 'entity.taxonomy_term.canonical') {
    $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
    $variables['term'] = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
  }

  // Apply link manager library to all non-admin pages.
  $variables['#attached']['library'][] = 'nidirect_common/link_manager';

  // Sometimes $variables['node'] is not a node object, but just contains the
  // nid as a string, so we'll avoid errors and allow for that here.
  if (isset($variables['node']) && is_string($variables['node'])) {
    $variables['node'] = Node::load($variables['node']);
  }

  // Assign our banner image entity, either a node or a taxonomy term.
  if (isset($variables['node'])) {
    $entity = $variables['node'];
  }
  elseif (isset($variables['term'])) {
    $entity = $variables['term'];
  }

  // If the entity has a banner image field, fetch and display the node or
  // theme banner image and footer text.
  if (!empty($entity) && $entity->hasField('field_banner_image')) {
    $banner_image = _retrieve_hierarchical_field($entity, 'field_banner_image');

    if (!empty($banner_image)) {
      $variables['page']['top_banner']['banner_display'] = $banner_image->view('default');
    }
  }
}

/**
 * Implements hook_preprocess_status_messages().
 */
function nidirect_common_preprocess_status_messages(&$variables) {
  // Add additional explanation to to the oEmbed resource not found message.
  if (isset($variables['message_list']['error'])) {
    $error_messages = $variables['message_list']['error'];

    foreach ($error_messages as $index => $message) {
      if ($message == 'Could not retrieve the oEmbed resource.') {
        $message .= t("This may be due to media being inaccessible or set to private.");
        $variables['message_list']['error'][$index] = $message;
      }
    }
  }
}