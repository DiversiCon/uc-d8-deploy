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
 *   id = "build_education_training"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: build_education_training
 *   source: example_field
 * @endcode
 */
class BuildEducationTraining extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $start_date = $row->getSourceProperty('StartDate');
    $end_date = $row->getSourceProperty('EndDate');
    $institution = $row->getSourceProperty('Institution');
    $location = $row->getSourceProperty('Location');
    $degree_earned = $row->getSourceProperty('DegreeEarned');
    $major_field = $row->getSourceProperty('MajorField');

    if (empty($start_date) && empty($end_date) && empty($institution) && empty($location) && empty($degree_earned) && empty($major_field)) {
      return [NULL, NULL];
    }

    $paragraph = Paragraph::create([
      'type' => 'education_and_training',
      'field_degree_earned' => [
        'value' => $degree_earned,
      ],
      'field_end_date' => [
        'value' => $end_date,
      ],
      'field_start_date' => [
        'value' => $start_date,
      ],
      'field_institution' => [
        'value' => $institution,
      ],
      'field_location' => [
        'value' => $location,
      ],
      'field_major_field' => [
        'value' => $major_field,
      ],
    ]);

    if ($paragraph->save()) {
      $target_id = $paragraph->id();
      $rev_id = $paragraph->getRevisionId();
    }

    return [$target_id, $rev_id];
  }

}
