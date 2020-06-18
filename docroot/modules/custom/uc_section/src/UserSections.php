<?php

namespace Drupal\uc_section;

use Drupal\user\Entity\User;

/**
 * UserSections class.
 */
class UserSections {

  protected $user = FALSE;
  protected $allowedSections = [];
  protected $allowedSectionIds = [];
  protected $allSections = FALSE;
  protected $noSection = FALSE;
  protected $isAdmin = FALSE;

  /**
   * Load method to load allowed sections for user.
   *
   * @param int|null $uid
   *   User ID.
   *
   * @return \Drupal\uc_section\UserSections|bool
   *   User Sections object.
   */
  public function __construct($uid = NULL) {
    if ($uid) {
      /* @var \Drupal\user\Entity\User $user */
      $user = User::load($uid);
      if ($user) {
        $this->user = $user;

        // Get allowed sections data.
        if ($user->hasField('field_allowed_sections')) {
          if ($user->get('field_allowed_sections')->getValue()) {
            $this->allowedSections = $user->get('field_allowed_sections');
            $this->allowedSectionIds = $this->allowedSections->getValue();
          }
        }

        // All sections allowed?
        if ($user->hasField('field_all_sections')) {
          if ($user->get('field_all_sections')->value) {
            $this->allSections = TRUE;
          }
        }

        // No section allowed?
        if ($user->hasField('field_no_section')) {
          if ($user->get('field_no_section')->value) {
            $this->noSection = TRUE;
          }
        }

        // Is admin?
        $this->isAdmin = $user->hasRole('administrator');
      }
    }
    return $this;
  }

  /**
   * Method to confirm valid user.
   *
   * @return bool
   *   Boolean indicating whether or not the current user is valid.
   */
  public function isUserValid() {
    return ($this->user) ? TRUE : FALSE;
  }

  /**
   * Method to determine if user is admin.
   *
   * @return bool
   *   Boolean indicating whether or not current user is admin.
   */
  public function isUserAdmin() {
    return $this->isAdmin;
  }

  /**
   * Method to get All Sections permission value for user.
   *
   * @return bool
   *   Boolean indicating whether or not "all sections" is allowed.
   */
  public function isAllSectionsAllowed() {
    return $this->allSections;
  }

  /**
   * Method to get No Section permission value for user.
   *
   * @return bool
   *   Boolean indicating whether or not "no section" is allowed.
   */
  public function isNoSectionAllowed() {
    return $this->noSection;
  }

  /**
   * Method to get array of allowed section ids.
   *
   * @return array
   *   Section ID's array.
   */
  public function getAllowedSectionIds() {
    $ids = [];
    if ($this->allowedSectionIds && is_array($this->allowedSectionIds)) {
      foreach ($this->allowedSectionIds as $delta => $section) {
        if (isset($section['target_id'])) {
          $ids[] = $section['target_id'];
        }
      }
    }

    return $ids;
  }

  /**
   * Method to get array section based grant ids.
   *
   * @return array
   *   Grant ID's array.
   */
  public function getSectionGids() {

    // Each allowed section id represents a grant id.
    $gids = $this->getAllowedSectionIds();

    // All sections represents a grant id.
    if ($this->allSections) {
      $gids[] = 0;
    }

    // No section represents a grant id.
    if ($this->noSection) {
      $gids[] = -1;
    }

    return $gids;
  }

  /**
   * Method to determine if the specified section(s) are allowed by user.
   *
   * @param array|null $section_id
   *   Section tid.
   *
   * @return bool
   *   Boolean indicating whether or not section is allowed.
   */
  public function isSectionAllowed($section_id = NULL) {

    // If we have a section and a match then we are good regardless.
    if ($section_id) {
      $section[]['target_id'] = $section_id;
      $matches = array_map("unserialize", array_intersect($this->serialize($section), $this->serialize($this->allowedSectionIds)));
      if ($matches) {
        $allowed = TRUE;
      }
      // If no match but user has all section permission we are ok.
      else {
        $allowed = $this->allSections;
      }
    }
    // If we have no section but user has no section permission we are ok.
    else {
      $allowed = $this->noSection;
    }

    return $allowed;
  }

  /**
   * Helper method to serialize array values.
   *
   * @param array $array
   *   Source array.
   *
   * @return array
   *   Serialized array.
   */
  private function serialize($array) {
    foreach ($array as $key => $value) {
      sort($value);
      $array[$key] = serialize($value);
    }
    return $array;
  }

}
