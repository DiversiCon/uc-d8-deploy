<?php

namespace Drupal\uc_cdr_client\Controller;

use Drupal\uc_sauce\Controller\CustomLinkAutocompleteController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @see \Drupal\uc_cdr_client\Plugin\Field\FieldWidget\LinkExternalReferenceWidget
 * @see \Drupal\uc_cdr_client\Element\LinkExternalReferenceAutocomplete
 */
class LinkExternalReferenceAutocompleteController extends CustomLinkAutocompleteController {

  /**
   *
   */
  public function handleAutocomplete(Request $request, $target_type, $selection_handler, $selection_settings_key) {
    // Retrieve the node matches from the parent method, first.
    $entity_matches_response = parent::handleAutocomplete($request, $target_type, $selection_handler, $selection_settings_key);

    // Convert the JSON response content string back into an array.
    $entity_matches = json_decode($entity_matches_response->getContent(), TRUE);

    // Initialize the faculty matches array.
    $faculty_matches = [];

    // Using input typed in by the user, search for faculty members.
    if ($input = $request->query->get('q')) {
      /* @var \Drupal\uc_cdr_client\Service\CdrFacultyService $cdr_faculty_service */
      $cdr_faculty_service = \Drupal::service('uc_cdr_client.cdr_faculty_service');

      $faculty_members_data = $cdr_faculty_service->getFacultyMembersByName($input);

      // Loop through the results, building the array of matches.
      foreach ($faculty_members_data as $member_id => $member_data) {
        $faculty_matches[] = [
          'value' => $member_data['url_alias'],
          'label' => $member_data['first_name'] . ' ' . $member_data['last_name'] . ' (Faculty)',
        ];
      }
    }

    array_splice($faculty_matches, $this->maxResultsPer);

    // Combine the node matches and faculty matches.
    $combined_matches = array_merge($entity_matches, $faculty_matches);

    return new JsonResponse($combined_matches);
  }

}
