<?php

namespace Drupal\whatlinkshere\EventSubscriber;

use Drupal\Core\Field\FieldStorageDefinitionEvent;
use Drupal\Core\Field\FieldStorageDefinitionEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FieldListener
 * @package Drupal\whatlinkshere\EventSubscriber
 */
class FieldListener implements EventSubscriberInterface {

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * For instance:
   *
   *  * ['eventName' => 'methodName']
   *  * ['eventName' => ['methodName', $priority]]
   *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
   *
   * @return array
   *   The event names to listen to
   */
  public static function getSubscribedEvents() {
    $events[FieldStorageDefinitionEvents::DELETE][] = 'onFieldStorageDefinitionDelete';
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldStorageDefinitionDelete(FieldStorageDefinitionEvent $storage_definition) {
    \Drupal::database()->delete('whatlinkshere')
      ->condition('reference_field', $storage_definition->getFieldStorageDefinition()->getName())
      ->execute();
  }

}
