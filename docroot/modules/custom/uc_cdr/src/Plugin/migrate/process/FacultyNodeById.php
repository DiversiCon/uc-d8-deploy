<?php

namespace Drupal\uc_cdr\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Perform custom value transformations.
 *
 * @MigrateProcessPlugin(
 *   id = "faculty_by_id"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: faculty_by_id
 *   source: example_field
 * @endcode
 */
class FacultyNodeById extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage->getQuery();
    $query->condition('type', 'faculty');
    $query->condition('field_external_id', $value);

    $nids = $query->execute();
    $nid = array_shift($nids);
    return $nid;

  }

}
