<?php

namespace Drupal\uc_cdr\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Perform custom value transformations.
 *
 * @MigrateProcessPlugin(
 *   id = "build_faculty_name"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: build_faculty_name
 *   source: example_field
 * @endcode
 */
class BuildFacultyName extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $name_string = (!empty($value[2])) ? $value[2] . ', ' : '';
    $name_string .= (!empty($value[0])) ? $value[0] . ' ' : '';
    $name_string .= (!empty($value[1])) ? $value[1] : '';

    return trim($name_string);
  }

}
