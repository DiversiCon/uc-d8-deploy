<?php

namespace Drupal\uc_cdr_client;

use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class CdrClient {

  /**
   * @var string
   */
  public $api_url;

  /**
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * CdrClient constructor.
   *
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   */
  public function __construct($http_client_factory) {
    $this->api_url = $this->getApiUrl();
    $this->client = $http_client_factory->fromOptions([
      'base_uri' => $this->api_url,
    ]);
  }

  /**
   * Get event data.
   *
   * @param int $event_id
   * @param bool $filter_site
   *
   * @return array
   */
  public function getEventData($event_id, $filter_site = FALSE) {

    $request = [
      'query' => [
        'include' => 'field_event_image',
      ],
    ];

    // Add instance selection as needed.
    if ($filter_site) {
      $instance_id = $this->getInstanceId();
      $request['query']['_bsdInstanceId'] = $instance_id;
    }

    $data = NULL;

    try {
      $response = $this->client->get('/api/node/event/' . $event_id, $request);
      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      if ($e->getCode() !== 404) {
        \Drupal::logger('uc_cdr_client')
          ->error('An error occurred when requesting data from the CDR: ' .
            $e->__toString());
      }
      else {
        \Drupal::logger('uc_cdr_client')
          ->error('Event ID not found: ' . $event_id);
      }
    }

    return $data;
  }

  /**
   * Get event title.
   *
   * @param int $event_id
   * @param bool $filter_site
   *
   * @return array
   */
  public function getEventTitle($event_id, $filter_site = FALSE) {

    $request = [
      'query' => [
        'fields' => 'title',
      ],
    ];

    // Add instance selection as needed.
    if ($filter_site) {
      $instance_id = $this->getInstanceId();
      $request['query']['_bsdInstanceId'] = $instance_id;
    }

    $data = NULL;

    try {
      $response = $this->client->get('/api/node/event/' . $event_id, $request);
      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      if ($e->getCode() !== 404) {
        \Drupal::logger('uc_cdr_client')
          ->error('An error occurred when requesting data from the CDR: ' .
            $e->__toString());
      }
      else {
        \Drupal::logger('uc_cdr_client')
          ->error('Event ID not found: ' . $event_id);
      }
    }

    $title = '';
    if ($data != NULL && isset($data['data']['attributes']['title']) && $data['data']['attributes']['title']) {
      $title = $data['data']['attributes']['title'];
    }

    return $title;
  }

  /**
   * Get events by title.
   *
   * @param string $title
   * @param bool $filter_site
   *
   * @return array
   */
  public function getEventsByTitle($title, $filter_site = FALSE) {

    // Build query string values.
    $request = [
      'query' => [
        'filter[title-filter][condition][path]' => 'title',
        'filter[title-filter][condition][operator]' => 'CONTAINS',
        'filter[title-filter][condition][value]' => $title,
        'page[limit]' => 10,
      ],
    ];

    // Add instance selection as needed.
    if ($filter_site) {
      $instance_id = $this->getInstanceId();
      $request['query']['_bsdInstanceId'] = $instance_id;
    }

    // Request data, decode response and return results.
    $response = $this->client->get('/api/node/event', $request);
    $data = Json::decode($response->getBody());
    return $data;
  }

  /**
   * Get event ids by page.
   *
   * @param int $page
   *
   * @return array
   */
  public function getEventIdsPaged($page) {
    if (is_null($page)) {
      $page = 0;
    }
    $page_size = 50;
    $ids = [];

    $request = [
      'query' => [
        'fields[node--event]' => 'id',
        'page[limit]' => $page_size,
        'page[offset]' => $page_size * $page,
      ],
    ];

    // Add instance filter.
    $instance_id = $this->getInstanceId();
    $request['query']['_bsdInstanceId'] = $instance_id;

    try {
      $response = $this->client->get('/api/node/event', $request);
      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      $data = [];
    }

    if (isset($data['data']) && !empty($data['data'])) {
      foreach ($data['data'] as $delta => $row) {
        if (isset($row['id']) && $row['id']) {
          $ids[] = $row['id'];
        }
      }
    }

    if (empty($ids)) {
      $ids = NULL;
    }

    return $ids;
  }

  /**
   * Get image URL.
   *
   * @param int $image_id
   *
   * @return string
   */
  public function getImageURL($image_id) {
    $response = $this->client->get('/api/file/file/' . $image_id, [
      'query' => [
        "fields['file--file']" => 'uri',
      ],
    ]);

    $data = Json::decode($response->getBody());
    $imageURL = $this->api_url . '/' . $data['attributes']['uri']['url'];
    return $imageURL;
  }

  /**
   * Get faculty profile data based on an id and return the resulting data.
   *
   * @param $faculty_id
   *   The id of the faculty member, either uuid or field_external_id.
   * @param $is_external_id
   *   Indicates whether or not faculty_id is an external id (vs. uuid).
   *
   * @return array
   *   Faculty member data.
   */
  public function getFacultyProfileData($faculty_id, $is_external_id = FALSE) {
    $data = NULL;
    $query = [];
    $endpoint = '/api/node/faculty';

    // Define common fields to include in the result.
    $fields_to_include = 'field_research_and_scholarly_int,'
      . 'field_graduate_program_websites,'
      . 'field_awards,'
      . 'field_education_and_training,'
      . 'field_websites,'
      . 'field_academic_appointment,'
      . 'field_faculty_image,'
      . 'field_primary_section,'
      . 'field_clinical_interests';
    // Add included fields to query parameters.
    $query['include'] = $fields_to_include;

    // We slightly adjust things for external ids.
    if ($is_external_id) {
      $endpoint .= '/' . $faculty_id;
    }
    else {
      $query['filter[drupal_internal__nid]'] = $faculty_id;
    }

    // Give it a whirl.
    try {
      $response = $this->client->get($endpoint, ['query' => $query]);
      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      if ($e->getCode() !== 404) {
        \Drupal::logger('uc_cdr_client')
          ->error('An error occurred when requesting data from the CDR: ' .
            $e->__toString());
      }
      else {
        \Drupal::logger('uc_cdr_client')
          ->error('Faculty ID not found: ' . $faculty_id);
      }
    }

    // Do one final cleanup to remove the cardinal index of the single value.
    if (isset($data['data'][0])) {
      $data['data'] = $data['data'][0];
    }

    return $data;
  }

  /**
   * Returns up to the first 50 publications for the given uuid.
   *
   * @param $uuid
   *
   * @return array
   *   Publication data.
   */
  public function getPublications($uuid) {
    $query = [
      'filter[field_associated_people.id]' => $uuid,
      'sort' => '-field_year,-field_publication_date',
    ];

    $data = NULL;

    try {
      $response = $this->client->get('/api/node/publication', ['query' => $query]);

      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      \Drupal::logger('uc_cdr_client')
        ->error('An error occurred when requesting publication data from the CDR: ' .
          $e->__toString());
    }

    return $data;
  }

  /**
   * Retrieve faculty information for the given faculty ids.
   *
   * @param array $faculty_ids
   *   Array of faculty external ids.
   *
   * @return array|null
   */
  public function getFaculty(array $faculty_ids) {
    $fields_to_include = 'field_first_name,'
      . 'field_last_name,'
      . 'field_faculty_image,'
      . 'field_show_photo,'
      . 'field_degree,'
      . 'field_websites,'
      . 'field_administrative_title,'
      . 'field_academic_appointment,'
      . 'field_external_id,'
      . 'path';

    $relationship_fields = 'field_faculty_image,'
      . 'field_websites,'
      . 'field_academic_appointment';

    $query = [
      'include' => $relationship_fields,
      'fields[node--faculty]' => $fields_to_include,
      'filter[id-group][group][conjunction]' => 'OR',
    ];

    for ($i = 0; $i < count($faculty_ids); $i++) {
      $faculty_id = $faculty_ids[$i];

      $query["filter[faculty-id-filter-$i][condition][path]"] = "field_external_id";
      $query["filter[faculty-id-filter-$i][condition][value]"] = "$faculty_id";
      $query["filter[faculty-id-filter-$i][condition][memberOf]"] = "id-group";
    }

    $data = NULL;
    try {
      $response = $this->client->get('/api/node/faculty', ['query' => $query]);

      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      if ($e->getCode() !== 404) {
        \Drupal::logger('uc_cdr_client')
          ->error('An error occurred when requesting data from the CDR: ' .
            $e->__toString());
      }
      else {
        \Drupal::logger('uc_cdr_client')
          ->error('Faculty ID not found: ' . $faculty_id);
      }
    }

    return $data;
  }

  /**
   * Get faculty title.
   *
   * @param int $faculty_id
   *   The id of the faculty member, either uuid or field_external_id.
   * @param $filter_field
   *   Allows override of using id as the filter field. For example, to use
   *   'field_external_id' instead of id, simply pass that value.
   *
   * @return array
   */
  public function getFacultyTitle($faculty_id, $filter_field = 'id') {
    $endpoint = '/api/node/faculty';

    // Build up request.
    $request = [
      'query' => [
        'fields[node--faculty]' => 'title',
      ],
    ];

    // Set filter scheme.
    $filter = 'filter[' . $filter_field . ']';
    $request['query'][$filter] = $faculty_id;

    // Give it a whirl.
    try {
      $response = $this->client->get($endpoint, $request);
      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      $data = [];
    }

    // Extract the title.
    $title = '';
    $item = [];
    if (isset($data['data'][0])) {
      $item = $data['data'][0];
    }

    if (isset($item['attributes']['title']) && $item['attributes']['title']) {
      $title = $item['attributes']['title'];
    }

    return $title;
  }

  /**
   * Get faculty by title.
   *
   * @param string $title
   *
   * @return array
   */
  public function getFacultyByTitle($title) {

    // Build query string values.
    $request = [
      'query' => [
        'filter[title-filter][condition][path]' => 'title',
        'filter[title-filter][condition][operator]' => 'CONTAINS',
        'filter[title-filter][condition][value]' => $title,
        'page[limit]' => 10,
      ],
    ];

    // Request data, decode response and return results.
    $response = $this->client->get('/api/node/faculty', $request);
    $data = Json::decode($response->getBody());
    return $data;
  }

  /**
   * Get faculty ids by page.
   *
   * @param int $page
   *
   * @return array
   */
  public function getFacultyIdsPaged($page) {
    if (is_null($page)) {
      $page = 0;
    }
    $page_size = 50;
    $ids = [];

    $request = [
      'query' => [
        'fields[node--faculty]' => 'id',
    // 'filter[field_primary_department.id]' => 'c095965b-268e-4372-b8b8-3e4619118dea',
        'page[limit]' => $page_size,
        'page[offset]' => $page_size * $page,
      ],
    ];

    try {
      $response = $this->client->get('/api/node/faculty', $request);
      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      $data = [];
    }

    if (isset($data['data']) && !empty($data['data'])) {
      foreach ($data['data'] as $delta => $row) {
        if (isset($row['id']) && $row['id']) {
          $ids[] = $row['id'];
        }
      }
    }

    if (empty($ids)) {
      $ids = NULL;
    }

    return $ids;
  }

  /**
   * Get department title.
   *
   * @param int $department_id
   *
   * @return array
   */
  public function getDepartmentTitle($department_id) {

    $request = [
      'query' => [
        'fields' => 'name',
      ],
    ];

    try {
      $response = $this->client->get('/api/taxonomy_term/faculty_departments/' . $department_id, $request);
      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      $data = [];
    }

    $title = '';
    if (isset($data['data']['attributes']['name']) && $data['data']['attributes']['name']) {
      $title = $data['data']['attributes']['name'];
    }

    return $title;
  }

  /**
   * Get departments by title.
   *
   * @param string $title
   *
   * @return array
   */
  public function getDepartmentsByTitle($title) {

    // Build query string values.
    $request = [
      'query' => [
        'filter[title-filter][condition][path]' => 'name',
        'filter[title-filter][condition][operator]' => 'CONTAINS',
        'filter[title-filter][condition][value]' => $title,
        'page[limit]' => 10,
      ],
    ];

    // Request data, decode response and return results.
    $response = $this->client->get('/api/taxonomy_term/faculty_departments', $request);
    $data = Json::decode($response->getBody());
    return $data;
  }

  /**
   * Get API URL.
   *
   * @return string
   */
  public function getApiUrl() {
    $config = \Drupal::config('uc_cdr_client.settings');
    $api_url = $config->get('api_url');
    return $api_url;
  }

  /**
   * Get BSD Instance ID.
   *
   * @return string
   */
  public function getInstanceId() {
    $site_path_elements = explode('/', \Drupal::service('site.path'));
    $instance_id = array_pop($site_path_elements);
    return $instance_id;
  }

  /**
   * Get UUID from a path alias.
   *
   * @return string
   */
  public function getUuidFromPathAlias($pathAlias) {
    $request = [
      'query' => [
        'path' => $pathAlias,
      ],
    ];

    try {
      $response = $this->client->get('/router/translate-path', $request);
      $data = Json::decode($response->getBody());
    }
    catch (\Exception $e) {
      throw new NotFoundHttpException();
    }

    return $data['entity']['uuid'];
  }

}
