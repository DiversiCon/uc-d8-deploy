<?php

namespace Drupal\uc_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\image\Entity\ImageStyle;

/**
 * Provides a resource to get staff data.
 *
 * @RestResource(
 *   id = "staff_rest_resource",
 *   label = @Translation("Staff resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/staff"
 *   }
 * )
 */
class StaffRestResource extends ResourceBase {

  private $allCategories = ['All'];

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
    $staff = [];
    $categories = [];

    $database = \Drupal::service('database');
    /* @var \Drupal\Core\Database\Query\Select $query */
    $query = $database->select('node_field_data', 'n');
    $query->condition('n.status', 1);
    $query->condition('n.type', 'person');
    $query->addField('n', 'nid');
    $query->addField('n', 'title');
    $query->addJoin('LEFT OUTER', 'draggableviews_structure', 'dv', 'n.nid = dv.entity_id');
    $query->condition('dv.view_name', 'sort_person');
    $query->condition('dv.view_display', 'within_category');
    $query->addJoin('LEFT OUTER', 'node__field_hide_in_directory', 'h', 'n.nid = h.entity_id');
    $query->condition($query->orConditionGroup()
      ->isNull('h.field_hide_in_directory_value')
      ->condition('h.field_hide_in_directory_value', 0, '='));
    $query->addJoin('LEFT OUTER', 'node__field_category_primary', 'c', 'n.nid = c.entity_id');
    $query->addJoin('LEFT OUTER', 'taxonomy_term_field_data', 't', 'c.field_category_primary_target_id = t.tid');
    $query->addJoin('LEFT OUTER', 'taxonomy_term__parent', 'p', 't.tid = p.entity_id');
    $query->addJoin('LEFT OUTER', 'taxonomy_term_field_data', 'pt', 'p.parent_target_id = pt.tid');
    $query->orderBy('pt.weight');
    $query->orderBy('t.weight');
    $query->orderBy('dv.weight');
    $query->orderBy('n.title');

    $results = $query->execute();
    $rows = $results->fetchAll();

    if ($rows) {
      foreach ($rows as $delta => $row) {
        $node = Node::load($row->nid);
        $staff[] = [
          'name' => $row->title,
          'title' => $node->get('field_office_title')->getString(),
          'office' => $this->getTermName($node->get('field_category_primary')->getString()),
          'location' => $node->get('field_office_location')->getString(),
          'phone' => $node->get('field_phone')->getString(),
          'email' => $node->get('field_email_address')->getString(),
          'image' => $this->getImagePath($node->get('field_image_main')),
        ];
      }
    }

    // asort($this->allCategories);.
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
   * Helper method to get term name.
   */
  private function getTermName($tid) {
    if ($tid && !empty($tid)) {
      $term = Term::load($tid);
      $name = $term->getName();
      if ($name && !empty($name)) {
        if (!array_search($name, $this->allCategories)) {
          $this->allCategories[] = $name;
        }
        return $name;
      }
    }
    return '';
  }

  /**
   * Helper method to get image path.
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
            return ImageStyle::load('staff_thumbnail')->buildUrl($uri);
          }
        }
      }
    }
    return '';
  }

}
