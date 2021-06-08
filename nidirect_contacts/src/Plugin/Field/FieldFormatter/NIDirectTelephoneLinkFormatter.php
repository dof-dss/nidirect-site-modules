<?php

namespace Drupal\nidirect_contacts\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\telephone_plus\Plugin\Field\FieldFormatter\TelephonePlusLinkFormatter;
use Drupal\telephone_plus\TelephonePlusFormatter;
use Drupal\telephone_plus\TelephonePlusValidator;

/**
 * Plugin extending the 'telephone_plus_link' formatter.
 *
 * @FieldFormatter(
 *   id = "nidirect_telephone_link",
 *   label = @Translation("NIDirect telephone link"),
 *   description = @Translation("Extends the TelephonePlus Link formatter with some custom output formatting."),
 *   field_types = {
 *     "telephone_plus_field"
 *   }
 * )
 */
class NIDirectTelephoneLinkFormatter extends TelephonePlusLinkFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $title = NULL;
      $supplementary = NULL;

      $telephone = new TelephonePlusValidator($item->telephone_number, $item->telephone_extension, $item->country_code);

      // If we don't have a valid number, set variables to allow fallback to
      // plain text.
      if (!$telephone->isValid()) {
        $telephone_text = $item->telephone_number;
        $telephone_link = '';
      }
      else {
        $telephone = new TelephonePlusFormatter($item->telephone_number, $item->telephone_extension, $item->country_code);
        // TelephonePlus link text.
        $telephone_link = $telephone->url();
        // TelephonePlus display text.
        $telephone_text = $telephone->text($item->display_international_number);
      }

      // Add extension to TelephonePlus display text if there is one.
      if ($item->telephone_extension) {
        $telephone_text .= ' ' . t('ext. :extension', [':extension' => $item->telephone_extension]);
      }

      // Use the raw textfield input for textphone formatting.
      if (substr($telephone_text, 0, 5 ) === "18001") {
        $telephone_text = $item->getValue('telephone_number')['telephone_number'];
      }

      // Check for international freephone numbers and reformat the output
      if (substr($item->getValue('telephone_number')['telephone_number'], 0, 6 ) === "00 800") {
        $telephone_text = $item->getValue('telephone_number')['telephone_number'];
      }

      if (!empty($item->telephone_title)) {
        $title = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => ['class' => ['title']],
          '#value' => $item->telephone_title,
        ];

        // Add vCard title if enabled.
        if ($this->getSetting('vcard')) {
          $title['#attributes']['class'][] = 'type';
        }
      }

      if (!empty($item->telephone_supplementary)) {
        $supplementary = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => ['class' => ['supplementary']],
          '#value' => $item->telephone_supplementary,
        ];
      }

      if (!empty($telephone_link)) {
        // Url::fromUri() doesn't place nice with the generated tel: URI
        // so reverting to using an html_tag instead of a link element.
        $phone = [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#attributes' => ['href' => $telephone_link],
          '#value' => $telephone_text,
        ];
      }
      else {
        $phone = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $telephone_text,
        ];
      }

      if ($item->telephone_extension) {
        $phone['#suffix'] = ' ' . t('extension  :extension', [':extension' => $item->telephone_extension]);
      }

      // If vCard option is enabled and add required classes and
      // enclose in div element with 'tel' class.
      if ($this->getSetting('vcard')) {
        $phone['#attributes']['class'][] = 'value';
        if (!isset($item->_attributes)) {
          $item->_attributes = [];
        }
        $item->_attributes += ['class' => ['tel']];
      }

      if ($title) {
        $elements[$delta]['title'] = $title;
      }

      if ($supplementary) {
        $elements[$delta]['supplementary'] = $supplementary;
      }

      $elements[$delta]['number'] = $phone;
    }

    return $elements;
  }

}
