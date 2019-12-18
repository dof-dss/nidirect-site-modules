<?php

namespace Drupal\nidirect_webforms\Plugin\WebformHandler;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\Markup;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    WebformSubmissionConditionsValidatorInterface $conditions_validator)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory, $entity_type_manager, $conditions_validator);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('webform_submission.conditions_validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'introduction' => '',
      'pass_text' => '',
      'fail_text' => '',
      'pass_mark' => 0,
      'feedback' => '',
      'answers' => [],
      'message' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
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

    $form['result']['feedback'] = [
      '#type' => 'webform_html_editor',
      '#title' => $this->t('Feedback text'),
      '#format' => 'full_html',
      '#default_value' => $this->configuration['feedback'],
    ];

    // Result display.
    $form['answers'] = [
      '#type' => 'details',
      '#title' => $this->t('Answers'),
      '#weight' => 5,
    ];

    $form['answers']['pass_mark'] = [
      '#type' => 'number',
      '#title' => $this->t('Pass mark'),
      '#description' => $this->t('Number of questions to get correct for a pass grade.'),
      '#default_value' => $this->configuration['pass_mark'],
      '#min' => 0,
    ];

    $webform_elements = $form_state->getFormObject()->getWebform()->getElementsDecodedAndFlattened();
    $webform_questions = [];

    // Iterate Webform and extract question elements.
    foreach ($webform_elements as $key => $element) {
      if ($element['#type'] == 'radios') {
        $webform_questions[$key] = $element;
        $form['answers'][$key] = [
            '#type' => 'details',
            '#title' => ucfirst(str_replace('_', ' ', $key)),
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

    // Update maximum pass mark value based on the number of questions.
    $total_questions = count($webform_questions);
    $form['answers']['pass_mark']['#max'] = $total_questions;
    $form['answers']['pass_mark']['#suffix'] = $this->t('out of %total questions.', ['%total' => $total_questions]);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['introduction'] = $values['result']['introduction'];
    $this->configuration['pass_text'] = $values['result']['pass_text'];
    $this->configuration['fail_text'] = $values['result']['fail_text'];
    $this->configuration['feedback'] = $values['result']['feedback'];
    $this->configuration['pass_mark'] = $values['answers']['pass_mark'];

    // Remove the passmark and set the question answers.
    unset($values['answers']['pass_mark']);
    $this->configuration['answers'] = $values['answers'];
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessConfirmation(array &$variables) {
    $handlers = $variables['webform']->getHandlers();

    foreach ($handlers as $handler) {
      if ($handler->getPluginId() == $this->getPluginId()) {
        $user_response = $variables['webform_submission']->getData();;
        $config = $handler->getConfiguration();
        $config = $config['settings'];
        $elements = $variables['webform']->getElementsDecodedAndFlattened();

        $answers = $config['answers'];
        $message = '';

        foreach ($answers as $id => $answer) {
            $question_number = '<h2>' . ucfirst(str_replace('_', ' ', $id)) . '</h2>';
            $question_title = '<p>' . $elements[$id]['#title'] . '</p>';

            if ($user_response[$id] == $answer['correct_answer']) {
              $answer_feedback = '<h3>Correct</h3>' . $answer['correct_feedback'];
            } else {
              $answer_feedback = '<h3>Incorrect</h3>' . $answer['incorrect_feedback'];
            }

          $message .= $question_number . $question_title . $answer_feedback;
        }

      $variables['message'] = [
        '#markup' => $message
      ];
      }
    }
  }

}
