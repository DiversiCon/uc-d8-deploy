<?php

namespace Drupal\uc_sauce\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Table Wrap Filter class. Implements process() method only.
 *
 * @Filter(
 *   id = "filter_uc_table_wrap",
 *   title = @Translation("Wrap HTML tables in a responsive div."),
 *   description = @Translation("Adds a specific div around all inline HTML tables."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class FilterTableWrap extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    if ($filtered = $this->responsiveTablesFilter($text)) {
      $result = new FilterProcessResult($filtered);
    }
    else {
      $result = new FilterProcessResult($text);
    }

    return $result;
  }

  /**
   * Business logic for adding classes & attributes to <table> tags.
   */
  public function responsiveTablesFilter($text) {
    if ($text != '') {
      $tables = [];
      libxml_use_internal_errors(TRUE);
      /* @var \DOMDocument $dom */
      $dom = new \DOMDocument();
      $dom->loadHTML(mb_convert_encoding('<html>' . $text . '</html>', 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      $tables = $dom->getElementsByTagName('table');
      // Find all tables in text.
      if ($tables->length !== 0) {
        /* @var \DOMNode $table */
        foreach ($tables as $table) {
          $parent = $dom->createElement('div');
          $parent->setAttribute('class', 'c-table table-scroll');
          $parent->appendChild($table->cloneNode(TRUE));
          $table->parentNode->replaceChild($parent, $table);
        }

        $html = $dom->saveHTML();
        return $html;
      }
    }
    return FALSE;
  }

}
