<?php

namespace Drupal\uc_sauce\Commands;

use Drush\Commands\DrushCommands;
use Drupal\eck\Entity\EckEntity;
use Drupal\redirect\Entity\Redirect;

/**
 * A Drush commandfile.
 */
class TemporaryCommands extends DrushCommands {

  /**
   * Populate name field for contact eck content based on title.
   *
   * @command uc-temp:contact-name
   * @aliases uc-temp-cn
   * @usage uc-temp:contact-name
   *   Populate name field in eck contact content.
   */
  public function contactName() {

    // Process all contact bundles of reusable eck entity.
    $database = \Drupal::service('database');
    /* @var \Drupal\Core\Database\Query\Select $query */
    $query = $database->select('reusable_field_data', 'd');
    $query->condition('d.type', 'contacts');
    $query->addField('d', 'id');
    $query->addField('d', 'title');
    $results = $query->execute();
    $rows = $results->fetchAll();
    if ($rows) {
      foreach ($rows as $delta => $row) {
        /* @var \Drupal\eck\Entity\EckEntity $contact */
        $contact = EckEntity::load($row->id);
        if ($contact) {
          $name = $contact->get('field_eck_name')->getValue();
          if (!$name) {
            $given = NULL;
            $middle = NULL;
            $family = NULL;
            $parts = explode(' ', $row->title);
            if ($parts) {
              $skip = [
                'Campus',
                'Career',
                'College',
                'Courtesy',
                'courtesy',
                'Gather',
                'Institute',
                'New',
                'Provided',
                'RCH',
                'Smart',
                'The',
                'UChicago',
                'University',
              ];

              if (!in_array($parts[0], $skip)) {
                $given = $parts[0];
                if (count($parts) > 1) {
                  $family = $parts[count($parts) - 1];
                }
                if (count($parts) == 3) {
                  $middle = $parts[1];
                }
                $name = [
                  [
                    'title' => NULL,
                    'given' => $given,
                    'middle' => $middle,
                    'family' => $family,
                    'generational' => NULL,
                    'credentials' => NULL,
                  ],
                ];
                $contact->set('field_eck_name', $name);
                $contact->save();
                $this->output()->writeln($row->id . ' - ' . $row->title);
              }
            }
          }
        }
      }
    }
    $this->output()->writeln('-OK-');
  }

  /**
   * Dedupe contact eck entities via sheer brute force.
   *
   * @command uc-temp:contact-dedupe
   * @aliases uc-temp-cdd
   * @usage uc-temp:contact-dedupe
   *   Populate name field in eck contact content.
   */
  public function contactDedupe() {
    $database = \Drupal::service('database');
    $map = [
      '179' => '178',
      '63' => '22',
      '64' => '22',
      '67' => '66',
      '68' => '66',
      '120' => '119',
      '80' => '32',
      '154' => '153',
      '87' => '82',
      '108' => '100',
    ];

    foreach ($map as $was => $is) {
      $count = $database->update('node__field_byline')
        ->fields([
          'field_byline_target_id' => $is,
        ])
        ->condition('field_byline_target_id', $was)
        ->execute();

      /* @var \Drupal\eck\Entity\EckEntity $contact */
      $contact = EckEntity::load($was);
      if ($contact) {
        $contact->delete();
      }

      $this->output()->writeln($was . ' -> ' . $is . ' = ' . $count);
    }
    $this->output()->writeln('-OK-');
  }

  /**
   * Move field_eck_title value to field_headline_text in contact eck content.
   *
   * @command uc-temp:contact-title
   * @aliases uc-temp-ct
   * @usage uc-temp:contact-title
   *   Move display title values from line 2 to 1.
   */
  public function contactTitle() {

    // Process all contact entities.
    $database = \Drupal::service('database');
    // @var \Drupal\Core\Database\Query\Select $query //
    $query = $database->select('reusable_field_data', 'd');
    $query->condition('d.type', 'contacts');
    $query->addField('d', 'id');
    $query->addField('d', 'title');
    $results = $query->execute();
    $rows = $results->fetchAll();
    if ($rows) {
      foreach ($rows as $delta => $row) {
        // @var \Drupal\eck\Entity\EckEntity $contact //
        $contact = EckEntity::load($row->id);
        if ($contact) {
          $headline = $contact->get('field_headline_text')->getValue();
          $title = $contact->get('field_eck_title')->getValue();
          if ($title) {
            $contact->set('field_headline_text', $title);
            $contact->set('field_eck_title', []);
          }
          else {
            if (isset($headline[0]['value']) && $headline[0]['value'] == $row->title) {
              $contact->set('field_headline_text', []);
            }
          }
          $contact->save();
          $this->output()->writeln($row->id . ' - ' . $row->title);
        }
      }
    }
    $this->output()->writeln('-OK-');
  }

  /**
   * Print legacy aliases in CSV format.
   *
   * @command uc-temp:legacy-urls
   * @aliases uc-legacy-urls
   * @usage uc-temp:legacy-urls
   *   Print legacy urls in CSV format.
   */
  public function legacyUrls() {

    // Establish a database connection to the legacy database.
    $db_info = [
      'driver' => 'mysql',
      'username' => 'drupal7',
      'password' => 'drupal7',
      'host' => 'legacydata',
      'port' => '3306',
      'database' => 'drupal7',
      'prefix' => NULL,
    ];

    /* @var \Drupal\Core\Database\Connection $legacy */
    $legacy = \Drupal::database()->open($db_info);

    // Loop through all url_aliases.
    $alias_query = $legacy->query(
      "select source as source, alias as alias from url_alias where source like 'node%' order by alias"
    );
    $alias_rows = $alias_query->fetchAll();
    foreach ($alias_rows as $alias_row) {
      $source = explode('/', $alias_row['source']);
      $source_type = '"' . $source[0] . '"';
      $source_id = '"' . $source[1] . '"';
      $alias = '"' . $alias_row['alias'] . '"';
      $this->output()->writeln($source_id . ',' . $alias);
    }

    $this->output()->writeln('-OK-');
  }

  /**
   * Add redirects for legacy URL's.
   *
   * @command uc-temp:legacy-redirects
   * @aliases uc-legacy-redirects
   * @usage uc-temp:legacy-redirects
   *   Add redirects for legacy URL's.
   */
  public function legacyRedirects($filename) {
    $this->output()->writeln('Processing redirects from ' . $filename);

    // Add a redirect for every item in the CSV data.
    $csv_data = $this->getCSVData($filename);
    if ($csv_data) {
      foreach ($csv_data as $delta => $row) {

        // If we have a new nid just use it.
        if ($row['new_nid']) {
          $nid = $row['new_nid'];
        }
        // No new nid see if we have new url.
        else {
          if ($row['new_url']) {
            $nid = $this->getNidByAlias($row['new_url']);
          }
          // No new url. See if we previously migrated this content.
          else {
            if ($row['legacy_nid']) {
              $nid = $this->getNidByLegacyNid($row['legacy_nid']);
            }
            // Nothing use use.
            else {
              $nid = FALSE;
            }
          }
        }

        // If we have a NID create the redirect.
        if ($nid) {
          $this->createRedirect($nid, $row['legacy_url']);
        }
        else {
          $this->output()->writeln('Could not find anything to update: ' . $row['legacy_nid'] . ' ** ' . $row['legacy_url']);
        }
      }
    }
    else {
      $this->output()->writeln('No CSV data found.');
    }

    $this->output()->writeln('-OK-');
  }

  /**
   * Helper function to get Node ID by alias.
   *
   * @param $alias
   *
   * @return bool|string
   */
  private function getNidByAlias($alias) {
    $nid = FALSE;
    $alias = '/' . $alias;

    $db = \Drupal::database();
    $select = $db->select('url_alias', 'a');
    $select->addField('a', 'source');
    $select->where('a.alias = :alias', [':alias' => $alias]);
    $result = $select->execute()->fetch();
    if ($result) {
      $nid = substr($result->source, strrpos($result->source, '/') + 1);
    }

    return $nid;
  }

  /**
   * Helper function to get Node ID by Legacy Node ID.
   *
   * @param $legacy_nid
   *
   * @return bool|string
   */
  private function getNidByLegacyNid($legacy_nid) {
    $nid = FALSE;

    $db = \Drupal::database();
    $select = $db->select('migrate_map_migrate_content_story', 'm');
    $select->addJoin('LEFT OUTER', 'node_field_data', 'n', 'm.destid1 = n.nid');
    $select->addField('m', 'sourceid1');
    $select->addField('n', 'nid');
    $select->addField('n', 'type');
    $select->where('m.sourceid1 = :legacy_nid', [':legacy_nid' => $legacy_nid]);
    $result = $select->execute()->fetch();
    if ($result) {
      if ($result->nid && $result->type == 'story') {
        $nid = $result->nid;
      }
    }

    return $nid;
  }

  /**
   * Helper function to create a redirect.
   *
   * @param $nid
   * @param $alias
   */
  private function createRedirect($nid, $alias) {

    // Make sure we have a valid node.
    $query = \Drupal::entityQuery('node');
    $query->condition('nid', $nid);
    $matching = $query->execute();
    if ($matching) {
      if (reset($matching) == $nid) {

        // Build target redirect string.
        $redirect = 'internal:/node/' . $nid;

        // Make sure we don't already have this redirect.
        $query = db_query("select * from redirect where redirect_source__path = '" . $alias . "'");
        $duplicate = $query->fetchAll();
        if (!$duplicate) {

          // Now we just need to create the redirect.
          $this->output()->writeln($redirect . ' <-- ' . $alias);
          try {
            Redirect::create([
              'redirect_source' => ['path' => $alias, 'query' => []],
              'redirect_redirect' => $redirect,
              'status_code' => '301',
            ])->save();
          } catch (\Exception $e) {
            $this->output()->writeln('Ooops!');
          }
        }
        else {
          $this->output()->writeln('Duplicate: ' . $redirect . ' == ' . $alias);
        }
      }
      else {
        $this->output()->writeln('No match (A)');
      }
    }
    else {
      $this->output()->writeln('No match (B)');
    }
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

    // Get path to CSV.
    $csv_path = \Drupal::root() . '/sites/default/files/redirects/' . $filename;
    $this->output()->writeln('CSV data source: ' . $csv_path);

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
          $csv_data[] = $csv_row;
        }

        // Increment row.
        $row++;
      }
      fclose($handle);
    }

    return $csv_data;
  }

  /**
   * Fix faculty references.
   *
   * @command uc-temp:fix-faculty-ref
   * @aliases uc-temp-ffr
   * @usage uc-temp:fix-faculty-ref
   *   Change from uuid to external id.
   */
  public function fixFacultyReference() {

    // Process all contact entities.
    $database = \Drupal::service('database');
    // @var \Drupal\Core\Database\Query\Select $query //
    $query = $database->select('paragraph__field_faculty_reference', 'd');
    $query->addField('d', 'field_faculty_reference_value');
    $results = $query->execute();
    $rows = $results->fetchAll();

    if ($rows) {
      $this->output()->writeln('Fixing faculty reference ids.');
      foreach ($rows as $delta => $row) {
        $cdr_faculty_service = \Drupal::service('uc_cdr_client.cdr_faculty_service');
        $data = $cdr_faculty_service->getFacultyMember($row->field_faculty_reference_value);
        if ($data && $data['external_id']) {
          $count = $database->update('paragraph__field_faculty_reference')
            ->fields([
              'field_faculty_reference_value' => $data['external_id'],
            ])
            ->condition('field_faculty_reference_value', $row->field_faculty_reference_value)
            ->execute();
          $this->output()->writeln($row->field_faculty_reference_value . ' --> ' . $data['external_id']);
        }
      }
    }

    $query = $database->select('paragraph__field_ids', 'd');
    $query->condition('d.bundle', 'faculty_callout_selected');
    $query->addField('d', 'field_ids_value');
    $results = $query->execute();
    $rows = $results->fetchAll();

    if ($rows) {
      $this->output()->writeln('Fixing callout selected ids.');
      foreach ($rows as $delta => $row) {
        $cdr_faculty_service = \Drupal::service('uc_cdr_client.cdr_faculty_service');
        $data = $cdr_faculty_service->getFacultyMember($row->field_ids_value);
        if ($data && $data['external_id']) {
          $count = $database->update('paragraph__field_ids')
            ->fields([
              'field_ids_value' => $data['external_id'],
            ])
            ->condition('field_ids_value', $row->field_ids_value)
            ->execute();
          $this->output()->writeln($row->field_ids_value . ' --> ' . $data['external_id']);
        }
      }
    }

    $this->output()->writeln('-OK-');
  }

  /**
   * Fix publication dates.
   *
   * @command uc-temp:fix-pub-date
   * @aliases uc-temp-fpd
   * @usage uc-temp:fix-pub-date
   *   Change from publication dates from timestamp to YYYY-MM-DD.
   */
  public function fixPublicationDate() {

    // Select all publication date field rows.
    $database = \Drupal::service('database');
    // @var \Drupal\Core\Database\Query\Select $query //
    $query = $database->select('node__field_publication_date', 'd');
    $query->addField('d', 'field_publication_date_value');
    $results = $query->execute();
    $rows = $results->fetchAll();

    if ($rows) {
      $this->output()->writeln('Fixing publication dates.');
      foreach ($rows as $delta => $row) {

        // Determine if value is invalid.
        if ($row->field_publication_date_value && !strpos($row->field_publication_date_value, '-')) {
          $revised = date('Y-m-d', $row->field_publication_date_value);

          $count = $database->update('node__field_publication_date')
            ->fields([
              'field_publication_date_value' => $revised,
            ])
            ->condition('field_publication_date_value', $row->field_publication_date_value)
            ->execute();
          $this->output()->writeln('Fixing: ' . $row->field_publication_date_value . ' --> ' . $revised);
        }
      }
    }

    $this->output()->writeln('-OK-');
  }
}
