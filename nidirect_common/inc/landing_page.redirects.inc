<?php

/**
 * @file
 * Contains functions for handling landing page creation.
 */

use Drupal\Core\Link;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\Entity\Node;
use Drupal\redirect\Entity\Redirect;

/**
 * Validation handler for landing page node forms.
 *
 * Checks whether there is already a landing page associated with the theme selected.
 *
 * @param array $form
 *   The form array.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state collection.
 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
 */
function nidirect_common_landing_page_form_validate(array &$form, FormStateInterface $form_state) {
  $theme_selected = $form_state->getValue('field_subtheme')[0]['target_id'];

  // retrieve current node (if we are editing).
  $current_node = \Drupal::routeMatch()->getParameter('node');

  $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
  $query->condition('type', 'landing_page', '=');
  if (!empty($current_node)) {
    // Exclude current node.
    $query->condition('nid', $current_node->id(), '!=');
  }
  $query->condition('field_subtheme', $theme_selected, '=');
  $result = $query->execute();

  if (!empty($result)) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($theme_selected);
    $node = \Drupal::entityTypeManager()->getStorage('node')->load(array_pop($result));
    $link = Link::createFromRoute($node->label(), 'entity.node.edit_form', ['node' => $node->id()])->toRenderable();

    $message = new TranslatableMarkup("There is already a landing page node (@landing_page) associated with '@term_name'", [
      '@landing_page' => \Drupal::service('renderer')->render($link),
      '@term_name' => $term->label()
    ]);

    $form_state->setErrorByName('field_subtheme', $message);
  }
}

/**
 * Function to re-use checks for landing page node with valid theme field selection.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity to evaluate.
 * @return bool
 *   TRUE if it is a landing page with theme field value, otherwise FALSE.
 */
function nidirect_common_is_landing_page_with_theme_value(EntityInterface &$entity) {
  if ($entity instanceof Node == FALSE) {
    return FALSE;
  }

  $has_subtheme_value = $entity->hasField('field_subtheme') && !empty($entity->get('field_subtheme')->getString());

  // Chain logic conditions: if one condition fails any remaining checks are skipped and the expression returns FALSE.
  return ($entity->bundle() == 'landing_page' && $has_subtheme_value);
}

/**
 * Detect whether a redirect already exists, return a URL of the
 * destination if it does.
 *
 * @param string $source_path
 *   The source path.
 * @return string
 *   Redirect URL if found, empty string if not.
 */
function nidirect_common_get_redirect_url(string $source_path) {
  $redirect = \Drupal::service('redirect.repository')->findBySourcePath($source_path);
  $redirect_url = '';

  if (!empty(($redirect))) {
    $redirect = array_pop($redirect);
    $redirect_url = $redirect->getRedirectUrl()->toString();
  }

  return $redirect_url;
}

/**
 * Function to check:
 * - Whether there is already a landing page node associated with the theme selected.
 * - Create a redirect or warn user if there is already a landing page for this theme.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The landing page node entity.
 */
function nidirect_common_create_landing_page_redirect(EntityInterface $entity) {
  $subtheme_tid = $entity->get('field_subtheme')->getString();
  $taxonomy_term_path = 'taxonomy/term/' . $subtheme_tid;
  $redirect_url = nidirect_common_get_redirect_url($taxonomy_term_path);

  if (empty($redirect_url)) {
    Redirect::create([
      'redirect_source' => $taxonomy_term_path,
      'redirect_redirect' => 'internal:/node/' . $entity->id(),
      'language' => Language::LANGCODE_NOT_SPECIFIED,
      'status_code' => '301',
    ])->save();
  }
  else {
    // Notify the user that a redirect could not be created.
    \Drupal::messenger()->addWarning(t('A redirect from the theme taxonomy page to this content was not created,
        because there are already one or more redirects used for the theme taxonomy page.'));

    // Add a convenience link for users who can administer redirects.
    if (\Drupal::currentUser()->hasPermission('administer redirects')) {
      $link = Link::createFromRoute('review existing redirects', 'redirect.list', [], [
        'query' => ['text' => $taxonomy_term_path]
      ])->toString();

      \Drupal::messenger()->addWarning(new TranslatableMarkup(
        "You can @link to remove any unwanted redirects.", ["@link" => $link])
      );
    }
  }
}
