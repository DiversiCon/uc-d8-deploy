<?php

namespace Drupal\uc_cdr_client\Controller;

use Drupal\config_pages\Entity\ConfigPages;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\uc_cdr_client\CdrClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Uuid\Uuid;

/**
 * Class CentralDataRepoClientController.
 */
class CentralDataRepoClientController extends ControllerBase {


  /**
   * @var \Drupal\uc_cdr_client\CdrClient
   */
  private $cdrClient;

  /**
   * CentralDataRepoClientController constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\uc_cdr_client\CdrClient $cdr_client
   */
  public function __construct(CdrClient $cdrClient) {
    $this->cdrClient = $cdrClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uc_cdr_client.cdr_client')
    );
  }

  /**
   * Render event page.
   */
  public function renderEventPage($event_id) {
    $event_id = (Uuid::isValid($event_id)) ? $event_id : $this->cdrClient->getUuidFromPathAlias($event_id);

    $data = $this->cdrClient->getEventData($event_id);

    if ($data == NULL) {
      throw new NotFoundHttpException();
    }

    $attributes = $data['data']['attributes'];

    $event_data['id'] = $data['data']['id'];
    $event_data['nid'] = $attributes['drupal_internal__nid'];
    $event_data['title'] = $attributes['title'];
    $event_data['status'] = $attributes['field_event_status'];
    $event_data['image_url'] = (isset($data['included'][0]['attributes']['uri']['url'])) ? $this->cdrClient->api_url . '/' . $data['included'][0]['attributes']['uri']['url'] : '';
    $event_data['location'] = $attributes['field_location'];
    $event_data['start_time'] = $attributes['field_event_dates']['value'];
    $event_data['end_time'] = $attributes['field_event_dates']['end_value'];
    $event_data['when'] = $this->getWhenString($event_data['start_time'], $event_data['end_time']);
    $event_data['description'] = $attributes['field_description']['value'];
    $event_data['contact'] = $attributes['field_contact'];
    $event_data['notes'] = $attributes['field_notes'];
    $event_data['speaker_name'] = $attributes['field_speaker_name'];
    $event_data['speaker_affiliation'] = $attributes['field_speaker_affiliation'];

    return [
      '#theme' => 'cdr_event_detail',
      '#event_data' => $event_data,
    ];
  }

  /**
   * Generates an ics file for the given event.
   *
   * @param $event_id
   *   The guid for the event.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function getEventCalendar($event_id) {
    // Retrieve the event data.
    $data = $this->cdrClient->getEventData($event_id)['data'];

    // Extract the needed event data points.
    $event_name = $data['attributes']['title'];
    $event_location = $data['attributes']['field_location'];

    // Convert the event start time to ics required format.
    $event_start_datetime = \DateTime::createFromFormat(
      'Y-m-d\TH:i:sP',
      $data['attributes']['field_event_dates']['value']
    );
    $calendar_start_time = $event_start_datetime->format('Ymd\THi00');

    // Convert the event end time to ics required format.
    $event_end_datetime = \DateTime::createFromFormat(
      'Y-m-d\TH:i:sP',
      $data['attributes']['field_event_dates']['end_value']
    );
    $calendar_end_time = $event_end_datetime->format('Ymd\THi00');

    // Create the calendar event.
    $calendar_event = <<<EVENT
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
SUMMARY:$event_name
DTSTART:$calendar_start_time
DTEND:$calendar_end_time
LOCATION:$event_location
DESCRIPTION: $event_name
STATUS:CONFIRMED
SEQUENCE:0
END:VEVENT
END:VCALENDAR
EVENT;

    // Create the response.
    $response = new Response();

    // Set needed headers for an ics file.
    $response->headers->set('Cache-Control', 'private');
    $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename=event.ics');

    // Set the response content.
    $response->setContent($calendar_event);

    return $response;
  }

  /**
   * @param $url_alias
   *   The uuid of the faculty member.
   *
   * @return array
   *   Render array.
   */
  public function renderFacultyProfilePage($url_alias) {

    // Leverage the faculty service (DI would be better).
    /* @var \Drupal\uc_cdr_client\Service\CdrFacultyService $cdr_faculty_service */
    $cdr_faculty_service = \Drupal::service('uc_cdr_client.cdr_faculty_service');

    // Get render array for faculty profile.
    $render = $cdr_faculty_service->renderFacultyMember($url_alias);

    if ($render) {
      // Add caching information. Cache based on each unique URL, and based on
      // the node id cache tag.
      $render['#cache'] = [
        'max-age' => 900,
        'contexts' => [
          'url',
        ],
        'tags' => [
          'node:' . $render['#faculty_data']['drupal_id'],
          'cdr_client',
          'cdr_client:faculty',
        ],
      ];

      return $render;
    }
    else {
      return NULL;
    }
  }

  /**
   * @param $url_alias
   *   The uuid of the faculty member.
   *
   * @return array
   *   Render array.
   */
  public function renderFacultyPublicationsPage($url_alias) {
    $faculty_id = (Uuid::isValid($url_alias)) ? $url_alias : $this->cdrClient->getUuidFromPathAlias($url_alias);

    $faculty_config = ConfigPages::load('faculty');
    $headline = NULL;
    $hide_topics = 'false';

    if ($faculty_config != NULL) {
      if (!$faculty_config->get('field_headline_text')->isEmpty()) {
        $headline = $faculty_config->get('field_headline_text')->getValue()[0]['value'];
      }

      if (!$faculty_config->get('field_hide')->isEmpty()) {
        $hide_topics = $faculty_config->get('field_hide')->getValue()[0]['value'];
      }
    }

    return [
      '#theme' => 'cdr_publications',
      '#publications_data' => [
        'uuid' => $faculty_id,
        'headline' => $headline,
        'return_url' => '/faculty/' . $url_alias,
        'author_type' => 'faculty',
        'hide_topics' => $hide_topics,
      ],
    ];
  }

  /**
   * Build 'when' string.
   */
  private function getWhenString($start, $end) {
    $start_date = date_create_from_format('Y-m-d\TH:i:se', $start);
    $end_date = date_create_from_format('Y-m-d\TH:i:se', $end);

    if ($start_date->format('Y-m-d') == $end_date->format('Y-m-d')) {
      $when_string = $start_date->format('l, F j, Y g:ia') . ' - ' . $end_date->format('g:ia');
    }
    else {
      $when_string = $start_date->format('l, F j, Y g:ia') . ' - ' . $end_date->format('l, F j, Y g:ia');
    }

    return $when_string;
  }

  /**
   * Handler for autocomplete request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function handleEventAutocomplete(Request $request) {

    // Extract search value from request and get endpoint data.
    $title = $request->query->get('q');
    $data = $this->cdrClient->getEventsByTitle($title, TRUE);

    // Process results get relevant data.
    $results = [];
    foreach ($data['data'] as $delta => $event) {
      $value = $event['id'];
      $label = $event['attributes']['title'];
      $results[] = [
        'value' => $label . ' (' . $value . ')',
        'label' => $label,
      ];
    }

    // Prepare response.
    $cache = new CacheableMetadata();
    $cache->setCacheContexts(['url.path', 'url.query_args']);
    $cache->setCacheMaxAge(300);
    $response = new CacheableJsonResponse($results);
    $response->addCacheableDependency($cache);
    $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

    return $response;
  }

  /**
   * Handler for autocomplete request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function handleFacultyAutocomplete(Request $request) {

    // Extract search value from request and get endpoint data.
    $title = $request->query->get('q');
    $data = $this->cdrClient->getFacultyByTitle($title);

    // Process results get relevant data.
    $results = [];
    foreach ($data['data'] as $delta => $faculty) {
      $value = $faculty['attributes']['field_external_id'];
      $label = $faculty['attributes']['title'];
      $results[] = [
        'value' => $label . ' (' . $value . ')',
        'label' => $label,
      ];
    }

    // Prepare response.
    $cache = new CacheableMetadata();
    $cache->setCacheContexts(['url.path', 'url.query_args']);
    $cache->setCacheMaxAge(300);
    $response = new CacheableJsonResponse($results);
    $response->addCacheableDependency($cache);
    $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

    return $response;
  }

  /**
   * Handler for autocomplete request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function handleDepartmentAutocomplete(Request $request) {

    // Extract search value from request and get endpoint data.
    $title = $request->query->get('q');
    $data = $this->cdrClient->getDepartmentsByTitle($title);

    // Process results get relevant data.
    $results = [];
    foreach ($data['data'] as $delta => $department) {
      $value = $department['id'];
      $label = $department['attributes']['name'];
      $results[] = [
        'value' => $label . ' (' . $value . ')',
        'label' => $label,
      ];
    }

    // Prepare response.
    $cache = new CacheableMetadata();
    $cache->setCacheContexts(['url.path', 'url.query_args']);
    $cache->setCacheMaxAge(300);
    $response = new CacheableJsonResponse($results);
    $response->addCacheableDependency($cache);
    $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

    return $response;
  }

}
