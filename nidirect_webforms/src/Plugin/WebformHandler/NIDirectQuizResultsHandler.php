<?php

namespace Drupal\nidirect_webforms\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;

/**
 * NIDirect Quiz Results Webform Handler.
 *
 * @WebformHandler(
 *   id = "nidirect_quiz_results",
 *   label = @Translation("Quiz Results"),
 *   category = @Translation("NIDirect"),
 *   description = @Translation("Displays quiz answers and feedback."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class NIDirectQuizResultsHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'introduction' => '',
      'pass_text' => '',
      'fail_text' => '',
      'display_score' => TRUE,
      'display_feedback' => TRUE,
      'pass_score' => 0,
      'feedback_introduction' => '',
      'answers' => [],
      'message' => '',
      'delete_submissions' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Result display.
    $form['result'] = [
      '#type' => 'details',
      '#title' => $this->t('Result text'),
      '#weight' => 5,
    ];

    $form['result']['introduction'] = [
      '#type' => 'webform_html_editor',
      '#title' => $this->t('Introduction'),
      '#format' => 'full_html',
      '#default_value' => $this->configuration['introduction'],
    ];

    $form['result']['pass_text'] = [
      '#type' => 'webform_html_editor',
      '#title' => $this->t('Pass text'),
      '#format' => 'full_html',
      '#default_value' => $this->configuration['pass_text'],
    ];

    $form['result']['fail_text'] = [
      '#type' => 'webform_html_editor',
      '#title' => $this->t('Fail text'),
      '#format' => 'full_html',
      '#default_value' => $this->configuration['fail_text'],
    ];

    $form['result']['feedback_introduction'] = [
      '#type' => 'webform_html_editor',
      '#title' => $this->t('Feedback introduction'),
      '#format' => 'full_html',
      '#default_value' => $this->configuration['feedback_introduction'],
    ];

    // Answers display.
    $form['answers'] = [
      '#type' => 'details',
      '#title' => $this->t('Answers'),
      '#weight' => 5,
    ];

    $form['answers']['pass_score'] = [
      '#type' => 'number',
      '#title' => $this->t('Pass score'),
      '#description' => $this->t('Number of questions to get correct for a pass grade. Set to zero to disable pass/fail display.'),
      '#default_value' => $this->configuration['pass_score'],
      '#min' => 0,
    ];

    $webform_elements = $form_state->getFormObject()->getWebform()->getElementsDecodedAndFlattened();
    $webform_questions = [];
    $multiple_answers = FALSE;

    // Iterate the Webform and extract question elements.
    foreach ($webform_elements as $key => $element) {
      if (($element['#type'] == 'radios' || $element['#type'] == 'checkboxes') && substr($key, 0, 9) == 'question_') {
        $webform_questions[$key] = $element;
        $form['answers'][$key] = [
          '#type' => 'details',
          '#title' => $this->getReadableQuestionId($key),
          '#weight' => 5,
        ];

        // Display the question text.
        $form['answers'][$key]['question'] = [
          '#markup' => $element['#title'],
        ];

        // Select the correct answer.
        $form['answers'][$key]['correct_answer'] = [
          '#type' => 'select',
          '#title' => $this->t('Correct answer'),
          '#options' => $element['#options'],
          '#default_value' => $this->configuration['answers'][$key]['correct_answer'] ?? '',
        ];

        // Support multiple answers if form element is checkboxes.
        if ($element['#type'] == 'checkboxes') {
          $form['answers'][$key]['correct_answer']['#title'] = $this->t('Correct answer(s)');
          $form['answers'][$key]['correct_answer']['#multiple'] = TRUE;
          $multiple_answers = TRUE;

          $form['answers'][$key]['match_all_answers'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Match all for a correct answer'),
            '#default_value' => $this->configuration['answers'][$key]['match_all_answers'] ?? TRUE,
          ];

          $form['answers'][$key]['match_total'] = [
            '#type' => 'number',
            '#title' => $this->t('Number to match for correct answer'),
            '#default_value' => $this->configuration['answers'][$key]['match_total'] ?? 0,
            '#min' => 0,
            '#max' => count($element['#options']),
            '#states' => [
              'invisible' => [
                ':input[name="settings[answers][' . $key . '][match_all_answers]"]' => ['checked' => TRUE],
              ],
            ],
          ];
        }

        // Correct text response.
        $form['answers'][$key]['correct_feedback'] = [
          '#type' => 'webform_html_editor',
          '#title' => $this->t('Correct answer feedback'),
          '#format' => 'full_html',
          '#default_value' => $this->configuration['answers'][$key]['correct_feedback'] ?? '',
        ];

        // Incorrect text response.
        $form['answers'][$key]['incorrect_feedback'] = [
          '#type' => 'webform_html_editor',
          '#title' => $this->t('Incorrect answer feedback'),
          '#format' => 'full_html',
          '#default_value' => $this->configuration['answers'][$key]['incorrect_feedback'] ?? '',
        ];
      }
    }

    // Update maximum pass score value based on the number of questions
    // if we only have single answer options, i.e radios and no checkboxes.
    if (!$multiple_answers) {
      $total_questions = count($webform_questions);
      $form['answers']['pass_score']['#max'] = $total_questions;
      $form['answers']['pass_score']['#suffix'] = $this->t('out of %total questions.', ['%total' => $total_questions]);
    }

    // Option to delete the Webform submission when the results are displayed.
    $form['delete_submissions'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete quiz submissions when completed.'),
      '#default_value' => $this->configuration['delete_submissions'],
      '#group' => 'advanced',
    ];

    $form['display']['display_score'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display score on results page.'),
      '#default_value' => $this->configuration['display_score'],
      '#group' => 'advanced',
    ];

    $form['display']['display_feedback'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display feedback on results page.'),
      '#default_value' => $this->configuration['display_feedback'],
      '#group' => 'advanced',
    ];

    return $this->setSettingsParents($form);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['introduction'] = $values['introduction'];
    $this->configuration['display_score'] = $values['display_score'];
    $this->configuration['display_feedback'] = $values['display_feedback'];
    $this->configuration['pass_score'] = $values['pass_score'];
    $this->configuration['pass_text'] = $values['pass_text'];
    $this->configuration['fail_text'] = $values['fail_text'];
    $this->configuration['feedback_introduction'] = $values['feedback_introduction'];
    $this->configuration['delete_submissions'] = $values['delete_submissions'];

    // Remove the pass score value so we can
    // assign all the answer values.
    unset($values['answers']['pass_score']);
    $this->configuration['answers'] = $values['answers'];

    // Notify answers with blank pass or fail feedback entries.
    $incomplete_feedback = [];
    foreach ($values['answers'] as $key => $answer) {
      if (empty($answer['correct_feedback']) || empty($answer['incorrect_feedback'])) {
        $incomplete_feedback[] = $this->getReadableQuestionId($key);
      }
    }

    if (count($incomplete_feedback) > 0) {
      $this->messenger()->addMessage("Incomplete feedback for questions: " . implode(',', $incomplete_feedback));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessConfirmation(array &$variables) {
    $handlers = $variables['webform']->getHandlers();

    // Iterate each Webform handler until we match one of this type.
    foreach ($handlers as $handler) {
      if ($handler->getPluginId() == $this->getPluginId()) {
        $config = $handler->getConfiguration();
        $settings = $config['settings'];

        $user_responses = $variables['webform_submission']->getData();
        $elements = $variables['webform']->getElementsDecodedAndFlattened();

        $answers = $settings['answers'];
        $user_score = 0;
        $max_score = count($answers);
        $pass_score = $settings['pass_score'];
        $answer_feedback = [];

        // Iterate each answer and generate user feedback and scoring.
        foreach ($answers as $id => $answer) {
          // Process multiple and single answer elements.
          if (is_array($answer['correct_answer'])) {
            // User response must match all correct answers or a set number.
            if ($answer['match_all_answers']) {
              $incorrect = array_diff_assoc($user_responses[$id], array_keys($answer['correct_answer']));
              $passed = (count($incorrect) == 0) ? TRUE : FALSE;
            }
            else {
              // Preliminary check that we meet the minimum number of answers.
              if (count($user_responses[$id]) < $answer['match_total']) {
                $passed = FALSE;
              }
              else {
                $total = array_intersect($user_responses[$id], array_keys($answer['correct_answer']));
                $passed = (count($total) >= $answer['match_total']) ? TRUE : FALSE;
              }
            }
          }
          else {
            $passed = ($user_responses[$id] == $answer['correct_answer']) ? TRUE : FALSE;
          }

          if ($passed) {
            $feedback = $answer['correct_feedback'];
            $user_score++;
          }
          else {
            $feedback = $answer['incorrect_feedback'];
          }

          $answer_feedback[] = [
            '#theme' => 'nidirect_webforms_quiz_answer_feedback',
            '#title' => ucfirst(str_replace('_', ' ', $id)),
            '#question' => $elements[$id]['#title'],
            '#feedback' => [
              '#markup' => $feedback,
            ],
            '#passed' => $passed,
          ];
        }

        $variables['message'] = [
          '#theme' => 'nidirect_webforms_quiz_results',
          '#introduction' => [
            '#markup' => $settings['introduction'],
          ],
          '#score' => $user_score,
          '#max_score' => $max_score,
          '#pass_score' => $pass_score,
          '#display_score' => $settings['display_score'],
          '#display_feedback' => $settings['display_feedback'],
          '#feedback_introduction' => [
            '#markup' => $settings['feedback_introduction'],
          ],
          '#feedback' => $answer_feedback,
        ];

        // Determine if the user has passed if we have scoring enabled.
        if ($settings['pass_score'] > 0) {
          if ($user_score >= $settings['pass_score']) {
            $variables['message']['#result'] = [
              '#markup' => $settings['pass_text'],
            ];
            $variables['message']['#passed'] = TRUE;
          }
          else {
            $variables['message']['#result'] = [
              '#markup' => $settings['fail_text'],
            ];
            $variables['message']['#passed'] = FALSE;
          }
        }

        // Delete this submission from the database if option is enabled.
        if ($settings['delete_submissions']) {
          $variables['webform_submission']->delete();
        }
      }
    }
  }

  /**
   * Displays the question machine name in a readable form.
   *
   * @param string $id
   *   Question machine name to convert.
   *
   * @return string
   *   Human readable version of a machine name.
   */
  private function getReadableQuestionId($id) {
    return ucfirst(str_replace('_', ' ', $id));
  }

}
