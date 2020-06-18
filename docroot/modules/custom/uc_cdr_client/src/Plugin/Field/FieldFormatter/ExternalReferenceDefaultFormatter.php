<?php

namespace Drupal\uc_cdr_client\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'uc_cdr_client_external_reference_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "uc_cdr_client_external_reference_formatter",
 *   module = "uc_cdr_client",
 *   label = @Translation("External reference text"),
 *   field_types = {
 *     "uc_cdr_client_external_reference"
 *   }
 * )
 */
class ExternalReferenceDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $item->value];
    }
    return $elements;
  }

}
