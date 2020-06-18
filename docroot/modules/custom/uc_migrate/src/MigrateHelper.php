<?php

namespace Drupal\uc_migrate;

/**
 * A Drush commandfile.
 */
class MigrateHelper {


  /**
   * Normalize image metadata.
   *
   * We get tons of variations for specifying image information.  Reduce things
   * down to a common set of elements for all images.  Brute force permitted.
   *
   * @param $source
   * @param $field
   * @param $image
   *
   * @return array|bool
   */
  public function normalizeImageMetadata($source, $field, $image) {
    $metadata = [
      'title' => '',
      'description' => '',
      'image' => '',
      'url' => '',
    ];

    // Set expectations regarding which media fields we expect to get and
    // how metadata for each is defined.
    $media_fields = [
      'basic_sidebar_image' => [
        'title' => 'basic_sidebar_image_title',
        'description' => 'basic_sidebar_image_description',
        'image' => 'basic_sidebar_image_image',
        'url' => 'basic_sidebar_image_url',
      ],
      'basic_main_images' => [
        'title' => 'basic_main_images_headline',
        'description' => 'basic_main_images_desc',
        'image' => 'basic_main_images_image',
      ],
      'basic_image_list' => [
        'title' => 'basic_image_list_title',
        'description' => 'basic_image_list_description',
        'image' => 'basic_image_list_image',
        'url' => 'basic_image_list_url',
      ],
      'people_profile_picture' => [
        'title' => 'title',
        'caption' => 'caption',
        'description' => 'desc',
        'filename' => 'filename',
        'extension' => 'extension',
        'alt_text' => 'alt_text',
        'url' => 'url',
      ],
      'lab_main_images' => [
        'title' => 'lab_main_image_headline',
        'description' => 'lab_main_image_desc',
        'image' => 'lab_main_image',
      ],
      'news_image' => [
        'title' => 'title',
        'caption' => 'caption',
        'description' => 'desc',
        'filename' => 'filename',
        'extension' => 'extension',
        'alt_text' => 'alt_text',
        'url' => 'url',
      ],
      'publication_image' => [
        'title' => 'title',
        'caption' => 'caption',
        'description' => 'desc',
        'filename' => 'filename',
        'extension' => 'extension',
        'alt_text' => 'alt_text',
        'url' => 'url',
      ],
    ];

    // Figure out directory name where the file should be located.
    $dir = $field;
    switch ($dir) {
      case 'basic_main_images':
        $dir = 'basic_feature_images';
        break;
      case 'basic_sidebar_image':
        $dir = 'basic_sidebar_images';
        break;
      case 'people_profile_picture':
        $dir = 'people_images';
        break;
      case 'lab_main_images':
        $dir = 'feature_interior';
        break;
      case 'news_image':
        $dir = 'news';
        break;
      case 'publication_image':
        $dir = 'people_images';
        break;
    }
    $metadata['dir'] = $dir;

    // Get the common field data that we will use based on field.
    foreach ($media_fields[$field] as $common_field => $source_field) {
      $metadata[$common_field] = $image[$source_field];
    }

    // If this is a people image then we do some extra work.
    if ($source == 'people' || $source == 'news' || $source == 'pubs') {

      // Build up the file name from components.
      $metadata['image'] = $metadata['filename'] . '.' . $metadata['extension'];

      // Figure out description.
      if ($metadata['caption']) {
        $metadata['description'] = $metadata['caption'];
      }
      else {
        if ($metadata['alt_text']) {
          $metadata['description'] = $metadata['alt_text'];
        }
      }

      // Get rid of the extra stuff.
      unset($metadata['filename']);
      unset($metadata['extension']);
      unset($metadata['caption']);
      unset($metadata['alt_text']);
      unset($metadata['desc']);
    }

    // Capture image details.
    $image_details = $this->getImageDetails($dir, $metadata['image']);

    // There are times when we outright reject an image.
    if (!$image_details) {
      return FALSE;
    }
    if (!$image_details['mimetype']) {
      return FALSE;
    }
    if ($image_details['size'][0] <= 300 && $field !== 'basic_image_list') {
      return FALSE;
    }

    // If we're still here then add more image details.
    foreach ($image_details as $key => $value) {
      $metadata[$key] = $value;
    }

    return $metadata;
  }

  /**
   * Get image details based on a location and image.
   *
   * @param $dir
   * @param $image
   *
   * @return array
   */
  public function getImageDetails($dir, $image) {
    $path = 'sites/default/files/legacy/i/' . $dir . '/';
    $image_path = $path . $image;

    if (file_exists($image_path)) {
      return [
        'path' => $image_path,
        'mimetype' => mime_content_type($image_path),
        'size' => getimagesize($image_path),
      ];
    }
    else {
      return [];
    }
  }

  /**
   * Extract information files that will be migrated to photo_inset paragraphs.
   *
   * @param bool $return_values
   *   Determines whether to return values or keys.
   *
   * @return array
   *   Array of inset images.
   */
  public function getInsetImages($return_values = FALSE) {
    $inset_images = [];

    // Establish a database connection to the legacy database.
    /* @var \Drupal\Core\Database\Connection $legacy */
    $legacy = $this->getLegacyConnection();

    // Loop through all nodes with narrow images.
    $query = $legacy->query(
      "select entity_id as nid, body_value as body from field_data_body where body_value like '%class=\"image-narrow%' and bundle = 'news'"
    );
    $rows = $query->fetchAll();
    foreach ($rows as $row) {
      $tags = [];
      preg_match_all('/<img[^>]+\>/i', $row['body'], $tags);
      foreach ($tags[0] as $delta => $tag) {
        if (strpos($tag, 'image-narrow')) {
          preg_match("/[^\/]+?(?=\?|$)/", $tag, $file);
          $filename = urldecode($file[0]);
          $fid = $this->getFileId($filename, $legacy);
          if ($return_values) {
            $inset_images[] = [
              'nid' => $row['nid'],
              'fid' => $fid,
              'file' => $filename,
            ];
          }
          else {
            $inset_images[] = $row['nid'] . ':' . $fid;
          }
        }
      }
    }

    return $inset_images;
  }

  /**
   * Extract embedded file references in the order they appear.
   *
   * @param bool $return_values
   *   Determines whether to return values or keys.
   *
   * @return array
   *   Array of inset images.
   */
  public function getOrderedImages($return_values = FALSE) {
    $ordered_images = [];

    // Establish a database connection to the legacy database.
    /* @var \Drupal\Core\Database\Connection $legacy */
    $legacy = $this->getLegacyConnection();

    // Loop through all nodes with narrow/wide images.
    $query = $legacy->query(
      "select entity_id as nid, body_value as body from field_data_body where (body_value like '%class=\"image-narrow%' or body_value like '%class=\"image-wide%') and bundle = 'news'"
    );
    $rows = $query->fetchAll();
    foreach ($rows as $row) {
      $tags = [];
      preg_match_all('/<img[^>]+\>/i', $row['body'], $tags);
      foreach ($tags[0] as $delta => $tag) {
        preg_match("/[^\/]+?(?=\?|$)/", $tag, $file);
        $filename = urldecode($file[0]);
        $fid = $this->getFileId($filename, $legacy);
        if ($return_values) {

          // Now we need to lookup the original image id for each image.
          $new_fid = 0;
          $database = \Drupal::service('database');
          $query = $database->select('migrate_map_migrate_file', 'map');
          $query->fields('map', [
            'sourceid1',
          ]);
          $query->condition('destid1', $fid);
          $source = $query->execute()->fetch();
          if ($source) {
            $new_fid = $source->sourceid1;
          }

          // Set our values.
          $ordered_images[] = [
            'nid' => $row['nid'],
            'fid' => $fid,
            'file' => $filename,
            'new_fid' => $new_fid,
          ];
        }
        else {
          $ordered_images[] = $row['nid'] . ':' . $fid;
        }
      }
    }

    return $ordered_images;
  }

  /**
   * Extract embedded file references in the order they appear.
   *
   * @param string $nid
   *   Original node id to process.
   *
   * @return array
   *   Array of ordered images.
   */
  public function getOrderedImagesNode($nid) {
    $ordered_images = [];

    // Establish a database connection to the legacy database.
    /* @var \Drupal\Core\Database\Connection $legacy */
    $legacy = $this->getLegacyConnection();

    // Loop through all narrow/wide images in the body text.
    $query = $legacy->query(
      "select entity_id as nid, body_value as body from field_data_body where (body_value like '%class=\"image-narrow%' or body_value like '%class=\"image-wide%') and entity_id = " . $nid . " and bundle = 'news'"
    );
    $rows = $query->fetchAll();
    foreach ($rows as $row) {
      $tags = [];
      preg_match_all('/<img[^>]+\>/i', $row['body'], $tags);
      foreach ($tags[0] as $delta => $tag) {
        preg_match("/[^\/]+?(?=\?|$)/", $tag, $file);
        $filename = urldecode($file[0]);
        $fid = $this->getFileId($filename, $legacy);

        // Now we need to lookup the original image id for each image.
        $new_fid = 0;
        $database = \Drupal::service('database');
        $query = $database->select('migrate_map_migrate_file', 'map');
        $query->fields('map', [
          'sourceid1',
        ]);
        $query->condition('destid1', $fid);
        $source = $query->execute()->fetch();
        if ($source) {
          $new_fid = $source->sourceid1;
        }

        // Set our values.
        $ordered_images[] = [
          'nid' => $row['nid'],
          'fid' => $fid,
          'file' => $filename,
          'new_fid' => $new_fid,
        ];
      }
    }

    return $ordered_images;
  }

  /**
   * Helper function to get file ID.
   *
   * @param string $filename
   *   Name of file.
   * @param object $legacy
   *   Connection to legacy database.
   *
   * @return int
   *   File ID.
   */
  private function getFileId($filename, $legacy) {

    // Try to find file id based on given filename.
    $query = $legacy->query(
      "select fid from file_managed where filename = '" . $filename . "'"
    );
    $rows = $query->fetchAll();
    $fid = 0;
    if ($rows) {
      $fid = $rows[0]['fid'];
    }

    // If nothing assume filename deduplication is at play remove, try again.
    else {
      $query = $legacy->query(
        "select fid from file_managed where uri like '%" . $filename . "'"
      );
      $rows = $query->fetchAll();
      if ($rows) {
        $fid = $rows[0]['fid'];
      }
      /*
      $filename = preg_replace('/\_0./', '.', $filename);
      $query = $legacy->query(
      "select fid from file_managed where filename = '" . $filename . "'"
      );
      $rows = $query->fetchAll();
      if ($rows) {
      $fid = $rows[0]['fid'];
      }
       */
    }
    return $fid;
  }

  /**
   * Helper function to get db connection info.
   *
   * @return \Drupal\Core\Database\Connection|bool
   *   Database connection info.
   */
  public function getLegacyConnection() {
    /*
    $db_info = [
    'driver' => 'mysql',
    'username' => 'drupal7',
    'password' => 'drupal7',
    'host' => 'legacydata',
    'port' => '3306',
    'database' => 'drupal7',
    'prefix' => NULL
    ];
     */
    $db_info = [
      'driver' => 'mysql',
      'username' => 's61308',
      'password' => 'oJPjrYf3UdxcQRd',
      'host' => '127.0.0.1',
      'database' => 'uchicagobdb269344',
      'prefix' => NULL,
    ];

    /* @var \Drupal\Core\Database\Connection $legacy */
    $legacy = \Drupal::database()->open($db_info);
    if ($legacy) {
      return $legacy;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Helper function to get CSV data.
   *
   * @param $filename
   * @param $key
   *
   * @return array
   */
  public function getCSVData($filename, $key = FALSE) {
    $csv_data = [];
    $fields = [];

    // Define path to CSV.
    $csv_path = \Drupal::root() . $filename;

    // Read all rows.
    if (($handle = fopen($csv_path, "r")) !== FALSE) {
      $row = 1;
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        // First row contains field names.
        if ($row == 1) {
          foreach ($data as $delta => $value) {
            $fields[$delta] = $value;
          }
        }
        // Other rows are data.
        else {
          $csv_row = [];
          foreach ($data as $delta => $value) {
            $csv_row[$fields[$delta]] = $value;
          }
          if ($key) {
            $csv_data[$csv_row[$key]] = $csv_row;
          }
          else {
            $csv_data[] = $csv_row;
          }
        }

        // Increment row.
        $row++;
      }
      fclose($handle);
    }

    return $csv_data;
  }

}
