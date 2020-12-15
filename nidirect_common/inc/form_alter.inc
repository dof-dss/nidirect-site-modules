<?php

/**
 * @file
 * Contains hook_form_alter functions for nidirect_common.module.
 */

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Nette\Utils\Arrays;

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Form alter hook for the LinkIt dialogue box
 * presented in the WYSIWYG editor.
 */
function nidirect_common_form_linkit_editor_dialog_form_alter(array &$form, FormStateInterface $form_state) {

  // Hide option to open link in new window.
  $form['linkit_attributes']['target']['#access'] = FALSE;

  // Provide additional guidance information.
  $form['attributes']['href']['#description'] .= '<p>'
    . t('To reference an external URL type the full address, eg: https://www.nidirect.gov.uk') . '</p>'
    . '<p>' . t('To reference an e-mail address type the address and select the "E-mail" option from the dropdown list.')
    . '</p>';
}

/**
 * Implements hook_inline_entity_form_entity_form_alter().
 */
function nidirect_common_inline_entity_form_entity_form_alter(&$entity_form, &$form_state) {
  if ($entity_form['#entity_type'] == 'media') {

    // To reduce form clutter, move authoring fields into a
    // collapsed field group.
    $entity_form['authoring_information'] = [
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#weight' => 5,
    ];

    $entity_form['authoring_information']['created'] = $entity_form['created'];
    unset($entity_form['created']);

    $entity_form['authoring_information']['uid'] = $entity_form['uid'];
    unset($entity_form['uid']);

    $entity_form['authoring_information']['path'] = $entity_form['path'];
    unset($entity_form['path']);

    $entity_form['authoring_information']['revision_log_message'] = $entity_form['revision_log_message'];
    unset($entity_form['revision_log_message']);
  }
}

/**
 * Implements hook_form_BASE_FORM_ID_alter().
 */
function nidirect_common_form_taxonomy_term_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if ($form['vid']['#value'] != 'site_themes') {
    return;
  }

  // Only affect terms from the Site themes vocab.
  if (NestedArray::keyExists($form, ['name', 'widget', 0, 'value'])) {
    $title_description =& $form['name']['widget'][0]['value']['#description'];
    if (empty($title_description)) {
      $title_description = t('
          Used as the theme title on landing pages, and in search results and breadcrumb trails.
          Must be descriptive and concise (60 characters or less).
        ');
      $form['name']['widget'][0]['value']['#description'] = $title_description;
    }
  }

  // Relation is fixed with a weight of 10
  // see https://www.drupal.org/project/drupal/issues/2655328.
  // So we want to change this to something more in line with the broader
  // weight on other form elements.
  $form['relations']['#weight'] = 1;
  $form['relations']['#description'] = t("The primary parent theme where a link to this theme will appear. Sets the breadcrumb trail shown on the theme landing page.");

  $form['description']['widget'][0]['#description'] = t("Optional main body content. Generally not used as it is preferred to show an automatically generated list of the theme's content.");

  if (!empty($form['flag']['hide_theme'])) {
    $form['flag']['hide_theme']['#description'] = t('Prevent links to this theme appearing in navigation menus and on landing pages.');
  }
}

/**
 * Implements hook_form_BASE_FORM_ID_alter().
 */
function nidirect_common_form_node_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  // Add a description to the title field.  This cannot be done in the UI.
  if (NestedArray::keyExists($form, ['title', 'widget', 0, 'value'])) {
    $title_description =& $form['title']['widget'][0]['value']['#description'];
    if (empty($title_description)) {
      if ($form_id === 'node_featured_content_list_form' || $form_id === 'node_featured_content_list_edit_form') {
        $title_description = t('Title is for administrative purposes only and is not displayed.');
      }
      else {
        $title_description = t('
          Title should be descriptive and concise (60 characters or less). A good title is essential to helping users decide if the
          content will be relevant to them. Google truncates titles longer than 60 to 70 characters.
        ');
      }
    }
  }

  // Update description for Hide Content flag.
  if (NestedArray::keyExists($form, ['flag', 'hide_content'])) {
    $hide_content_description =& $form['flag']['hide_content']['#description'];
    if (empty($hide_content_description)) {
      $hide_content_description = t('
          Check this box to prevent links to this page appearing in navigation menus and landing pages.
        ');
    }
  }

  // Hide Featured Content flag.
  if (NestedArray::keyExists($form, ['flag', 'featured_content'])) {
    $form['flag']['featured_content']['#access'] = FALSE;
  }

  // Hide 'promote to front-page' option.
  $form['promote']['#access'] = FALSE;

  // Hide 'Sticky at top of lists' option, except for news and contact nodes.
  if (!preg_match('/^node_(news|contact)/', $form['#form_id'])) {
    $form['sticky']['#access'] = FALSE;
  }

  // Update description for Table of Contents option.
  if (array_key_exists('field_toc_enable', $form)) {
    $field_toc_enable_description =& $form['field_toc_enable']['widget']['value']['#description'];
    if (empty($field_toc_enable_description)) {
      $field_toc_enable_description = t(
        'Enable to display a list of quick links to Heading 2s on the page.'
      );
    }
  }

  if (array_key_exists('field_top_level_theme', $form)) {
    // Can't use a hidden input type on a container element or
    // set #access to FALSE as we need the form_state values,
    // so using the .hidden class to hide from the user.
    $form['field_top_level_theme']['#attributes']['class'][] = 'hidden';
  }

  // Add extra date validation code to override core bug with
  // date formats as described here
  // https://www.drupal.org/project/drupal/issues/3093267
  $add_date_validation = FALSE;
  foreach ($form as $key => $field) {
    if (preg_match('/_date$/', $key) || preg_match('/_period$/', $key) || preg_match('/publish_on$/', $key)) {
      $add_date_validation = TRUE;
    }
  }
  if ($add_date_validation) {
    array_unshift($form['#validate'], 'nidirect_common_validate_publication_date');
  }

  // Custom validation for Telephone_Plus field.
  if (array_key_exists('field_telephone', $form)) {
    array_unshift($form['#validate'], 'nidirect_common_validate_telephone');
  }

  // Increase the size of the teaser textfield.
  if (NestedArray::keyExists($form, ['field_teaser', 'widget', 0, 'value'])) {
    $form['field_teaser']['widget'][0]['value']['#size'] = 140;
  }

  // Change button text for 'Alternative Condition Titles' inline entity form.
  if ($form_id === 'node_health_condition_form' || $form_id === 'node_health_condition_edit_form') {
    // Inline Entity Form table.
    $form['field_alternative_condition']['widget']['actions']['ief_add']['#value'] = 'Add new';
    $form['field_alternative_condition']['widget']['actions']['ief_add_existing']['#value'] = 'Add existing';

    // Add existing form.
    $form['field_alternative_condition']['widget']['form']['#title'] = 'Add alternative health condition title';
    $form['field_alternative_condition']['widget']['form']['entity_id']['#title'] = 'Title';
    $form['field_alternative_condition']['widget']['form']['actions']['ief_reference_save']['#value'] = 'Add';
  }

  // Apply Related Info library to forms with field_related_info.
  if (array_key_exists('field_related_info', $form)) {
    $form['#attached']['library'][] = 'nidirect_common/related_info';

    $autocomplete_entity =  $form_state->getUserInput()['add_more_entity'];

    if (empty($autocomplete_entity)) {
      $form['field_related_info']['add_more_entity'] = [
        '#type' => 'hidden',
        '#value' => 'node',
      ];
    }

    $form['field_related_info']['widget']['add_more']['#value'] = t('Add link');

    if ($autocomplete_entity === 'taxonomy_term') {
      $delta = $form['field_related_info']['widget']['#max_delta'];
      $form['field_related_info']['widget'][$delta]['uri']['#target_type'] = 'taxonomy_term';
      $form['field_related_info']['widget'][$delta]['#entity_type'] = 'taxonomy_term';
      $form['field_related_info']['widget'][$delta]['#bundle'] = 'site_themes';
    }

  }
}

/**
 * Implements hook_field_widget_WIDGET_TYPE_form_alter().
 *
 * Overrides the default Telephone Plus widget to provide a
 * default list of title options.
 */
function nidirect_common_field_widget_telephone_plus_widget_form_alter(&$element, FormStateInterface $form_state, $context) {

  $telephone_predefined = [
    'Phone' => 'Phone',
    'Fax' => 'Fax',
    'Textphone' => 'Textphone',
    'Freephone' => 'Freephone',
    'Helpline' => 'Helpline',
    'General enquiries' => 'General enquiries',
    'If calling from outside the UK' => 'If calling from outside the UK',
    'Headquarters' => 'Headquarters',
    'Main office' => 'Main office',
    'Main switchboard' => 'Main switchboard',
    'Billing and payments' => [
      'Billing and payments' => 'Billing and payments',
      'Billing enquiries' => 'Billing enquiries',
      'Payments' => 'Payments',
      'Payment enquiries' => 'Payment enquiries',
      'Payments using a credit or debit card' => 'Payments using a credit or debit card',
    ],
    'Complaints' => [
      'Complaints' => 'Complaints',
      'Complaints helpline' => 'Complaints helpline',
      'Customer complaints' => 'Customer complaints',
    ],
    'Enquiries' => [
      'General enquiries' => 'General enquiries',
      'Billing enquiries' => 'Billing enquiries',
      'Payment enquiries' => 'Payment enquiries',
      'Course enquiries' => 'Course enquiries',
    ],
    'Emergency' => [
      'Emergency out-of-hours' => 'Emergency out-of-hours',
      'Non-emergency' => 'Non-emergency',
      'Non-emergency and general enquiries' => 'Non-emergency and general enquiries',
      '24 hour emergency helpline' => '24 hour emergency helpline',
    ],
    'Freephone numbers' => [
      'Freephone advice line' => 'Freephone advice line',
      'Freephone helpline' => 'Freephone helpline',
    ],
    'Helpline numbers' => [
      'Helpline' => 'Helpline',
      'Freephone helpline' => 'Freephone helpline',
      'Helpline for adults (18+)' => 'Helpline for adults (18+)',
      'Helpline for young people (under 18)' => 'Helpline for young people (under 18)',
      'National helpline' => 'National helpline',
      'Northern Ireland helpline' => 'Northern Ireland helpline',
      '24 hour helpline' => '24 hour helpline',
    ],
    'Out-of-hours' => [
      'Out-of-hours phone' => 'Out-of-hours phone',
      'Out of office hours phone' => 'Out of office hours phone',
    ],
    'Other' => 'Other (please specify)',
  ];

  // Fetch the stored Title field value.
  $telephone_title = $context['items']->get($context['delta'])->get('telephone_title')->getValue();

  // Determine if we have a title matching the predefined options
  // and assign a default value for the select list.
  if (empty($telephone_title)) {
    $telephone_predefined_value = '';
  }
  elseif (in_array($telephone_title, Arrays::flatten($telephone_predefined))) {
    $telephone_predefined_value = $telephone_title;
  }
  else {
    $telephone_predefined_value = 'Other';
  }

  // Select element containing predefined title options.
  $element['telephone_predefined'] = [
    '#type' => 'select',
    '#weight' => '-50',
    '#title' => t('Title'),
    '#options' => $telephone_predefined,
    '#default_value' => $telephone_predefined_value,
    '#attached' => [
      'library' => [
        'nidirect_common/telephone_plus_predefined_titles',
      ],
    ],
    '#attributes' => [
      'class' => ['telephone-predefined'],
    ],
    '#chosen' => FALSE,
    '#empty_value' => '',
  ];

  // Update the default Telephone Plus title field.
  $element['telephone_title']['#title'] = t('Other title');
  $element['telephone_title']['#attributes']['class'][] = 'telephone-title';

  // Display the title textfield if the user selects 'Other'.
  $element['telephone_title']['#states'] = [
    'visible' => [
      ':input[name="field_telephone[' . $context['delta'] . '][telephone_predefined]"]' => ['value' => 'Other'],
    ],
  ];
}
