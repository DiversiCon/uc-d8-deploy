<?php

namespace Drupal\it_smartbody\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

const IT_SMARTBODY_FULL_WIDTH_PLACEHOLDER = '<hr />';

/**
 * Plugin implementation of the 'text_smartbody' formatter.
 *
 * @FieldFormatter(
 *   id = "text_smartbody",
 *   label = @Translation("Smartbody"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class TextSmartbodyFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $index = 0;

    // All we're doing is searching through the text value and break it into
    // chunks based on full-width placeholder, render each separately.
    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $chunks = explode(IT_SMARTBODY_FULL_WIDTH_PLACEHOLDER, $item->value);
      foreach ($chunks as $cid => $chunk) {
        // We skip anything that doesn't have real content, except first chunk.
        // if ($cid == 0 || trim(strip_tags(str_replace('&nbsp;', NULL, $chunk)))) {.
        $elements['chunks'][$index] = [
          '#type' => 'processed_text',
          '#text' => $chunk,
          '#format' => $item->format,
          '#langcode' => $item->getLangcode(),
        ];
        $index++;
        // }
      }
    }

    return $elements;
  }

}
