<?php

namespace Drupal\uc_migrate\Commands;

use Drush\Commands\DrushCommands;
use Drupal\redirect\Entity\Redirect;
use Drupal\uc_migrate\MigrateHelper;
use Drupal\Core\Database\Connection;
use Drupal\Component\Serialization\Json;

/**
 * A Drush commandfile.
 */
class MigrateCommands extends DrushCommands {

  /**
   * Convert PME migration JSON to CSV.
   *
   * Assumes a very specific structure for JSON data.
   *
   * @command uc-temp:pme-json-csv
   * @aliases uc-temp-pjc
   * @usage uc-temp:pme-json-csv
   *   Output CSV data based on given JSON endpoint.
   */
  public function convertJsonToCsv($filename) {
    $this->output()->writeln('Processing JSON data from ' . $filename);

    // Define fields to omit.
    $omit = [
      'channel_id',
      'author_id',
      'entry_date',
      'edit_date',
      'expiration_date',
      'publication_ime_lab',
      'publication_ime_authors',
      'publication_journal_information',
      'publication_related_links',
      'publication_image',
      'categories',
    ];

    // Get data and convert.
    $json_data = file_get_contents($filename);
    $data = json_decode($json_data, TRUE);
    $first = TRUE;

    foreach ($data as $data_item) {
      $output = '';
      $entry = $data_item['entry'];

      // On the first row output field names.
      if ($first) {
        foreach ($entry as $field => $value) {
          if (!in_array($field, $omit)) {
            $output .= '"' . $field . '",';
          }
        }
        $output = rtrim($output, ',');
        $this->output()->writeln($output);
        $first = FALSE;
        $output = '';
      }

      // Get values for each row and output.
      foreach ($entry as $field => $value) {
        if (!in_array($field, $omit)) {
          // Append non-array items to our string.
          if (!is_array($value)) {
            $output .= '"' . $value . '",';
          }
          else {
            if (!empty($value)) {
              $output .= '"Array",';
            }
            else {
              $output .= '"",';
            }
          }
        }
      }
      $output = rtrim($output, ',');
      $this->output()->writeln($output);
    }

    $this->output()->writeln('-OK-');
  }

  /**
   * Find data in JSON endpoint.
   *
   * Assumes a very specific structure for JSON data.
   *
   * @command uc-temp:pme-find-data
   * @aliases uc-temp-pfd
   * @usage uc-temp:pme-find-data
   *   Find field data in given JSON endpoint.
   */
  public function findData($field, $filename) {
    $find = $field;
    $this->output()->writeln('Finding field data for ' . $find . ' in ' . $filename);

    // Get data and output.
    $json_data = file_get_contents($filename);
    $data = json_decode($json_data, TRUE);

    foreach ($data as $data_item) {
      $entry = $data_item['entry'];
      $value = $entry[$find];
      if ($value) {
        $this->output()->writeln($entry['entry_id'] . ' - ' . $entry['title'] . ': ' . count($value));
//        $this->output()->writeln(var_export($value, TRUE));
      }
    }

    $this->output()->writeln('-OK-');
  }

  /**
   * Print data in JSON endpoint.
   *
   * Assumes a very specific structure for JSON data.
   *
   * @command uc-temp:pme-print-data
   * @aliases uc-temp-ppd
   * @usage uc-temp:pme-print-data
   *   Print field data in given JSON endpoint.
   */
  public function printData($field, $filename) {
    $find = $field;
    $this->output()->writeln('Finding field data for ' . $find . ' in ' . $filename);

    // Get data and output.
    $json_data = file_get_contents($filename);
    $data = json_decode($json_data, TRUE);

    foreach ($data as $data_item) {
      $entry = $data_item['entry'];
      $value = $entry[$find];
      if ($value) {
        $this->output()->writeln($entry['entry_id'] . ' - ' . $entry['title'] . ': ' . $value);
      }
    }

    $this->output()->writeln('-OK-');
  }

  /**
   * Find document references in related links of JSON endpoint.
   *
   * Assumes a very specific structure for JSON data.
   *
   * @command uc-temp:pme-find-docs
   * @aliases uc-temp-pfdc
   * @usage uc-temp:pme-find-docs
   *   Find document data in given JSON endpoint.
   */
  public function findDocs($filename) {
    $find = 'basic_related_links';
    $this->output()->writeln('Finding document data for ' . $find . ' in ' . $filename);

    // Get data and output.
    $json_data = file_get_contents($filename);
    $data = json_decode($json_data, TRUE);

    foreach ($data as $data_item) {
      $entry = $data_item['entry'];
      $value = $entry[$find];
      if ($value) {
        foreach($value as $item) {
          if (!empty($item['basic_related_link_title']) &&
              empty($item['basic_related_link_url']) &&
              !empty($item['basic_related_link_document'])) {
//            $this->output()->writeln($item['basic_related_link_document'] . ' - ' . $item['basic_related_link_title'] . '  (' . $value['entry_id'] . ')');
            $this->output()->writeln(
              $entry['entry_id'] . ',' .
              $item['basic_related_link_document'] . ', ' .
              $item['basic_related_link_title'] . ', ' .
              $entry['title'] . ',)' .
              $entry['title_url']
            );
          }
        }
      }
    }

    $this->output()->writeln('-OK-');
  }

  /**
   * Find image references in JSON endpoint.
   *
   * Assumes a very specific structure for JSON data.
   *
   * @command uc-temp:pme-find-images
   * @aliases uc-temp-pfi
   * @usage uc-temp:pme-find-images
   *   Find image data in given JSON endpoint.
   */
  public function findImage($filename) {
    $find = 'basic_related_links';
    $this->output()->writeln('Finding document data for ' . $find . ' in ' . $filename);

    // Get data and output.
    $json_data = file_get_contents($filename);
    $data = json_decode($json_data, TRUE);

    foreach ($data as $data_item) {
      $entry = $data_item['entry'];
      $value = $entry[$find];
      if ($value) {
        foreach($value as $item) {
          if (!empty($item['basic_related_link_title']) &&
            empty($item['basic_related_link_url']) &&
            !empty($item['basic_related_link_document'])) {
            //            $this->output()->writeln($item['basic_related_link_document'] . ' - ' . $item['basic_related_link_title'] . '  (' . $value['entry_id'] . ')');
            $this->output()->writeln(
              $entry['entry_id'] . ',' .
              $item['basic_related_link_document'] . ', ' .
              $item['basic_related_link_title'] . ', ' .
              $entry['title'] . ',)' .
              $entry['title_url']
            );
          }
        }
      }
    }

    $this->output()->writeln('-OK-');
  }

  /**
   * Convert sitemap to DB table.
   *
   * @command uc-migrate:sitemap-db
   * @aliases uc-sitemap-db
   * @usage uc-migrate:sitemap-db
   *   Transfer sitemap to databas table.
   */
  public function convertSitemapDb() {
    $this->output()->writeln('Processing data from ' . $filename);

    // Current database connection.
    /* @var \Drupal\Core\Database\Connection $connection */
    $connection = \Drupal::database();
    $table = 'legacy_sitemap';
    $table_def = [
      'fields' => [
        'entry_id' => array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'id' => [
          'type' => 'char',
          'length' => 255,
          'not null' => TRUE,
        ],
        'section' => [
          'type' => 'varchar',
          'length' => 500,
          'not null' => TRUE,
        ],
        'url' => [
          'type' => 'varchar',
          'length' => 2048,
          'not null' => TRUE,
        ],
      ],
      'primary key' => [
        'entry_id',
      ],
    ];

    // Drop and create the legacy_sitemap table.
    $connection->schema()->dropTable($table);
    $connection->schema()->createTable($table, $table_def);

    /*
    // Get data and convert.
    $filename = '/sites/default/files/legacy/sitemap.json';
    $json_data = file_get_contents($filename);
    $data = json_decode($json_data, TRUE);
    foreach ($data['url'] as $entry) {
    */

    // Get data and convert.
    $filename = '/sites/default/files/legacy/sitemap.csv';
    $csv_data = $this->getCSVData($filename);
    if ($csv_data) {
      foreach ($csv_data as $delta => $entry) {
        $output = '';
//        $this->output()->writeln($entry['loc']);


        // Figure out the relevant parts of the URL.
        $url_full = $entry['loc'];
        if ($url_full) {

          // Strip off any trailing slash and domain name.
          $url_full = rtrim($url_full, '/');
          $url_nohost = trim(parse_url($url_full)['path'], '/');

          // Capture last chunk.
          $id = FALSE;
          $url_noid = '';
          $last_slash = strrpos($url_nohost, '/');
          if ($last_slash) {
            $last_slash += 1;
            $last_slash_from_end = (strlen($url_nohost) - $last_slash) * -1;
            $id = substr($url_nohost, $last_slash_from_end);
            $url_noid = trim(substr($url_nohost, 0, $last_slash), '/');
          }

          if ($id) {
            $result = $connection->insert($table)
              ->fields([
                'id' => $id,
                'section' => $url_noid,
                'url' => $url_full,
              ])
              ->execute();

            $output .= $id . ' - ' . $url_noid . ' - ' . $url_full;
            $this->output()->writeln($output);
          }
        }
      }
    }

    $this->output()->writeln('-OK-');
  }


  /**
   * Print basic page sections.
   *
   * @command uc-migrate:basic-sections
   * @aliases uc-basic-sections
   * @usage uc-migrate:basic-sections
   *   Print basic pages with section ID.
   */
  public function printBasicSections() {

    /* @var \GuzzleHttp\Client $client */
    $http = \Drupal::service('http_client_factory');
    if ($http) {

      // Setup our HTTP client.
      $request = [];
      $client = $http->fromOptions([
        'base_uri' => 'https://pme.uchicago.edu',
      ]);

      // Get our source data.
      $source = FALSE;
      try {
        $response = $client->get('/site/export/basic', $request);
        $source = Json::decode($response->getBody());
      }
      catch (\Exception $e) {
        \Drupal::logger('uc_migrate')
          ->error('An error occurred when requesting source data: ' .
            $e->__toString() . ' (' . $e->getCode() . ')');
      }

      // If we got data then turn it around.
      if ($source) {

        // Process each row.
        foreach ($source as $source_row) {
          $source_row = $source_row['entry'];

          // Get legacy URL data for additional insight.
          $section = 'not-found';
          $query = db_query("select * from legacy_sitemap where id = '" . $source_row['url_title'] . "'");
          $url_data = $query->fetchAssoc();
          if ($url_data) {
            $section = $url_data['section'];
          }

          // Print row.
          $output = $source_row['entry_id'] . ',"' . $section . '","' . $source_row['url_title'] . '","' . $source_row['title'] . '"';
          $this->output()->writeln($output);
        }
      }
    }
  }

  /**
   * Print people titles.
   *
   * @command uc-migrate:people-titles
   * @aliases uc-people-titles
   * @usage uc-migrate:people-titles
   *   Print people with title.
   */
  public function printPeopleTitles() {

    /* @var \GuzzleHttp\Client $client */
    $http = \Drupal::service('http_client_factory');
    if ($http) {

      // Setup our HTTP client.
      $request = [];
      $client = $http->fromOptions([
        'base_uri' => 'https://pme.uchicago.edu',
      ]);

      // Get our source data.
      $source = FALSE;
      try {
        $response = $client->get('/site/export/people', $request);
        $source = Json::decode($response->getBody());
      }
      catch (\Exception $e) {
        \Drupal::logger('uc_migrate')
          ->error('An error occurred when requesting source data: ' .
            $e->__toString() . ' (' . $e->getCode() . ')');
      }

      // If we got data then turn it around.
      if ($source) {

        // Process each row.
        foreach ($source as $source_row) {
          $source_row = $source_row['entry'];

          // Get legacy URL data for additional insight.
          $section = 'not-found';
          $query = db_query("select * from legacy_sitemap where id = '" . $source_row['url_title'] . "'");
          $url_data = $query->fetchAssoc();
          if ($url_data) {
            $section = $url_data['section'];
          }

          // Aggregate categories.
          $categories = [];
          foreach ($source_row['categories'] as $delta => $category) {
            $categories[] = $category['category_name'];
          }
          $category_list = implode(':', $categories);

          // Print row.
          $output = $source_row['entry_id'] . ',"' . $section . '","' . $source_row['people_first_name'] . '","' . $source_row['people_last_name'] . '","' . $source_row['title'] . '","' . $source_row['url_title'] . '","' . $source_row['people_academic_title'] . '","' . $category_list . '"';
          $this->output()->writeln($output);
        }
      }
    }
  }

  /**
   * Extract information files that will be migrated to photo_inset paragraphs.
   *
   * @command uc-migrate:inset-images
   * @aliases uc-inset-images
   * @usage uc-migrate:inset-images
   *   Extract file information for potential photo_inset paragraphs.
   */
  public function listInsetImages() {
    $helper = new MigrateHelper();
    $inset_images = $helper->getInsetImages(TRUE);
    foreach ($inset_images as $delta => $image) {
      $this->output()->writeln($image['nid'] . ':' . $image['fid'] . ':' . $image['file']);
    }
    $this->output()->writeln('-OK-');
  }

  /**
   * List node with images and images in order of appearance.
   *
   * @command uc-migrate:ordered-images
   * @aliases uc-ordered-images
   * @usage uc-migrate:ordered-images
   *   Get the list we need.
   */
  public function listOrderedImages() {
    $helper = new MigrateHelper();
    $ordered_images = $helper->getOrderedImages(TRUE);
    foreach ($ordered_images as $delta => $image) {
      $this->output()->writeln($image['nid'] . ':' . $image['fid'] . ':' . $image['new_fid'] . ":" . $image['file']);
    }
    $this->output()->writeln('-OK-');
  }

  /**
   * Populate redirects for migrated content.
   *
   * @command uc-migrate:redirects
   * @aliases uc-redirects
   * @usage uc-migrate:redirects
   *   Populate redirects.
   */
  public function redirects() {

    // Establish a database connection to the legacy database.
    $helper = new MigrateHelper();
    /* @var \Drupal\Core\Database\Connection $legacy */
    $legacy = $helper->getLegacyConnection();

    // Get data from legacy aliases table.
    $query = $legacy->query("select * from url_alias where source like 'node%'");
    $rows = $query->fetchAll();
    foreach ($rows as $row) {

      // Extract nid from source.
      $source = explode('/', $row['source']);
      $original_nid = $source[1];

      // Get the mapped node id, which we must have.
      $nid = $this->getMapId($original_nid, 'story');
      if (!$nid) {
        $nid = $this->getMapId($original_nid, 'person');
      }
      if ($nid) {

        // Make sure we have a valid node.
        $query = \Drupal::entityQuery('node');
        $query->condition('nid', $nid);
        $matching = $query->execute();
        if ($matching) {
          if (reset($matching) == $nid) {

            // Build target redirect string.
            $redirect = 'entity:node/' . $nid;

            // Make sure we don't already have this redirect.
            $query = db_query("select * from redirect where redirect_source__path = '" . $row['alias'] . "'");
            $duplicate = $query->fetchAll();
            if (!$duplicate) {
              // Now we just need to create the redirect.
              $this->output()->writeln($redirect);
              try {
                Redirect::create([
                  'redirect_source' => $row['alias'],
                  'redirect_redirect' => $redirect,
                  'language' => 'und',
                  'status_code' => '301',
                ])->save();
              }
              catch (\Exception $e) {
                $this->output()->writeln('Ooops!');
              }
            }
            else {
              $this->output()->writeln('Duplicate path (' . $redirect . ') (' . $nid . ')');
            }
          }
        }
        else {
          $this->output()->writeln('No match (' . $nid . ')');
        }
      }
      else {
        $this->output()->writeln('No migration map (' . $original_nid . ')');
      }
    }
    $this->output()->writeln('-OK-');
  }

  /**
   * Helper function to get migration map id (new nid).
   *
   * @param string $original_nid
   *   Original nid.
   * @param string $type
   *   Content/migration type (person|story)
   *
   * @return mixed
   *   Node ID if available.
   */
  private function getMapId($original_nid, $type) {
    $nid = FALSE;

    $database = \Drupal::service('database');
    /* @var \Drupal\Core\Database\Query\Select $query */
    $query = $database->select('migrate_map_migrate_content_' . $type, 'm');
    $query->condition('m.sourceid1', $original_nid);
    $query->addField('m', 'destid1');
    $results = $query->execute();
    $rows = $results->fetchAll();
    if ($rows) {
      $nid = $rows[0]->destid1;
    }

    return $nid;
  }

  /**
   * Helper function to get CSV data.
   *
   * @param $filename
   *
   * @return array
   */
  private function getCSVData($filename) {
    $csv_data = [];
    $fields = [];

    // Define path to CSV.
    $csv_path = \Drupal::root() . $filename;
    $this->output()->writeln('CSV data source: ' . $csv_path);

    // Read all rows.
    if (($handle = fopen($csv_path, "r")) !== FALSE) {
      $row = 1;
      while (($data = fgetcsv($handle, 500, ",")) !== FALSE) {

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
          $csv_data[] = $csv_row;
        }

        // Increment row.
        $row++;
      }
      fclose($handle);
    }

    return $csv_data;
  }

}
