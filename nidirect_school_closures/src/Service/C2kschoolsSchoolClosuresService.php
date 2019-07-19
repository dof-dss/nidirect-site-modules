<?php

namespace Drupal\nidirect_school_closures\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Drupal\nidirect_school_closures\SchoolClosuresServiceInterface;
use Drupal\nidirect_school_closures\SchoolClosure;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class C2kschoolsSchoolClosuresService.
 */
class C2kschoolsSchoolClosuresService implements SchoolClosuresServiceInterface {

  /**
   * Http request attempt counter.
   *
   * @var int
   */
  protected $attempt = 1;

  /**
   * Maximum number of http request attempts to be made.
   *
   * @var int
   */
  protected $maxAttempts = 3;

  /**
   * URL for the http get request.
   *
   * @var string
   */
  protected $url = NULL;

  /**
   * Parsed XML response from the service call.
   *
   * @var SimpleXMLElement
   */
  protected $xml = NULL;

  /**
   * Array of school closures.
   *
   * @var array
   */
  protected $data = NULL;

  /**
   * Cached data object.
   *
   * @var object
   */
  protected $cached = NULL;

  /**
   * Duration in minutes to keep closured cached.
   *
   * @var int
   */
  protected $cacheDuration = 10;

  /**
   * When the data was retrived.
   *
   * @var DateTime
   */
  protected $updated;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Drupal\Core\Cache\CacheBackendInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheService;

  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Constructs a new C2kschoolsSchoolClosuresService object.
   */
  public function __construct(ClientInterface $http_client, CacheBackendInterface $cache, ConfigFactory $config_service, LoggerChannelFactory $logger) {
    $this->httpClient = $http_client;
    $this->cacheService = $cache;
    $this->logger = $logger->get('nidirect_school_closures');

    // Fetch the config settings.
    $config = $config_service->get('nidirect_school_closures.settings');
    $this->url = $config->get('data_source_url') ?? $this->url;
    $this->cacheDuration = $config->get('cache_duration') ?? $this->cacheDurarion;
    $this->maxAttempts = $config->get('max_attempts') ?? $this->maxAttempts;
  }

  /**
   * Return closures data.
   *
   * @return array
   *   closures array.
   */
  public function getClosures() {
    $this->cached = $this->cacheService->get('school_closures');

    // If we have cached data, check the expiry.
    if (!empty($this->cached)) {
      $this->data = $this->cached->data;
      $this->updated = date_timestamp_set(new \DateTime(), $this->cached->created);

      $now = new \DateTime('now');
      $interval = $now->diff($this->updated);

      // If the cached data is stale, delete cache and call again.
      if ($interval->i >= $this->cacheDuration) {
        $this->cacheService->delete('school_closures');
        $this->getClosures();
      }
    }
    else {
      // Fetch data from the web endpoint.
      $this->fetchData();

      // Process recieved data or attempt again.
      if (!empty($this->xml)) {
        $this->processData();
        $this->updated = date('Y-m-d H:i:s');
        // Cache the data indefinitely. The cache will be deleted based
        // on the cache duration setting in the config.
        $this->cacheService->set('school_closures', $this->data, CacheBackendInterface::CACHE_PERMANENT);
        $this->attempt = 1;
      }
      else {
        // Attempt to fetch data until configured maximum attempts to prevent
        // looping and PHP throwing an error when the source feed is down.
        if ($this->attempt < $this->maxAttempts) {
          $this->attempt++;
          $this->getClosures();
        }
        else {
          // If fetching data failed, fall back to the cached data.
          if (!empty($this->cached)) {
            // Cache the data indefinitely. The cache will be deleted based
            // on the cache duration setting in the config.
            $this->cacheService->set('school_closures', $this->cached, CacheBackendInterface::CACHE_PERMANENT);
            $this->attempt = 1;
            $this->logger->notice('Unable to update school closure data, reverting to cached data.');
          }
          else {
            // Warn if we can't retrive data from the service or the cache.
            $this->logger->warning('Unable to update school closure data or revert to cached data.');
          }
        }
      }
    }

    return $this->data;
  }

  /**
   * Last updated date.
   *
   * @return date
   *   returns last updated date.
   */
  public function getUpdated() {
    return $this->updated;
  }

  /**
   * Fetch and convert the source data to XML.
   */
  protected function fetchData() {
    // If we have a URL call it and parse the results.
    if (!empty($this->url)) {
      try {
        $response = $this->httpClient->get($this->url);

        if ($response->getStatusCode() == 200) {
          $xml_string = $response->getBody()->getContents();
          $this->xml = simplexml_load_string($xml_string);
        }
      }
      catch (ClientException $e) {
        $this->logger->warning('Failed to fetch school closure data. ' . $e->getMessage());
      }
      catch (Exception $e) {
        $this->logger->warning('Failed to fetch school closure data. ' . $e->getMessage());
      }
    }
  }

  /**
   * Process the XML data into array.
   */
  protected function processData() {
    if (!empty($this->xml->channel->item)) {
      $this->data = NULL;
      foreach ($this->xml->channel->item as $item) {
        $title = utf8_decode($item->title);
        $description = utf8_decode($item->description);

        // Extract reason and date, skip if not matched.
        if (preg_match('/^(.*)<br\/><br\/>Closure takes place on (\d{2}\/\d{2}\/\d{4})$/', $description, $matches)) {
          $date = trim($matches[2]);

          $closure_date = explode('/', $date);
          $date_formatted = $closure_date[2] . '-' . $closure_date[1] . '-' . $closure_date[0];
          $date = new \DateTime($date_formatted, new \DateTimeZone('Europe/London'));

          $reason = trim($matches[1]);
        }
        else {
          continue;
        }

        // Extract name and location, skip if not matched.
        if (preg_match('/^(.*?),\s(.*)\(\d+\)$/', $title, $matches)) {
          $name = trim($matches[1]);
          $location = trim($matches[2]);
        }
        else {
          continue;
        }

        $closure = new SchoolClosure($name, $location, $date, $reason);

        // If the closure is before today, skip.
        if ($closure->expiredClosure()) {
          continue;
        }

        $this->data[] = $closure->getData();
      }

      // Sort the results by closure date.
      usort($this->data, function ($a, $b) {
        return $a['date']->getTimestamp() - $b['date']->getTimestamp();
      });
    }
  }

}
