<?php

namespace Drupal\uc_cdr\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

use Drupal\paragraphs\Entity\Paragraph;

/**
 * Perform custom value transformations.
 *
 * @MigrateProcessPlugin(
 *   id = "build_awards"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: build_awards
 *   source: example_field
 * @endcode
 */
class BuildAwards extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $year = $row->getSourceProperty('Year');
    $thru_year = $row->getSourceProperty('ThruYear');
    $name = $row->getSourceProperty('Name');
    $institution = $row->getSourceProperty('Institution');

    if (empty($year) && empty($thru_year) && empty($name) && empty($institution)) {
      return [NULL, NULL];
    }

    $paragraph = Paragraph::create([
      'type' => 'awards',
      'field_year' => [
        'value' => $year,
      ],
      'field_thru_year' => [
        'value' => $thru_year,
      ],
      'field_name' => [
        'value' => $name,
      ],
      'field_institution' => [
        'value' => $institution,
      ],
    ]);

    if ($paragraph->save()) {
      $target_id = $paragraph->id();
      $rev_id = $paragraph->getRevisionId();
    }

    return [$target_id, $rev_id];
  }

}
