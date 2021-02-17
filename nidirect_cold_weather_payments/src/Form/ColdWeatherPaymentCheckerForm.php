<?php

namespace Drupal\nidirect_cold_weather_payments\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form for checking cold weather payments.
 */
class ColdWeatherPaymentCheckerForm extends FormBase {

  /**
   * Drupal\Core\Http\ClientFactory definition.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * Drupal\Core\Render\Renderer definition.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Constructs a new ColdWeatherPaymentCheckerForm object.
   */
  public function __construct(
    ClientFactory $http_client_factory,
    Renderer $renderer,
    RequestStack $request
  ) {
    $this->httpClientFactory = $http_client_factory;
    $this->renderer = $renderer;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client_factory'),
      $container->get('renderer'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cold_weather_payment_checker_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes'] = [
      'role' => 'search',
      'class' => ['search-form', 'search-form--cwp'],
    ];

    // Create our own label for the postcode input.
    $form['postcode_label'] = [
      '#markup' => '<label for="edit-postcode">' . t('Enter your Northern Ireland postcode') . '</label>',
      '#allowed_tags' => ['label'],
    ];

    // Contain a search input and submit together.
    $form['search-and-submit'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['search-and-submit'],
      ],
      'postcode' => [
        '#type' => 'textfield',
        '#maxlength' => 8,
        '#size' => 8,
        '#weight' => '0',
      ],
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Check postcode'),
        '#attributes' => [
          'class' => ['form-submit'],
        ],
        '#ajax' => [
          'callback' => '::cwpCheck',
        ],
      ],
    ];

    // Placeholder to put the results/ajax error messages.
    $form['message'] = [
      '#type' => 'container',
      '#attributes' => [
        'role' => 'alert',
        'class' => ['cwp-message-container'],
      ],
      'content' => [
        '#markup' => $form_state->get('message', ''),
        '#prefix' => '<div id="cwp-message">',
        '#suffix' => '</div>',
      ],

    ];

    return $form;
  }

  /**
   * AJAX callback function.
   */
  public function cwpCheck(array $form, FormStateInterface $form_state) {
    $postcode = $form_state->getValue('postcode');
    $postcode_district = $this->cwpGetDistrictFromNIPostcode($postcode);
    $response = new AjaxResponse();

    // Postcode district validation - must be 1-99.
    if (!$postcode_district || $postcode_district < 0 || $postcode_district > 99) {
      $response->addCommand(
        new HtmlCommand('#cwp-message', $this->t('Postcode must be a valid Northern Ireland postcode.'))
      );

      return $response;
    }

    $data = $this->cwpLookup($postcode_district);

    // Check we have data back from the API.
    if (is_null($data)) {
      $output = $this->t('Sorry, we were unable to process this request.');
    }
    else {
      $renderable = [
        '#theme' => 'cwp_search_result',
        '#postcode' => $data['postcode'],
        '#period_start' => $data['payments_period']['date_start'],
        '#period_end' => $data['payments_period']['date_end'],
        '#payments' => $data['payments'],
      ];
      $output = $this->renderer->render($renderable);
    }

    $response->addCommand(
      new HtmlCommand('#cwp-message', $output)
    );

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Adding this validation to take care of older browsers.
    $postcode = $form_state->getValue('postcode');
    $postcode_district = $this->cwpGetDistrictFromNIPostcode($postcode);

    if (!is_numeric($postcode_district) || $postcode_district < 1 || $postcode_district > 99) {
      $form_state->setErrorByName('postcode', $this->t('Postcode must be a valid Northern Ireland postcode.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $postcode = $form_state->getValue('postcode');
    $postcode_district = $this->cwpGetDistrictFromNIPostcode($postcode);
    $data = $this->cwpLookup($postcode_district);

    // Check we have data back from the API.
    if (is_null($data)) {
      $output = $this->t('Sorry, we were unable to process this request.');
    }
    else {
      $renderable = [
        '#theme' => 'cwp_search_result',
        '#postcode' => $data['postcode'],
        '#period_start' => $data['payments_period']['date_start'],
        '#period_end' => $data['payments_period']['date_end'],
        '#payments' => $data['payments'],
      ];
      $output = $this->renderer->render($renderable);
    }

    $form_state->set('message', $output);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Call CWP API and process data.
   */
  private function cwpLookup($postcode_district) {
    $data = NULL;

    try {
      $client = $this->httpClientFactory->fromOptions([
        'base_uri' => $this->request->getCurrentRequest()->getSchemeAndHttpHost(),
      ]);

      $api_response = $client->get('api/cwp/BT' . $postcode_district, []);
      $json = $api_response->getBody()->getContents();
      $data = Json::decode($json);

      $payments = [];
      foreach ($data['payments_triggered'] as $trigger) {
        if ($trigger['payment_granted']) {
          $payments[] = [
            'date_start' => $trigger['date_start'],
            'date_end' => $trigger['date_end'],
          ];
        }
      }

      $data['payments'] = $payments;
    }
    catch (\Exception $e) {
      \Drupal::logger('type')->error($e->getMessage());
    }
    finally {
      return $data;
    }
  }

  /**
   * Get postcode district from a Northern Ireland postcode.
   *
   * For Northern Ireland postcodes, the postcode district is always 1 or 2 digits following the postcode area 'BT'.
   */

  private function cwpGetDistrictFromNIPostcode(string $postcode){
    $postcode_district = NULL;

    // If postcode is a full NI postcode, or just the first part (outward code - e.g. BT1) ...
    if (preg_match('/^[bB][tT][0-9]{1,2}( ?[0-9][a-zA-Z]{2})?$/', $postcode)) {
      if (strlen($postcode) > 4) {
        // Full postcode - remove first 2 and last 3 characters to get the district number.
        $postcode_district = substr($postcode, 2, -3);
      } else {
        // Postcode outward code - just strip of first two 'BT' characters.
        $postcode_district = substr($postcode, 2);
      }
    }

    return $postcode_district;
  }
}
