<?php

namespace Drupal\nidirect_services\Plugin\views\style;

use Drupal\rest\Plugin\views\style\Serializer;

/**
 * Custom serializer for view generating services-catalogue.json.
 * Ensure relative URLs in content are convert to absolute URLs.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "custom_serializer",
 *   title = @Translation("Custom Serializer"),
 *   help = @Translation("Serializes views row data using the Serializer component."),
 *   display_types = {"data"}
 * )
 */
class CustomSerializer extends Serializer {

  /**
   * {@inheritdoc}
   */
  public function render() {
    // Replace relative URLs in certain fields with absolute URLs.
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $pattern = '/(?:src|href)=[\'"]\K\/(?!\/)[^\'"]*/';
    $rows = [];
    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;
      $row_render = $this->view->rowPlugin->render($row);
      if (isset($row_render['before_you_start'])) {
        $content = preg_replace($pattern,"$host$0", $row_render['before_you_start']);
        $row_render['before_you_start'] = $content;
      }
      $rows[] = $row_render;
    }
    unset($this->view->row_index);

    // Get the content type configured in the display or fallback to the
    // default.
    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }
    return $this->serializer->serialize($rows, $content_type, ['views_style_plugin' => $this]);
  }
}
