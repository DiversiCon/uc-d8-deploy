<?php

namespace Drupal\uc_sauce\Plugin\Field\FieldFormatter;

use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'file_url_plain' formatter.
 *
 * @FieldFormatter(
 *   id = "file_url_no_encoding",
 *   label = @Translation("URL to file no encoding"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class UrlFormatterNoEncoding extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $url = file_url_transform_relative(file_create_url($file->getFileUri()));
      $url = urldecode($url);

      $elements[$delta] = [
        '#markup' => $url,
        '#cache' => [
          'tags' => $file->getCacheTags(),
        ],
      ];
    }

    return $elements;
  }

}
