<?php

namespace Drupal\uc_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node as D7Node;

/**
 * Custom D7 node source that returns node referenced by leading content.
 *
 * @MigrateSource(
 *   id = "d7_node_hero"
 * )
 */
class D7NodeHero extends D7Node {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return ['hero_type' => $this->t('Hero type')] + parent::fields();
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    parent::prepareRow($row);

    // Get leading content content type.
    if (NULL !== $row->getSourceProperty('field_leading_content')) {
      $hero = $row->getSourceProperty('field_leading_content');
      if (!empty($hero)) {
        $query = $this->select('node', 'n')
          ->fields('n', ['type']);
        $query->condition('n.nid', $hero[0]['target_id']);
        $type = $query->execute()->fetchField();
        if (!empty($type)) {
          $row->setSourceProperty('hero_type', $type);
        }
      }
    }

    // Get YouTube ID.
    if (NULL !== $row->getSourceProperty('field_video_path_youtube')) {
      $video = $row->getSourceProperty('field_video_path_youtube');
      if (!empty($video)) {
        $query = $this->select('file_managed', 'f')
          ->fields('f', ['filename', 'uri']);
        $query->condition('f.fid', $video[0]['fid']);
        $info = $query->execute()->fetchAssoc();
        if (!empty($info)) {
          if (isset($info['uri']) && !empty($info['filename'])) {
            $row->setSourceProperty('yt_filename', $info['filename']);
          }
          if (isset($info['uri']) && !empty($info['uri'])) {
            $uri = end(explode('/', $info['uri']));
            $row->setSourceProperty('yt_uri', $uri);
          }
        }
      }
    }

    // Get Video start/end dates.
    if (NULL !== $row->getSourceProperty('field_video_date')) {
      $date = $row->getSourceProperty('field_video_date');
      if (!empty($date)) {
        if (!empty($date[0]['value'])) {
          $row->setSourceProperty('video_start_date', $date[0]['value']);
        }
        if (!empty($date[0]['value2'])) {
          $row->setSourceProperty('video_end_date', $date[0]['value2']);
        }
      }
    }

    return TRUE;
  }

}
