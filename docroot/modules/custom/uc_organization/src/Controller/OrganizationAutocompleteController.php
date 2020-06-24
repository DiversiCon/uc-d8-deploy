<?php

namespace Drupal\uc_organization\Controller;

use Drupal\system\Controller\EntityAutocompleteController;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\user\Entity\User;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class OrganizationAutocompleteController extends EntityAutocompleteController {

  /**
   * Autocomplete the organization field.  Based on same method in parent class.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object that contains the typed tags.
   * @param string $target_type
   *   The ID of the target entity type.
   * @param string $selection_handler
   *   The plugin ID of the entity reference selection handler.
   * @param string $selection_settings_key
   *   The hashed key of the key/value entry that holds the selection handler
   *   settings.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The matched entity labels as a JSON response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown if the selection settings key is not found in the key/value store
   *   or if it does not match the stored data.
   */
  public function handleAutocomplete(Request $request, $target_type, $selection_handler, $selection_settings_key) {
    $matches = [];
    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = mb_strtolower(array_pop($typed_string));

      // Selection settings are passed in as a hashed key of a serialized array
      // stored in the key/value store.
      $selection_settings = $this->keyValue->get($selection_settings_key, FALSE);
      if ($selection_settings !== FALSE) {
        $selection_settings_hash = Crypt::hmacBase64(serialize($selection_settings) . $target_type . $selection_handler, Settings::getHashSalt());
        if ($selection_settings_hash !== $selection_settings_key) {
          // Disallow access when the selection settings hash does not match the
          // passed-in key.
          throw new AccessDeniedHttpException('Invalid selection settings key.');
        }
      }
      else {
        // Disallow access when the selection settings key is not found in the
        // key/value store.
        throw new AccessDeniedHttpException();
      }

      $matches = $this->matcher->getMatches($target_type, $selection_handler, $selection_settings, $typed_string);
    }

    // UC Custom call to filter by allowed organizations per user.
    $this->filterAllowed($matches);

    return new JsonResponse($matches);
  }

  /**
   * Custom helper function to filter out all matches that are not permitted.
   *
   * Permission is based on the current user's allowed organizations.
   *
   * @param array $matches
   *   Defines allowed organizations.
   */
  private function filterAllowed(array &$matches) {

    // Get current user and allowed organizations.
    $user = User::load($this->currentUser()->id());
    if ($user) {

      // If no allowed organizations, then no restrictions.
      if ($user->get('field_allowed_organizations')->getValue()) {
        $allowed = [];
        foreach ($user->get('field_allowed_organizations') as $delta => $organization) {
          $allowed[] = $organization->entity->getName();
        }
        if ($allowed) {

          // Only keep matches in allowed roles.
          $filtered = [];
          foreach ($matches as $delta => $match) {
            if (in_array($match['label'], $allowed)) {
              $filtered[] = $match;
            }
          }
          $matches = $filtered;
        }
      }
    }
  }

}
