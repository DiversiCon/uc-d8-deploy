<?php

namespace Drupal\uc_search\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\search_api\Entity\Index;
use Drupal\node\Entity\Node;
use Drupal\image\Entity\ImageStyle;

/**
 * Provides a resource to get search results data.
 *
 * @RestResource(
 *   id = "search_rest_resource",
 *   label = @Translation("Search resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/search/list/{order}/{keys}"
 *   }
 * )
 */
class SearchRestResource extends ResourceBase {

  /**
   * @var CdrClient
   */

  protected $cdrClient;

  /**
   * @var CdrFacultyService
   */

  protected $cdrFaculty;

  /**
   * Responds to GET requests.
   *
   * @param string $order
   * @param string $keys
   *
   * @return \Drupal\rest\ResourceResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\search_api\SearchApiException
   */
  public function get($order = 'date', $keys = '') {
    $items = [];
    $this->cdrClient = \Drupal::service('uc_cdr_client.cdr_client');
    $this->cdrFaculty = \Drupal::service('uc_cdr_client.cdr_faculty_service');

    // Clean up keywords.
    $keywords = str_replace('+', ' ', str_replace(',', ' ', $keys));

    // Initialize query.
    /* @var \Drupal\search_api\Query\Query $query */
    $index = Index::load('primary');
    $query = $index->query();
    $query->keys($keywords);

    // Set conditions.
    $conditions = $query->createConditionGroup('OR');
    $conditions->addCondition('status', 1);

    if ($index->getField('faculty_status') != NULL) {
      $conditions->addCondition('faculty_status', TRUE);
    }


    if ($index->getField('event_status') != NULL) {
      $conditions->addCondition('event_status', TRUE);
    }
    
    $query->addConditionGroup($conditions);

    // Pager query string parameters.
    $page = \Drupal::request()->query->get('page', 0);
    $items_per_page = \Drupal::request()->query->get('items_per_page', 10);
    $query->range($page * $items_per_page, $items_per_page);

    // Set sort order.
    if ($order !== 'date') {
      $query->sort('search_api_relevance', 'DESC');
    }
    else {
      $query->sort('created', 'DESC');
    }

    // Set one or more tags for the query.
    // @see hook_search_api_query_TAG_alter()
    // @see hook_search_api_results_TAG_alter()
    $query->addTag('uc_api_search');

    // Execute the search and process results.
    /* @var \Drupal\search_api\Query\ResultSetInterface $results */
    $results = $query->execute();
    foreach (array_keys($results->getResultItems()) as $delta => $key) {
      $score = $results->getResultItems()[$key]->getScore();
      $items[] = $this->getRowContent($key, $score);
    }

    // Set new pager data.
    $totalResults = (int) $results->getResultCount();
    $total = ($totalResults <= $items_per_page) ? 1 : ceil($totalResults / $items_per_page);
    $pager = [
      'current' => $page + 1,
      'total' => $total,
      'totalResults' => $totalResults,
    ];

    // Setup response and add cache-ability metadata.
    $data = [
      'pager' => $pager,
      'items' => $items,
    ];
    $response = new ResourceResponse($data, 200);
    $list_tags = \Drupal::entityTypeManager()->getDefinition('node')->getListCacheTags();
    $response->getCacheableMetadata()->addCacheTags($list_tags);
    $response->getCacheableMetadata()->addCacheContexts(['url.query_args']);
    $response->addCacheableDependency($index);

    return $response;
  }

  /**
   * Helper function to get search result entity.
   *
   * @param int $key
   *   In the form of entity:node/1234:und.
   * @param string $score
   *
   * @return array
   *
   * @throws |\Drupal\Core\Entity\EntityMalformedException
   */
  private function getRowContent($key, $score = 'n/a') {
    $result = [];

    // Get node data based on key.
    $parts = explode(':', $key);
    if ($parts[0] == 'entity') {
      $entity_info = explode('/', $parts[1]);
      if ($entity_info[0] = 'node') {
        /* @var \Drupal\node\NodeInterface $node */
        $node = Node::load($entity_info[1]);
        if ($node) {
          $result = $this->normalizeNodeData($node);
        }
      }
    }

    // Get faculty data based on key.
    $parts = explode('/', $key);
    if ($parts[0] == 'cdr_faculty') {
      $faculty_id = $parts[1];
      if ($faculty_id) {
        $faculty = $this->cdrFaculty->getFacultyMember($faculty_id, TRUE);
        if ($faculty) {
          $result = $this->normalizeFacultyData($faculty);
        }
      }
    }

    // Get event data based on key.
    $parts = explode('/', $key);
    if ($parts[0] == 'cdr_event') {
      $event_id = $parts[1];
      if ($event_id) {
        $event = $this->cdrClient->getEventData($event_id);
        if ($event) {
          $result = $this->normalizeEventData($event);
        }
      }
    }

    // Add score.
    $result['rank'] = $score;

    return $result;
  }

  /**
   * Normalize node based data.
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @return array
   *
   * @throws |\Drupal\Core\Entity\EntityMalformedException
   */
  private function normalizeNodeData($node) {

    // Set author as needed.
    $author = '';
    $authorurl = '#';
    if ($node->hasField('field_byline') && $node->get('field_byline')) {
      if ($node->get('field_byline')->entity) {
        $name = $node->get('field_byline')->entity->get('field_eck_name')->getValue();
        if ($name) {
          $author = trim(implode(' ', $name[0]));
        }
        $authorurl = $node->get('field_byline')->entity->toUrl()->setAbsolute()->toString(TRUE)->getGeneratedUrl();
      }
    }

    // Headline/subheadline fields may not always be present.
    $headline = '';
    if ($node->hasField('field_headline_text') && $node->get('field_headline_text')) {
      $headline = (string) $node->get('field_headline_text')->value;
    }

    $subheadline = '';
    if ($node->hasField('field_subheadline_text') && $node->get('field_subheadline_text')) {
      $subheadline = (string) $node->get('field_subheadline_text')->value;
    }

    // Get image info.
    $image_field = NULL;
    if ($node->hasField('field_image_main') && $node->get('field_image_main')) {
      $image_field = $node->get('field_image_main');
    }
    $image_info = $this->getImageInfo($image_field);

    // Get section ID.
    $category = NULL;
    if ($node->hasField('field_section') && !$node->get('field_section')->isEmpty()) {
      $category = $node->get('field_section')->target_id;
    }

    // Get URL.
    $url = $node->toUrl()->setAbsolute()->toString(TRUE)->getGeneratedUrl();
    $authored_url = '';
    if ($node->bundle() == 'external') {
      if ($node->get('field_link_single')->getValue()) {
        $authored_url = $node->get('field_link_single')->getValue()[0]['uri'];
      }
    }

    // Get node data.
    $data = [
      'nid' => $node->id(),
      'title' => $headline,
      'description' => $subheadline,
      'author' => $author,
      'authorurl' => $authorurl,
      'date' => date('F j, Y', $node->get('created')->value),
      'datetime' => date('Y-m-d', $node->get('created')->value),
      'categoryid' => $category,
      'image' => $image_info['image'],
      'imagealt' => $image_info['alt'],
      'url' => $url,
      'authored_url' => $authored_url,
    ];

    return $data;
  }

  /**
   * Normalize faculty based data.
   *
   * @param array $faculty
   *
   * @return array
   *
   * @throws |\Drupal\Core\Entity\EntityMalformedException
   */
  private function normalizeFacultyData($faculty) {

    // Build academic titles text.
    $academic_titles = '';
    if ($faculty['academic_titles']) {
      foreach ($faculty['academic_titles'] as $delta => $title) {
        $academic_titles .= $title['title'];
        if ($title['department']) {
          $academic_titles .= ' of ' . $title['department'];
        }
        $academic_titles .= '<br />';
      }
    }

    // Get node URL.
    $data = [
      'nid' => $faculty['faculty_id'],
      'title' => $faculty['faculty_title'],
      'description' => $academic_titles,
      'author' => '',
      'authorurl' => '#',
      'date' => '',
      'datetime' => '',
      'categoryid' => '',
      'image' => $faculty['photo'],
      'imagealt' => $faculty['name'],
      'url' => '/faculty' . $faculty['pathAlias'],
    ];

    return $data;

  }

  /**
   * Normalize event based data.
   *
   * @param array $event
   *
   * @return array
   *
   * @throws |\Drupal\Core\Entity\EntityMalformedException
   */
  private function normalizeEventData($event) {

    $event_data = $event['data'];

    // Build up a good description.
    $description = '';
    if (isset($event_data['attributes']['field_event_dates']['value']) && $event_data['attributes']['field_event_dates']['value']) {
      $time = strtotime($event_data['attributes']['field_event_dates']['value']);
      $description .= '<strong>When:</strong> ' . date('l, F j, Y', $time) . '<br />';
    }

    if (isset($event_data['attributes']['field_speaker_name']) && $event_data['attributes']['field_speaker_name']) {
      $description .= '<strong>Who:</strong> ' . $event_data['attributes']['field_speaker_name'];
      if (isset($event_data['attributes']['field_speaker_affiliation']) && $event_data['attributes']['field_speaker_affiliation']) {
        $description .= ', ' . $event_data['attributes']['field_speaker_affiliation'];
      }
      $description .= '<br />';
    }
    if (isset($event_data['attributes']['field_location']) && $event_data['attributes']['field_location']) {
      $description .= '<strong>Where:</strong> ' . $event_data['attributes']['field_location'] . '<br />';
    }

    // Get image.
    $image = '';
    if (isset($event_data['relationships']['field_event_image']['data']['id']) && $event_data['relationships']['field_event_image']['data']['id']) {
      $image_id = $event_data['relationships']['field_event_image']['data']['id'];
      foreach ($event['included'] as $delta => $item) {
        if ($item['id'] == $image_id) {
          if (isset($item['links']['event_image']['href']) && $item['links']['event_image']['href']) {
            $image = $item['links']['event_image']['href'];
          }
        }
      }
    }

    // Get node URL.
    $data = [
      'nid' => $event_data['id'],
      'title' => $event_data['attributes']['title'],
      'description' => $description,
      'author' => '',
      'authorurl' => '#',
      'date' => '',
      'datetime' => '',
      'categoryid' => '',
      'image' => $image,
      'imagealt' => $event_data['attributes']['field_event_image_caption'],
      'url' => '/event' . $event_data['attributes']['path']['alias'],
    ];

    return $data;

  }

  /**
   * Helper function to get image info.
   *
   * @param $media_field
   *
   * @return array
   */
  private function getImageInfo($media_field) {
    $info = [
      'image' => '',
      'alt' => '',
    ];
    if ($media_field) {
      /* @var \Drupal\media\Entity\Media $image_entity */
      $image_entity = $media_field->entity;
      if ($image_entity) {

        // Get styled image url.
        $alt = NULL;
        $image_field = $image_entity->get('field_media_image');
        if ($image_field) {
          /* @var \Drupal\file\Entity\File $file_entity */
          $file_entity = $image_field->entity;
          if ($file_entity) {
            $uri = $file_entity->getFileUri();
            $info['image'] = ImageStyle::load('medium')->buildUrl($uri);
          }
          if (isset($image_field->getValue()[0]['alt'])) {
            $alt = $image_field->getValue()[0]['alt'];
          }
        }

        // Get alt text if it's not already set.
        if (!$alt) {
          $caption_field = $image_entity->get('field_image_caption');
          if ($caption_field) {
            $alt = (string) $caption_field->value;
          }
        }
        $info['alt'] = (string) $alt;
      }
    }

    return $info;
  }

}
