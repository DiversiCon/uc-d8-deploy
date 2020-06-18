<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'DestinationFilePath' migrate process plugin.
 *
 * This plugin can be used generate a specific full path.
 *
 * @MigrateProcessPlugin(
 *  id = "destination_file_path"
 * )
 */
class DestinationFilePath extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $full_path = '';

    // Must be valid arguments.
    if (empty($value) || is_array($value)) {
      return $full_path;
    }

    // Replace public:// with public://migrated/.
    // $full_path = str_replace('public://', 'public://migrated/', $value);

    // Add a Drupal friendly prefix.
    $full_path = 'public://migrated/' . $value;

    return $full_path;
  }

}
