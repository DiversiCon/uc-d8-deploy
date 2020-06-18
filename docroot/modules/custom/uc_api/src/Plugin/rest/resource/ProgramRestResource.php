<?php

namespace Drupal\uc_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a resource to get program data.
 *
 * @RestResource(
 *   id = "program_rest_resource",
 *   label = @Translation("Program resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/programs"
 *   }
 * )
 */
class ProgramRestResource extends ResourceBase {

  private $allDivisions = ['All' => 'All'];
  private $allTypes = ['All' => 'All'];

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    $programs = [];
    $divisions = [];
    $types = [];

    $database = \Drupal::service('database');
    $query = $database->select('node_field_data', 'n');
    $query->condition('n.status', 1);
    $query->condition('n.type', 'program');
    $query->addField('n', 'nid');
    $query->addField('n', 'title');
    $query->addJoin('LEFT OUTER', 'node__body', 'nb', 'n.nid = nb.entity_id');
    $query->addField('nb', 'body_value', 'text');
    $query->orderBy('n.title');
    $results = $query->execute();
    $rows = $results->fetchAll();

    if ($rows) {
      foreach ($rows as $delta => $row) {
        $programs[] = [
          'name' => $row->title,
          'text' => $row->text,
          'links' => $this->loadLinks($row->nid),
          'division' => $this->loadDivisions($row->nid),
          'types' => $this->loadTypes($row->nid),
        ];
      }
    }

    asort($this->allDivisions);
    foreach ($this->allDivisions as $delta => $division) {
      $divisions[] = ['name' => $division];
    }

    asort($this->allTypes);
    foreach ($this->allTypes as $delta => $type) {
      $types[] = ['name' => $type];
    }

    $data = [
      'divisions' => $divisions,
      'types' => $types,
      'programs' => $programs,
    ];

    // Setup response and add cache-ability metadata.
    $response = new ResourceResponse($data, 200);
    $list_tags = \Drupal::entityTypeManager()->getDefinition('node')->getListCacheTags();
    $response->getCacheableMetadata()->addCacheTags($list_tags);

    return $response;
  }

  /**
   *
   */
  private function loadLinks($nid) {
    $links = [];

    $database = \Drupal::service('database');
    $query = $database->select('node__field_links_ultd', 'n');
    $query->condition('n.entity_id', $nid);
    $query->addField('n', 'entity_id');
    $query->addField('n', 'field_links_ultd_title', 'title');
    $query->addField('n', 'field_links_ultd_uri', 'url');
    $query->orderBy('n.delta');
    $results = $query->execute();
    $rows = $results->fetchAll();

    if ($rows) {
      foreach ($rows as $delta => $row) {
        $links[] = [
          'title' => $row->title,
          'url' => $row->url,
          'target' => '_self',
        ];
      }
    }
    return $links;
  }

  /**
   *
   */
  private function loadDivisions($nid) {
    $divisions = [];

    $database = \Drupal::service('database');
    $query = $database->select('node__field_tag_reference', 'n');
    $query->condition('n.entity_id', $nid);
    $query->addField('n', 'field_tag_reference_target_id', 'tid');
    $query->addJoin('LEFT OUTER', 'taxonomy_term_field_data', 'nb', 'n.field_tag_reference_target_id = nb.tid');
    $query->addField('nb', 'name', 'name');
    $query->orderBy('n.delta');
    $results = $query->execute();
    $rows = $results->fetchAll();

    if ($rows) {
      foreach ($rows as $delta => $row) {
        $divisions[] = $row->name;
        $this->allDivisions[$row->name] = $row->name;
      }
    }
    if (count($divisions) <= 1) {
      if (isset($divisions[0])) {
        $divisions = $divisions[0];
      }
    }

    return $divisions;
  }

  /**
   *
   */
  private function loadTypes($nid) {
    $types = [];

    $database = \Drupal::service('database');
    $query = $database->select('node__field_program_type', 'n');
    $query->condition('n.entity_id', $nid);
    $query->addField('n', 'field_program_type_target_id', 'tid');
    $query->addJoin('LEFT OUTER', 'taxonomy_term_field_data', 'nb', 'n.field_program_type_target_id = nb.tid');
    $query->addField('nb', 'name', 'name');
    $query->orderBy('n.delta');
    $results = $query->execute();
    $rows = $results->fetchAll();

    if ($rows) {
      foreach ($rows as $delta => $row) {
        $types[] = $row->name;
        $this->allTypes[$row->name] = $row->name;
      }
    }

    return $types;
  }

}
