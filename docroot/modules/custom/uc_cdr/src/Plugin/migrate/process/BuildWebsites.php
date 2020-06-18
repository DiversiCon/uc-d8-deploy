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
 *   id = "build_websites"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: build_websites
 *   source: example_field
 * @endcode
 */
class BuildWebsites extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $website_name = $row->getSourceProperty('WebsiteName');
    $website_url = $row->getSourceProperty('WebsiteURL');

    if (empty($website_name) && empty($website_url)) {
      return [NULL, NULL];
    }

    $paragraph = Paragraph::create([
      'type' => 'websites',
      'field_website_name' => [
        'value' => $website_name,
      ],
      'field_website_url' => [
        'value' => $website_url,
      ],
    ]);

    if ($paragraph->save()) {
      $target_id = $paragraph->id();
      $rev_id = $paragraph->getRevisionId();
    }

    return [$target_id, $rev_id];
  }

}
