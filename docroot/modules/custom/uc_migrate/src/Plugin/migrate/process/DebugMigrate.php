<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'DebugMigrate' migrate process plugin.
 *
 * This plugin can be used during debugging to inspect current values.
 *
 * @MigrateProcessPlugin(
 *  id = "debug_migrate"
 * )
 */
class DebugMigrate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    print_r(var_export($value, TRUE) . PHP_EOL);

    if (empty($value)) {
      return '';
    }

    // print_r(var_export($row->getSource(),TRUE));
    // print_r(var_export($row->getDestination(),TRUE));
    return FALSE;
  }

}
