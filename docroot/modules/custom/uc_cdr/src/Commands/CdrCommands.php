<?php

namespace Drupal\uc_cdr\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class CdrCommands extends DrushCommands {

  /**
   * Remove paragraphs of type.
   *
   * @option type
   *   Type of paragraph to delete (or 'all' for all types).
   * @usage drush uc_cdr:delete-paragraphs --type="academic_appointment"
   *   Delete paragraphs of type 'academic_appointment' in system.
   *
   * @command uc_cdr:delete-paragraphs
   * @aliases delete-paragraphs, dp
   */
  public function deleteParagraphs($options = ['type' => NULL]) {
    if ($options['type'] == NULL) {
      throw new \Exception('Please enter a paragraph type.');
    }

    if ($options['type'] == 'all') {
      $types = $this->_get_paragraph_types();
    }
    else {
      $types = [$options['type']];
    }

    foreach ($types as $type) {
      $this->output()->writeln('Attempting to delete all paragraphs of type: ' . $type);
      $paragraphs = \Drupal::entityTypeManager()
        ->getStorage('paragraph')
        ->loadByProperties([
          'type' => $type,
        ]);

      if (count($paragraphs) < 1) {
        $this->output()->writeln('No paragraphs of type ' . $type . ' available to delete.');
        continue;
      }

      $i = 0;
      foreach ($paragraphs as $paragraph) {
        $paragraph->delete();
        $i++;
      }

      $this->output()->writeln('Successfully deleted ' . $i . ' ' . $type . ' paragraph(s)');
    }
  }

  /**
   * Process Faculty JSON File.
   *
   * @option file-path
   *   Path to the file to process.
   * @usage drush uc_cdr:process --file-path="/example/path/filename.json"
   *   Process faculty data in /example/path/filename.json
   *
   * @command uc_cdr:process-faculty-file
   * @aliases process-faculty-file, pff
   */
  public function processFacultyFile($options = ['file-path' => NULL]) {
    if ($options['file-path'] == NULL || !is_readable($options['file-path'])) {
      throw new \Exception('Please enter a valid file path.');
    }

    $this->logger()->info('Attempting to process faculty data file: ' . $options['file-path']);
    $zip = new \ZipArchive();
    if ($res = $zip->open($options['file-path'])) {
      // @TODO: Allow for different filename
      if ($fh = $zip->getStream('BSDWebsiteFaculty.json')) {
        $json = stream_get_contents($fh);
        $data = json_decode($json);
        if (json_last_error()) {
          $converted_json = $this->_convert_utf16_to_utf8($json);
          $final_json = $converted_json;
        }
        else {
          $final_json = $json;
        }
        if (file_put_contents('public://faculty_data.json', $final_json)) {
          $this->logger()->info('Successfully processed faculty data file: ' . $options['file-path']);
          $publication_json = $this->_get_publication_json('public://faculty_data.json');
          if (file_put_contents('public://publication_data.json', $publication_json)) {
            $this->logger()->info('Successfully processed publication data file: ' . $options['file-path']);
            $this->output()->writeln('Successfully processed ' . $options['file-path']);
          }
          else {
            throw new \Exception('Unable to write publication data to file.');
          }
        }
        else {
          throw new \Exception('Unable to write data to file.');
        }
      }
    }
    else {
      throw new \Exception('Unable to open zip file at ' . $options['file-path']);
    }
  }

  /**
   * Identify orphaned faculty records from source JSON and remove them.
   *
   * @usage drush uc_cdr:purge-orphan-faculty
   *   Identify orphaned faculty records using source JSON and remove them.
   *
   * @command uc_cdr:purge-orphan-faculty
   * @aliases purge-orphan-faculty, pof
   */
  public function purgeOrphanFaculty($options = ['force' => NULL]) {
    $json = file_get_contents('public://faculty_data.json');
    $data = json_decode($json);

    foreach ($data as $record) {
      $ids[] = $record->SKPersonID;
    }

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    $query = $node_storage->getQuery();
    $query->condition('type', 'faculty');
    $query->exists('field_external_id');
    $query->condition('field_external_id', $ids, 'NOT IN');
    $nids = $query->execute();

    $nodes = $node_storage->loadMultiple($nids);

    if (!isset($options['force'])) {
      if (count($nodes) > (count($ids) * 0.1)) {
        throw new \Exception('Number of orphaned faculty is greater than 10% of total faculty in data set (if intended use --force).');
      }
    }

    if (count($nodes)) {
      $this->output()->writeln('Found ' . count($nodes) . ' orphaned faculty records:');
    }
    else {
      throw new \Exception('Found no orphaned faculty records.');
    }

    foreach ($nodes as $n) {
      $this->output()->writeln('Purging ' . $n->title->value . ' (NID: ' . $n->id() . ')...');

      $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
      $query = $paragraph_storage->getQuery();
      $query->condition('parent_type', $n->getEntityTypeId());
      $query->condition('parent_id', $n->id());
      $query->condition('type', $this->_get_paragraph_types(), 'IN');
      $pids = $query->execute();

      if ($pids) {
        $paragraphs = $paragraph_storage->loadMultiple($pids);
        $paragraph_storage->delete($paragraphs);
      }

      $n->delete();
      $this->logger()->info('Purged orphaned faculty record ' . $n->title->value . ' (NID: ' . $n->id() . ')');
    }
  }

  /**
   *
   */
  private function _convert_utf16_to_utf8($str) {
    $c0 = ord($str[0]);
    $c1 = ord($str[1]);

    if ($c0 == 0xFE && $c1 == 0xFF) {
      $be = TRUE;
    }
    elseif ($c0 == 0xFF && $c1 == 0xFE) {
      $be = FALSE;
    }
    else {
      return $str;
    }

    $str = substr($str, 2);
    $len = strlen($str);
    $dec = '';
    for ($i = 0; $i < $len; $i += 2) {
      $c = ($be) ? ord($str[$i]) << 8 | ord($str[$i + 1]) :
                ord($str[$i + 1]) << 8 | ord($str[$i]);
      if ($c >= 0x0001 && $c <= 0x007F) {
        $dec .= chr($c);
      }
      elseif ($c > 0x07FF) {
        $dec .= chr(0xE0 | (($c >> 12) & 0x0F));
        $dec .= chr(0x80 | (($c >> 6) & 0x3F));
        $dec .= chr(0x80 | (($c >> 0) & 0x3F));
      }
      else {
        $dec .= chr(0xC0 | (($c >> 6) & 0x1F));
        $dec .= chr(0x80 | (($c >> 0) & 0x3F));
      }
    }
    return $dec;
  }

  /**
   *
   */
  private function _get_paragraph_types() {
    $types = [
      'academic_appointment',
      'awards',
      'education_and_training',
      'graduate_program_websites',
      'websites',
    ];

    return $types;
  }

  /**
   *
   */
  private function _get_publication_json($file) {
    $json = file_get_contents($file);
    $data = json_decode($json);

    $publications = [];
    foreach ($data as $record) {
      if (!isset($record->Publications)) {
        continue;
      }
      foreach ($record->Publications as $pub) {
        $hybrid_key = $pub->PMID . ':' . $pub->Label;
        foreach (array_keys(get_object_vars($pub)) as $key) {
          $publications[$hybrid_key][$key] = $pub->{$key};
        }
        $publications[$hybrid_key]['referencedFaculty'][] = ['SKPersonID' => $record->SKPersonID];
      }
    }

    $publication_data = array_values($publications);

    $final_json = json_encode($publication_data);
    return $final_json;
  }

}
