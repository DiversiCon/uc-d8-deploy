<?php

namespace Drupal\uc_cdr\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Perform custom value transformations.
 *
 * @MigrateProcessPlugin(
 *   id = "build_primary_department"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: build_primary_department
 *   source: example_field
 * @endcode
 */
class BuildPrimaryDepartment extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_array($value)) {
      foreach ($value as $v) {
        $values = ['DeptName' => $v];
      }
    } else {
      $values[] = ['DeptName' => $value];
    }
    return $values;
  }

}
