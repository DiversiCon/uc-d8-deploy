<?php

namespace Drupal\uc_sauce\Controller;

use Drupal\Component\Utility\Tags;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\system\Controller\EntityAutocompleteController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomLinkAutocompleteController
 *
 * @package Drupal\uc_sauce\Controller
 *
 * @see \Drupal\uc_sauce\Element\CustomLinkAutocomplete
 * @see \Drupal\uc_sauce\Plugin\Field\FieldWidget\CustomLinkWidget
 */
class CustomLinkAutocompleteController extends EntityAutocompleteController {

  protected $maxResultsPer = 6;

  public function handleAutocomplete(Request $request, $target_type, $selection_handler, $selection_settings_key) {
    // Retrieve the node matches from the parent method, first.
    $node_matches_response = parent::handleAutocomplete($request, $target_type, $selection_handler, $selection_settings_key);

    // Convert the JSON response content string back into an array.
    $node_matches = json_decode($node_matches_response->getContent(), TRUE);

    // Limit the node matches to a max result size.
    array_splice($node_matches, $this->maxResultsPer);

    // Initialize the media matches array.
    $media_matches = [];

    // Follow similar logic present in the parent controller.
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = mb_strtolower(array_pop($typed_string));

      // Retrieve a list of media ids of potential matches.
      $media_ids = \Drupal::entityQuery('media')
        ->condition('bundle', 'document')
        ->condition('name', $typed_string, 'CONTAINS')
        ->range(0, $this->maxResultsPer)
        ->execute();

      // Loop through the entities and convert them into autocomplete output.
      foreach ($media_ids as $media_id) {

        // We want to link to the file associated with the media entity. So,
        // we must load the media entity, build the URL, and include it in
        // the matches result set.
        $media_item = Media::load($media_id);
        $media_file_id = $media_item->get('field_media_file')->getValue()[0]['target_id'];

        // Load the file and get a relative URL of the file.
        $url = parse_url(File::load($media_file_id)->url(), PHP_URL_PATH);
        $url = urldecode($url);

        // Add this to the list of matches.
        $media_matches[] = [
          'value' => $url,
          'label' => $media_item->getName() . ' (Media Document)',
        ];

      }
    }

    // Combine the node matches and media matches.
    $combined_matches = array_merge($node_matches, $media_matches);

    return new JsonResponse($combined_matches);
  }

}
