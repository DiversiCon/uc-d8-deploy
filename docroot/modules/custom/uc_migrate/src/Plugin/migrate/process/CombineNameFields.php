<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'CombineNameFields' migrate process plugin.
 *
 * This plugin can be used concatenate multiple name fields into a single array.
 *
 * @MigrateProcessPlugin(
 *  id = "combine_name_fields"
 * )
 */
class CombineNameFields extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $name = [];

    // Must be valid arguments.
    if (empty($value) || !is_array($value)) {
      return $name;
    }

    // First name.
    if (isset($value[0][0]) && isset($value[0][0]['value'])) {
      $name['first'] = $value[0][0]['value'];
    }

    // Last name.
    if (isset($value[1][0]) && isset($value[1][0]['value'])) {
      $name['last'] = $value[1][0]['value'];
    }

    $result[] = $name;
    return $result;
  }

}
