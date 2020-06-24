<?php

namespace Drupal\uc_organization;

use Drupal\user\Entity\User;

/**
 * UserOrganizations class.
 */
class UserOrganizations {

  /**
   * User object.
   *
   * @var bool|\Drupal\user\Entity\User
   */
  protected $user = FALSE;

  /**
   * Allowed organization entities list.
   *
   * @var array|\Drupal\Core\Field\FieldItemListInterface|mixed
   */
  protected $allowedOrganizations = [];

  /**
   * Allowed organization ids list.
   *
   * @var array|mixed
   */
  protected $allowedOrganizationIds = [];

  /**
   * Allowed organization ids list.
   *
   * @var bool
   */
  protected $allOrganizations = FALSE;

  /**
   * Allowed organization ids list.
   *
   * @var bool
   */
  protected $noOrganization = FALSE;

  /**
   * Allowed organization ids list.
   *
   * @var bool
   */
  protected $isAdmin = FALSE;

  /**
   * Load method to load allowed organizations for user.
   *
   * @param int|null $uid
   *   User ID.
   *
   * @return \Drupal\uc_organization\UserOrganizations|bool
   *   User Organizations object.
   */
  public function __construct($uid = NULL) {
    if ($uid) {
      /* @var \Drupal\user\Entity\User $user */
      $user = User::load($uid);
      if ($user) {
        $this->user = $user;

        // Get allowed organizations data.
        if ($user->hasField('field_allowed_organizations')) {
          if ($user->get('field_allowed_organizations')->getValue()) {
            $this->allowedOrganizations = $user->get('field_allowed_organizations');
            $this->allowedOrganizationIds = $this->allowedOrganizations->getValue();
          }
        }

        // All organizations allowed?
        if ($user->hasField('field_all_organizations')) {
          if ($user->get('field_all_organizations')->value) {
            $this->allOrganizations = TRUE;
          }
        }

        // No organization allowed?
        if ($user->hasField('field_no_organization')) {
          if ($user->get('field_no_organization')->value) {
            $this->noOrganization = TRUE;
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
   * Method to get All Organizations permission value for user.
   *
   * @return bool
   *   Boolean indicating whether or not "all organizations" is allowed.
   */
  public function isAllOrganizationsAllowed() {
    return $this->allOrganizations;
  }

  /**
   * Method to get No Organization permission value for user.
   *
   * @return bool
   *   Boolean indicating whether or not "no organization" is allowed.
   */
  public function isNoOrganizationAllowed() {
    return $this->noOrganization;
  }

  /**
   * Method to get array of allowed organization ids.
   *
   * @return array
   *   Organization ID's array.
   */
  public function getAllowedOrganizationIds() {
    $ids = [];
    if ($this->allowedOrganizationIds && is_array($this->allowedOrganizationIds)) {
      foreach ($this->allowedOrganizationIds as $delta => $organization) {
        if (isset($organization['target_id'])) {
          $ids[] = $organization['target_id'];
        }
      }
    }

    return $ids;
  }

  /**
   * Method to get array organization based grant ids.
   *
   * @return array
   *   Grant ID's array.
   */
  public function getOrganizationGids() {

    // Each allowed organization id represents a grant id.
    $gids = $this->getAllowedOrganizationIds();

    // All organizations represents a grant id.
    if ($this->allOrganizations) {
      $gids[] = 0;
    }

    // No organization represents a grant id.
    if ($this->noOrganization) {
      $gids[] = 999999;
    }

    return $gids;
  }

  /**
   * Method to determine if the specified organization(s) are allowed by user.
   *
   * @param array|null $organization_id
   *   Organization tid.
   *
   * @return bool
   *   Boolean indicating whether or not organization is allowed.
   */
  public function isOrganizationAllowed($organization_id = NULL) {

    // If we have a organization and a match then we are good regardless.
    if ($organization_id) {
      $organization[]['target_id'] = $organization_id;
      $matches = array_map("unserialize", array_intersect($this->serialize($organization), $this->serialize($this->allowedOrganizationIds)));
      if ($matches) {
        $allowed = TRUE;
      }
      // If no match but user has all organization permission we are ok.
      else {
        $allowed = $this->allOrganizations;
      }
    }
    // If we have no organization but user has no organization permission we
    // are ok.
    else {
      $allowed = $this->noOrganization;
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
  private function serialize(array $array) {
    foreach ($array as $key => $value) {
      sort($value);
      $array[$key] = serialize($value);
    }
    return $array;
  }

}
