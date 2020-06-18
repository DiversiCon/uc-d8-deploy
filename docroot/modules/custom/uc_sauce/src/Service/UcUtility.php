<?php

namespace Drupal\uc_sauce\Service;

use Drupal\Core\Database\Connection;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A class providing utility functions used across the application.
 */
class UcUtility {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface $nodeStorage
   */
  protected $nodeStorage;

  /**
   * @var \Drupal\Core\Database\Connection $db
   */
  private $db;

  /**
   * Constructs a new GroupTools object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entityTypeManager) {
    $this->db = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * Generate an array of people based on section and/or person type.
   *
   * @param \Drupal\taxonomy\Entity\Term $section
   * @param array $types
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function getPeople(Term $section = NULL, array $types = NULL) {

    // Build basic query to get people.
    /* @var \Drupal\Core\Entity\Query\QueryInterface $query_people */
    $query_people = $this->nodeStorage->getQuery()
      ->condition('type', 'person')
      ->condition('status', TRUE)
      ->sort('field_name.family')
      ->sort('field_name.given')
      ->sort('field_headline_text');

    // Add section criteria as needed.
    if (!is_null($section)) {
      $query_people->condition('field_section.target_id', $section->id());
    }

    // Add type criteria as needed.
    if (!is_null($types)) {
      $query_people->condition('field_person_type.target_id', $types, 'IN');
    }

    // Get matching entity id's, then nodes.
    $people_ids = $query_people->execute();
    $people = $this->nodeStorage->loadMultiple($people_ids);

    return $people;
  }

  /**
   * Determines if the provided node has video content.
   *
   * @param \Drupal\node\Entity\Node $node
   *
   * @return bool
   */
  public function hasVideo(Node $node) {
    $has_video = FALSE;

    // Only story content type currently supports YouTube video content.
    if ($node->bundle() == 'story') {
      if (!$node->get('field_intro_paragraph')->isEmpty()
        && !$node->get('field_intro_paragraph')->entity->get('field_par_paragraph_single')->isEmpty()
        && $node->get('field_intro_paragraph')->entity->get('field_par_paragraph_single')->entity->getType() == 'video_youtube') {
        $has_video = TRUE;
      }
    }

    return $has_video;
  }

  /**
   * Helper function to get term id by name using lowest id'd match.
   *
   * @param string $name
   * @param string|boolean $vid
   *
   * @return integer $tid
   */
  public function getTermIdByName($name, $vid = FALSE) {
    $table = 'taxonomy_term_field_data';
    $query = $this->db->select($table, 't');
    $query->condition('name', $name);
    if ($vid) {
      $query->condition('vid', $vid);
    }
    $query->addField('t', 'tid');
    $query->orderBy('t.tid');
    $row = $query->execute()->fetch();
    if ($row) {
      if ($row->tid) {
        return $row->tid;
      }
    }
    return FALSE;
  }

  /**
   * Helper function to get faculty name based on ID.
   *
   * @param string $id
   *
   * @return string $name
   */
  public function getFacultyNameById($id) {
    $name = '';

    // Build basic query to get faculty.
    /* @var \Drupal\Core\Entity\Query\QueryInterface $query_faculty */
    $query_faculty = $this->nodeStorage->getQuery()
      ->condition('type', 'faculty')
      ->condition('status', TRUE)
      ->condition('field_external_id', $id);

    // Get matching entity id's, then nodes.
    $faculty_ids = $query_faculty->execute();
    if ($faculty_ids) {
      $faculty = $this->nodeStorage->load(reset($faculty_ids));
      if ($faculty) {
        $name = $faculty->field_last_name->value . ' ' . $faculty->field_first_name->value;
      }
    }

    return $name;
  }

}
