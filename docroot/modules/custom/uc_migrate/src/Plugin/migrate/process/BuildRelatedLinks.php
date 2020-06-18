<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

use Drupal\paragraphs\Entity\Paragraph;

/**
 * Perform custom value transformations.
 *
 * @MigrateProcessPlugin(
 *   id = "build_related_links"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * title:
 *   plugin: build_related_links
 *   source: example_field
 * @endcode
 */
class BuildRelatedLinks extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $title = (empty($row->getSourceProperty('basic_related_link_title'))) ? '' : $row->getSourceProperty('basic_related_link_title');
    $url = (empty($row->getSourceProperty('basic_related_link_url'))) ? '' : $row->getSourceProperty('basic_related_link_url');
    $document = (empty($row->getSourceProperty('basic_related_link_document'))) ? '' : $row->getSourceProperty('basic_related_link_document');

    if (empty($title) && empty($url)) {
      return [NULL, NULL];
    }

    /* @var Paragraph $paragraph */
    $paragraph = Paragraph::create([
      'type' => 'custom_link',
      'field_headline_text' => [
        'value' => $title,
      ],
      'field_headline_link' => [
        'uri' => $url,
        'options' => [
          'attributes' => [
            'target' => '_blank',
          ]
        ]
      ],
    ]);

    if ($paragraph->save()) {
      $target_id = $paragraph->id();
      $rev_id = $paragraph->getRevisionId();
    }

    return [$target_id, $rev_id];
  }

}
