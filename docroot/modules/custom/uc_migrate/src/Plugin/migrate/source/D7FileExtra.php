<?php

namespace Drupal\uc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\Condition;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 7 file source from database.
 *
 * @MigrateSource(
 *   id = "d7_file_extra",
 *   source_module = "file"
 * )
 */
class D7FileExtra extends DrupalSqlBase {

  /**
   * The public file directory path.
   *
   * @var string
   */
  protected $publicPath;

  /**
   * The private file directory path, if any.
   *
   * @var string
   */
  protected $privatePath;

  /**
   * The temporary file directory path.
   *
   * @var string
   */
  protected $temporaryPath;

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('file_managed', 'f')
      ->fields('f')
      ->condition('uri', 'temporary://%', 'NOT LIKE')
      ->orderBy('f.timestamp');

    // Filter by scheme(s), if configured.
    if (isset($this->configuration['scheme'])) {
      $schemes = [];
      // Remove 'temporary' scheme.
      $valid_schemes = array_diff((array) $this->configuration['scheme'], ['temporary']);
      // Accept either a single scheme, or a list.
      foreach ((array) $valid_schemes as $scheme) {
        $schemes[] = rtrim($scheme) . '://';
      }
      $schemes = array_map([$this->getDatabase(), 'escapeLike'], $schemes);

      // Add conditions, uri LIKE 'public://%' OR uri LIKE 'private://%'.
      $conditions = new Condition('OR');
      foreach ($schemes as $scheme) {
        $conditions->condition('uri', $scheme . '%', 'LIKE');
      }
      $query->condition($conditions);
    }

    // Add joins to title and alt text.
    $query->addJoin('LEFT OUTER', 'field_data_field_maincontentimage', 'mi', 'f.fid = mi.field_maincontentimage_fid');
    $query->addJoin('LEFT OUTER', 'field_data_field_rightimage', 'ri', 'f.fid = ri.field_rightimage_fid');
    $query->addField('mi', 'field_maincontentimage_alt', 'mi_alt_text');
    $query->addField('mi', 'field_maincontentimage_title', 'mi_title_text');
    $query->addField('ri', 'field_rightimage_alt', 'ri_alt_text');
    $query->addField('ri', 'field_rightimage_title', 'ri_title_text');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $this->publicPath = $this->variableGet('file_public_path', 'sites/default/files');
    $this->privatePath = $this->variableGet('file_private_path', NULL);
    $this->temporaryPath = $this->variableGet('file_temporary_path', '/tmp');
    return parent::initializeIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Compute the filepath property, which is a physical representation of
    // the URI relative to the Drupal root.
    $path = str_replace(
      [
        'public:/',
        'private:/',
        'temporary:/',
      ],
      [
        $this->publicPath,
        $this->privatePath,
        $this->temporaryPath,
      ],
      $row->getSourceProperty('uri')
    );
    // At this point, $path could be an absolute path or a relative path,
    // depending on how the scheme's variable was set. So we need to shear out
    // the source_base_path in order to make them all relative.
    $path = str_replace($this->configuration['constants']['source_base_path'], NULL, $path);
    $row->setSourceProperty('filepath', $path);

    // Figure out what to use for proper alt/title text.
    if ($row->getSourceProperty('mi_alt_text')) {
      $row->setSourceProperty('alt_text', $row->getSourceProperty('mi_alt_text'));
    }
    else {
      if ($row->getSourceProperty('ri_alt_text')) {
        $row->setSourceProperty('alt_text', $row->getSourceProperty('ri_alt_text'));
      }
    }
    if ($row->getSourceProperty('mi_title_text')) {
      $row->setSourceProperty('title_text', $row->getSourceProperty('mi_title_text'));
    }
    else {
      if ($row->getSourceProperty('ri_title_text')) {
        $row->setSourceProperty('title_text', $row->getSourceProperty('ri_title_text'));
      }
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'fid' => $this->t('File ID'),
      'uid' => $this->t('The {users}.uid who added the file. If set to 0, this file was added by an anonymous user.'),
      'filename' => $this->t('File name'),
      'filepath' => $this->t('File path'),
      'filemime' => $this->t('File MIME Type'),
      'status' => $this->t('The published status of a file.'),
      'timestamp' => $this->t('The time that the file was added.'),
      'mi_alt_text' => $this->t('Middle Alt text.'),
      'mi_title_text' => $this->t('Middle Title text.'),
      'ri_alt_text' => $this->t('Right Alt text.'),
      'ri_title_text' => $this->t('Right Title text.'),
      'alt_text' => $this->t('Alt text.'),
      'title_text' => $this->t('Title text.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['fid']['type'] = 'integer';
    return $ids;
  }

}
