<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'SourceFilePath' migrate process plugin.
 *
 * This plugin can be used generate a specific full path.
 *
 * @MigrateProcessPlugin(
 *  id = "source_file_path"
 * )
 */
class SourceFilePath extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $full_path = '';

    // Must be valid arguments.
    if (empty($value) || is_array($value)) {
      return $full_path;
    }

    // For some reason, the file path includes "site" in the first fragment
    // instead of the expected "sites".  We just want to fix that issue if
    // it exists (i.e. site --> sites).
    $chunks = explode('/', $value);
    if ($chunks[0] == 'site') {
      $chunks[0] = 'sites';
    }
    $path = implode('/', $chunks);

    // Now we just add the hard-coded protocol/domain.
    $full_path = 'https://pritzker.uchicago.edu/' . $path;

    return $full_path;
  }

}
