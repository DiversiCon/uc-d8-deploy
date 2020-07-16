<?php

namespace Drupal\uc_api\Plugin\rest\resource;

use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
// @codingStandardsIgnoreLine
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides group data as a rest resource.
 *
 * @RestResource(
 *   id = "group_rest_resource",
 *   label = @Translation("Group rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/groups"
 *   }
 * )
 */
class GroupRestResource extends ResourceBase {

  /**
   * Returns a list of group data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   JSON response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function get() {
    // Retrieve the node storage service.
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    /* @var \Drupal\uc_sauce\Service\UcUtility $utility_service */
    $utility_service = \Drupal::service('uc_sauce.uc_utility');

    // Query for the group node ids.
    $node_ids = $node_storage->getQuery()
      ->condition('type', 'group')
      ->sort('field_headline_text')
      ->execute();

    // Load the group nodes.
    $group_nodes = $node_storage->loadMultiple($node_ids);

    // Initialize the groups data array.
    $groups = [];

    // Iterate through the group nodes and convert the data into the new format.
    /* @var \Drupal\node\Entity\Node $group_node */
    foreach ($group_nodes as $group_node) {
      // Get the group name.
      $name = '';
      if (!$group_node->get('field_headline_text')->isEmpty()) {
        $name = $group_node->get('field_headline_text')->getValue()[0]['value'];
      }

      // Sort name.
      $sort_name = $name;
      $faculty = $group_node->field_lead_faculty->value;
      if ($faculty) {
        $faculty_name = $utility_service->getFacultyNameById($faculty);
        if ($faculty_name) {
          $sort_name = $faculty_name;
        }
      }

      // Get the preview description text.
      $description = '';
      if (!$group_node->get('body')->isEmpty()) {
        $description = $group_node->get('body')->getValue()[0]['value'];
      }

      // Determine the image url.
      $image_url = '';
      if (!$group_node->get('field_image_main')->isEmpty()) {
        // Get the image file id.
        $image_file_id = $group_node->get('field_image_main')->entity->get('field_media_image')->getValue()[0]['target_id'];
        $image_file_uri = File::load($image_file_id)->getFileUri();
        $image_url = ImageStyle::load('medium')->buildUrl($image_file_uri);
      }

      // Calculate the number of members in a group.
      $members = 0;
      if (!$group_node->get('field_section')->isEmpty()) {
        // Get the section term.
        /* @var \Drupal\taxonomy\Entity\Term $section_term */
        $section_term = $group_node->get('field_section')->entity;
        $section_term_id = NULL;
        if ($section_term) {
          $section_term_id = $section_term->id();
        }

        // Setup types that need to be included.
        // @todo This is a problem, replace with a settings file.
        $types = [];
        $type_names = [
          'Student',
          'Researcher',
          'Junior Fellow',
          'STAGE/IME Fellow',
        ];
        foreach ($type_names as $term_name) {
          $types[] = $utility_service->getTermIdByName($term_name);
        }

        // Query for persons with the same section term.
        $person_ids = $node_storage->getQuery()
          ->condition('type', 'person')
          ->condition('status', TRUE)
          ->condition('field_section.target_id', $section_term_id)
          ->condition('field_person_type.target_id', $types, 'IN')
          ->execute();

        $members = count($person_ids);
      }

      // Get the related topic terms.
      $topics = [];
      if (!$group_node->get('field_topics')->isEmpty()) {
        foreach ($group_node->get('field_topics') as $topic) {
          $topics[] = $topic->entity->get('name')->getValue()[0]['value'];
        }
      }

      // Make sure we have the correct URL.
      $url_target = '';
      if ($group_node->get('field_link_single')->getValue()) {
        $url = $group_node->get('field_link_single')->getValue()[0]['uri'];
        $url_target = '_blank';
      }
      else {
        $url = $group_node->toUrl('canonical')->toString(TRUE)->getGeneratedUrl();
      }

      // Determine if the group has an associated publication.
      $latest_publication = NULL;
      $publication_node_ids = $node_storage->getQuery()
        ->condition('type', 'publication')
        ->condition('field_associated_people.target_id', $group_node->id())
        ->condition('status', 1)
        ->sort('field_publication_date', 'DESC')
        ->execute();

      if (!empty($publication_node_ids)) {
        $publication_node_id = reset($publication_node_ids);

        /* @var \Drupal\node\Entity\Node $publication_node */
        $publication_node = Node::load($publication_node_id);

        // Get the appropriate publication URL.
        $latest_publication = [
          'name' => $publication_node->get('title')->getValue()[0]['value'],
          'url' => $publication_node->toUrl()->toString(TRUE)->getGeneratedUrl(),
          'url_target' => '',
        ];
        if ($publication_node->get('field_link_single')->getValue()) {
          $latest_publication['url'] = $publication_node->get('field_link_single')->getValue()[0]['uri'];
          $latest_publication['url_target'] = '_blank';
        }
      }

      // Construct the data for the group.
      $group = [
        'name' => $name,
        'sort_name' => $sort_name,
        'url' => $url,
        'url_target' => $url_target,
        'image' => $image_url,
        'members' => $members,
        'description' => $description,
        'topics' => $topics,
        'latest_publication' => $latest_publication,
      ];

      // Add the group to the list of groups.
      array_push($groups, $group);
    }

    // Finally we sort by sort_name.
    usort($groups, function ($item1, $item2) {
      return $item1['sort_name'] <=> $item2['sort_name'];
    });

    // Wrap the group data.
    $response_data = [
      'items' => $groups,
    ];

    // Create the response with the data and any needed cache tags.
    $response = new ResourceResponse($response_data);

    // Build the list of cache tags.
    $cache_tags = \Drupal::entityTypeManager()->getDefinition('node')->getListCacheTags();
    $cache_tags[] = 'api:groups';

    $response->getCacheableMetadata()->addCacheTags($cache_tags);

    return $response;
  }

}
