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
 *   id = "build_academic_appointment"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: build_academic_appointment
 *   source: example_field
 * @endcode
 */
class BuildAcademicAppointment extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $title = (empty($row->getSourceProperty('Title'))) ? '' : $row->getSourceProperty('Title');
    $academic_unit_name = $row->getSourceProperty('AcademicUnitName');
    $department_name = $row->getSourceProperty('DepartmentName');
    $section_name = $row->getSourceProperty('SectionName');

    if (empty($academic_unit_name) && empty($department_name) && empty($section_name)) {
      return [NULL, NULL];
    }

    $paragraph = Paragraph::create([
      'type' => 'academic_appointment',
      'field_academic_unit_name' => [
        'value' => $academic_unit_name,
      ],
      'field_department_name' => [
        'value' => $department_name,
      ],
      'field_section_name' => [
        'value' => $section_name,
      ],
      'field_title' => [
        'value' => $title,
      ],
    ]);

    if ($paragraph->save()) {
      $target_id = $paragraph->id();
      $rev_id = $paragraph->getRevisionId();
    }

    return [$target_id, $rev_id];
  }

}
