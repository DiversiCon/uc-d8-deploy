<?php

namespace Drupal\uc_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\node\Entity\Node;
use Drupal\image\Entity\ImageStyle;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Url;

/**
 * Provides a resource to get staff data.
 *
 * @RestResource(
 *   id = "people_rest_resource",
 *   label = @Translation("People resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/people/grouped/{pid}"
 *   }
 * )
 */
class PeopleRestResource extends ResourceBase {

  private $allCategories = ['All'];

  /**
   * Responds to GET requests.
   *
   * @param null $pid
   *
   * @return \Drupal\rest\ResourceResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function get($pid = NULL) {
    $staff = [];
    $categories = [];

    // Build out filter list, if present.
    $filter_list = \Drupal::request()->get('filter');
    $filters = [];

    // A list of available fields to filter.
    $filterable_fields = [
      'name',
      'title',
      'location',
      'phone',
      'email',
      'image',
      'positions',
    ];

    if (!empty($filter_list)) {
      // Parse the list of filters in the query parameter.
      $filters = explode(',', $filter_list);

      // Filter the field list. Any fields left are the ones that will be
      // from the final output.
      foreach ($filters as $filter) {
        // Since we can only unset based on key, search the field list
        // for that key, then unset.
        if (($key = array_search($filter, $filterable_fields)) !== FALSE) {
          unset($filterable_fields[$key]);
        }
      }
    }
    else {
      // If no filters were passed in, don't filter out anything. Empty the list.
      $filterable_fields = [];
    }

    // Get the specified paragraph.
    if ($pid) {
      $paragraph = Paragraph::load($pid);
      if ($paragraph && $paragraph->hasField('field_par_paragraphs')) {
        $groups = $paragraph->get('field_par_paragraphs')->getValue();
        foreach ($groups as $gdelta => $group) {
          $group_paragraph = Paragraph::load($group['target_id']);
          $group_name = NULL;
          if ($group_paragraph->get('field_group_title')->getValue()) {
            if (isset($group_paragraph->get('field_group_title')->getValue()[0]['value'])) {
              $group_name = $group_paragraph->get('field_group_title')->getValue()[0]['value'];
            }
          }
          if ($group_name && !empty($group_name)) {
            if (!array_search($group_name, $this->allCategories)) {
              $this->allCategories[] = $group_name;
            }
          }
          if ($group_paragraph && $group_paragraph->hasField('field_person_reference_ultd')) {
            $people = $group_paragraph->get('field_person_reference_ultd')->getValue();
            foreach ($people as $ndelta => $person) {
              $node = Node::load($person['target_id']);

              if ($node) {
                // If the person is flagged to not display in a directory.
                if (!$node->get('field_hide_in_directory')->isEmpty() && $node->get('field_hide_in_directory')->getValue()[0]['value']) {
                  // Skip to the next person.
                  continue;
                }

                // Default empty link values.
                $link = NULL;

                // Retrieve link values if available.
                if (!$node->get('field_teaser_link')->isEmpty()) {
                  $link_base = $node->get('field_teaser_link')->getValue()[0];

                  $link = [];
                  if (isset($link_base['options']['attributes']['target'])) {
                    $link['target'] = $link_base['options']['attributes']['target'];
                  }
                  $link['href'] = URL::fromUri($link_base['uri'])->toString(TRUE)->getGeneratedUrl();
                  $link['text'] = $link_base['title'];
                }

                // Retrieve positions and replace any newlines with HTML line breaks.
                $positions = $node->get('field_positions')->getValue()[0]['value'];
                $positions = preg_replace("/\r\n|\r|\n/", '<br/>', $positions);

                $person = [
                  'name' => $node->get('field_headline_text')->getValue()[0]['value'],
                  'title' => $node->get('field_office_title')->getString(),
                  'positions' => $positions,
                  'group' => $group_name,
                  'location' => $node->get('field_office_location')->getString(),
                  'phone' => $node->get('field_phone')->getString(),
                  'email' => $node->get('field_email_address')->getString(),
                  'image' => $this->getImagePath($node->get('field_image_main')),
                  'link' => $link,
                ];

                // Filter out any unwanted values.
                foreach ($filterable_fields as $field) {
                  unset($person[$field]);
                }

                // Add the person to the staff list.
                $staff[] = $person;
              }
            }
          }
        }
      }
    }

    foreach ($this->allCategories as $delta => $category) {
      $categories[] = ['name' => $category];
    }

    $data = [
      'options' => $categories,
      'items' => $staff,
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
  private function getImagePath($media_field) {
    if ($media_field) {
      $image_entity = $media_field->entity;
      if ($image_entity) {
        $image_field = $image_entity->get('field_media_image');
        if ($image_field) {
          $file_entity = $image_field->entity;
          if ($file_entity) {
            $uri = $file_entity->getFileUri();
            return ImageStyle::load('faculty_image')->buildUrl($uri);
          }
        }
      }
    }
    return '';
  }

}
