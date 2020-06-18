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
 *   id = "build_grad_program_websites"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: build_grad_program_websites
 *   source: example_field
 * @endcode
 */
class BuildGradProgramWebsites extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $grad_program_name = $row->getSourceProperty('GraduateProgramName');
    $grad_program_url = $row->getSourceProperty('GraduateProgramURL');

    if (empty($grad_program_name) && empty($grad_program_url)) {
      return [NULL, NULL];
    }

    $paragraph = Paragraph::create([
      'type' => 'graduate_program_websites',
      'field_graduate_program_name' => [
        'value' => $grad_program_name,
      ],
      'field_graduate_program_url' => [
        'value' => $grad_program_url,
      ],
    ]);

    if ($paragraph->save()) {
      $target_id = $paragraph->id();
      $rev_id = $paragraph->getRevisionId();
    }

    return [$target_id, $rev_id];
  }

}
