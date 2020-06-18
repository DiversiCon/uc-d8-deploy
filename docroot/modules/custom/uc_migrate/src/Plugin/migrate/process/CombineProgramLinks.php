<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'CombineProgramLinks' migrate process plugin.
 *
 * This plugin can be used concatenate multiple links fields into a single array.
 *
 * @MigrateProcessPlugin(
 *  id = "combine_program_links"
 * )
 */
class CombineProgramLinks extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $result_array = [];

    // Must be valid arguments.
    if (empty($value) || !is_array($value)) {
      return $result_array;
    }

    // First link is catalog link.
    if (isset($value[0])) {
      $result_array = $this->doCheck($value[0], $result_array, 'College Catalog');
    }
    // Second link is program page.
    if (isset($value[1])) {
      $result_array = $this->doCheck($value[1], $result_array, 'Departmental Site');
    }
    // Third link is mailing list.
    if (isset($value[2])) {
      $result_array = $this->doCheck($value[2], $result_array, 'Mailing List');
    }
    // Final links are related links.
    if (isset($value[3])) {
      $result_array = $this->doCheck($value[3], $result_array);
    }
    return $result_array;
  }

  /**
   *
   */
  private function doCheck($array, $result_array, $title = FALSE) {
    foreach ($array as $delta => $item) {
      if (isset($item['url']) && !empty($item['url']) && $item['url'] !== '') {
        if ($title) {
          $item['title'] = $title;
        }
        $result_array[] = $item;
      }
    }

    return($result_array);
  }

}
