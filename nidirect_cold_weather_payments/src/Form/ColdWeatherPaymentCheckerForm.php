<?php

namespace Drupal\nidirect_cold_weather_payments\Form;

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
 * Class ColdWeatherPaymentCheckerForm.
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

    $form['container'] = ['#type' => 'container', '#attributes' => ['class' => ['container-inline']]];
    $form['container']['postcode'] = [
      '#type' => 'number',
      '#maxlength' => 2,
      '#size' => 2,
      '#min' => 1,
      '#max' => 99,
      '#weight' => '0',
      '#prefix' => 'BT',
    ];
    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#ajax' => [
        'callback' => '::cwpCheck',
      ],
    ];

    // Placeholder to put the results/ajax error messages.
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => $form_state->get('message', ''),
      '#prefix' => '<div id="cwp-message">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * AJAX callback function.
   */
  public function cwpCheck(array $form, FormStateInterface $form_state) {
    $postcode = $form_state->getValue('postcode');
    $response = new AjaxResponse();

    // Postcode validation.
    if (!is_numeric($postcode)) {
      $response->addCommand(
        new HtmlCommand('#cwp-message', $this->t('Postcode must consist of 1 or 2 digits.'))
      );

      return $response;
    }

    $data = $this->cwpLookup($postcode);

    $renderable = [
      '#theme' => 'cwp_search_result',
      '#postcode' => $data['postcode'],
      '#period_start' => $data['payments_period']['date_start'],
      '#period_end' => $data['payments_period']['date_end'],
      '#payments' => $data['payments'],
    ];
    $rendered = $this->renderer->render($renderable);

    $response->addCommand(
      new HtmlCommand('#cwp-message', $rendered)
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
    if (!is_numeric($postcode)) {
      $form_state->setErrorByName('postcode', $this->t('Postcode must consist of 1 or 2 digits.'));
    }
    elseif ($postcode < 1 || $postcode > 99) {
      $form_state->setErrorByName('postcode', $this->t('Postcode must be between 1 and 99.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $postcode = $form_state->getValue('postcode');

    $data = $this->cwpLookup($postcode);

    $renderable = [
      '#theme' => 'cwp_search_result',
      '#postcode' => $data['postcode'],
      '#period_start' => $data['payments_period']['date_start'],
      '#period_end' => $data['payments_period']['date_end'],
      '#payments' => $data['payments'],
    ];
    $rendered = $this->renderer->render($renderable);

    $form_state->set('message', $rendered);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Call CWP API and process data.
   */
  private function cwpLookup($postcode) {

    try {
      $client = $this->httpClientFactory->fromOptions([
        'base_uri' => $this->request->getCurrentRequest()->getSchemeAndHttpHost(),
      ]);

      $api_response = $client->get('api/cwp/BT' . $postcode, []);
      $json = $api_response->getBody()->getContents();
      $data = Json::decode($json);

      $payments = [];
      foreach ($data['payments_triggered'] as $trigger) {
        if ($trigger['payment_granted']) {
          $payments[] = ['date_start' => $trigger['date_start'], 'date_end' => $trigger['date_end']];
        }
      }

      $data['payments'] = $payments;
      return $data;
    }
    catch (Exception $e) {
      // TODO: handle Guzzle exception.
    }
  }

}
