<?php

namespace Drupal\uc_cdr\Controller;

use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class CentralDataRepoController extends ControllerBase {

  protected $entityTypeManager;

  /**
   * Constructs a new CdrController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   *
   */
  public function getFacultyIndex() {

    $data = [];

    $node_storage = $this->entityTypeManager->getStorage('node');

    $nids = $node_storage->getQuery()
      ->condition('type', 'faculty')
      ->execute();

    $nodes = $node_storage->loadMultiple($nids);

    foreach ($nodes as $n) {
      $departments = [];
      foreach ($n->field_primary_department as $dept) {
        if (isset($dept->entity) && isset($dept->entity->name) && isset($dept->entity->name->value)) {
          $departments[] = $dept->entity->name->value;
        }
      }

      $interests = [];
      foreach ($n->field_research_and_scholarly_int as $int) {
        if (isset($int->entity) && isset($int->entity->name) && isset($int->entity->name->value)) {
          $interests[] = $int->entity->name->value;
        }
      }

      $websites = [];
      foreach ($n->field_websites as $site) {
        $websites[] = [
          'name' => str_replace('ResearchNetworkProfile', 'Research Network Profile', $site->entity->field_website_name->value),
          'url' => $site->entity->field_website_url->value,
        ];
      }

      $photoUrl = NULL;
      if (!$n->get('field_faculty_image')->isEmpty()) {
        if ($n->field_faculty_image->entity) {
          $uri = $n->field_faculty_image->entity->getFileUri();
          $photoUrl = ImageStyle::load('faculty_image')->buildUrl($uri);
        }
      }

      // Query for the research group.
      $group_nid = $node_storage->getQuery()
        ->condition('type', 'group')
        ->condition('field_lead_faculty.value', $n->field_external_id->value)
        ->execute();

      $research_group = NULL;
      if (!empty($group_nid)) {

        // Get the matching node object.
        $group_nid = reset($group_nid);
        $group_node = Node::load($group_nid);
        if ($group_node) {

          // Set link value based on presence of external link value.
          $group_link = $group_node->get('field_link_single')->getValue();
          if ($group_link && isset($group_link[0]['uri'])) {
            $group_url = $group_link[0]['uri'];
          }
          else {
            $group_url = $group_node->toUrl()->toString(TRUE)->getGeneratedUrl();
          }

          // Wrap it up.
          $group_name = $group_node->get('title')->getValue()[0]['value'];
          $research_group = [
            'url' => $group_url,
            'name' => $group_name,
          ];
        }
      }

      $topics = NULL;
      if (!$n->get('field_topics')->isEmpty()) {
        foreach ($n->get('field_topics') as $topic) {
          $topics[] = $topic->entity->get('name')->getValue()[0]['value'];
        }
      }

      $title = '';
      if (isset($n->field_academic_appointment[0]) && is_object($n->field_academic_appointment[0]->entity)) {
        $title = $n->field_academic_appointment[0]->entity->field_title->value;
      }
      else {
        if ($n->hasField('field_administrative_title')) {
          if (!$n->field_administrative_title->isEmpty()) {
            $title = $n->field_administrative_title->value;
          }
        }
      }

      if (!$n->get('field_preferred_name')->isEmpty()) {
        $full_name = $n->field_preferred_name->value;
      }
      else {
        $full_name = $n->title->value;
      }

      $data['data'][] = [
        'id' => $n->uuid(),
        'pathAlias' => $n->toUrl()->toString(TRUE)->getGeneratedUrl(),
        'fullName' => $full_name,
        'firstName' => $n->field_first_name->value,
        'middleName' => $n->field_middle_name->value,
        'lastName' => $n->field_last_name->value,
        'degree' => $n->field_degree->value,
        'title' => $title,
        'photoUrl' => $photoUrl,
        'showPhoto' => ($photoUrl) ? TRUE : FALSE,
        'department' => $departments,
        'section' => (is_object($n->field_primary_section->entity)) ? $n->field_primary_section->entity->name->value : NULL,
        'interests' => $interests,
        'websites' => $websites,
        'topics' => $topics,
        'research_group' => $research_group,
      ];
    }

    $data['#cache'] = [
      'max-age' => 86400,
      'contexts' => [
        'url',
      ],
      'tags' => [
        'api:faculty:index',
        'cdr_related',
        'cdr:faculty_index',
      ],
    ];

    $data['meta']['generated'] = time();

    $response = new CacheableJsonResponse($data, 200);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($data));
    return $response;

  }

  /**
   *
   */
  public function getEventIndex() {

    $data = [];

    $request = \Drupal::request();

    if ($request->headers->get('bsdInstanceId')) {
      $instance_id = $request->headers->get('bsdInstanceId');
    }

    if ($request->query->get('_bsdInstanceId')) {
      $instance_id = $request->query->get('_bsdInstanceId');
      $request->query->remove('_bsdInstanceId');
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $node_query = $node_storage->getQuery()
      ->condition('type', 'event');

    if (!empty($instance_id)) {
      $config = \Drupal::config('uc_cdr.settings');
      $departments = $config->get("department_mapping.$instance_id");

      if (is_array($departments) && count($departments)) {
        foreach ($departments as $dept) {
          $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $dept]);
          foreach ($terms as $term) {
            $tids[] = $term->id();
          }
        }
        $node_query->condition('field_department', $tids, 'IN');
      }
    }

    $node_query->condition('field_event_dates.value', date('Y-m-d'), '>=');
    $node_query->sort('field_event_dates.value', 'ASC');
    $nids = $node_query->execute();

    $nodes = $node_storage->loadMultiple($nids);

    foreach ($nodes as $n) {
      $start_date = date('Y-m-d\TH:i:s', strtotime($n->field_event_dates->value . ' UTC'));
      $end_date = date('Y-m-d\TH:i:s', strtotime($n->field_event_dates->end_value . ' UTC'));
      $data['data'][] = [
        'id' => $n->uuid(),
        'pathAlias' => $n->toUrl()->toString(TRUE)->getGeneratedUrl(),
        'status' => $n->field_event_status->value,
        'title' => $n->title->value,
        'start' => $start_date,
        'end' => $end_date,
        'location' => $n->field_location->value,
        'contact' => $n->field_contact->value,
        'department' => (is_object($n->field_department->entity)) ? $n->field_department->entity->name->value : NULL,
        'description' => $n->field_description->value,
        'imageUrl' => ($n->get('field_event_image')->isEmpty()) ? NULL : file_create_url($n->field_event_image->entity->getFileUri()),
        'isFeatured' => ($n->field_featured->value) ? TRUE : FALSE,
      ];
    }

    $data['#cache'] = [
      'max-age' => 86400,
      'tags' => [
        'cdr_related',
        'node_list',
      ],
      'contexts' => [
        'url',
      ],
    ];

    $data['meta']['generated'] = time();

    $response = new CacheableJsonResponse($data, 200);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($data));
    return $response;

  }

  /**
   *
   */
  public function getFacultyData() {
    $json = file_get_contents('public://faculty_data.json');
    $data = json_decode($json);
    $response = new JsonResponse($data);
    return $response;
  }

  /**
   *
   */
  public function getPublicationData() {
    $json = file_get_contents('public://publication_data.json');
    $data = json_decode($json);
    $response = new JsonResponse($data);
    return $response;
  }

}
