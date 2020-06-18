<?php

namespace Drupal\uc_migrate\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Drupal\migrate\Row;

/**
 * Drupal 7 gallery items source from database.
 *
 * @MigrateSource(
 *   id = "d7_gallery_items",
 *   source_module = "node"
 * )
 */
class D7GalleryItems extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {

    // First execute subquery to get entity ids to process.
    $ids = [];
    $subquery = $this->select('field_data_field_maincontentimage', 'image')
      ->fields('image', [
        'entity_id',
      ]);
    $subquery->condition('image.bundle', 'news');
    $subquery->groupBy('image.entity_id');
    $subquery->having('count(*) > 1');
    $result = $subquery->execute();
    foreach ($result as $delta => $row) {
      $ids[] = $row['entity_id'];
    }

    // Select main content images.
    $query = $this->select('field_data_field_maincontentimage', 'image')
      ->fields('image', [
        'entity_id',
        'revision_id',
        'delta',
        'field_maincontentimage_fid',
        'field_maincontentimage_alt',
        'field_maincontentimage_title',
        'field_maincontentimage_width',
        'field_maincontentimage_height',
      ]);
    $query->condition('image.bundle', 'news');
    $query->condition('image.entity_id', $ids, 'in');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'entity_id' => $this->t('Entity ID'),
      'revision_id' => $this->t('Revision ID'),
      'delta' => $this->t('Delta'),
      'field_maincontentimage_fid' => $this->t('File ID'),
      'field_maincontentimage_alt' => $this->t('Alt text'),
      'field_maincontentimage_title' => $this->t('Title'),
      'field_maincontentimage_width' => $this->t('Width'),
      'field_maincontentimage_height' => $this->t('Height'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['entity_id']['type'] = 'integer';
    $ids['entity_id']['alias'] = 'image';
    $ids['revision_id']['type'] = 'integer';
    $ids['revision_id']['alias'] = 'image';
    $ids['delta']['type'] = 'integer';
    $ids['delta']['alias'] = 'image';
    return $ids;
  }

}
