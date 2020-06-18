<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'GetExternalFacultyId' migrate process plugin.
 *
 * This plugin can be used convert an entity id to a CDR external id.
 *
 * @MigrateProcessPlugin(
 *  id = "get_external_faculty_id"
 * )
 */
class GetExternalFacultyId extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $uuid = '';

    // Must be valid value.
    if (!empty($value) && !is_array($value)) {

      // Get cross reference data from appropriate table.
      $query = db_query("select field_external_id_value from node__field_external_id where entity_id = " . $value);
      $uuid_data = $query->fetchAssoc();
      if ($uuid_data) {
        $uuid = $uuid_data['field_external_id_value'];
      }

    }

    return $uuid;
  }

}
