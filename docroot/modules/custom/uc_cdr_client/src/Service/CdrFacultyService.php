<?php

namespace Drupal\uc_cdr_client\Service;

use Drupal\Component\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\node\Entity\Node;
use Drupal\config_pages\Entity\ConfigPages;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;

/**
 * Serves as an abstraction layer between the call to the cdr, and the logic
 * using the data. Primarily parses the data from the cdr and prepares it into
 * a usable structure.
 */
class CdrFacultyService {

  /**
   * @var \Drupal\uc_cdr_client\CdrClient
   */

  protected $cdr_client;

  /**
   *
   */
  public function __construct() {
    $this->cdr_client = \Drupal::service('uc_cdr_client.cdr_client');
  }

  /**
   * Generate a render array for a Faculty Profile page.
   *
   * @param $url_alias
   *   The uuid or path alias of the faculty member.
   *
   * @return array
   *   Render array.
   */
  public function renderFacultyMember($url_alias) {
    // Account for the possibility of getting a uuid or an alias.
    $faculty_id = (Uuid::isValid($url_alias)) ? $url_alias : $this->cdr_client->getUuidFromPathAlias($url_alias);
    $faculty_data = $this->getFacultyMember($faculty_id, TRUE);

    // We gotta have data.
    if ($faculty_data == NULL) {
      throw new NotFoundHttpException();
    }

    // Put all the pieces together in a render array and send it along.
    return [
      '#theme' => 'cdr_faculty_profile',
      '#faculty_data' => $faculty_data,
      '#faculty_profile_page' => ['section' => $this->getFacultyProfileSection()],
      '#course_schedule' => $this->getCourseSchedule($faculty_id),
      '#patient_information' => $this->getPatientInformation(),
    ];
  }

  /**
   * Get Faculty Profile NID.
   *
   * @param $url_alias
   *   The uuid or path alias of the faculty member.
   *
   * @return string
   *   Faculty NID.
   */
  public function getFacultyMemberNid($url_alias) {
    // Account for the possibility of getting a uuid or an alias.
    $faculty_id = (Uuid::isValid($url_alias)) ? $url_alias : $this->cdr_client->getUuidFromPathAlias($url_alias);
    $faculty_data = $this->getFacultyMember($faculty_id, TRUE);

    // We gotta have data.
    if ($faculty_data == NULL) {
      throw new NotFoundHttpException();
    }

    // Put all the pieces together in a render array and send it along.
    return $faculty_data['drupal_id'];
  }

  /**
   *
   */
  public function getFacultyMember($faculty_id, $is_external_id = FALSE) {
    $data = $this->cdr_client->getFacultyProfileData($faculty_id, $is_external_id);

    if ($data == NULL) {
      return NULL;
    }

    $attributes = [];
    if (isset($data['data']['attributes']) && $data['data']['attributes']) {
      $attributes = $data['data']['attributes'];
    }
    $relationships = [];
    if (isset($data['data']['relationships']) && $data['data']['relationships']) {
      $relationships = $data['data']['relationships'];
    }
    $included = [];
    if (isset($data['included']) && $data['included']) {
      $included = $data['included'];
    }

    // Photo image.
    $photo_url = '';
    foreach ($included as $datum) {
      if ($datum['id'] == $relationships['field_faculty_image']['data']['id']) {
        if (isset($datum['links']['faculty_image'])) {
          $photo_url = $datum['links']['faculty_image']['href'];
        }
        else {
          if (isset($datum['attributes']['uri']['value'])) {
            $photo_url = $datum['attributes']['uri']['value'];
            if ($photo_url) {
              $style = ImageStyle::load('faculty_image');
              if ($style) {
                $style_url = $style->buildUrl($photo_url);
                if ($style_url) {
                  $photo_url = $style_url;
                }
              }
            }
          }
        }
      }
    }

    // Full name.
    $full_name = '';
    if (isset($attributes['field_preferred_name'])) {
      $full_name = $attributes['field_preferred_name'];
    }
    else {
      if (isset($attributes['title'])) {
        $full_name = $attributes['title'];
      }
    }

    // Academic titles.
    $academic_title_ids = [];
    if (isset($relationships['field_academic_appointment']['data']) && $relationships['field_academic_appointment']['data']) {
      foreach ($relationships['field_academic_appointment']['data'] as $data) {
        $academic_title_ids[] = $data['id'];
      }
    }

    $academic_titles = [];
    foreach ($included as $datum) {
      if (in_array($datum['id'], $academic_title_ids)) {
        $title = [
          'title' => $datum['attributes']['field_title'],
          'department' => $datum['attributes']['field_department_name'],
        ];

        $academic_titles[] = $title;
      }
    }

    // Process clinical interests.
    $clinical_interests_ids = [];
    if (isset($relationships['field_clinical_interests']['data']) && $relationships['field_clinical_interests']['data']) {
      foreach ($relationships['field_clinical_interests']['data'] as $data) {
        $clinical_interests_ids[] = $data['id'];
      }
    }

    $clinical_interests = [];
    foreach ($included as $datum) {
      if (in_array($datum['id'], $clinical_interests_ids)) {
        $clinical_interests[] = $datum['attributes']['name'];
      }
    }

    // Process scholarly interests.
    $scholar_interests_ids = [];
    if (isset($relationships['field_research_and_scholarly_int']['data']) && $relationships['field_research_and_scholarly_int']['data']) {
      foreach ($relationships['field_research_and_scholarly_int']['data'] as $data) {
        $scholar_interests_ids[] = $data['id'];
      }
    }

    $scholar_interests = [];
    foreach ($included as $datum) {
      if (in_array($datum['id'], $scholar_interests_ids)) {
        $scholar_interests[] = $datum['attributes']['name'];
      }
    }

    // Primary section.
    $section_id = '';
    $primary_section = '';
    if (isset($relationships['field_primary_section']['data']['id'])) {
      $section_id = $relationships['field_primary_section']['data']['id'];
    }
    foreach ($included as $datum) {
      if ($datum['id'] == $section_id) {
        $primary_section = $datum['attributes']['name'];
      }
    }

    // Process websites.
    $website_ids = [];
    if (isset($relationships['field_websites']['data']) && $relationships['field_websites']['data']) {
      foreach ($relationships['field_websites']['data'] as $data) {
        $website_ids[] = $data['id'];
      }
    }

    $websites = [];
    foreach ($included as $datum) {
      if (in_array($datum['id'], $website_ids)) {
        $site = [
          'name' => str_replace('ResearchNetworkProfile', 'Research Network Profile', $datum['attributes']['field_website_name']),
          'url' => $datum['attributes']['field_website_url'],
        ];

        $websites[] = $site;
      }
    }

    // Process email.
    $email = '';
    if (isset($attributes['field_email'])) {
      $email = $attributes['field_email'];
    }

    // Graduate program.
    $graduate_program_ids = [];
    if (isset($relationships['field_graduate_program_websites']['data']) && $relationships['field_graduate_program_websites']['data']) {
      foreach ($relationships['field_graduate_program_websites']['data'] as $data) {
        $graduate_program_ids[] = $data['id'];
      }
    }

    $graduate_programs = [];
    foreach ($included as $datum) {
      if (in_array($datum['id'], $graduate_program_ids)) {
        $program = [
          'name' => $datum['attributes']['field_graduate_program_name'],
          'url' => $datum['attributes']['field_graduate_program_url'],
        ];

        $graduate_programs[] = $program;
      }
    }

    // Process office location.
    $office_location = '';
    if (isset($attributes['field_office_location'])) {
      $office_location = $attributes['field_office_location'];

      // Detect and replace any new line returns.
      $office_location = str_replace("\\r\\n", "<br /><br />", $office_location);
      $office_location = str_replace("\\n", "<br />", $office_location);
    }

    // Description/overview.
    $overview = '';
    if (isset($attributes['field_overview'])) {
      $overview = $attributes['field_overview'];

      // Detect and replace any new line returns.
      $overview = str_replace("\\r\\n", "<br /><br />", "$overview");
    }

    // Current research.
    $current_research = '';
    if (isset($attributes['field_description']['value'])) {
      $current_research = $attributes['field_description']['value'];
    }

    // Education and training.
    $education_training_ids = [];
    if (isset($relationships['field_education_and_training']['data']) && $relationships['field_education_and_training']['data']) {
      foreach ($relationships['field_education_and_training']['data'] as $data) {
        $education_training_ids[] = $data['id'];
      }
    }

    $education_training = [];
    foreach ($included as $datum) {
      if (in_array($datum['id'], $education_training_ids)) {
        $item = [
          'institution' => $datum['attributes']['field_institution'],
          'institution_location' => $datum['attributes']['field_location'],
          'major_field' => $datum['attributes']['field_major_field'],
          'degree' => $datum['attributes']['field_degree_earned'],
          'end_date' => $datum['attributes']['field_end_date'],
        ];

        $education_training[] = $item;
      }
    }

    // Publication data.
    $publication_data = $this->cdr_client->getPublications($faculty_id);

    $publications = [];
    $publication_count = 0;
    $has_more_publications = FALSE;

    if ($publication_data != NULL && isset($publication_data['data'])) {
      foreach ($publication_data['data'] as $datum) {
        // Loop through the publications, grabbing up to the first 10.
        // If we hit 10, then there are additional publications to display.
        // Mark the boolean flag as such and break the loop.
        if ($publication_count >= 10) {
          $has_more_publications = TRUE;
          break;
        }

        $publication = [
          'name' => $datum['attributes']['title'],
          'author_list' => $datum['attributes']['field_author_list'],
          'citation_list' => $datum['attributes']['field_citation'],
          'publication' => $datum['attributes']['field_publication'],
          'publication_date' => $datum['attributes']['field_publication_date'],
          'pmid' => $datum['attributes']['field_pmid'],
        ];

        $publications[] = $publication;

        $publication_count++;
      }
    }

    // Parse any awards.
    $award_ids = [];
    if (isset($relationships['field_awards']['data']) && $relationships['field_awards']['data']) {
      foreach ($relationships['field_awards']['data'] as $data) {
        $award_ids[] = $data['id'];
      }
    }

    $awards = [];
    foreach ($included as $datum) {
      if (in_array($datum['id'], $award_ids)) {
        $award = [
          'name' => $datum['attributes']['field_name'],
          'institution' => $datum['attributes']['field_institution'],
          'year' => $datum['attributes']['field_year'],
          'thru_year' => $datum['attributes']['field_thru_year'],
        ];

        $awards[] = $award;
      }
    }

    $news_media_citations = '';
    if (isset($attributes['field_extra_info']['processed'])) {
      $news_media_citations = $attributes['field_extra_info']['processed'];
    }

    $assistant_link = [
      'url' => '',
      'text' => '',
      'target' => '',
    ];
    if (isset($attributes['field_link_single'])) {
      $url = Url::fromUri($attributes['field_link_single']['uri']);
      $target = '';
      if (isset($attributes['field_link_single']['options']['attributes']['target'])) {
        $target = $attributes['field_link_single']['options']['attributes']['target'];
      }

      $assistant_link = [
        'url' => $url,
        'text' => $attributes['field_link_single']['title'],
        'target' => $target,
      ];
    }

    // Retrieve the URL alias.
    $url_alias = '/' . $faculty_id;
    if (isset($attributes['path']['alias'])) {
      $url_alias = $attributes['path']['alias'];
    }

    // Build the faculty data array for the TWIG template.
    $faculty_data = [
      'faculty_id' => $faculty_id,
      'external_id' => $attributes['field_external_id'],
      'drupal_id' => $attributes['drupal_internal__nid'],
      'faculty_title' => $attributes['title'],
      'degree' => $attributes['field_degree'],
      'pathAlias' => $attributes['path']['alias'],
      'name' => $full_name,
      'photo' => $photo_url,
      'show_photo' => $attributes['field_show_photo'],
      'academic_titles' => $academic_titles,
      'section' => $primary_section,
      'title' => $attributes['field_administrative_title'],
      'phone' => $attributes['field_phone'],
      'office_location' => $office_location,
      'assistant' => $assistant_link,
      'clinical_interests' => $clinical_interests,
      'scholar_interests' => $scholar_interests,
      'websites' => $websites,
      'email' => $email,
      'graduate_programs' => $graduate_programs,
      'description' => $overview,
      'current_research' => $current_research,
      'education_training' => $education_training,
      'publications' => $publications,
      'has_more_publications' => $has_more_publications,
      'awards' => $awards,
      'news_media_citations' => $news_media_citations,
      'profile_url' => $attributes['field_physician_profile_url'],
      'url_alias' => $url_alias,
      'changed' => $attributes['changed'],
      'status' => $attributes['status'],
    ];

    return $faculty_data;
  }

  /**
   * Returns any section term provided by the Faculty config page.
   */
  protected function getFacultyProfileSection() {
    $section = NULL;
    $faculty_config = ConfigPages::load('faculty');

    if ($faculty_config != NULL) {
      $section = $faculty_config->get('field_section')->view('section_plain');
    }

    return $section;
  }

  /**
   * Returns faculty profile related patient information provided by
   * the Faculty config page.
   *
   * @return array|null
   */
  protected function getPatientInformation() {
    $patient_information = NULL;
    $show_patient_info = FALSE;

    $faculty_config = ConfigPages::load('faculty');

    if ($faculty_config != NULL) {
      $patient_information = [];

      if (!$faculty_config->get('field_number')->isEmpty()) {
        $patient_information['phone_number'] = $faculty_config->get('field_number')->getValue()[0]['value'];
      }

      // Loop through links and add them to the data structure.
      $links = [];

      foreach ($faculty_config->get('field_cp_links_ultd_1')->getValue() as $link) {
        $links[] = [
          'text' => $link['title'],
          'url' => Url::fromUri($link['uri'])->toString(),
        ];
      }

      $patient_information['links'] = $links;

      if (!$faculty_config->get('field_number')->isEmpty()
        || !$faculty_config->get('field_cp_links_ultd_1')->isEmpty()) {
        $show_patient_info = TRUE;
      }
    }

    $patient_information['show'] = $show_patient_info;

    return $patient_information;
  }

  /**
   * @param array $faculty_ids
   *   Array of faculty ids used to retrieve information.
   *
   * @return array
   */
  public function getFacultyMembers(array $faculty_ids) {

    // Retrieve data for the given faculty ids.
    $data = $this->cdr_client->getFaculty($faculty_ids);
    if ($data) {
      return $this->normalizeMembersData($data);
    }
    else {
      return [];
    }
  }

  /**
   * Retrieve faculty members, searching for their name with a given value.
   *
   * @param $search_text
   *   Text to search for faculty members with.
   *
   * @return array
   */
  public function getFacultyMembersByName($search_text) {
    $data = $this->cdr_client->getFacultyByTitle($search_text);

    return $this->normalizeMembersData($data);
  }

  /**
   * @param int $page
   *   Page number.
   *
   * @return array
   */
  public function getFacultyIds($page) {
    // Retrieve data for the given page.
    $data = $this->cdr_client->getFacultyIdsPaged($page);
    return $data;
  }

  /**
   * Helper function to normalize a dataset when retrieving multiple faculty members.
   *
   * @param array $data
   *
   * @return array
   */
  protected function normalizeMembersData(array $data) {
    // Included data is not specific to any one faculty member.
    $included = [];
    if (isset($data['included']) && $data['included']) {
      $included = $data['included'];
    }

    // Initialized returned data set.
    $faculty_members_data = [];

    foreach ($data['data'] as $faculty_data) {
      $attributes = $faculty_data['attributes'];
      $relationships = $faculty_data['relationships'];

      // Parse the image url.
      $image_url = '';
      foreach ($included as $datum) {
        if ($datum['id'] == $relationships['field_faculty_image']['data']['id']) {
          if (isset($datum['links']['faculty_image'])) {
            $image_url = $datum['links']['faculty_image']['href'];
          }
        }
      }

      // Academic titles.
      $academic_title_ids = [];
      foreach ($relationships['field_academic_appointment']['data'] as $data) {
        $academic_title_ids[] = $data['id'];
      }

      $academic_titles = [];
      foreach ($included as $datum) {
        if (in_array($datum['id'], $academic_title_ids)) {
          $title = [
            'title' => $datum['attributes']['field_title'],
            'department' => $datum['attributes']['field_department_name'],
          ];

          $academic_titles[] = $title;
        }
      }

      // Process websites.
      $website_ids = [];
      foreach ($relationships['field_websites']['data'] as $data) {
        $website_ids[] = $data['id'];
      }

      $websites = [];
      foreach ($included as $datum) {
        if (in_array($datum['id'], $website_ids)) {
          $site = [
            'name' => $datum['attributes']['field_website_name'],
            'url' => $datum['attributes']['field_website_url'],
          ];

          $websites[] = $site;
        }
      }

      // Process url alias.
      $url_alias = '/faculty/' . $faculty_data['id'];
      if (isset($attributes['path']['alias'])) {
        $url_alias = '/faculty' . $attributes['path']['alias'];
      }

      $faculty_members_data[$attributes['field_external_id']] = [
        'id' => $faculty_data['id'],
        'external_id' => $attributes['field_external_id'],
        'first_name' => $attributes['field_first_name'],
        'last_name' => $attributes['field_last_name'],
        'degree' => $attributes['field_degree'],
        'admin_title' => $attributes['field_administrative_title'],
        'image' => $image_url,
        'websites' => $websites,
        'academic_titles' => $academic_titles,
        'url_alias' => $url_alias,
      ];

    }

    return $faculty_members_data;
  }

  /**
   * Retrieve any courses related to the given faculty id.
   *
   * @param $faculty_id
   *   Faculty uuid.
   *
   * @return array|null
   */
  protected function getCourseSchedule($faculty_id) {
    $course_schedule = NULL;
    $year_range = NULL;

    // Search for nodes that have any reference to the supplied faculty id.
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'course')
      ->condition('status', 1)
      ->condition('field_instructors', $faculty_id)
      ->execute();

    // Loop through any matching nodes, retrieving the needed data for
    // the presentation layer.
    if (count($nids) > 0) {
      $course_schedule = [];

      foreach ($nids as $nid) {
        $course_node = Node::load($nid);

        $course_info = [
          'name' => $course_node->get('field_course_name')->getValue()[0]['value'],
        ];

        $course_schedule[] = $course_info;
      }

      // Determine the current school year.
      // If the month is August or later, it is the current year + next year.
      $current_month = date('n');

      if ((int) $current_month >= 8) {
        $year_range = date('Y') . '-' . date('Y', strtotime('+1 year'));
      }
      else {
        $year_range = date('Y', strtotime('-1 year')) . '-' . date('Y');
      }
    }

    return [
      'year_range' => $year_range,
      'courses' => $course_schedule,
      'show' => ($course_schedule != NULL),
    ];
  }

}
